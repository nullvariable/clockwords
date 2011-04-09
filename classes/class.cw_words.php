<?php

class cw_words {
  private static $instance;
  public static $allWords;
  private static $usedWords;
	public static function instance() {
		if (self::$instance == NULL) {
			self::$instance = new cw_words();
		}
		return self::$instance;
	}
  function __construct() {
    $this->allWords = file(CLOCKWORDS_ROOT."words.txt");
  }
  /**
   * @param string $desired the letters that are most important
   * @return string a word with the highest score possible
   */
  public function getWord($desired) {
    if (empty($desired) || strlen($desired) < 1 || preg_match('/[^a-zA-Z]/', $desired)) {
      throw new Exception("\$desired was not properly formatted, getWord(\$desired)");
    } 
    $this->fbl = $desired;$biglist = array();
    while (count($biglist) < 11) {
      $biglist = array_filter($this->allWords, array('self','filterByLetters'));
      $this->reducefbl();
    } //this is our nastiest function since it goes over and over again.
    $rkeys = array_rand($biglist, 10);
    $return = $this->bestWord($biglist, $rkeys, $desired);
    dpr(array('getWord',count($biglist),$this->fbl,$return));
    return $return;
  }
  function bestWord($words, $keys, $desired) {
    foreach ($keys as $key) {
      $word=$words[$key];
      $return[$word]=0;
      foreach (str_split($word) as $letter) {
        $p = $this->getPoints($letter);
        $return[$word]= ($p !=1) ? $return[$word] + $p : $return[$word] - 1;
      }
    }
    arsort($return);
    end($return);
    dpr($return);
    return key($return);
  }
  function getPoints($letter) {
    $pointvalues = $GLOBALS[CLOCKWORDS_WORD_POINTS];
    foreach ($pointvalues as $points => $letters) {
      if (strpos($letters, $letter) !== FALSE) {
        return $points;
      }
    }
    return 1;
  }
  function filterByLetters($word) {
    foreach (str_split($this->fbl) as $letter) {
      if (strpos($word, $letter) === FALSE) { return FALSE; }
    }
    return TRUE;
  }
  /*
   take a string and reduce it by one letter based on the letter with the fewest points
  */
  function reducefbl() {
    if (!empty($this->fbl) && strlen($this->fbl) > 1) {
      foreach (str_split($this->fbl) as $letter) {
        $bypoints[$letter] = $this->getPoints($letter);
      }
      arsort($bypoints);
      end($bypoints);
      $key = key($bypoints);
      unset($bypoints[$key]);
      $this->fbl = implode('', array_keys($bypoints));
    }
  }
}