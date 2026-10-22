<?php
// CLI only: metrics are never exposed through a public route.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../models/ChatbotGateway.php';
$day = $argv[1] ?? gmdate('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $day)) { fwrite(STDERR, "Usage: php tools/chatbot-usage.php [YYYY-MM-DD] [log-directory]\n"); exit(1); }
$directory = $argv[2] ?? ChatbotGateway::logDirectory();
$file = $directory . '/' . $day . '.jsonl';
$summary = ['date_utc' => $day, 'file' => $file, 'requests' => 0, 'sources' => [], 'models' => [], 'incomplete_log_lines' => 0];
$handle = @fopen($file, 'rb');
if (!$handle) { echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\nNo metrics file for this date/path.\n"; exit; }
while (($line = fgets($handle)) !== false) {
    $row = json_decode($line, true);
    if (!is_array($row)) { $summary['incomplete_log_lines']++; continue; }
    if (($row['type'] ?? '') === 'result') {
        $summary['requests']++;
        $source = $row['source'] ?? 'unknown';
        $summary['sources'][$source] = ($summary['sources'][$source] ?? 0) + 1;
    } elseif (($row['type'] ?? '') === 'attempt') {
        $model = $row['model'];
        $summary['models'][$model] ??= ['attempts' => 0, 'http_200' => 0, 'without_usage' => 0,
            'prompt_tokens' => 0, 'answer_tokens' => 0, 'thinking_tokens' => 0, 'cached_tokens' => 0, 'reported_total_tokens' => 0];
        $totals = &$summary['models'][$model];
        $totals['attempts']++;
        if (($row['http_status'] ?? 0) === 200) { $totals['http_200']++; }
        if (($row['tokens']['totalTokenCount'] ?? null) === null) { $totals['without_usage']++; }
        foreach (['prompt_tokens' => 'promptTokenCount', 'answer_tokens' => 'candidatesTokenCount',
            'thinking_tokens' => 'thoughtsTokenCount', 'cached_tokens' => 'cachedContentTokenCount', 'reported_total_tokens' => 'totalTokenCount'] as $target => $key) {
            $totals[$target] += $row['tokens'][$key] ?? 0;
        }
        unset($totals);
    }
}
fclose($handle);
$summary['note'] = 'Only provider-reported tokens. Missing usage is unknown, not zero. Cached tokens may overlap prompt tokens; do not add all columns. Not remaining quota or a billing statement.';
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
