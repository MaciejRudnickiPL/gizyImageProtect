<!DOCTYPE html>
<html>

<head>
    <title>gizyProtectImage</title>
    <meta charset="UTF-8">
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <link rel="stylesheet" href="css.css" type="text/css">
</head>

<body>
<header class="heder">

</header>

<main>

    <div class="c">

        <div class="title">TESTOWY OBRAZEK 1 s</div>
        <div class="gipImage">
            <?php
            include 'Gip.php';
            $gip = new \gizyGip\Gip('srcImg/im1.jpg');   //image to protect
            $gip->createProtectImg(); //no resize
            echo $gip->showAlerts();
            echo $gip->getHtmlImgProtect();
            ?>
        </div>

        <div class="title">TESTOWY OBRAZEK 2 zmiana wielkości</div>
        <div class="gipImage">
            <?php
            $gip = new \gizyGip\Gip('srcImg/im2.jpg');   //image to protect
            $gip->createProtectImgResize(500, 200); //resize
            echo $gip->getHtmlImgProtect();
            ?>
        </div>

        <div class="title">TESTOWY OBRAZEK 3 przeskalowanie</div>
        <div class="gipImage">
            <?php
            $gip = new \gizyGip\Gip('srcImg/image3.jpg');   //image to protect
            $gip->createProtectImgResize(400, null); //resize by witdh
            echo $gip->getHtmlImgProtect();
            ?>
        </div>


    </div>


</main>
<footer>
</footer>
</body>

