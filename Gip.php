<?php
/**
 * Created by -=Gizy=- .
 * User: Maciej Rudnicki
 * e-mail: maciejrudnickipl@gmail.c
 * Date: 2018-07-23
 * Time: 16:01
 */

namespace App\Gip;

class GipConfig
{
    private static $instance;

    public $config = [
        'pettern' => [
            'shapes' => 10,
            'minShapeOfImage' => 7,//maxImageSize/minShapeOfImage image 300 => 300/10 = 30
            'maxShapeOfImage' => 3,//maxImageSize/maxShapeOfImage image 300 => 300/3 = 100
        ],
        'dirImage' => 'picture',// katalog dla zdjęć
    ];
    private $alerts = [];

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new GipConfig();
        }
        return self::$instance;
    }

    public function showAlerts()
    {
        $ret = '';
        foreach ($this->alerts as $key => $value) {
            $ret = $ret . $key . ' -> ' . $value . '<br>';
        }
        return $ret;
    }

    public function addAlert($alert)
    {
        array_push($this->alerts, $alert);
    }

    private function __clone()
    {
    }

}

class GipImage
{
    public $width;
    public $height;
    public $fileName;
    public $fileNameShort;
    public $iamgeBinary;

    public function __construct($fileName)
    {

        if (file_exists($fileName)) {
            $this->fileName = $fileName;

            $this->iamgeBinary = imagecreatefromstring(file_get_contents($this->fileName));
            $this->fileNameShort = pathinfo($this->fileName)['filename'];

            $this->getSizeFromImg();
        } else {
            GipConfig::getInstance()->addAlert('ALERT: No file to protect.');
        }
        return $this;
    }

    private
    function getSizeFromImg()
    {
        $this->width = imagesx($this->iamgeBinary);
        $this->height = imagesy($this->iamgeBinary);
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHight($hight)
    {
        $this->width = $hight;

    }

}

class GipMask
{
    public $imageMaskFileName;
    public $imageMaskBinary;
    private $height, $width;

    public function __construct($gipImage)
    {
        if ($gipImage) {
            $this->height = $gipImage->height;
            $this->width = $gipImage->width;
            $this->imageMaskFileName = GipConfig::getInstance()->config['dirImage'] . '/mask_' . $gipImage->fileNameShort . '.png';
        }
    }

    public function setSize($height, $width)
    {
        $this->height = $height;
        $this->width = $width;
    }

    public function createMask($colorBack, $colorFront, $temFileName, $shape)
    {
        //create img
        GipConfig::getInstance()->addAlert('GipMask->createMask: Start create mask');
        $dirImg = GipConfig::getInstance()->config['dirImage'];
        $this->imageMaskBinary = imagecreatetruecolor($this->width, $this->height);

        //wypełnienie w zależności od color
        imagefill($this->imageMaskBinary, 0, 0, $colorBack);
        for ($x = 0; $x < GipConfig::getInstance()->config['pettern']['shapes']; $x++) {
            imagefilledellipse($this->imageMaskBinary, $shape[$x][0], $shape[$x][1], $shape[$x][2], $shape[$x][2], $colorFront);
        }
//        imagepng( $this->imageMaskBinary, $dirImg . '/mask1.png');
//        $this->imageMaskFileName = $dirImg . '/mask1.png';
//        $this->imageMaskBinary = imagecreatefrompng($this->imageMaskFileName);


        //usunięcie temapa
//        imagedestroy($maskGen);
        return $this->imageMaskBinary;

    }


    public function deleteMask()
    {
        unlink($this->imageMaskFileName);
        GipConfig::getInstance()->addAlert('GipMask->deleteMask: Delete temp mask');
    }


}

class GipRescale
{
    private $gipImg;
    private $newHeight, $newWidth;

