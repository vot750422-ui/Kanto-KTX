<?php
// Chỉ đọc tài liệu nguồn; không kết nối hoặc sửa dữ liệu nghiệp vụ.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 3);
$out = __DIR__ . '/working';
if (!is_dir($out)) { mkdir($out, 0777, true); }
foreach (glob($root . '/docs/sources/*.docx') as $file) {
    $zip = new ZipArchive();
    $zip->open($file);
    $xml = new DOMDocument();
    $xml->loadXML($zip->getFromName('word/document.xml'));
    $xpath = new DOMXPath($xml);
    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
    $lines = [];
    foreach ($xpath->query('//w:body//w:p') as $p) {
        $text = '';
        foreach ($xpath->query('.//w:t', $p) as $t) { $text .= $t->textContent; }
        if ($text !== '') { $lines[] = $text; }
    }
    // Không xuất báo cáo học phần cũ có thể có thông tin cá nhân; chỉ đọc cấu trúc chương.
    if (str_contains(basename($file), 'BÁO CÁO')) {
        $lines = array_values(array_filter($lines, fn($s) => preg_match('/^(CHƯƠNG|[1-9]\.[0-9](?:\.[0-9])?\.?\s)/u', trim($s))));
    }
    file_put_contents($out . '/' . pathinfo($file, PATHINFO_FILENAME) . '.txt', implode("\n", $lines));
    $zip->close();
}
$pdf = file_get_contents($root . '/docs/sources/DA1 KTPM2026-Hướng dẫn chung.pdf');
preg_match_all('/(\d+)\s+\d+\s+obj\b(.*?)endobj/s', $pdf, $matches, PREG_SET_ORDER);
$objects = [];
foreach ($matches as $m) { $objects[(int)$m[1]] = $m[2]; }
$decodeStream = function(string $object): string {
    if (!preg_match('/stream\r?\n(.*?)\r?\nendstream/s', $object, $s)) { return ''; }
    return str_contains($object, '/FlateDecode') ? (@gzuncompress($s[1]) ?: '') : $s[1];
};
$fontMaps = [];
foreach ($objects as $id => $object) {
    if (!preg_match('/\/ToUnicode\s+(\d+)\s+0\s+R/', $object, $u)) { continue; }
    $cmap = $decodeStream($objects[(int)$u[1]]);
    $map = [];
    preg_match_all('/beginbfchar(.*?)endbfchar/s', $cmap, $chars);
    foreach ($chars[1] as $charBlock) {
        preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $charBlock, $pairs, PREG_SET_ORDER);
        foreach ($pairs as $pair) { $map[strtoupper($pair[1])] = iconv('UTF-16BE', 'UTF-8', hex2bin($pair[2])); }
    }
    preg_match_all('/beginbfrange(.*?)endbfrange/s', $cmap, $ranges);
    foreach ($ranges[1] as $block) {
        preg_match_all('/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $block, $triples, PREG_SET_ORDER);
        foreach ($triples as $t) {
            for ($k=hexdec($t[1]); $k<=hexdec($t[2]); $k++) {
                $key = strtoupper(str_pad(dechex($k), strlen($t[1]), '0', STR_PAD_LEFT));
                $dest = str_pad(dechex(hexdec($t[3]) + $k - hexdec($t[1])), strlen($t[3]), '0', STR_PAD_LEFT);
                $map[$key] = iconv('UTF-16BE', 'UTF-8', hex2bin($dest));
            }
        }
    }
    $fontMaps[$id] = $map;
}
$texts = [];
foreach ($objects as $object) {
    if (!preg_match('/\/Type\s*\/Page\b/', $object)) { continue; }
    preg_match_all('/\/(F\d+)\s+(\d+)\s+0\s+R/', $object, $resources, PREG_SET_ORDER);
    $fonts = [];
    foreach ($resources as $resource) { $fonts[$resource[1]] = $fontMaps[(int)$resource[2]] ?? []; }
    if (!preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $object, $content)) { continue; }
    $stream = $decodeStream($objects[(int)$content[1]]);
    preg_match_all('/\/(F\d+)\s+[\d.]+\s+Tf|<([0-9A-Fa-f]+)>\s*Tj|\[(.*?)\]\s*TJ|\bET\b/s', $stream, $tokens, PREG_SET_ORDER);
    $current = [];
    $page = '';
    foreach ($tokens as $token) {
        if (!empty($token[1])) { $current = $fonts[$token[1]] ?? []; continue; }
        if ($token[0] === 'ET') { $page .= "\n"; continue; }
        $hexes = [];
        if (!empty($token[2])) { $hexes = [$token[2]]; }
        elseif (isset($token[3])) { preg_match_all('/<([0-9A-Fa-f]+)>/', $token[3], $h); $hexes = $h[1]; }
        foreach ($hexes as $hex) {
            foreach (str_split(strtoupper($hex), 4) as $key) { $page .= $current[$key] ?? ''; }
        }
    }
    $texts[] = $page;
}
file_put_contents($out . '/huong-dan-do-an.txt', implode("\n--- PAGE ---\n", $texts));
echo 'Đã trích xuất tài liệu vào thư mục working; không sửa tài liệu nguồn.' . PHP_EOL;
