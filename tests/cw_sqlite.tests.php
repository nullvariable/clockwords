<?php
if (!class_exists('PHPUnit_Framework_TestCase')) {
  include_once '/usr/share/php/PHPUnit/Framework.php';
}
$folder = '/home/doug/Desktop/clockwords/';
require_once $folder.'classes/class.cw_sqlite.php';
require_once $folder.'classes/class.cw_words.php';
require_once $folder.'constants.php';
require_once $folder.'dpr.php';
$GLOBALS[CLOCKWORDS_DEBUG] = TRUE;

class cw_sqliteTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->cw_sqlite = cw_sqlite::instance();
    $test_cw_words = $this->cw_sqlite->cw_words();
    $this->TOTAL_WORDS = count($test_cw_words->allWords);
  }
  public function tearDown() {
    unset($this->cw_words);
  }
  public function testObjLoaded() {
    $this->assertTrue(is_object($this->cw_sqlite));
    $this->assertEquals("cw_sqlite", get_class($this->cw_sqlite));

    $test_cw_words = $this->cw_sqlite->cw_words();
    $this->assertTrue(is_object($test_cw_words));
    $this->assertEquals("cw_words", get_class($test_cw_words));
    
    $test_db = $this->cw_sqlite->db();
    $this->assertTrue(is_object($test_db));
    $this->assertEquals("SQLiteDatabase", get_class($test_db));
    
    $result = $test_db->query("SELECT * FROM words;");
    $this->assertGreaterThanOrEqual($this->TOTAL_WORDS, $result->numRows());
  }
  public function testGetAWord() {
    $result = $this->cw_sqlite->getAWord("qzfjx");
    $this->assertTrue(is_string($result));
    $this->assertGreaterThan(1, strlen($result));
  }
  /*
  public function testcw_sqlite_regex() {
    $test_db = $this->cw_sqlite->db();
    $result = $test_db->query("SELECT * FROM words WHERE REGEXP('[a-z]',word) ");
    $this->assertGreaterThanOrEqual($this->TOTAL_WORDS, $result->numRows());    
    $result = $test_db->query("SELECT * FROM words WHERE REGEXP('[q]',word) ");
    $result = $test_db->query("SELECT * FROM words WHERE word LIKE '%q%' AND word LIKE '%z%'");
    $this->assertGreaterThanOrEqual("41", $result->numRows());    
  }*/
}
