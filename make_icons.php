<?php
$src = imagecreatefromjpeg('public/favicon-src.jpg');
$w = imagesx($src);
$h = imagesy($src);

function makeCircleIcon($src, $w, $h, $size, $outFile) {
    $dst = imagecreatetruecolor($size, $size);
    imagesavealpha($dst, true);
    $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $trans);
    
    $tmp = imagecreatetruecolor($size, $size);
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $size, $size, $w, $h);
    
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $cx = $size/2;
            $cy = $size/2;
            if (pow($x - $cx, 2) + pow($y - $cy, 2) <= pow($size/2, 2)) {
                $c = imagecolorat($tmp, $x, $y);
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;
                imagesetpixel($dst, $x, $y, imagecolorallocate($dst, $r, $g, $b));
            }
        }
    }
    imagedestroy($tmp);
    imagepng($dst, $outFile);
    imagedestroy($dst);
}

makeCircleIcon($src, $w, $h, 512, 'public/icons/icon-512.png');
makeCircleIcon($src, $w, $h, 192, 'public/icons/icon-192.png');
makeCircleIcon($src, $w, $h, 180, 'public/apple-touch-icon.png');
makeCircleIcon($src, $w, $h, 64,  'public/favicon.png');
makeCircleIcon($src, $w, $h, 32,  'public/favicon-32x32.png');
makeCircleIcon($src, $w, $h, 16,  'public/favicon-16x16.png');
// Also put PWA icons at public root so manifest.json can reference them
makeCircleIcon($src, $w, $h, 192, 'public/icon-192.png');
makeCircleIcon($src, $w, $h, 512, 'public/icon-512.png');

imagedestroy($src);
echo 'All icons generated OK' . PHP_EOL;
