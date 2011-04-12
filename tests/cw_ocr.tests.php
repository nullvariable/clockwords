<?php
if (!class_exists('PHPUnit_Framework_TestCase')) {
  include_once '/usr/share/php/PHPUnit/Framework.php';
}
$folder = '/home/doug/Desktop/clockwords/';
require_once $folder.'classes/class.cw_ocr.php';
//require_once $folder.'classes/class.clockwords.php';
require_once $folder.'constants.php';
require_once $folder.'dpr.php';
$GLOBALS[CLOCKWORDS_DEBUG] = FALSE;

class ocrTest extends PHPUnit_Framework_TestCase {
  private $inputs;
  public function setUp() {
    $this->ocr = new cw_ocr();
    $this->images = array(
      'location-good.png' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/location-good.png",
        'dest' => CLOCKWORDS_ROOT."temp/location-good.png",
        ),
      'location-bad.png' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/location-bad.png",
        'dest' => CLOCKWORDS_ROOT."temp/location-bad.png",
        ),
      'ocr-ready-l.pnm' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/ocr-ready-l.pnm",
        'dest' => CLOCKWORDS_ROOT."temp/ocr-ready-l.pnm",
        ),
      'ocr-ready-l.jpg' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/ocr-ready-l.jpg",
        'dest' => CLOCKWORDS_ROOT."temp/ocr-ready-l.jpg",
        ),
      'cropthis.png' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/cropthis.png",
        'dest' => CLOCKWORDS_ROOT."temp/cropthis.png",
        ),
      'cropped.png' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/cropped.png",
        'dest' => CLOCKWORDS_ROOT."temp/cropped.png",
        ),/*
      'cleanme.png' => array(
        'source' => CLOCKWORDS_ROOT."tests/images/cleanme.png",
        'dest' => CLOCKWORDS_ROOT."temp/cleanme.png",
        ),*/
    );
    foreach ($this->images as $image) {
      $this->do_copy($image['source'], $image['dest']);
    }
  }
  private function do_copy($source, $dest) {
    $copied = copy($source, $dest);
    if (!$copied) {
      $this->fail('couldn\'t copy a file for test setup!!');
    }
  }
  public function tearDown() {
    unset($this->ocr);
    foreach ($this->images as $image) {
      if (file_exists($image['dest'])) { unlink($image['dest']); }
    }
  }
  public function testConstants() {
    $cs = array("_WIDTH","_HEIGHT");
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
    $imgname = $this->images['cropped.png']['dest'];
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
    $imgname = $this->images['ocr-ready-l.jpg']['dest'];
    $result = $this->ocr->convertToPNM($imgname);
    //dpr(array('$result',$result,stristr($result, 'pnm')));
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
    $imgname = $this->images['ocr-ready-l.pnm']['dest'];
    $result = $this->ocr->getSomeText($imgname);
    $this->assertEquals('l', $result, "couldn't find the L in our test image");
  }
  public function testGetSomeText_Invalid() {
    try {
      $this->ocr->getSomeText("fake.pnm");
    } catch (Exception $e) {
      return;
    }
    $this->fail("expected exception for GetSomeText did not fire");
  }
  public function testPatternTest() {
    $imgname = $this->images['location-good.png']['dest'];
    $result = $this->ocr->patternTest($imgname, CLOCKWORDS_ROOT."match_image.pat");
    $this->assertEquals("135,167 -1", $result);
    unlink($imgname);
  }
  public function testPatternTest_Invalid() {
    $imgname = $this->images['location-bad.png']['dest'];
    $this->setExpectedException("Exception", "No matches were found");
    try {
      $result = $this->ocr->patternTest($imgname, CLOCKWORDS_ROOT."match_image.pat");
    } catch (Exception $e) {
      unlink($imgname);
      throw $e;
    }
  }
  public function testSetCoords() {
    $imgname = $this->images['location-good.png']['dest'];
    $result = $this->ocr->setCoords($imgname);
    $this->assertEquals("332", $GLOBALS[CLOCKWORDS_CROP_X]);
    $this->assertEquals("587", $GLOBALS[CLOCKWORDS_CROP_Y]);
  }
}
