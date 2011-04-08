<?php
if (!class_exists('PHPUnit_Framework_TestCase')) {
  include_once '/usr/share/php/PHPUnit/Framework.php';
}
$folder = '/home/doug/Desktop/clockwords/';
require_once $folder.'classes/class.ocr.php';
//require_once $folder.'classes/class.clockwords.php';
require_once $folder.'constants.php';
require_once $folder.'dpr.php';
$GLOBALS[CLOCKWORDS_DEBUG] = FALSE;

class ocrTest extends PHPUnit_Framework_TestCase {
  private $inputs;
  public function setUp() {
    $this->ocr = new ocr();
  }
  public function tearDown() {
    unset($this->ocr);
  }
  public function testConstants() {
    $cs = array("_X","_Y","_WIDTH","_HEIGHT");
    $test = true;
    foreach ($cs as $c) {
      if (!defined("CLOCKWORDS_CROP$c")) {
        $test = false;
        dpr("d CLOCKWORDS_CROP$c");
      } elseif (!is_numeric(constant("CLOCKWORDS_CROP$c"))) {
        $test = false;
        dpr("n CLOCKWORDS_CROP$c");
      }
    }
    $this->assertTrue($test, "something is wrong with our constants");
    if (!$test) { $this->fail("something is wrong with our constants"); }
  }
  public function testScreenGrab() {
    $imgname = $this->ocr->screenGrab();
    $this->assertTrue(file_exists($imgname));
    $imagestats = getimagesize($imgname);
    $this->assertEquals($imagestats['mime'],'image/png');
    unlink($imgname);
  }
  public function testTrimImage() {
    if (defined("CLOCKWORDS_ROOT")) {
      $source = CLOCKWORDS_ROOT."tests/images/cropthis.png";
      $imgname = CLOCKWORDS_ROOT."temp/cropme.png";
      $copied = copy($source, $imgname);
      if (!$copied) {
        $this->fail('couldn\'t copy files to trim image');
      }
      $result = $this->ocr->trimImage($imgname);
      $imagesize = getimagesize($imgname);
      $this->assertEquals($imagesize[0], CLOCKWORDS_CROP_WIDTH);
      $this->assertEquals($imagesize[1], CLOCKWORDS_CROP_HEIGHT);
      $this->assertEquals($imagesize['mime'], 'image/png');
      unlink($imgname);
    } else {
      $this->fail("root constant not defined");
    }
  }
  public function testTrimimage_Invalid() {
    $return = 0;
    try {
      $result = $this->ocr->trimImage("");
    } catch (Exception $e) {
        $return++;
    }
 
    $f = tmpfile();
    fwrite($f,uniqid());
    try {
      $result = $this->ocr->trimImage($f);
    } catch (Exception $e) {
      $return++;
    }
    fclose($f);
    $this->assertEquals($return, 2, "missing expected exception for file error");
    if ($return != 2) {
      $this->fail("expected exception for non-existant file");
    }
  }

}