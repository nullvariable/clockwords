<?php
/**
 * Doug's Print R debugging function
 * @param mixed $var variable to print
 * @param bool $backtrace print backtrace info
 * @param bool $die true/false should the function die when done
 */
function dpr($var,$backtrace = true,$die = false) {
  $bt = debug_backtrace();
  $printr = "\n".dpr_storeable($var);
  $bts = "\n".dpr_storeable($bt);
  if (CLOCKWORDS_DEBUG_TO_FILE) {
    $dbg_txt = fopen('debug.txt','a+');

    fwrite($dbg_txt, "\n[".date(DATE_RSS)."] ".$printr."\n[".$bt[0]['file']."][".$bt[0]['line']."]\n");
    fclose($dbg_txt);
  }
  if (!empty($GLOBALS[CLOCKWORDS_DEBUG]) && $GLOBALS[CLOCKWORDS_DEBUG] === TRUE) {
    print "\n";
    print $printr;
    if ($backtrace) {
      print "\n[".$bt[0]['file']."][".$bt[0]['line']."]";
    }
    if ($die) {
      die("\n\nend dpr called at: ".$bt[0]['file']." line: ".$bt[0]['line']."\n\n");
    }
  } 
}
function dpr_storeable($input) {
  ob_start();
    print_r($input);
    $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
