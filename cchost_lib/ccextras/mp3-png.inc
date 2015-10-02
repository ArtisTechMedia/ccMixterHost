<?php
  
// how much detail we want. Larger number means less detail
// (basically, how many bytes/frames to skip processing)
// the lower the number means longer processing time
define("DETAIL", 5);

function findValues($byte1, $byte2) {
  $byte1 = hexdec(bin2hex($byte1));                        
  $byte2 = hexdec(bin2hex($byte2));                        
  return ($byte1 + ($byte2*256));
}

class MP32PNG
{
  function MP32PNG($MP3File, $width, $height)
  {
    global $CC_GLOBALS;
    
    if( empty($CC_GLOBALS['imagemagick-path']) || !file_exists($CC_GLOBALS['imagemagick-path']) )
    {
        $this->dead = true;
        return;
    }

    if( empty($CC_GLOBALS['lame-path']) || !file_exists($CC_GLOBALS['lame-path']) )
    {
        $this->dead = true;
        return;
    }

    $fullpath = realpath($MP3File);
    $parts = pathinfo($fullpath);
    $this->currDir = $parts['dirname'];
    // chmod($this->currDir,0x777);
    chdir($this->currDir);

    $this->MP3File = $parts['basename'];
    $this->width   = $width;
    $this->height  = $height;


    $this->workDir = './';

    $this->finalPNGfile =  $this->workDir . $parts['filename'] . '.png';

    $this->lamePath   = $CC_GLOBALS['lame-path'];
    $this->imgMgkPath = $CC_GLOBALS['imagemagick-path'];
  }

  function removeCachedFile() 
  {
    if( file_exists($this->finalPNGfile) ) 
    {
      unlink($this->finalPNGfile);
    }
  }

  function sendFile() 
  {
    if( !empty($this->dead) ) 
    {
      return;
    }

    if( !file_exists($this->finalPNGfile) ) 
    {
      $this->makePNG();
    }

    // because this
    // http://stackoverflow.com/questions/2611852/imagecreatefrompng-makes-a-black-background-instead-of-transparent
    $this->img = imagecreatefrompng($this->finalPNGfile);
    $background = imagecolorallocate($this->img, 0, 0, 0);
    imagecolortransparent($this->img, $background);
    imagealphablending($this->img, false);
    imagesavealpha($this->img, true);

    header("Content-Type: image/png");
    imagepng($this->img);
  }


  function cleanup()
  {
    //chmod( $this->currDir, cc_default_file_perms() );

    if( !empty($this->handle) )
      fclose($this->handle);

    if( !empty($this->WAVFile) )
      unlink($this->WAVFile);

    if( !empty($this->img) )
      imagedestroy($this->img);

  }

  function makePNG()
  {
    $this->decodeMP3();
    $this->readHeader();
    $this->makeImage();
    $this->resizeImage();
    $this->normalizeImage();
  }

  function decodeMP3() 
  {
    /**
     * convert mp3 to wav using lame decoder
     * First, resample the original mp3 using as 
     *      mono (-m m), 16 bit (-b 16), and 8 KHz (--resample 8)
     * Secondly, convert that resampled mp3 into a wav
     * We don't necessarily need high quality audio to produce a waveform, doing 
     * this process reduces the WAV
     * to it's simplest form and makes processing significantly faster
     */
    $mp3File = $this->MP3File;
    $wavFile = $this->makeTmp('.wav');
    $mp3Temp = $this->makeTmp('.mp3');

    $lame = $this->lamePath;
    $cmd = "{$lame} \"{$mp3File}\" -m m -S -f -b 16 --resample 8 \"{$mp3Temp}\" && " .
           "{$lame} -S --decode \"{$mp3Temp}\" \"{$wavFile}\"";

    exec($cmd);

    unlink($mp3Temp);

    $this->WAVFile = $wavFile;
    $this->handle = fopen($wavFile, "r");
  }


