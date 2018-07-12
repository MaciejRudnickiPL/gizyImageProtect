<?php
/**
 * Created by PhpStorm.
 * User: GIzyPL
 * Date: 2018-07-11
 * Time: 10:08
 */


function imageSecure($strImageToProtection, $maxHeight, $maxWitdh)
{
    error_reporting(E_ALL);
    $myImg = '';
    $image_s = imagecreatefromstring(file_get_contents($strImageToProtection));
    $width = imagesx($image_s);
    $height = imagesy($image_s);


    //tworzenie obrazka
    $image = imagecreatetruecolor($width, $height);

    createMask($width, $height);

    //opcja alpha działa
    imagealphablending($image, true);

    imagecopyresampled($image, $image_s, 0, 0, 0, 0, $width, $height, $width, $height);

    // wczytanie maski
    $mask = imagecreatefrompng('img/mask1.png');


    // okreslenie co jest przezrcz
    $transparent = imagecolorallocate($mask, 255, 255, 255);

    imagecolortransparent($mask, $transparent);


    $red = imagecolorallocate($mask, 255, 255, 255);

    imagecopymerge($image_s, $mask, 0, 0, 0, 0, $width, $height, 100);
    imagecolortransparent($image_s, $red);
    imagefill($image_s, 0, 0, $red);


    imagepng($image_s, 'img/2b.png');
    imagedestroy($image);
    imagedestroy($mask);

//--------------------------------------------------------------------------------

    $image_s = imagecreatefromstring(file_get_contents($strImageToProtection));
    $width = imagesx($image_s);
    $height = imagesy($image_s);


    //tworzenie obrazka
    $image = imagecreatetruecolor($width, $height);

    //opcja alpha działa
    imagealphablending($image, true);

    imagecopyresampled($image, $image_s, 0, 0, 0, 0, $width, $height, $width, $height);

    // wczytanie maski
    $mask = imagecreatefrompng('img/mask2.png');

    // okreslenie co jest przezrcz

    imagefilter($mask, IMG_FILTER_NEGATE);
    $transparent = imagecolorallocate($mask, 255, 255, 255);
    imagecolortransparent($mask, $transparent);

    $red = imagecolorallocate($mask, 0, 0, 0);

    imagecopymerge($image_s, $mask, 0, 0, 0, 0, $width, $height, 100);
    imagecolortransparent($image_s, $red);
    imagefill($image_s, 0, 0, $red);

    imagepng($image_s, 'img/1b.png');
    imagedestroy($image);
    imagedestroy($mask);

    $tempHeight = $height + 3;

    $myImg = ' <div class="wrap"><img src="img/1b.png" style="position: relative;left: 0px;top: 0px">
            <img src="img/2b.png" style="position: relative;top: ' . -$tempHeight . 'px;left: 0;">
        </div>';

    return $myImg;

}


function createMask($widthMask, $heightMask, $rectHeight = 40, $rectWidth = 60, $marginHeightY = 20, $marginWidthX = 20, $maskType = 0)
{
    $fileName = 'img/mask1.png';
    $maskGen = imagecreatetruecolor($widthMask, $heightMask);

    $startX = $marginWidthX;
    $startY = $marginHeightY;

    $colorAvers = imagecolorallocate($maskGen, 0, 0, 0);
    $colorRevers = imagecolorallocate($maskGen, 255, 255, 255);

    if ($maskType == 1) {
        imagefill($maskGen, 0, 0, $colorAvers);
        $fileName = 'img/mask2.png';
    }

    $colorNumber = 0;
    while ($startY < $heightMask) {

        if ($colorNumber == 0) {
            $color = $colorAvers;
            $colorNumber = 1;
        } else {
            $color = $colorRevers;
            $colorNumber = 0;
        }

        imagefilledrectangle($maskGen, $startX, $startY, $startX + $rectWidth, $startY + $rectHeight, $color);
        $startX = $startX + $rectWidth + $marginWidthX;

        if ($startX + $rectWidth > $widthMask) {
            $startX = $marginWidthX;
            $startY = $startY + $rectHeight + $marginHeightY;
        }

    }

    imagepng($maskGen, $fileName);
    imagedestroy($maskGen);
    if ($maskType !== 1) {
        createMask($widthMask, $heightMask, $rectHeight, $rectWidth, $marginHeightY, $marginWidthX, $maskType = 1);
    }


    return $maskGen;
}