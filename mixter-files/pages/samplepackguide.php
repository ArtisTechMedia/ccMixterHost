<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_samplepackguide_init($T,&$targs) {
    
}
?><div >

<style >
.ep li { margin-bottom: 7px; }
pre { font-size: 11px; }
#spack_content {
  padding-left: 25px;
}

#spack_content h3 {
  margin-left: -25px;
}

</style>
<h1 >Sample Pack Submissions Guidelines</h1>
<p >
  First off, thanks for helping out with being a sample pack maker on ccMixter. As a 
  remixer yourself you know the value that pre-cut loops and samples can have when you
  want to jump start a mix.
</p>
<p >
  Here are some guidelines for making and uploading sample packs:
</p>
<div  style="font-family:Verdana; font-size: 11px" id="spack_content">
<h3 >How to Browse for Samples</h3>
<p >
        It's highly recommended you use the <a  href="<?= $A['home-url']?>view/media/samples/browse">sample 
        browser</a> to find the specific genres and instruments you've been assigned to find.
      </p>
<p >
<i >However...</i> you should also look to chop up remixes as well since that's more work and
        the remixers will appreciate us having done some of that for them, plus it helps expose 
        remixers across the site.
      </p>
<p >Don't forget: sample pools (Magnatune, Freesound) are also fair game.</p>
<h3 >What to Pick</h3>
<p >
        Please do  not use samples that are already in another sample pack. Please
        take the time to double check against other sample packs. <b >PLEASE</b>.
      </p>
<h3 >Format of the Samples</h3>
<p >The prefernce for samples you make, is 16bit 44k WAV files, ACIDized loops. Diverging is OK if it makes
      sense.</p>
<h3 >Naming the Samples</h3>
<p >
      We are (mostly) using a specific naming convention for all the samples. Try not to drift 
      too far from the convention and <i >only</i> do so if it <i >really</i> makes sense to 
      be different. (N.B. If you get this part wrong you're just making more work for the admins.)
      </p>
<p >The format for the names is:</p>
<p ><pre >xxx_yy_zzzzzz_nnn_mmm</pre></p>
<table >
<tr ><th >Section</th><th >Values</th><th >Examples</th></tr>
<tr >
<th >xxx</th>
<td >Three digit BPM. If the BPM is less than 100, put a zero (0)
            in front of it. If there is no BPM then use the letter OS (Oh, ess) 
            which stands for 'One Shot.'</td>
<td ><pre >
<b >090</b>_C_teru_kaching.wav
      <b >121</b>_D#_sunbyrn_wahoo-bass.wav
      <b >OS</b>_x_sunbyrn_wahoo-crash.wav
      </pre>
</td>
</tr>
<tr >
<th >yy</th>
<td >The key of the sample. Only use sharp '#' keys (e.g. D# not Eb ). If there is 
            no key, use the letter lower case 'x'.</td>
<td ><pre >
      090_<b >C</b>_teru_kaching.wav
      121_<b >D#</b>_sunbyrn_wahoo-bass.wav
      OS_<b >x</b>_sunbyrn_wahoo-crash.wav</pre>
</td>
</tr>
<tr >
<th >zzz</th>
<td >The artist's name. You can use the friendly name ('Pat Chilla', 'Ms. Vybe') 
        but it's recommended you use the login name ('beatgorilla', 'kendra') because
        this one is almost always shorter and is guaranteed to have file and 
        url safe characters.</td>
<td ><pre >
      090_C_<b >teru</b>_kaching.wav
      121_D#_<b >sunbyrn</b>_wahoo-bass.wav
      OS_x_<b >sunbyrn</b>_wahoo-crash.wav
      </pre>
</td>
</tr>
<tr >
<th >nnn</th>
<td >The name of upload you sampled. This should almost alway be
           abrreviated to (hopefully) 9-10 characters. Use something
           relatively unique about the name. (e.g. if the upload is 
           called 'Trival Dawn Music', try to use 'trivial' and 'dawn' and
           forget about 'music'.</td>
<td ><pre >
      090_C_teru_<b >kaching</b>.wav
      121_D#_sunbyrn_<b >wahoo</b>-bass.wav
      OS_x_sunbyrn_<b >wahoo</b>-crash.wav
      </pre>
</td>
</tr>
<tr >
<th >mmm</th>
<td >Use this section the type of instrument (e.g. 'snare', 'oboe',
           'AcGtr') and don't be afraid to abbreviate. The name can not
           be too short. If you are doing a pack with only one instrument,
           you can completely leave this off.</td>
<td ><pre >
      090_C_teru_kaching.wav
      121_D#_sunbyrn_wahoo-<b >bass</b>.wav
      OS_x_sunbyrn_wahoo-<b >crash</b>.wav
      </pre>
</td>
</tr>
</table>
<h3 >Including a License</h3>
<p >You must include the following text:</p>
<pre >
|------------------ CUT BELOW HERE -------------------------|

This sample pack represents a collection of samples taken
from http://ccmixter.org

The samples are named with the following format:

bpm_key_artist_uploadname

BPM can be number representing the beats per minute or
'OS' for 'one shot' meaning tempo doesn't apply.

All samples in this pack are licensed on some kind of
a Creative Commons license. The sample pack is marked 
with the most restrictive license from all included. 
For more details regarding the individual licenses
for each sample, please check the 'Uses Sample From' 
section of the sample pack's specific web page.

|------------------ CUT ABOVE HERE -------------------------|
</pre>
<p >In a file called LICENSE.TXT in the zip archive.</p>
<h3 >Directory Structure</h3>
<p >The samples should be in a directory and you should zip 
the <i >directory</i> so that when people unzip, they will
get a directory with the samples in them.</p>
<p >The name of the directory should be the same as the name
of your sample pack.</p>
<h3 >Uploading the Sample Pack</h3>
<p >Upload the pack using the 'Submit Samples' form 
to the account called 'samplepacks'. (Get the
password from the admins.)</p>
<p ><b >NOTE</b>: Make sure to <b >UNCHECK</b> the 'Publish
Now' checkbox on the submit forum.</p>
<h3 >Attributing the Pack</h3>
<p >After you've uploaded the pack, use 'Manage Remixes' to
attribute each and every sample you used.</p>
<h3 >You're Done!</h3>
<p >The admins will get notified automatically when a pack 
has been uploaded. Once they approve the pack it will be published.</p>
</div>
</div>