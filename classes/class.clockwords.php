<?php
class clockwords {
  function __construct() {
    /*for($i =4; $i>0; $i--) {
      print "\n   $i";
      sleep(1);
    }*/
      
    $this->putItAllTogether();
  }
  function putItAllTogether() {
    $ocr = new cw_ocr;
    $w = new cw_words;
    $sql = new cw_sqlite;
    $starttime = time();
    $word='';
    $exceptions=0;
    $notready=true;
    while ($notready) {
      print "\n Waiting for game screen. ".((time()) - ($starttime))." Seconds elapsed.";
      try {
        $filename = $ocr->screenGrab();
        $ocr->patternTest($filename, CLOCKWORDS_ROOT."match_image.pat");
        $notready = false;
        @unlink($filename);
        print "\n Game screen found, let's play!";
      } catch (Exception $e) {
        $notready = true;
        @unlink($filename);
      }
      usleep(500000);
    }
    $stillplaying=0;
    while ($stillplaying < 10) {
      $filename = $ocr->screenGrab();

      try {
        
        if (empty($GLOBALS[CLOCKWORDS_CROP_X])) { $result = $ocr->setCoords($filename); }
        try {
          dpr(array('$stillplaying'=>$stillplaying,'$filename'=>$filename));
          $result = $ocr->patternTest($filename, CLOCKWORDS_ROOT."match3.pat");
          //$stillplaying = ($stillplaying > 0) ? $stillplaying-1 : 0;
        } catch (Exception $e) {
          dpr($e->getMessage());
          //$stillplaying++;
          usleep(500000);
          try {
            $result = $ocr->patternTest($filename, CLOCKWORDS_ROOT."level.pat");
            $stillplaying = 100;
          } catch (Exception $e) {
            //game has not ended...
          }
          }
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
      if ($exceptions > 10) {
        print("\n   too many exceptions!\n");break;
        }
      $sql->removeWord($word);
      $len = strlen($word);
      if ($len > 5) { usleep($len . "00000"); }//the longer the word, the longer we need to wait to reload letters
      
    }
    //die("\n   ".CLOCKWORDS_RUN_TIME." seconds are up\n");
    $this->cleanup();
  }
  function type($input) {
    if (!empty($input) && strlen($input) > 1) {
      $cmd = '"key Escape"';
      exec("xte $cmd");
      foreach (str_split(trim($input)) as $letter) {
        $cmd = ' "key '.$letter.'"';
        exec("xte $cmd");
        usleep(rand(5,25).'000');
      }
      $cmd = '"key Return" "key Escape"';
      exec("xte $cmd");
      //usleep(250000);
    } else {
      throw new Exception("something is wrong with the input");
    }
  }
  function cleanup() {
    if ($handle = opendir(CLOCKWORDS_ROOT."temp")) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..")
          unlink(CLOCKWORDS_ROOT."temp/".$file);
      }
      closedir($handle);
    }
  }
}
