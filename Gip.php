<?php
/**
 * Created by -=Gizy=- .
 * User: Maciej Rudnicki
 * e-mail: maciejrudnickipl@gmail.com
 * Date: 2018-07-23
 * Time: 16:01
 */

namespace App\Gip;

class GipConfig
{
    public $config = [
        'pettern' => [
            'shapes' => 10,
            'minShapeOfImage' => 7,//maxImageSize/minShapeOfImage image 300 => 300/10 = 30
            'maxShapeOfImage' => 3,//maxImageSize/maxShapeOfImage image 300 => 300/3 = 100
        ],
        'dirImage' => 'picture',// katalog dla zdjęć
    ];
    private static $instance;
    private $alerts = [];

    private function __construct()
    {

    }

    public function addAlert($alert)
    {
        array_push($this->alerts, $alert);
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

    private function __clone()
    {
    }

}

class GipImage
{
    public $widthOrg;
    public $heightOrg;
    public $widthRescale, $heightRescale;
    public $fileName;
    public $fileNameShort;
    public $imageBinary;
    public $imageResized;

    public function __construct($fileName)
    {

        if (file_exists($fileName)) {
            $this->fileName = $fileName;

            $this->imageBinary = imagecreatefromstring(file_get_contents($this->fileName));
            $this->fileNameShort = pathinfo($this->fileName)['filename'];

            $this->widthOrg = imagesx($this->imageBinary);
            $this->heightOrg = imagesy($this->imageBinary);
        } else {
            GipConfig::getInstance()->addAlert('ALERT: No file to protect.');
        }
        return $this;
    }

    /**
     * @param $newWidth
     */
    public function rescaleWidth($newWidth, $oldHeight)
    {

        $newHeight = $this->heightOrg / ($this->widthOrg / $newWidth);
        if ($newHeight > $oldHeight) {
            $newWidth = $this->widthOrg / ($this->widthOrg / $newWidth);
            $newHeight = $this->heightOrg / ($this->widthOrg / $newWidth);
        }


        $imagePettern = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($imagePettern, $this->imageBinary, 0, 0, 0, 0, $newWidth, $newHeight, $this->widthOrg, $this->heightOrg);

        $this->imageResized = $imagePettern;
        $this->heightRescale = imagesy($this->imageResized);
        $this->widthRescale = imagesx($this->imageResized);

    }
}

class GipMask
{
//    public $imageMaskFileName;
    public $imageMaskBinary;
    private $height, $width;

    public function __construct(GipImage $gipImage)
    {
        if ($gipImage) {
            $this->height = $gipImage->heightOrg;
            $this->width = $gipImage->widthOrg;
        }
    }

    public function createMask($colorBack, $colorFront, $shape)
    {
        //create img
        $this->imageMaskBinary = imagecreatetruecolor($this->width, $this->height);

        //wypełnienie całości w zależności od color
        imagefill($this->imageMaskBinary, 0, 0, $colorBack);

        //tworzenie elips
        for ($x = 0; $x < GipConfig::getInstance()->config['pettern']['shapes']; $x++) {
            imagefilledellipse($this->imageMaskBinary, $shape[$x][0], $shape[$x][1], $shape[$x][2], $shape[$x][2], $colorFront);
        }

    }

    public function setSize($height, $width)
    {
        $this->height = $height;
        $this->width = $width;
    }

}

class Gip
{
    public $imageToProtectFileName;
    private $gipImage;
    private $divWidth = null, $divHeight = null;

    public function __construct($imgToProtectFileName)
    {
        if (file_exists($imgToProtectFileName)) {
            $this->imageToProtectFileName = $imgToProtectFileName;

        } else {
            GipConfig::getInstance()->addAlert('ALERT: No file to protect.');
            $this->imageToProtectFileName = null;
        }
    }

    /**
     * @param $newWidth - szerokość lub null jeśli ma być dobrana
     * @param $newHeight - wysokość lub nul jeśli ma być dobrana
     * @param $file - nazwa pliku do zapisu
     * @param GipMask $mask - maska nakładana
     * @param $tc1 - kolor 1
     * @param $tc2 - kolor 2
     * @param GipImage $gipImage - obraz do zabezpieczenia
     */
    public function createProtectImageResize($newWidth, $newHeight, $file, GipMask $mask, $tc1, $tc2, GipImage $gipImage)
    {
        $this->gipImage = $gipImage;

        // usatwienie nowych wymiarów dla obiektu
        $this->divWidth = $newWidth;
        $this->divHeight = $newHeight;

        //określenie jaki kolor jest przezroczysty w masce
        imagecolortransparent($mask->imageMaskBinary, $tc1);

        //połączenie maski i zdjęcia
        imagecopymerge($this->gipImage->imageResized, $mask->imageMaskBinary, 0, 0, 0, 0, $newWidth, $newHeight, 100);

        //ustawienie przezroczystości na zdjęciu wynikowym
        imagecolortransparent($this->gipImage->imageResized, $tc2);

        //zapis do png
        imagepng($this->gipImage->imageResized, GipConfig::getInstance()->config['dirImage'] . '/' . $this->gipImage->fileNameShort . '_' . $file . '.png');

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
     * @param null $divHeight
     */
    public
    function setDivHeight($divHeight)
    {
        $this->divHeight = $divHeight;
    }

    public
    function setSize($width, $height)
    {
        $this->divHeight = $height;
        $this->divWidth = $width;
    }

    /**
     *
     * generowanie miejsc tworzenia kształtów zabezpieczeń
     *
     * @param $withMax - maksymalna szerokość obszaru
     * @param $heightMax - maks wys obszaru
     *
     * @return array
     */
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

    public function showAlerts()
    {
        return GipConfig::getInstance()->showAlerts();
    }


}

