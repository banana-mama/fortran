<?php


class Canvas
{


  /**
   * @var integer[] $rawData
   */
  private $rawData = [];

  /**
   * @var integer[] $background
   */
  private $background = [0, 0, 0];

  /**
   * @var integer[] $size
   */
  private $size = ['w' => 40, 'h' => 40];


  /**
   * Canvas constructor.
   *
   * @param  array  $size
   * @param  array  $background
   */
  function __construct(
    array $size = ['w' => 40, 'h' => 40],
    array $background = [0, 0, 0]
  )
  {

    $this->size = $size;
    $this->background = $background;

    $this->fill();

  }


  ### public


  /**
   * @return integer
   */
  public function getHeight(): int
  {
    return $this->size['h'];
  }


  /**
   * @return integer
   */
  public function getWidth(): int
  {
    return $this->size['w'];
  }


  /**
   * @param  array  $position
   *
   * @return string
   */
  public function getPixelColors(array $position): string
  {
    $colors = [];

    for ($i = 0; $i < count($this->background); $i++) {
      $index = $this->calculateIndex($position, $i);
      $colors[] = $this->rawData[$index];
    }

    return implode(', ', $colors);
  }


  /**
   * @param  array  $position
   * @param  array  $pixelColor
   *
   * @return void
   */
  public function setPixel(array $position, array $pixelColor): void
  {
    for ($i = 0; $i < count($pixelColor); $i++) {
      $index = $this->calculateIndex($position, $i);
      $this->rawData[$index] = $pixelColor[$i];
    }
  }


  /**
   * Вычисляем коэффициент функции прямой.
   *
   * @param  integer[]     $positionA
   * @param  integer[]     $positionB
   * @param  integer[]     $positionC
   * @param  null|array[]  $colors
   *
   * @return void
   */
  public function setTriangle(
    array $positionA,
    array $positionB,
    array $positionC,
    ?array $colors = [[255, 255, 255], [255, 255, 255]]
  ): void
  {

    # сортируем координаты 'y' от самой "высокой" до самой "низкой" на канвасе
    $unsortedPositions = [$positionA, $positionB, $positionC];
    usort($unsortedPositions, function ($a, $b) {
      if ($a['y'] == $b['y']) return 0;
      return (($a['y'] > $b['y']) ? 1 : -1);
    });

    $positions = ['A' => null, 'B' => null, 'C' => null];
    $i = 0;
    foreach ($positions as $key => &$value) {
      $value = $unsortedPositions[$i];
      $i++;
    }

    $Sy = floor($positions['A']['y']);
    $Ey = ceil($positions['C']['y']);

    ### Рисуем половинки треугольника

    $colorsIsset = ($colors[0] ?? null);
    $roundBy = round($positions['B']['y']);

    $this->setTriangleHalf($positions, $Sy, $roundBy, function ($positions, $y) {
      $y = ($y + 0.5);

      $data = ['Sx' => $this->getIntersectionPoint($positions['A'], $positions['B'], $y)];

      if (isset($positions['A']['r']) && isset($positions['B']['r'])) {
        $data['Sr'] = $this->getIntersectionPoint($positions['A'], $positions['B'], $y, 'r');
      }
      if (isset($positions['A']['g']) && isset($positions['B']['g'])) {
        $data['Sg'] = $this->getIntersectionPoint($positions['A'], $positions['B'], $y, 'g');
      }
      if (isset($positions['A']['b']) && isset($positions['B']['b'])) {
        $data['Sb'] = $this->getIntersectionPoint($positions['A'], $positions['B'], $y, 'b');
      }

      return $data;
    }, $colorsIsset);

    #

    $colorsIsset = ($colors[1] ?? null);
    $roundBy = ($roundBy + 1);

    $this->setTriangleHalf($positions, $roundBy, $Ey, function ($positions, $y) {
      $data = ['Sx' => $this->getIntersectionPoint($positions['B'], $positions['C'], ($y + 0.5))];

      if (isset($positions['B']['r']) && isset($positions['C']['r']))
        $data['Sr'] = $this->getIntersectionPoint($positions['B'], $positions['C'], ($y + 0.5), 'r');

      if (isset($positions['B']['g']) && isset($positions['C']['g']))
        $data['Sg'] = $this->getIntersectionPoint($positions['B'], $positions['C'], ($y + 0.5), 'g');

      if (isset($positions['B']['b']) && isset($positions['C']['b']))
        $data['Sb'] = $this->getIntersectionPoint($positions['B'], $positions['C'], ($y + 0.5), 'b');

      return $data;
    }, $colorsIsset);

    ###

  }


