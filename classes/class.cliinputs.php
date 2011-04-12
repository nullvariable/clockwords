<?php 
/**
 * CLIInputs Class
 * Handles all command line interactions
 */
class cliinputs {
  function __construct() {
    fwrite(STDOUT, "Start? (options: y, n, d)");
    $input = trim(fgets(STDIN));
    try {
      $act = $this->process_input($input);
    } catch (Exception $e) {
      dpr($e->getMessage());
    }
  }
  function process_input($input) {
      switch ($input) {
      case 'd' :
        $GLOBALS[CLOCKWORDS_DEBUG] = TRUE;
        exec(":>".CLOCKWORDS_ROOT."debug.txt");
        $return[] = 'debug on';
      case 'y' :
        $cw = new clockwords;
        $return[] = $cw;
        break;
      case 'n' :
        $return[] = false;
        print("\nok, exiting\n");
        break;
      case 'scr' :
        $ocr = new cw_ocr;
        sleep(2);
        $tmp = $ocr->screenGrab();
        $ocr->setCoords($tmp);
        $ocr->trimImage($tmp);
        dpr($tmp);
        exec("eog $tmp &");
        sleep(3);
        unlink($tmp);
        break;
      default :
        throw new Exception("unknown input, exiting");
    }
    return $return;
  }
}