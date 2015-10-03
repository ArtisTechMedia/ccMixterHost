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
    $this->lamePath = $CC_GLOBALS['lame-path'];

    $this->width   = $width;
    $this->height  = $height;

    $fullpath           = str_replace(getcwd() . '/','',realpath($MP3File));
    $parts              = pathinfo($fullpath);
    $this->parts        = $parts;
    $this->MP3File      = $fullpath;
    $this->workDir      = $parts['dirname'] . '/';
    $this->finalSVGfile = $this->workDir . $parts['filename'] . '.svg';
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

    if( !file_exists($this->finalSVGfile) ) 
    {
      $this->makeSVG();
    }

    if( empty($_GET['wrap']) ) 
    {
      header("Content-Type: image/svg+xml");
      file( $this->finalSVGfile );
    }
    else
    {
      $html =<<<EOF
<html>
<head>
<style>
svg {
  display: inline-block;
  width: 400;
  height: 80;
}
polyline {
  stroke: black;
  stroke-opacity: 0.3;
  stroke-width:1;
  fill:none;
  stroke-linecap: round;
}
</style>
</head>
<body>
<div>
EOF;
      print( $html );
      print( file_get_contents( $this->finalSVGfile ) );
      print( '</div></body></html>');
    }
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

    // bytes per sample per channel
    $this->bps = $riff['bitsPerSamp'] >> 3; 

    // total number of bytes in a sample 
    $sampleStep = $riff['numChannels'] * $this->bps;

    $this->numSamples  = $riff['samplesSize'] / $sampleStep;
    $this->overSample  = 4;

    // the number of samples to skip over to read the next one
    $this->sampleSkip  = (int)floor($this->numSamples / ($this->width*$this->overSample));

    // subtract one bps for the sample we actually
    // process (we're only looking at the left
    // channel for stereo recordings)

    $this->stepSize    = ($this->sampleSkip * $sampleStep) - $this->bps;
    $this->riff = $riff;
  }

  function traverseWAV($callback) 
  {
    $handle     = $this->handle;
    $bps        = $this->bps;
    $seek_dist  = $this->stepSize;
    $count      = $this->width * $this->overSample;

    for( $n = 0; $n < $count && !feof($handle); ++$n )
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

  function collectPeaks() 
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

    $this->scale = $this->height / ($maxY * 3); // not sure why 3 but it looks better
  }

  function makeImage() 
  {
    $this->collectPeaks();

    $baseLine = $this->height / 2;
    $prevY = $baseLine;

    $this->SVGtext  = $this->getSVGHead() . "<polyline points=\"";

    for( $x = 0; $x < count($this->peaks); $x++ )
    {
        $y  = sprintf("%.2f", $baseLine + ($this->peaks[$x] * $this->scale));
        $xo = sprintf("%.2f", $x / $this->overSample);
        $this->SVGtext .= preg_replace('/\.00(,| )/U', '\1', "{$xo},{$y} ");
    }

    $this->SVGtext .= "\" />" . $this->getSVGTail();
  }

  function getSVGHead()
  {
    return "<svg viewBox=\"0 0 {$this->width} {$this->height}\" >\n";
  }

  function getSVGTail()
  {
    return "\n</svg>";
  }

  function makeTmp($ext) 
  {
    return $this->workDir . substr(md5(time()), 0, 10) . $ext;
  }
}


?>