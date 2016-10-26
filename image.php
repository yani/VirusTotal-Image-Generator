<?php
/*
 * VirusTotal Image Generator
 * https://github.com/Yanikore/VirusTotal-Image-Generator
 * Version: 1.1.0
 *
 * created by Yani
 * https://github.com/Yanikore
 */

/*
 * Configuration
 */

// VirusTotal API key [REQUIRED]
$virusTotalAPI = '';

// Font
$font = 'res/ProFont.ttf';

// Logo
$logo = 'res/logo.png';

// Cache time in seconds (10800 = 3hrs)
$cacheTime = 10800;

// Cache dir
$cacheDir = 'cache';

// Size
$width  = 600;
$height = 1240; // Height should only be changed if there are new antivir added to VT

/*
 * Actual code
 */

//error_reporting(0);

if (!isset($_GET['q']) || !is_string($_GET['q'])) {
    die();
}
$qRes = $_GET['q'];

header('Pragma: public');
header('Cache-Control: max-age=' . $cacheTime);
header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + (int)$cacheTime));
header('Content-Type: image/png');

// Check if cached image exists
$cachedImage = $cacheDir . DIRECTORY_SEPARATOR . $qRes . '.png';
//echo $cachedImage;
if (file_exists($cachedImage) && (filemtime($cachedImage) > (time() - $cacheTime))) {
    die(readfile($cachedImage));
}

// Make image
$image = imagecreate($width, $height);

// Create
$logoImage  = imagecreatefrompng($logo);
imagealphablending($logoImage, true);
imagesavealpha($logoImage, true);

// Colors
$color['background'] = imagecolorallocate($image, 253, 253, 253);
$color['infobox']    = imagecolorallocate($image, 247, 247, 247);
$color['border']     = imagecolorallocate($image, 210, 210, 210);
$color['black']      = imagecolorallocate($image, 75, 75, 75);
$color['green']      = imagecolorallocate($image, 0, 210, 0);
$color['red']        = imagecolorallocate($image, 210, 0, 0);

// Background and border
imagefill($image, 0, 0, $color['background']);
imagerectangle($image, 0, 0, $width - 1, $height - 1, $color['border']);

// Add logo
$logoX = imagesx($logoImage);
$logoY = imagesy($logoImage);
imagecopy($image, $logoImage, ($width - $logoX) / 2, 15, 0, 0, $logoX, $logoY);

// Query VirusTotal
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.virustotal.com/vtapi/v2/file/report');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'resource=' . $qRes . '&apikey=' . $virusTotalAPI);
$result = curl_exec($ch);
curl_close($ch);

// JSON the result
$json = @json_decode($result, true);

// Check vor a valid result
if (!$json || $json['response_code'] != '1') {
    $bbox = imageftbbox(18, 0, $font, 'File not found.');
    $x = $bbox[0] + (imagesx($image) / 2) - ($bbox[4] / 2) - 5;
    $y = $bbox[1] + (imagesy($image) / 2) - ($bbox[5] / 2) - 5;
    imagefttext($image, 18, 0, $x, $y, $color['black'], $font, 'File not found.');
    imagepng($image);
    imagedestroy($image);
    die();
}

// Create the Infobox
imagefilledrectangle($image, 30, $logoY + 40, $width - 30, $logoY + 130, $color['infobox']);

// Add text to the Infobox
imagefttext($image, 11, 0, 40, $logoY + 60, $color['black'], $font, 'SHA1: ');
imagefttext($image, 11, 0, 140, $logoY + 60, $color['black'], $font, $json['sha1']);
imagefttext($image, 11, 0, 40, $logoY + 80, $color['black'], $font, 'MD5:  ');
imagefttext($image, 11, 0, 140, $logoY + 80, $color['black'], $font, $json['md5']);
imagefttext($image, 11, 0, 40, $logoY + 100, $color['black'], $font, 'Last Scan:  ');
imagefttext($image, 11, 0, 140, $logoY + 100, $color['black'], $font, $json['scan_date']);
imagefttext($image, 11, 0, 40, $logoY + 120, $color['black'], $font, 'Status:  ');

// Colour the scan result
if ($json['positives'] > 0) {
    imagefttext($image, 11, 0, 140, $logoY + 120, $color['red'], $font, $json['positives'] . '/' . $json['total']);
} else {
    imagefttext($image, 11, 0, 140, $logoY + 120, $color['green'], $font, $json['positives'] . '/' . $json['total']);
}

// Alphabetize the scanner names
ksort($json['scans']);

// Loop trough all scans
$scanY = $logoY + 165;
foreach ($json['scans'] as $name => $array) {
    imagefttext($image, 11, 0, 45, $scanY, $color['black'], $font, $name);

    if ($array['detected'] == '1') {
        imagefttext($image, 11, 0, $width / 2, $scanY, $color['red'], $font, $array['result']);
    } else {
        imagefttext($image, 11, 0, $width / 2, $scanY, $color['green'], $font, 'Clean');
    }

    $scanY = $scanY + 18;
}

// Add Yanistamp
$bbox = imageftbbox(11, 0, $font, 'made by Yani');
imagefttext($image, 11, 0, imagesx($image) - $bbox[2] - 5, $height - 5, $color['border'], $font, 'made by Yani');


// Start a buffer to cache the image after outputting it
ob_start(function ($data) {
    global $cachedImage, $cacheDir, $cacheTime;

    // Delete old cached images
    foreach (glob($cacheDir . DIRECTORY_SEPARATOR . "*") as $imageFile) {
        if (file_exists($imageFile) && (filemtime($imageFile) < (time() - $cacheTime))) {
            unlink($imageFile);
        }
    }

    // Save the new image in our cache
    @file_put_contents($cachedImage, $data, LOCK_EX);

    // Return back the image to the main buffer
    return $data;
});

// Output the file, and clear resources
imagepng($image);
imagedestroy($image);

// Flush output buffer and cache the image
ob_end_flush();