  ### private


  /**
   * @return void
   */
  private function fill(): void
  {

    $height = $this->size['h'];
    $width = $this->size['w'];

    for ($y = 1; $y <= $height; $y++) {
      for ($x = 1; $x <= $width; $x++) {
        $xy = ['x' => $x, 'y' => $y];
        $this->setPixel($xy, $this->background);
      }
    }

  }


  /**
   * @param  array    $position
   * @param  integer  $colorIndex
   *
   * @return integer
   */
  private function calculateIndex(array $position, int $colorIndex): int
  {

    $x = ($position['x'] - 1);
    $y = ($position['y'] - 1);
    $colorsCount = count($this->background);

    $rowAndColumnOffset = (($this->size['w'] * $y) + $x);
    return (($rowAndColumnOffset * $colorsCount) + $colorIndex);

  }


  /**
   * @param  array    $positionA
   * @param  array    $positionB
   * @param  integer  $value
   * @param  string   $key
   *
   * @return float
   */
  private function getIntersectionPoint(array $positionA, array $positionB, int $value, $key = 'x'): float
  {
    $dividend = ($value - $positionA['y']);
    $divider = ($positionB['y'] - $positionA['y']);
    return ($positionA[$key] + (($positionB[$key] - $positionA[$key]) * ($dividend / $divider)));
  }


  /**
   * @param  integer[]       $positions
   * @param  integer         $from
   * @param  integer         $to
   * @param  callable        $SxCallback
   * @param  null|integer[]  $color
   *
   * @return void
   */
  private function setTriangleHalf(
    array $positions,
    int $from,
    int $to,
    callable $SxCallback,
    ?array $color = [255, 255, 255]
  ): void
  {
    for ($y = $from; $y <= $to; $y++) {

      $data = $SxCallback($positions, $y);
      $Sx = $data['Sx'];

      if (isset($data['Sr'])) $Sr = $data['Sr'];
      if (isset($data['Sg'])) $Sg = $data['Sg'];
      if (isset($data['Sb'])) $Sb = $data['Sb'];

      $yPlus05 = ($y + 0.5);
      $Ex = $this->getIntersectionPoint($positions['A'], $positions['C'], $yPlus05);

      if (isset($positions['A']['r']) && isset($positions['C']['r'])) {
        $Er = $this->getIntersectionPoint($positions['A'], $positions['C'], $yPlus05, 'r');
      }
      if (isset($positions['A']['g']) && isset($positions['C']['g'])) {
        $Eg = $this->getIntersectionPoint($positions['A'], $positions['C'], $yPlus05, 'g');
      }
      if (isset($positions['A']['b']) && isset($positions['C']['b'])) {
        $Eb = $this->getIntersectionPoint($positions['A'], $positions['C'], $yPlus05, 'b');
      }

      if ($Sx > $Ex) {
        $this->swap($Sx, $Ex);
      }
      $Sx = floor($Sx);
      $Ex = ceil($Ex);

      for ($x = $Sx; $x <= $Ex; $x++) {

        if (isset($Sr) && isset($Sg) && isset($Sb) && isset($Er) && isset($Eg) && isset($Eb)) {

          $dividend = (($x + 0.5) - $Sx);
          $divider = ($Ex - $Sx);
          if ($Ex === $Sx) $divider = 1;

          $r = ($Sr + (($Er - $Sr) * ($dividend / $divider)));
          $g = ($Sg + (($Eg - $Sg) * ($dividend / $divider)));
          $b = ($Sb + (($Eb - $Sb) * ($dividend / $divider)));

          $color = [$r, $g, $b];

        }

        $pixelPositions = ['x' => $x, 'y' => $y];
        $this->setPixel($pixelPositions, $color);

      }

    }
  }


  /**
   * @param  mixed  $a
   * @param  mixed  $b
   *
   * @return void
   */
  private function swap(&$a, &$b): void
  {
    $temp = $a;
    $a = $b;
    $b = $temp;
  }


}

$canvasSize = ['w' => 40, 'h' => 40];
$canvasBackgroundColor = [255, 107, 107];
$canvas = new Canvas($canvasSize, $canvasBackgroundColor);

$A = ['x' => 30, 'y' => 5];
$B = ['x' => 10, 'y' => 20];
$C = ['x' => 30, 'y' => 35];
$triangleColors = [[78, 205, 196], [85, 98, 112]];

$canvas->setTriangle($A, $B, $C, $triangleColors);

### •
require_once ('./frontend.php');