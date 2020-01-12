<?php

use classes\Canvas;


/**
 * @var Canvas $canvas
 */

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Корягин</title>
    <link rel="stylesheet" type="text/css" href="/styles.css">
</head>
<body>

<?php
$canvasHeight = $canvas->getHeight();
$canvasWidth = $canvas->getWidth();
?>

<section>
  <?php for ($y = 1; $y <= $canvasHeight; $y++): ?>
      <p>
        <?php for ($x = 1; $x <= $canvasWidth; $x++): ?>
          <?php $pixelPosition = ['x' => $x, 'y' => $y]; ?>
          <?php $color = $canvas->getPixelColors($pixelPosition); ?>
            <span style="color: rgb(<?= $color ?>);">•</span>
        <?php endfor; ?>
      </p>
  <?php endfor; ?>
</section>

<script type="text/javascript" src="./javascript.js"></script>
</body>
</html>
