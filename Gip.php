<?php
/**
 * Created by -=Gizy=- .
 * User: Maciej Rudnicki
 * e-mail: maciejrudnickipl@gmail.com
 * Date: 2018-07-23
 * Time: 16:01
 */

namespace gizyGip;


class GipConfig
{
    private static $instance;

    public $config = [
        'pettern' => [
            'shapes' => 10,
            'minShapeOfImage' => 7,//maxImageSize/minShapeOfImage image 300 => 300/10 = 30
            'maxShapeOfImage' => 3,//maxImageSize/maxShapeOfImage image 300 => 300/3 = 100
        ],
        'dirImage' => 'srcImg',// katalog dla zdjęć
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
    public $image_s;

    public function __construct($fileName)
    {

        if (file_exists($fileName)) {
            $this->fileName = $fileName;

            $this->image_s = imagecreatefromstring(file_get_contents($this->fileName));
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
        $this->width = imagesx($this->image_s);
        $this->height = imagesy($this->image_s);
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
    public $imageMaskGD;
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

    public function createMask($colorBack, $colorFront, $adds, $shape)
    {
        //create img
        GipConfig::getInstance()->addAlert('GipMask->createMask: Start create mask');

        $dirImg = GipConfig::getInstance()->config['dirImage'];
        $maskGen = imagecreatetruecolor($this->width, $this->height);

        //wypełnienie w zależności od color
        imagefill($maskGen, 0, 0, $colorBack);
        for ($x = 0; $x < GipConfig::getInstance()->config['pettern']['shapes']; $x++) {

            imagefilledellipse($maskGen, $shape[$x][0], $shape[$x][1], $shape[$x][2], $shape[$x][2], $colorFront);
        }


        if (is_dir($dirImg)) {
//            imagepng($maskGen, $this->imageMaskFileName);
            if (!file_exists($dirImg . '/mask1.png')) {
                imagepng($maskGen, $dirImg . '/mask1.png');
                $this->imageMaskFileName = $dirImg . '/mask1.png';
            } else {
                imagepng($maskGen, $dirImg . '/mask2.png');
                $this->imageMaskFileName = $dirImg . '/mask2.png';
            }
            GipConfig::getInstance()->addAlert('GipMask->createMask: Save mask as png' . $this->imageMaskFileName);
            $this->imageMaskGD = imagecreatefrompng($this->imageMaskFileName);

        } else {
            GipConfig::getInstance()->addAlert('GipMask->createMask: No dirImage');
        }

        //usunięcie temapa
        imagedestroy($maskGen);
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

    public function __construct($imgToProtect)
    {
        if (file_exists($imgToProtect)) {
            $this->imageToProtect = $imgToProtect;

        } else {
            GipConfig::getInstance()->addAlert('ALERT: No file to protect.');
            $this->imageToProtect = null;
        }
    }

    public function showAlerts()
    {
        return GipConfig::getInstance()->showAlerts();
    }

    public
    function createProtectImgResize($newWidth, $newHeight)
    {

        $shapePost = [];
        if ($this->imageToProtect) {
            if (is_null($newWidth) && is_null($newHeight)) {
                $this->createProtectImg();// TODO: error
            } else {
                for ($pic = 0; $pic < 2; $pic++) {

                    GipConfig::getInstance()->addAlert('Gip->createProtectImg: Start create gip: ' . $pic);

                    $conf = GipConfig::getInstance()->config;

                    $this->gipImage = new GipImage($this->imageToProtect);
                    $size = new GipRescale($this->gipImage, $newWidth, $newHeight);

                    $newWidth = $size->getRescaleWidth();
                    $newHeight = $size->getRescaleHeight();

                    $this->divWidth = $newWidth;
                    $this->divHeight = $newHeight;

                    $this->imageRevers = $conf['dirImage'] . '/' . $this->gipImage->fileNameShort . '_1.png';
                    $this->imageAvers = $conf['dirImage'] . '/' . $this->gipImage->fileNameShort . '_0.png';

                    $image = imagecreatetruecolor($newWidth, $newHeight);
                    $colorAvers = imagecolorallocate($image, 0, 0, 0);//czarny
                    $colorRevers = imagecolorallocate($image, 255, 255, 255);//bialy
                    imagealphablending($image, true);

                    $destination = imagecreatetruecolor($newWidth, $newHeight);

                    imagecopyresampled($destination, $this->gipImage->image_s, 0, 0, 0, 0, $newWidth, $newHeight, $this->gipImage->width, $this->gipImage->height);
                    GipConfig::getInstance()->addAlert('Gip->createProtectImg: resize img to: ' . $newWidth . 'x' . $newHeight . ' px');
                    $this->gipImage->image_s = $destination;

                    $mask = new GipMask($this->gipImage);
                    $mask->setSize($newHeight, $newWidth);


                    if (empty($shapePost)) {
                        $shapePost = $this->shapePosArray();
                    }

                    if ($pic == 0) {
                        $mask->createMask($colorRevers, $colorAvers, 2, $shapePost);//TODO: przenieść adds do configu
                    } elseif ($pic == 1) {
                        $mask->createMask($colorAvers, $colorRevers, 2, $shapePost);
                    }

                    $mask->deleteMask();
                    $transparent = imagecolorallocate($mask->imageMaskGD, 255, 255, 255);
                    imagecolortransparent($mask->imageMaskGD, $transparent);
                    $red = imagecolorallocate($mask->imageMaskGD, 255, 255, 255);
                    imagecopymerge($this->gipImage->image_s, $mask->imageMaskGD, 0, 0, 0, 0, $newWidth, $newHeight, 100);


                    imagecolortransparent($this->gipImage->image_s, $red);
                    imagefill($this->gipImage->image_s, 0, 0, $red);

                    if (imagepng($this->gipImage->image_s, GipConfig::getInstance()->config['dirImage'] . '/' . $this->gipImage->fileNameShort . '_' . $pic . '.png')) {
                        GipConfig::getInstance()->addAlert('Gip->createProtectImg: Save image in png');
                    } else {
                        GipConfig::getInstance()->addAlert('Gip->createProtectImg: Save image in png error');
                    }
                }
            }
        }
    }

    public
    function createProtectImg()
    {
        $shapePost = [];
        if ($this->imageToProtect) {
            for ($pic = 0; $pic < 2; $pic++) {
                GipConfig::getInstance()->addAlert('Gip->createProtectImg: Start create gip: ' . $pic);

                $this->gipImage = new GipImage($this->imageToProtect);

                $this->divWidth = $this->gipImage->height;
                $this->divHeight = $this->gipImage->width;


                $this->imageRevers = GipConfig::getInstance()->config['dirImage'] . '/' . $this->gipImage->fileNameShort . '_1.png';
                $this->imageAvers = GipConfig::getInstance()->config['dirImage'] . '/' . $this->gipImage->fileNameShort . '_0.png';

                $image = imagecreatetruecolor($this->gipImage->width, $this->gipImage->height);
                $colorAvers = imagecolorallocate($image, 0, 0, 0);//czarny
                $colorRevers = imagecolorallocate($image, 255, 255, 255);//bialy
                imagealphablending($image, true);

                imagecopyresampled($this->gipImage->image_s, $this->gipImage->image_s, 0, 0, 0, 0, $this->gipImage->width, $this->gipImage->height, $this->gipImage->width, $this->gipImage->height);
                $mask = new GipMask($this->gipImage);


                if (empty($shapePost)) {
                    $shapePost = $this->shapePosArray();
                }

                if ($pic == 0) {
                    $mask->createMask($colorRevers, $colorAvers, 0, $shapePost);
                } elseif ($pic == 1) {
                    $mask->createMask($colorAvers, $colorRevers, 2, $shapePost);
                }

                $mask->deleteMask();
                $transparent = imagecolorallocate($mask->imageMaskGD, 255, 255, 255);
                imagecolortransparent($mask->imageMaskGD, $transparent);
                $red = imagecolorallocate($mask->imageMaskGD, 255, 255, 255);
                imagecopymerge($this->gipImage->image_s, $mask->imageMaskGD, 0, 0, 0, 0, $this->gipImage->width, $this->gipImage->height, 100);
                imagecolortransparent($this->gipImage->image_s, $red);
                imagefill($this->gipImage->image_s, 0, 0, $red);

                if (imagepng($this->gipImage->image_s, GipConfig::getInstance()->config['dirImage'] . '/' . $this->gipImage->fileNameShort . '_' . $pic . '.png')) {
                    GipConfig::getInstance()->addAlert('Gip->createProtectImg: Save image in png');
                } else {
                    GipConfig::getInstance()->addAlert('Gip->createProtectImg: Save image in png error');
                }
            }
        }

    }

    private function shapePosArray()
    {
        $conf = GipConfig::getInstance()->config;
        $shapePos = [];
        for ($x = 0; $x < $conf['pettern']['shapes']; $x++) {
            $xPos = rand(0, ($this->gipImage->width));
            $yPos = rand(0, $this->gipImage->height);
            $sizePos = rand($this->gipImage->width / $conf['pettern']['minShapeOfImage'], $this->gipImage->width / $conf['pettern']['maxShapeOfImage']);
            array_push($shapePos, array($xPos, $yPos, $sizePos));
        }

        return $shapePos;
    }

    public function getHtmlImgProtect()
    {
        if ($this->imageToProtect) {

            return '<div style="height:' . $this->divHeight . 'px;width:' . $this->divWidth . 'px;background-image:url(' . $this->imageRevers . '),url(' . $this->imageAvers . ');background-repeat: no-repeat"></div>';
        }
    }

    public function setSize($width, $height)
    {
        $this->divHeight = $height;
        $this->divWidth = $width;
    }


}

