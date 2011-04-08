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
}
/*
 
 

function trim_image($filename) {
  $start_crop_x = 332;
  $start_crop_y = 634;
  $width = 296;
  $height = 16;
  
  $finalimg = imagecreatetruecolor($width,$height);
  $screenshot = imagecreatefrompng($filename);
  imagecopyresampled($finalimg, $screenshot, 0, 0, $start_crop_x, $start_crop_y, $width, $height, $width, $height);
  imagepng($finalimg, $filename);
  imagedestroy($finalimg);
  imagedestroy($screenshot);  
  //print "\ntrimmed: ".$filename;
}
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