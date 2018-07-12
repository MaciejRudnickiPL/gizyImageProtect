<!DOCTYPE html>
<html>

<head>
    <title>gizyHelpBoard</title>
    <meta charset="UTF-8">
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <link rel="stylesheet" href="css.css" type="text/css">
    <!--    <script type="text/javascript" src="myHelpBoard/gizyBoard.js"></script>-->
</head>

<body>
<header class="heder">

</header>

<main>

    <!--        <div class="c">-->
    <!---->
    <!--            <div>-->
    <!--                <img class="cf" src="img/2b.png" style="position: relative;">-->
    <!--                <img src="img/1b.png" style="position: relative;top: -200px;left: 34px;">-->
    <!--            </div>-->
    <!--        </div>-->

    <div class="c">
        <?php
        include 'gizySecPic.php';
        echo imageSecure('kot4.jpg', 0, 0);
        echo imageSecure('kot2.jpg', 0, 0);

        //        echo imageSecure('1.jpg', 0, 0);
        //        echo imageSecure('2.jpg', 0, 0);
        //        echo imageSecure('3.jpg', 0, 0);
        //        echo imageSecure('4.jpg', 0, 0);
        ?>
    </div>


</main>


<footer>


</footer>
</body>


<script type="application/ecmascript">

    var board = new HelpBoard('myBoard');
    var info = new HelpInfo('Info');
    board.add(info);

</script>