    public function __construct($gipImage, $width, $height)
    {
        $this->gipImg = $gipImage;

        if (is_null($width) || is_null($height)) {
            if (is_null($width)) {
                $this->newWidth = $this->getWidth($height);
                $this->newHeight = $height;
            }
            if (is_null($height)) {
                $this->newHeight = $this->getHeight($width);
                $this->newWidth = $width;
            }
        } else {
            $this->newWidth = $width;
            $this->newHeight = $height;
        }


    }

    private function getWidth($newHeight)
    {
        return $this->gipImg->width / ($this->gipImg->height / $newHeight);
    }

    private function getHeight($newWidth)
    {
        return $this->gipImg->height / ($this->gipImg->width / $newWidth);
    }


    public
    function getRescaleHeight()
    {
        return $this->newHeight;
    }

    public
    function getRescaleWidth()
    {
        return $this->newWidth;
    }


}

class Gip
{
    public $imageToProtect;
    public $imageRevers;
    public $imageAvers;
    private $gipImage;
    private $divWidth = null, $divHeight = null;

    public function __construct($imgToProtectFileName)
    {
        if (file_exists($imgToProtectFileName)) {
            $this->imageToProtect = $imgToProtectFileName;

        } else {
            GipConfig::getInstance()->addAlert('ALERT: No file to protect.');
            $this->imageToProtect = null;
        }
    }

    public function showAlerts()
    {
        return GipConfig::getInstance()->showAlerts();
    }


    public function createProtectImageResize($newWidth, $newHeight, $file, GipMask $mask, $tc1, $tc2)
    {
        $conf = GipConfig::getInstance()->config;
        $this->gipImage = new GipImage($this->imageToProtect);
        $size = new GipRescale($this->gipImage, $newWidth, $newHeight);

        $newWidth = $size->getRescaleWidth();
        $newHeight = $size->getRescaleHeight();

        // usatwienie nowych wymiarów dla obiektu
        $this->divWidth = $newWidth;
        $this->divHeight = $newHeight;

        $image = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($image, true);

        //zeskalowanie obrazka do nowych wymiarów
        imagecopyresampled(
            $this->gipImage->iamgeBinary,
            $this->gipImage->iamgeBinary,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $this->gipImage->width,
            $this->gipImage->height
        );

        //określenie jaki kolor jest przezroczysty w masce
        imagecolortransparent($mask->imageMaskBinary, $tc1);

        //połączenie maski i zdjęcia
        imagecopymerge($this->gipImage->iamgeBinary, $mask->imageMaskBinary, 0, 0, 0, 0, $newWidth, $newHeight, 100);

        //ustawienie przezroczystości na zdjęciu wynikowym
        imagecolortransparent($this->gipImage->iamgeBinary, $tc2);

        //zapis do png
        imagepng($this->gipImage->iamgeBinary, GipConfig::getInstance()->config['dirImage'] . '/' . $this->gipImage->fileNameShort . '_' . $file . '.png');

    }

    public
    function shapePositionArray($withMax, $heightMax)
    {
        $conf = GipConfig::getInstance()->config;
        $shapePos = [];
        for ($x = 0; $x < $conf['pettern']['shapes']; $x++) {
            $xPos = rand(0, $withMax);
            $yPos = rand(0, $heightMax);
            $sizePos = rand($withMax / $conf['pettern']['minShapeOfImage'], $withMax / $conf['pettern']['maxShapeOfImage']);
            array_push($shapePos, array($xPos, $yPos, $sizePos));
        }

        return $shapePos;
    }


    public
    function setSize($width, $height)
    {
        $this->divHeight = $height;
        $this->divWidth = $width;
    }

    /**
     * @return null
     */
    public
    function getDivWidth()
    {
        return $this->divWidth;
    }

    /**
     * @param null $divWidth
     */
    public
    function setDivWidth($divWidth)
    {
        $this->divWidth = $divWidth;
    }

    /**
     * @return null
     */
    public
    function getDivHeight()
    {
        return $this->divHeight;
    }

    /**
     * @param null $divHeight
     */
    public
    function setDivHeight($divHeight)
    {
        $this->divHeight = $divHeight;
    }


}

