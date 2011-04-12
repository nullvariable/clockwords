<?php
class cw_sqlite {
  private static $instance;
  private static $db;
  private static $cw_words;
	public static function instance() {
		if (self::$instance == NULL) {
			self::$instance = new cw_sqlite();
		}
		return self::$instance;
	}
	public static function cw_words() {
		if (self::$cw_words == NULL) {
			self::$cw_words = cw_words::instance();
		}
		return self::$cw_words;
	}
  public static function db() {
    if (self::$db == NULL) {
			self::$db = new SQLiteDatabase(":memory:"); //sqlite_open(":memory:");
      self::$db->createFunction('REGEXP', array('cw_sqlite','cw_sqlite_regex'), 2);
      self::loaddb(self::$db);
		}
		return self::$db;
  }
  private function loaddb($db) {
    $db->query("CREATE TABLE words (id INTEGER PRIMARY KEY, word CHAR(255))");
    $cw_words = self::cw_words();
    foreach ($cw_words->allWords as $word) {
      $db->query("INSERT INTO words (word) VALUES ('$word')");
    }
  }
  function __construct() {
    $db = self::db();
  }
  function getAWord($desired) {
    $words = $this->getWords($desired);
    while (count($words) < 2) {
      $desired = $this->reduceletters($desired);//substr($desired,0,strlen($desired)-1);
      if (strlen($desired) <=0 ) { throw new Exception("Some how ran out of desired letters"); }
      $words = $this->getWords($desired);
    }
    $return = $words[array_rand($words)]['word'];
    dpr("Sending word: $return");
    return $return;
  }
  function getWords($desired) {
    dpr("asking for words with: $desired");
    $db = $this->db();
    $query = $this->makeQuery($desired);
    $result = $db->query($query);
    return $result->fetchAll();
  }
  function makeQuery($desired) {
    $query = 'SELECT * FROM words WHERE';$i = 0;
    foreach (str_split($desired) as $letter) {
      $query .= ($i > 0) ? " AND" : '';$i++;
      $query .= " word LIKE '%$letter%'";
    }
    return $query;
  }
  function cw_sqlite_regex($regex, $str) {
    if (preg_match("/$regex/", $str, $matches)) {
      return 1;
    }
    return false;
  }
	function reduceletters($letters) {
		$return = $letters;
    if (!empty($letters) && strlen($letters) > 1) {
      foreach (str_split($letters) as $letter) {
        $bypoints[$letter] = cw_words::getPoints($letter);
      }
      arsort($bypoints);
      end($bypoints);
      $key = key($bypoints);
      unset($bypoints[$key]);
      $return = implode('', array_keys($bypoints));
    }
		return $return;
  }
}