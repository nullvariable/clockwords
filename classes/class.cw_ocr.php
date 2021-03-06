<?php
/*
 This class performs the following functions:
 -take a screenshot
 -crop screenshot
 -remove colors to make OCR easier
 -run OCR and return results
*/
class cw_ocr {
  static $ocrText;
  static $lastScreenGrab;
  
  /**
   * simple function borrowed from php.net
   * converts red/green/blue codes to an array
   * @param hex $rgb color codes from hex
   */
  function rgb_to_array($rgb) {
    if ( empty($rgb) || !is_numeric($rgb) ) {
      throw new Exception("error constructing RGB array, invalid input");
    }
    $a[0] = ($rgb >> 16) & 0xFF;
    $a[1] = ($rgb >> 8) & 0xFF;
    $a[2] = $rgb & 0xFF;
    return $a;
  }
  
  /**
   * get a screenshot using scrot
   * @return string PNG filename
   */
  function screenGrab() {
    while (empty($imgname)) {
      $uid = uniqid('', TRUE);
      $imgname = CLOCKWORDS_ROOT."temp/".$uid.".png";
      if (file_exists($imgname)) {
        unset($imgname,$uid);
        dpr("file: $imgname already exists");
      }
    }
    $cmd = "scrot ".$imgname;
    exec($cmd);
    return $imgname;
  }
  /**
   * trimImage takes a given file and crops it to the coordinates defined in
   * the constants file
   * @param string $filename
   * @return bool true/false on success
   */
  function trimImage($filename) {
    try { $this->is_valid_image($filename,'png'); } catch (Exception $e) { throw $e; }
    if (!is_numeric(CLOCKWORDS_CROP_WIDTH) || !is_numeric(CLOCKWORDS_CROP_HEIGHT)) {
      throw new Exception("oops, something is wrong with our dimesion constants");
    }
    if (empty($GLOBALS[CLOCKWORDS_CROP_X]) || empty($GLOBALS[CLOCKWORDS_CROP_Y]) || !is_numeric($GLOBALS[CLOCKWORDS_CROP_X]) || !is_numeric($GLOBALS[CLOCKWORDS_CROP_Y])) {
      try { $this->setCoords($filename); } catch (Exception $e) { throw $e; }
    }
    $finalimg = imagecreatetruecolor(CLOCKWORDS_CROP_WIDTH,CLOCKWORDS_CROP_HEIGHT);
    $screenshot = imagecreatefrompng($filename);
    imagecopyresampled(
      $finalimg,
      $screenshot,
      0,
      0,
      $GLOBALS[CLOCKWORDS_CROP_X],
      $GLOBALS[CLOCKWORDS_CROP_Y],
      CLOCKWORDS_CROP_WIDTH,
      CLOCKWORDS_CROP_HEIGHT,
      CLOCKWORDS_CROP_WIDTH,
      CLOCKWORDS_CROP_HEIGHT
    );
    imagepng($finalimg, $filename);
    imagedestroy($finalimg);
    imagedestroy($screenshot);
    if (file_exists($filename)) {
      return true;
    }
    return false;
  }
  function cleanImg($filename) {
    try { $this->is_valid_image($filename,'png'); } catch (Exception $e) { throw $e; }
    $img = imagecreatefrompng($filename);
    $out = ImageCreateTrueColor(imagesx($img),imagesy($img)) or die('Problem In Creating image');
    //work through the image pixel by pixel
    for ($x = 0; $x < imagesx($img); $x++) {
      for ($y = 0; $y < imagesy($img); $y++) {
        $src_pix = imagecolorat($img,$x,$y);
        try {
          $pix_rgb = $this->rgb_to_array($src_pix);
        } catch (Exception $e) {
          throw $e;
        }
        if ($pix_rgb[0] > 180 && $pix_rgb[1] > 180 && $pix_rgb[2] > 180) {
          //do nothing
        } elseif ($pix_rgb[0] == 69 && $pix_rgb[1] == 41 && $pix_rgb[2] == 36) {
          $pix_rgb = array(255,255,255);
        } elseif ($pix_rgb[1] != 255) {
          $pix_rgb = array(0,0,0);
        }
  
  
        imagesetpixel($out, $x, $y, imagecolorallocate($out, $pix_rgb[0], $pix_rgb[1], $pix_rgb[2]));
      }
    }
  
    //$elements = explode('.', $filename);
    //unlink($filename);
    $imgname = str_replace(".png", ".jpg", $filename);
    imagejpeg($out, $imgname ); 
    imagedestroy($img);
    imagedestroy($out);
    //print_r(get_defined_vars());die();
    return $imgname;
  }
  function convertToPNM($filename) {
    try { $this->is_valid_image($filename,'jpeg'); } catch (Exception $e) { throw $e; }
    $img = imagecreatefromjpeg($filename);
    $newfilename = str_replace('.jpg', '.pnm', $filename);
    $cmd = "djpeg -pnm -gray $filename > ".$newfilename;
    exec($cmd);
    //unlink($filename);
    return $newfilename;
  }
  function getSomeText($filename, $destroy = true) {
    try { if (!file_exists($filename)) { throw new Exception('file not found'); }; } catch (Exception $e) { throw $e; }
    $cmd = "gocr -a 50 ".$filename;
    $result = exec($cmd);
    //tidy up
    $return = strtolower($result);
    $return = preg_replace('/[^a-z51]/','',$return);
    if ($destroy) {
      //unlink($filename);
    }
    return str_replace(array('5','1'), array('s','i'), $return);
  }
  function is_valid_image($filename, $type = 'png') {
    if (!file_exists($filename)) {
      throw new Exception("cannot find image to crop: $filename");
    } else {
      $imagestats = getimagesize($filename);
      if (empty($imagestats['mime']) || $imagestats['mime'] != "image/$type") {
        throw new Exception("$filename does not appear to be a valid $type image");
      }
    }
    return true;
  }
  function doOCR($filename=false) {
    if (!$filename) { try { $filename = $this->screenGrab(); } catch (Exception $e) { throw $e; } }
    try { $result = $this->trimImage($filename); } catch (Exception $e) { throw $e; }
    try { $filename = $this->cleanImg($filename); } catch (Exception $e) { throw $e; }
    try { $filename = $this->convertToPNM($filename); } catch (Exception $e) { throw $e; }
    try { $result = $this->getSomeText($filename); } catch (Exception $e) { throw $e; }
    if (!empty($result) && strlen(trim($result)) > 0) {
      return $result;
    } else {
      throw new Exception("no text found",100);
    }
  }
  function patternTest($filename, $pattern) {
    //just return true or false if we found a match
    $result = shell_exec("visgrep -t 200 ".escapeshellarg($filename)." ".escapeshellarg($pattern));
    if (empty($result)) {
      throw new Exception("No image pattern matches were found");
    }
    return trim($result);
  }
  function setCoords($filename = false) {
    if (!$filename) { $filename = $this->screenGrab();$rm = true; }
    $raw = $this->patternTest($filename, CLOCKWORDS_ROOT."match3.pat");
    $clean = explode(' ', $raw);
    $xy = explode(',', $clean[0]);
    $GLOBALS[CLOCKWORDS_CROP_X] = $xy[0];//+197;
    $GLOBALS[CLOCKWORDS_CROP_Y] = $xy[1]+(642-222);//+424;
    //if (!empty($rm)) { unlink($filename); }//clean up after ourselves 
    return true;
  }
}

