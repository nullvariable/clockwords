<?php
if (!class_exists('PHPUnit_Framework_TestCase')) {
  include_once '/usr/share/php/PHPUnit/Framework.php';
}
$folder = '/home/doug/Desktop/clockwords/';
require_once $folder.'classes/class.ocr.php';
//require_once $folder.'classes/class.clockwords.php';
require_once $folder.'constants.php';

class ocrTest extends PHPUnit_Framework_TestCase {
  private $inputs;
  public function setUp() {
    $this->ocr = new ocr();
  }
  public function tearDown() {
    unset($this->ocr);
  }
  public function testScreenGrab() {
    $imgname = $this->ocr->screenGrab();
    $this->assertEquals(file_exists($imgname), true);
    $this->assertEquals(filetype($imgname), 'file');
    $elements = explode('.', $imgname);
    $this->assertEquals($elements[2],'png');
    unlink($imgname);
  }
  /*public function testInput_Invalid() {
    try {
      cliinputs::process_input(" ");
    } catch (Exception $e) { return; }
    $this->fail("invalid input exception expected");
  }*/
}