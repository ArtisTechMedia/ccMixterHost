<?php
  

class MP32SVG
{
  function MP32SVG($MP3File, $width, $height)
  {
    global $CC_GLOBALS;
    
    if( empty($CC_GLOBALS['lame-path']) || !file_exists($CC_GLOBALS['lame-path']) )
    {
        $this->dead = true;
        return;
    }

    $fullpath = realpath($MP3File);
    $parts = pathinfo($fullpath);
    $this->currDir = $parts['dirname'];

    chdir($this->currDir);

    $this->MP3File = $parts['basename'];
    $this->width   = $width;
    $this->height  = $height;

    $this->workDir = './';

    $this->finalSVGfile =  $this->workDir . $parts['filename'] . '.svg';

    $this->lamePath = $CC_GLOBALS['lame-path'];
  }

  function removeCachedFile() 
  {
    if( file_exists($this->finalSVGfile) ) 
    {
      unlink($this->finalSVGfile);
    }
  }

  function sendFile() 
  {
    if( !empty($this->dead) ) 
    {
      return;
    }

    //if( !file_exists($this->finalSVGfile) ) 
    {
      $this->makeSVG();
    }

    //header("Content-Type: image/svg+xml");

    //CCDebug::PrintV($this->SVGtext);

    //file( $this->finalSVGfile );
    print( '<html><body> Wave image: ' . $this->SVGtext . '</body></html>');
  }

  function cleanup()
  {
    if( !empty($this->handle) )
      fclose($this->handle);

    if( !empty($this->WAVFile) )
      unlink($this->WAVFile);
  }

  function makeSVG()
  {
    $this->decodeMP3();
    $this->readHeader();
    $this->makeImage();
    file_put_contents($this->finalSVGfile, $this->SVGtext);
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

  function getRIFFPackCodes()
  {
    $riffPackCodes = array(
        'RIFF'        => 'a4',
        'chunkSize1'  => 'V',
        'WAVE'        => 'a4',
        'fmt'         => 'a4',
        'chunkSize2'  => 'V',
        'pcm'         => 'v',
        'numChannels' => 'v',
        'sampleRate'  => 'V',
        'byteRate'    => 'V',
        'blockAlign'  => 'v',
        'bitsPerSamp' => 'v',
        'DATA'        => 'a4',
        'samplesSize' => 'V' 
      );

    $RPCs = array();
    foreach( $riffPackCodes as $name => $code )
    {
      $RPCs[] = "{$code}{$name}";
    }

    return( join($RPCs,'/') );
  }

  function readHeader()
  {
    
    $handle = $this->handle;

    $buffer = fread($handle,44);

    $RPC = $this->getRIFFPackCodes();

    $riff = unpack($RPC, $buffer);

    $this->bps = $riff['bitsPerSamp'] >> 3; // byte per sample

    // We step this much for next sample
    // subtract one bps for the sample we actually
    // process (we're only looking at the left
    // channel for stereo recordings)

    $sampleStep = $riff['numChannels'] * $this->bps;
    $this->numSamples  = $riff['samplesSize'] / $sampleStep;
    $this->sampleSkip  = (int)floor($this->numSamples / $this->width);
    $this->stepSize    = ($this->sampleSkip * $sampleStep) - $this->bps;
    $this->riff = $riff;
  }

  function traverseWAV($callback) 
  {
    $handle     = $this->handle;
    $bps        = $this->bps;
    $seek_dist  = $this->stepSize;

    for( $n = 0; $n < $this->width && !feof($handle); ++$n )
    {
      $bytes = array();
      
      for ($i = 0; $i < $bps; $i++)
        $bytes[$i] = fgetc($handle);
      
      $data = 0;

      switch($bps){

        case 1:
          $data = ord($bytes[0]); // left change byte
          break;

        case 2:
          $lo   = ord($bytes[0]);
          $hi   = ord($bytes[1]);
          $data = ($hi << 8) | $lo;
          if( $data > 0x8000 )
            $data -= 0x10000;
          break;
        }
        
      $this->$callback( $data, $n );

      fseek($handle, $seek_dist, SEEK_CUR);   

    }
  }

  function yCollector($data,$sample_no)
  {
    $this->peaks[] = $data;
  }

  function makeImage() 
  {
    $this->peaks = array();
    $this->traverseWAV("yCollector");
    $maxY = 0;
    for( $i = 0; $i < count($this->peaks); $i++ )
    {
      $y = abs($this->peaks[$i]) / 2;
      if( $y > $maxY )
        $maxY = $y;
    }

    $scale = $this->height / ($maxY *3);

    $baseLine = $this->height / 2;
    $prevY = $baseLine;

    $this->SVGtext  = $this->getSVGHead();

    for( $x = 0; $x < count($this->peaks); $x++ )
    {
        $y = $baseLine + ($this->peaks[$x] * $scale);
        $svg =  "\n<line x1=\"{$x}\" y1=\"{$prevY}\" x2=\"{$x}\" y2=\"{$y}\" " .
            "style=\"stroke:rgba(0,0,0,0.3);stroke-width:3;stroke-linecap:round;\" />";

        $this->SVGtext .= $svg;
        $prevY = $y;
    }

    $this->SVGtext .= '</svg>';
  }

  function getSVGHead()
  {
    $cdataOpen = "<![CDATA[ " ;
    $cdataClose = "]]> ";

    $head = "<style type=\"text/css\" > " .
             " line: { stroke:rgba(0,0,0,0.5);stroke-width:1; } " .
          "  </style>\n" .

      "<svg xmlns=\"http://www.w3.org/2000/svg\" " .
          "xmlns:xlink=\"http://www.w3.org/1999/xlink\" " .
          "height=\"{$this->height}\" width=\"{$this->width}\">";

    return $head;    
  }
  function makeTmp($ext) 
  {
    return $this->workDir . substr(md5(time()), 0, 10) . $ext;
  }
}


?>