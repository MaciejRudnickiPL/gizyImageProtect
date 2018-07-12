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
        <?php
        include 'gizySecPic.php';
        echo imageSecure('srcImg/kot2.jpg', 0, 0);
//        echo imageSecure('imgSrc/kot2.jpg', 0, 0);

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
