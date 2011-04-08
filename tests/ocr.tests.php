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
  public function testrgb_to_array() {
    $result = $this->ocr->rgb_to_array(11899506);
    $this->assertEquals(count($result), 3, "rgb_to_array should return three elements, and it didn't");
    if (!empty($result) && is_array($result)) {
      foreach ($result as $color) {
        $this->assertTrue(is_numeric($color));
      }
    } else {
      $this->fail("rgb_to_array didn't return an array, unable to check values");
    }
  }
  public function testrgb_to_array_Invalid() {
    try {
      $this->ocr->rgb_to_array("");
      $this->ocr->rgb_to_array("bad apple");
    } catch (Exception $e) {
      return;
    }
    $this->fail("expected exception failed for rgb_to_array");
  }
  public function testCleanImg() {
    $source = CLOCKWORDS_ROOT."tests/images/cropped.png";
    $imgname = CLOCKWORDS_ROOT."temp/cleanme.png";
    $copied = copy($source, $imgname);
    if (!$copied) {
      $this->fail('couldn\'t copy files to clean image');
    }
    $result = $this->ocr->cleanImg($imgname);
    $imagesize = getimagesize($result);
    $this->assertEquals($imagesize[0], CLOCKWORDS_CROP_WIDTH);
    $this->assertEquals($imagesize[1], CLOCKWORDS_CROP_HEIGHT);
    $this->assertEquals($imagesize['mime'], 'image/jpeg');
    unlink($result);
  }
  public function testCleanImg_Invalid() {
    try {
      $result = $this->ocr->cleanImg("");
    } catch (Exception $e) {
      return;
    }
    $this->fail("expected exception for bad image missing");
  }
  public function testConvertToPNM() {
    $source = CLOCKWORDS_ROOT."tests/images/ocr-ready-l.jpg";
    $imgname = CLOCKWORDS_ROOT."temp/ocr-ready.jpg";
    $copied = copy($source, $imgname);
    if (!$copied) {
      $this->fail('couldn\'t copy files to convert to PNM image');
    }
    $result = $this->ocr->convertToPNM($imgname);
    dpr(array('$result',$result,stristr($result, 'pnm')));
    $this->assertTrue(file_exists($result));
    $this->assertLessThan(strpos($result, 'pnm'), 0);
    unlink($result);
  }
  public function testConvertToPNM_Invalid() {
    try {
      $result = $this->ocr->convertToPNM("fake.jpg");
    } catch (Exception $e) {
      return;
    }
    $this->fail("expected exception for bad image missing");
  }
  public function testGetSomeText() {
    $source = CLOCKWORDS_ROOT."tests/images/ocr-ready-l.pnm";
    $imgname = CLOCKWORDS_ROOT."temp/ocr-ready-l.pnm";
    $copied = copy($source, $imgname);
    if (!$copied) {
      $this->fail('couldn\'t copy files to convert to PNM image');
    }
    $result = $this->ocr->getSomeText($imgname);
    $this->assertTrue($result == 'L', "couldn't find the L in our test image");
  }
  public function testGetSomeText_Invalid() {
    try {
      $this->ocr->getSomeText("fake.pnm");
    } catch (Exception $e) {
      return;
    }
    $this->fail("expected exception for GetSomeText did not fire");
  }
}
