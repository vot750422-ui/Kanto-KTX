<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$path = dirname(__DIR__) . '/chapter2/Chuong_2_Kanto_KTX_da_sua.docx';
$zip = new ZipArchive();
if ($zip->open($path) !== true) { throw new RuntimeException('Cannot open approved draft'); }
$dom = new DOMDocument();
$dom->loadXML($zip->getFromName('word/document.xml'), LIBXML_NONET);
$xp = new DOMXPath($dom);
$xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
$xp->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
$xp->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
$active = false;
foreach ($xp->query('/w:document/w:body/*') as $i => $node) {
    $text = '';
    foreach ($xp->query('.//w:t', $node) as $t) { $text .= $t->textContent; }
    $style = $xp->evaluate('string(w:pPr/w:pStyle/@w:val)', $node);
    if (preg_match('/^2\.7(?:\.|\s)/u', trim($text))) { $active = true; }
    if (preg_match('/^2\.8(?:\.|\s)/u', trim($text))) { $active = false; }
    if ($active || preg_match('/^Hình 2\./u', trim($text)) || preg_match('/^2\.8\./u', trim($text))) {
        $embeds = [];
        foreach ($xp->query('.//a:blip/@r:embed', $node) as $b) { $embeds[] = $b->value; }
        echo json_encode(['index'=>$i,'kind'=>$node->localName,'style'=>$style,'text'=>$text,'images'=>$embeds], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
echo 'SECTION: ' . $dom->saveXML($xp->query('/w:document/w:body/w:sectPr')->item(0)) . PHP_EOL;
echo 'IMAGE COUNT: ' . $xp->query('//w:drawing')->length . PHP_EOL;
$zip->close();
