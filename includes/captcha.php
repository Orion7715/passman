<?php
require_once __DIR__ . '/session.php';

// Generate Random Text
$permitted_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_string = '';
for ($i = 0, $length = strlen($permitted_chars); $i < 6; $i++) {
    $captcha_string .= $permitted_chars[random_int(0, $length - 1)];
}
$_SESSION['captcha_result'] = $captcha_string;

// Creating Image
$image = imagecreatetruecolor(120, 45);
imagealphablending($image, false);
imagesavealpha($image, true);

// Colors
$background_color = imagecolorallocate($image, 26, 26, 26); // نفس لون الخلفية في تصميمك #1a1a1a
$text_color = imagecolorallocate($image, 52, 211, 153);    // لون الزمرد (Emerald-400)
$noise_color = imagecolorallocate($image, 50, 50, 50);

imagefill($image, 0, 0, $background_color);

// Bots Adding Nosie and Random to Stop Bots
for($i=0; $i<100; $i++) {
    imagesetpixel($image, rand(0,120), rand(0,45), $noise_color);
}


imagestring($image, 5, 25, 15, $captcha_string, $text_color);

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
imagepng($image);
imagedestroy($image);
