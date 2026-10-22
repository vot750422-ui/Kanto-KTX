<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../models/Chatbot.php';
$failed = 0; $total = 0;
function verify(bool $pass, string $name): void {
    global $failed, $total; $total++; if (!$pass) { $failed++; }
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $name . "\n";
}
$previousBackup = getenv('GEMINI_FALLBACK_MODEL');
putenv('GEMINI_FALLBACK_MODEL=backup-model');
$roomCalls = 0;
$bot = new Chatbot(fn() => ['candidates' => [['finishReason' => 'STOP', 'content' => ['role' => 'model', 'parts' => [['text' => '{"in_scope":true,"answer":"Mở mục Đăng ký."}']]]]]], function () use (&$roomCalls) { $roomCalls++; return []; });
$bot->answer('quy trình đăng ký lưu trú');
verify($roomCalls === 0, 'General guidance omits room snapshot to reduce input tokens');
$bot->answer('Phòng A101 còn chỗ không?');
verify($roomCalls === 1, 'Room questions still receive fresh public room snapshot');
$calls = []; $rows = []; $sleeps = [];
$good = ['usageMetadata' => ['promptTokenCount' => 120, 'candidatesTokenCount' => 30, 'thoughtsTokenCount' => 10, 'totalTokenCount' => 160]];
$gateway = new ChatbotGateway(function ($payload, $model) use (&$calls, $good) {
    $calls[] = $model; if (count($calls) < 3) { throw new ChatbotProviderError(503); } return $good;
}, function ($row) use (&$rows) { $rows[] = $row; }, function ($seconds) use (&$sleeps) { $sleeps[] = $seconds; });
verify($gateway->request(['sensitive' => 'secret-question'], 'primary-model') === $good && $calls === ['primary-model', 'backup-model', 'backup-model'], 'Primary then backup then one retry');
verify(count($sleeps) === 1 && $sleeps[0] >= 1, 'Retry has bounded backoff');
verify(count($rows) === 3 && $rows[2]['tokens']['totalTokenCount'] === 160, 'Record usage of successful attempt');
verify($rows[0]['tokens'] === null && $rows[1]['tokens'] === null, 'Failed attempt usage stays unknown');
verify(!str_contains(json_encode($rows), 'secret-question'), 'Metrics do not contain payload');
foreach ([400, 401, 403, 404] as $status) {
    $calls = 0;
    $g = new ChatbotGateway(function () use (&$calls, $status) { $calls++; throw new ChatbotProviderError($status); });
    try { $g->request([], 'primary-model'); } catch (ChatbotProviderError $e) {}
    verify($calls === 1, 'No retry for permanent error ' . $status);
}
$calls = 0;
$g = new ChatbotGateway(function () use (&$calls) { $calls++; throw new ChatbotProviderError(429, 0, 86400); });
try { $g->request([], 'primary-model'); } catch (ChatbotProviderError $e) {}
verify($calls === 1, 'Daily/long quota cooldown does not cause repeated calls');
$calls = 0; $sleeps = [];
$g = new ChatbotGateway(function () use (&$calls, $good) { $calls++; if ($calls === 1) { throw new ChatbotProviderError(429, 0, 2); } return $good; }, null, function ($s) use (&$sleeps) { $sleeps[] = $s; });
$g->request([], 'primary-model');
verify($calls === 2 && $sleeps === [2.0], 'Respect quota retry delay before switching');
$calls = 0;
$bot = new Chatbot(function () use (&$calls) { $calls++; throw new ChatbotProviderError(503); });
$answer = $bot->answer('quy trình đăng ký lưu trú');
verify($calls === 3 && str_contains($answer, 'Hướng dẫn có sẵn') && str_contains($answer, 'Chờ duyệt'), 'Curated answer only after attempts exhausted');
verify($bot->metrics()['source'] === 'static' && $bot->metrics()['attempts_without_usage'] === 3, 'Static fallback identifies source and unknown usage');
$calls = 0;
$bot = new Chatbot(function () use (&$calls) { $calls++; throw new ChatbotProviderError(401); });
$answer = $bot->answer('cách đăng nhập');
verify($calls === 1 && $bot->metrics()['source'] === 'static', 'Configuration failure goes directly to curated guidance');
$calls = 0;
$bot = new Chatbot(function () use (&$calls) { $calls++; throw new ChatbotProviderError(503); });
verify($bot->answer('KTX MSSV 12345678') === Chatbot::OUT_OF_SCOPE && $calls === 0, 'Sensitive question never reaches API or curated fallback');
try { $bot->answer('Phòng A101 còn bao nhiêu chỗ?'); verify(false, 'No invented live room numbers'); }
catch (RuntimeException $e) { verify($bot->metrics()['source'] === 'error', 'No invented live room numbers'); }
$bot = new Chatbot(fn() => ['usageMetadata' => $good['usageMetadata'], 'candidates' => [['finishReason' => 'STOP', 'content' => ['role' => 'model', 'parts' => [['text' => '{"in_scope":false,"answer":""}']]]]]]);
verify($bot->answer('thời tiết') === Chatbot::OUT_OF_SCOPE && $bot->metrics()['attempts'] === 1 && $bot->metrics()['reported_total_tokens'] === 160, 'AI refusal stops chain and records consumed tokens');
putenv($previousBackup === false ? 'GEMINI_FALLBACK_MODEL' : 'GEMINI_FALLBACK_MODEL=' . $previousBackup);
echo ($total - $failed) . "/$total tests passed\n";
exit($failed ? 1 : 0);
