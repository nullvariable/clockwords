<?php
if (!class_exists('PHPUnit_Framework_TestCase')) {
  include_once '/usr/share/php/PHPUnit/Framework.php';
}
$folder = '/home/doug/Desktop/clockwords/';
require_once $folder.'classes/class.cliinputs.php';
//require_once $folder.'classes/class.clockwords.php';
require_once $folder.'classes/class.cw_ocr.php';
require_once $folder.'classes/class.cw_words.php';
require_once $folder.'classes/class.cw_sqlite.php';
require_once $folder.'constants.php';
require_once $folder.'dpr.php';
$GLOBALS[CLOCKWORDS_DEBUG] = FALSE;

class cliinputsTest extends PHPUnit_Framework_TestCase {
  private $inputs;
  public function setUp() {
    //$this->inputs = new cliinputs();
  }
  public function tearDown() {
  }
  public function testInput() {
    $fake = $this->getMock("clockwords");
    $act = cliinputs::process_input('y');
    $this->assertEquals(count($act), 1);
    $act2 = cliinputs::process_input('n');
    $this->assertEquals($act2[0], false);
    $act3 = cliinputs::process_input('d');
    $this->assertEquals(count($act3), 2);
  }
  public function testInput_Invalid() {
    //$fake = $this->getMock("clockwords");
    $this->setExpectedException('Exception');
    cliinputs::process_input(" ");
  }
}