<?php
ini_set('session.save_path', sys_get_temp_dir());
session_start();
header('Content-Type: image/png');

$width = 100;
$height = 40;
$image = imagecreatetruecolor($width, $height);
$bgColor = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bgColor);

$code = '';
$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
for ($i = 0; $i < 4; $i++) {
    $code .= $chars[rand(0, strlen($chars)-1)];
}
$_SESSION['captcha_code'] = strtolower($code);

$textColor = imagecolorallocate($image, 0, 0, 0);
for ($i = 0; $i < 4; $i++) {
    imagestring($image, 5, 15+$i*20, rand(20,30), $code[$i], $textColor);
}
for ($i=0; $i<5; $i++) {
    $lineColor = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
    imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $lineColor);
}
imagepng($image);
imagedestroy($image);
?>