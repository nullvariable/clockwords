<?php
/*
 This class performs the following functions:
 -take a screenshot
 -crop screenshot
 -remove colors to make OCR easier
 -run OCR and return results
*/
class ocr {
  static $ocrText;
  static $lastScreenGrab;
  
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
    if (!file_exists($filename)) {
      throw new Exception("cannot find image to crop: $filename");
    } else {
      $imagestats = getimagesize($filename);
      dpr($imagestats,true,false);
      if (empty($imagestats['mime']) || $imagestats['mime'] != 'image/png') {
        throw new Exception("$filename does not appear to be a valid png image");
        return false;
      }
    }
    if (!is_numeric(CLOCKWORDS_CROP_X) || !is_numeric(CLOCKWORDS_CROP_Y) || !is_numeric(CLOCKWORDS_CROP_WIDTH) || !is_numeric(CLOCKWORDS_CROP_HEIGHT)) {
      throw new Exception("oops, something is wrong with our dimesion constants");
    }
    $finalimg = imagecreatetruecolor(CLOCKWORDS_CROP_WIDTH,CLOCKWORDS_CROP_HEIGHT);
    $screenshot = imagecreatefrompng($filename);
    imagecopyresampled(
      $finalimg,
      $screenshot,
      0,
      0,
      CLOCKWORDS_CROP_X,
      CLOCKWORDS_CROP_Y,
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
}
/*
 
 


function convert_to_pnm($filename) {
  $img = imagecreatefromjpeg($filename);
  //imagejpeg($img, $filename.'.jpg');
  //unlink($filename);
  //imagedestroy($img);
  //$filename = $filename.".jpg";
  $newfilename = str_replace('.jpg', '.pnm', $filename);
  $cmd = "djpeg -pnm -gray $filename > ".$newfilename;
  exec($cmd);
  unlink($filename);
  return $newfilename;
}
function clean_img($filename) {
  $img = imagecreatefrompng($filename);/*
  $index = imagecolorclosest ( $img,  /*106,85,64*//*240, 222, 210 );
  imagecolorset($img,$index,0,255,0); // SET NEW COLOR
  *//*
  $out = ImageCreateTrueColor(imagesx($img),imagesy($img)) or die('Problem In Creating image');

  for ($x = 0; $x < imagesx($img); $x++) {
    for ($y = 0; $y < imagesy($img); $y++) {
      $src_pix = imagecolorat($img,$x,$y);
      $pix_rgb = rgb_to_array($src_pix);
      if ($pix_rgb[0] > 180 && $pix_rgb[1] > 180 && $pix_rgb[2] > 180) {
        //do nothing
      } elseif ($pix_rgb[1] != 255) {
        $pix_rgb = array(0,0,0);
      }


      imagesetpixel($out, $x, $y, imagecolorallocate($out, $pix_rgb[0], $pix_rgb[1], $pix_rgb[2]));
    }
  }

  //$elements = explode('.', $filename);
  unlink($filename);
  $imgname = str_replace(".png", ".jpg", $filename);
  imagejpeg($out, $imgname ); 
  imagedestroy($img);
  imagedestroy($out);
  //print_r(get_defined_vars());die();
  return $imgname;
}
function rgb_to_array($rgb) {
    $a[0] = ($rgb >> 16) & 0xFF;
    $a[1] = ($rgb >> 8) & 0xFF;
    $a[2] = $rgb & 0xFF;

    return $a;
}

*/