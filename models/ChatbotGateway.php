<?php

/** Only provider failures are eligible for fallback; refusals never are. */
class ChatbotProviderError extends RuntimeException
{
    public function __construct(public int $status, public int $curlError = 0, public ?float $retryAfter = null)
    {
        parent::__construct('AI provider unavailable');
    }
    public function transient(): bool
    {
        return in_array($this->status, [408, 429, 500, 502, 503, 504], true)
            || in_array($this->curlError, [6, 7, 18, 28, 52, 55, 56], true);
    }
}

class ChatbotGateway
{
    public array $attempts = [];
    public string $id;

    public function __construct(private ?Closure $transport = null, private ?Closure $logger = null, private ?Closure $sleep = null)
    {
        $this->id = bin2hex(random_bytes(8));
    }

    public static function logDirectory(): string
    {
        // Stable across Apache's service account and the developer's CLI account.
        $base = PHP_OS_FAMILY === 'Windows' ? (getenv('ProgramData') ?: 'C:/ProgramData') : sys_get_temp_dir();
        return rtrim($base, '/\\') . '/KantoKTX/chatbot-metrics';
    }

    public function record(array $row): void
    {
        $row = ['at' => gmdate(DATE_ATOM), 'request_id' => $this->id] + $row;
        if ($this->logger) { ($this->logger)($row); return; }
        // Mock tests must not be mixed into real quota figures.
        if ($this->transport) { return; }
        $dir = self::logDirectory();
        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            error_log('UC13 metrics directory unavailable'); return;
        }
        $file = $dir . '/' . gmdate('Y-m-d') . '.jsonl';
        if (@file_put_contents($file, json_encode($row, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX) === false) {
            error_log('UC13 metrics write failed');
        }
    }

    public function request(array $payload, string $primary): array
    {
        $this->attempts = [];
        $backup = trim(getenv('GEMINI_FALLBACK_MODEL') ?: 'gemini-3.5-flash-lite');
        foreach ([$primary, $backup] as $model) {
            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]{0,99}$/D', $model)) {
                throw new ChatbotProviderError(400);
            }
        }
        $models = array_values(array_unique([$primary, $backup]));
        $deadline = microtime(true) + 28;
        $last = new ChatbotProviderError(503);
        // Initial model, backup model, then at most one delayed retry.
        for ($i = 0; $i < 3; $i++) {
            $model = $models[min($i, count($models) - 1)];
            if ($i === 2 || ($i > 0 && count($models) === 1)) {
                $delay = max(1 + random_int(0, 250) / 1000, $last->retryAfter ?? 0);
                if ($delay + 0.1 >= $deadline - microtime(true)) { break; }
                if ($this->sleep) { ($this->sleep)($delay); } else { usleep((int)($delay * 1000000)); }
            }
            $remaining = $deadline - microtime(true);
            if ($remaining < 0.1) { break; }
            $started = microtime(true);
            $row = ['type' => 'attempt', 'attempt' => $i + 1, 'model' => $model,
                'http_status' => 0, 'curl_errno' => 0, 'tokens' => null];
            try {
                $response = $this->transport
                    ? ($this->transport)($payload, $model, min(9, $remaining))
                    : $this->send($payload, $model, min(9, $remaining));
                $row['http_status'] = 200;
                $usage = $response['usageMetadata'] ?? null;
                if (is_array($usage)) {
                    $row['tokens'] = [];
                    foreach (['promptTokenCount', 'candidatesTokenCount', 'thoughtsTokenCount', 'cachedContentTokenCount', 'totalTokenCount'] as $field) {
                        $row['tokens'][$field] = isset($usage[$field]) && is_int($usage[$field]) && $usage[$field] >= 0 ? $usage[$field] : null;
                    }
                }
                return $response;
            } catch (ChatbotProviderError $error) {
                $last = $error;
                $row['http_status'] = $error->status;
                $row['curl_errno'] = $error->curlError;
                if (!$error->transient()) { throw $error; }
                // A 429 can be shared by models; respect server cooldown before another call.
                if ($error->status === 429) {
                    $delay = max(1.0, $error->retryAfter ?? 1.0);
                    if ($delay + 0.1 >= $deadline - microtime(true)) { break; }
                    if ($this->sleep) { ($this->sleep)($delay); } else { usleep((int)($delay * 1000000)); }
                }
            } finally {
                $row['elapsed_ms'] = (int)round((microtime(true) - $started) * 1000);
                $this->attempts[] = $row;
                $this->record($row);
            }
        }
        throw $last;
    }

    private function send(array $payload, string $model, float $timeout): array
    {
        $key = getenv('GEMINI_API_KEY');
        if (!$key || preg_match('/[\r\n]/', $key) || !function_exists('curl_init')) {
            throw new ChatbotProviderError(401);
        }
        $retryAfter = null;
        $curl = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . $key],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => min(3000, (int)($timeout * 1000)),
            CURLOPT_TIMEOUT_MS => max(1, (int)($timeout * 1000)),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$retryAfter): int {
                if (stripos($line, 'Retry-After:') === 0) {
                    $value = trim(substr($line, 12));
                    $retryAfter = is_numeric($value) ? max(0, (float)$value) : max(0, (strtotime($value) ?: time()) - time());
                }
                return strlen($line);
            },
        ]);
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errno = curl_errno($curl);
        curl_close($curl);
        $result = is_string($body) ? json_decode($body, true) : null;
        if ($status !== 200 || !is_array($result)) {
            foreach ($result['error']['details'] ?? [] as $detail) {
                if (isset($detail['retryDelay']) && preg_match('/^(\d+(?:\.\d+)?)s$/', $detail['retryDelay'], $match)) {
                    $retryAfter = max($retryAfter ?? 0, (float)$match[1]);
                }
            }
            throw new ChatbotProviderError($status, $errno, $retryAfter);
        }
        return $result;
    }
}
