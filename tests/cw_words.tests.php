<?php
if (!class_exists('PHPUnit_Framework_TestCase')) {
  include_once '/usr/share/php/PHPUnit/Framework.php';
}
$folder = '/home/doug/Desktop/clockwords/';
require_once $folder.'classes/class.cw_words.php';
//require_once $folder.'classes/class.clockwords.php';
require_once $folder.'constants.php';
require_once $folder.'dpr.php';
$GLOBALS[CLOCKWORDS_DEBUG] = FALSE;

class cw_wordsTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->cw_words = cw_words::instance();
  }
  public function tearDown() {
    unset($this->cw_words);
  }
  public function testGetWord() {
    $result = $this->cw_words->getWord('qzfae');
    $this->assertGreaterThan(2, strlen($result));
    unset($result);
    $result = $this->cw_words->getWord('t');
    $this->assertGreaterThan(2, strlen($result));
  }
  public function testGetWord_Invalid() {
    $expetions=0;
    try {
      $result = $this->cw_words->getWord('');
    } catch (Exception $e) {
      $expetions++;
    }
    try {
      $result = $this->cw_words->getWord('^');
    } catch (Exception $e) {
      $expetions++;
    }
    $this->assertEquals(2,$expetions, "expected exception was not thrown for getWord");
  }
  public function testReducefbl() {
    $this->cw_words->fbl = "qzxa";
    $this->cw_words->reducefbl();
    $result = $this->cw_words->fbl;
    $this->assertEquals('xzq', $result); //if score logic changes this will probably fail
    $this->cw_words->reducefbl();
    $result = $this->cw_words->fbl;
    $this->assertEquals('qz', $result); //if score logic changes this will probably fail
  }
  public function testGetPoints() {
    $total=0;
    foreach (str_split("fictionalizing") as $letter) {
      $p = $this->cw_words->getPoints($letter);
      $total= ($p !=1) ? $total + $p : $total - 1;
    }
    dpr($total);
    //die();
    
  }
}
