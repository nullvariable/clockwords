<?php
class clockwords {
  function __construct() {
    for($i =4; $i>0; $i--) {
      print "\n   $i";
      sleep(1);
    }
      
    $this->putItAllTogether();
  }
  function putItAllTogether() {
    $ocr = new cw_ocr;
    $w = new cw_words;
    $sql = new cw_sqlite;
    $starttime = time();
    $word='';
    $exceptions=0;
    while (time() < $starttime+CLOCKWORDS_RUN_TIME) {
      try {
        $filename = $ocr->screenGrab();
        if (empty($GLOBALS[CLOCKWORDS_CROP_X])) { $result = $ocr->setCoords($filename); }

        try {
          
          $desired = $ocr->doOCR($filename);
          dpr(array('$desired',$desired));
          try {
            //$word = $w->getWord($desired);
            $word = $sql->getAWord($desired);
            dpr(array('$word',$word));
            try {
              $this->type($word);
              $exceptions = ($exceptions > 0) ? $exceptions-1 : $exceptions;
            } catch (Exception $e) { throw $e; }
          } catch (Exception $e) { throw $e; }
        } catch (Exception $e) { throw $e; }
      } catch (Exception $e) { dpr(array($e->getMessage(),$e->getFile(),$e->getLine()));$exceptions++; }
      if ($exceptions > 10) { die("\n   too many exceptions!\n"); }
      $len = strlen($word);
      if ($len > 5) { usleep($len . "00000"); }//the longer the word, the longer we need to wait to reload letters
      /*try {
        $ocr->patternTest($ocr->screenGrab(), CLOCKWORDS_ROOT."match_image.pat");
      } catch (Exception $e) { print $e->getMessage(); }*/
    }
    die("\n   ".CLOCKWORDS_RUN_TIME." seconds are up\n");
  }
  function type($input) {
    if (!empty($input) && strlen($input) > 1) {
      $cmd = '"key Escape"';
      exec("xte $cmd");
      foreach (str_split(trim($input)) as $letter) {
        $cmd = ' "key '.$letter.'"';
        exec("xte $cmd");
        usleep(rand(5,25).'0000');
      }
      $cmd = '"key Return" "key Escape"';
      exec("xte $cmd");
      //usleep(250000);
    } else {
      throw new Exception("something is wrong with the input");
    }
  }
}
