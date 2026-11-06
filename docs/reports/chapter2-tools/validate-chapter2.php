<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$out = dirname(__DIR__) . '/chapter2';
$zip = new ZipArchive();
$draftName = $argv[1] ?? 'BAN_THAO_CHUONG_2_KANTO_KTX_DONG_BO.docx';
if (!preg_match('/^[A-Z0-9_]+\.docx$/D', $draftName)) { throw new RuntimeException('Invalid filename'); }
if ($zip->open($out . '/' . $draftName) !== true) { throw new RuntimeException('Cannot open DOCX'); }
$checked = 0;
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (str_ends_with($name, '.xml') || str_ends_with($name, '.rels')) {
        $dom = new DOMDocument();
        if (!$dom->loadXML($zip->getFromIndex($i), LIBXML_NONET)) { throw new RuntimeException('Invalid XML: ' . $name); }
        $checked++;
    }
}
$dom = new DOMDocument(); $dom->loadXML($zip->getFromName('word/document.xml'));
$xpath = new DOMXPath($dom);
$xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
$xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
$xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
$figures = $xpath->query('//w:drawing')->length;
if ($figures !== 11) { throw new RuntimeException('Expected 11 figures'); }
$md = file_get_contents($out . '/BAN_THAO_CHUONG_2_KANTO_KTX.md');
foreach (['2.1.', '2.2.', '2.3.', '2.4.', '2.5.', '2.6.', '2.7.', '2.8.', '2.9.'] as $section) {
    if (!str_contains($md, '## ' . $section)) { throw new RuntimeException('Missing section ' . $section); }
}
$texts = array_map(fn($node) => $node->textContent, iterator_to_array($xpath->query('//w:t')));
$joined = implode("\n", $texts);
// Every Markdown paragraph and table cell must be present as editable Word text.
foreach (preg_split('/\R/u', $md) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '![')) { continue; }
    if (str_starts_with($line, '|')) {
        $cells = array_map('trim', explode('|', trim($line, '|')));
        if (preg_match('/^:?-+:?$/', str_replace(' ', '', $cells[0]))) { continue; }
        foreach ($cells as $cell) { if (!in_array($cell, $texts, true)) { throw new RuntimeException('Missing Word cell: ' . $cell); } }
    } else {
        $line = preg_replace('/^#{1,3} /', '', $line);
        if (!in_array($line, $texts, true)) { throw new RuntimeException('Missing Word paragraph'); }
    }
}
preg_match_all('/!\[(.*?)\]\((.*?)\)/u', $md, $images, PREG_SET_ORDER);
foreach ($images as $image) {
    if (!is_file($out . '/' . $image[2]) || !getimagesize($out . '/' . $image[2])) { throw new RuntimeException('Invalid image ' . $image[2]); }
}
$relsDom = new DOMDocument(); $relsDom->loadXML($zip->getFromName('word/_rels/document.xml.rels'));
foreach ($relsDom->documentElement->childNodes as $rel) {
    if ($rel instanceof DOMElement && $zip->locateName('word/' . $rel->getAttribute('Target')) === false) { throw new RuntimeException('Missing relationship target'); }
}
$zip->close();
echo "Valid DOCX: $checked XML parts; $figures embedded images; all Markdown paragraphs and table cells match.\n";
echo 'Tables: ' . $xpath->query('//w:tbl')->length . '; headings: ' . $xpath->query('//w:pStyle[@w:val="Heading1" or @w:val="Heading2"]')->length . PHP_EOL;