  function readHeader()
  {
    $handle = $this->handle;

    // wav file header retrieval
    $heading[] = fread($handle, 4);
    $heading[] = bin2hex(fread($handle, 4));
    $heading[] = fread($handle, 4);
    $heading[] = fread($handle, 4);
    $heading[] = bin2hex(fread($handle, 4));
    $heading[] = bin2hex(fread($handle, 2));
    $heading[] = bin2hex(fread($handle, 2));
    $heading[] = bin2hex(fread($handle, 4));
    $heading[] = bin2hex(fread($handle, 4));
    $heading[] = bin2hex(fread($handle, 2));
    $heading[] = bin2hex(fread($handle, 2));
    $heading[] = fread($handle, 4);
    $heading[] = bin2hex(fread($handle, 4));
    
    // wav bitrate 
    $peek = hexdec(substr($heading[10], 0, 2));
    $byte = $peek / 8;
    
    // checking whether a mono or stereo wav
    $channel = hexdec(substr($heading[6], 0, 2));
    
    $ratio = ($channel == 2 ? 40 : 80);
    
    $data_size = floor((filesize($this->WAVFile) - 44) / ($ratio + $byte) + 1);

    $this->byte = $byte;
    $this->ratio = $ratio;
    $this->data_size = $data_size;
  }

  function traverseWAV($callback) 
  {
    $handle     = $this->handle;
    $ratio      = $this->ratio;
    $byte       = $this->byte;
    $data_point = 0;

    while(!feof($handle) && $data_point < $this->data_size)
    {
      if ($data_point++ % DETAIL == 0) {
        $bytes = array();
        
        // get number of bytes depending on bitrate
        for ($i = 0; $i < $byte; $i++)
          $bytes[$i] = fgetc($handle);
        
        switch($byte){
          // get value for 8-bit wav
          case 1:
            $data = findValues($bytes[0], $bytes[1]);
            break;
          // get value for 16-bit wav
          case 2:
            if(ord($bytes[1]) & 128)
              $temp = 0;
            else
              $temp = 128;
            $temp = chr((ord($bytes[1]) & 127) + $temp);
            $data = floor(findValues($bytes[0], $temp) / 256);
            break;
        }
        
        $this->$callback( $data, $data_point );

        // skip bytes for memory optimization
        fseek($handle, $ratio, SEEK_CUR);
        
      } else {
        // skip this one due to lack of detail
        fseek($handle, $ratio + $byte, SEEK_CUR);
      }
    }
  }

  function imgMaker($data,$data_point)
  {
      // draw this data point
      // relative value based on height of image being generated
      // data values can range between 0 and 255
      $v = (int) ($data / 255 * $this->height);
            
      // draw the line on the image using the $v value and centering it vertically on the canvas
      imageline(
        $this->img,
        // x1
        (int) ($data_point / DETAIL),
        // y1: height of the image minus $v as a percentage of the height for the wave amplitude
        $this->height - $v,
        // x2
        (int) ($data_point / DETAIL),
        // y2: same as y1, but from the bottom of the image
        $this->height  - ($this->height - $v),

        imagecolorallocate($this->img, 1, 1, 1) // reserve black for tansparency
      );         
  }

  function makeImage() 
  {
    // create original image width based on amount of detail
    // each waveform to be processed with be $height high, but will be condensed
    // and resized later (if specified)

    $img = imagecreatetruecolor($this->data_size / DETAIL, $this->height );        
    imagesavealpha($img, true);
    $transparentColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparentColor);
    $this->img = $img;
    $this->traverseWAV("imgMaker");
  }

  function resizeImage()
  {      
    $rimg = imagecreatetruecolor($this->width, $this->height);
    imagesavealpha($rimg, true);
    imagealphablending($rimg, false);

    $img = $this->img;
    imagecopyresampled($rimg, $img, 0, 0, 0, 0, 
        $this->width, $this->height, imagesx($img), imagesy($img));

    imagedestroy($img);
    $this->img = $rimg;
  }

  function normalizeImage()
  {
    /**
     * the php image library is unreliable when it comes to
     * auto cropping so we call out to imagemagick (convert)
     */
    $pngorg     = $this->makeTmp('org.png');
    $pngtrimmed = $this->makeTmp('trim.png');
    $pngfinal   = $this->finalPNGfile;

    $w = $this->width;
    $h = $this->height;

    imagepng($this->img, $pngorg);

    $imgmgk = $this->imgMgkPath;
    $cmd = "{$imgmgk} {$pngorg} -trim {$pngtrimmed} && " .
           "{$imgmgk} {$pngtrimmed} -resize {$w}x{$h}! {$pngfinal}";

    exec($cmd);

    unlink($pngorg);
    unlink($pngtrimmed);
  }

  function makeTmp($ext) {
    return $this->workDir . substr(md5(time()), 0, 10) . $ext;
  }
}


?>