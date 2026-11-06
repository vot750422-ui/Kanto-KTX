<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 3);
$files = glob($root . '/docs/05-ui/wireframes/*.png');
$font = 'C:/Windows/Fonts/arial.ttf';
foreach (array_chunk($files, 6) as $part => $group) {
    $im = imagecreatetruecolor(1600, 1800);
    imagefill($im, 0, 0, imagecolorallocate($im, 245, 247, 250));
    $ink = imagecolorallocate($im, 15, 30, 50);
    foreach ($group as $i => $file) {
        $x = ($i % 2) * 800 + 20; $y = intdiv($i, 2) * 600 + 15;
        $label = basename($file);
        foreach (str_split($label, 47) as $line => $text) { imagettftext($im, 13, 0, $x, $y + 20 + $line * 22, $ink, $font, $text); }
        $src = imagecreatefrompng($file); $w = imagesx($src); $h = imagesy($src);
        $scale = min(760/$w, 510/$h);
        imagecopyresampled($im, $src, $x, $y+70, 0, 0, (int)($w*$scale), (int)($h*$scale), $w, $h);
        imagedestroy($src);
    }
    imagepng($im, __DIR__ . '/working/wireframe-sheet-' . ($part + 1) . '.png');
    imagedestroy($im);
}
echo count($files) . ' original PNG wireframes inspected in contact sheets.' . PHP_EOL;
