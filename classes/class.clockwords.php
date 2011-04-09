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
    $starttime = time();
    $exceptions=0;
    while (time() < $starttime+CLOCKWORDS_RUN_TIME) {
      try {
        $desired = $ocr->doOCR();
        dpr(array('$desired',$desired));
        try {
          $word = $w->getWord($desired);
          dpr(array('$word',$word));
          try {
            $this->type($word);
            $exceptions = ($exceptions > 0) ? $exceptions-1 : $exceptions;
          } catch (Exception $e) { throw $e; }
        } catch (Exception $e) { throw $e; }
      } catch (Exception $e) { dpr(array($e->getMessage(),$e->getFile(),$e->getLine()));$exceptions++; }
      if ($exceptions > 10) { die("\n   too many exceptions!\n"); }
      usleep(3000);
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
        usleep(150000);
      }
      $cmd = '"key Return" "key Escape"';
      exec("xte $cmd");
    } else {
      throw new Exception("something is wrong with the input");
    }
  }
}
