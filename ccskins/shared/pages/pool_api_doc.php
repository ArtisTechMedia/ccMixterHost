<?if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?><style >
#doctable td {
    vetical-align: top;
}

#doctable th {
    background-color: black;
    padding: 3px;
    color: white;
}

.v {
    font-style: italic;
    font-wieght: bold;
}

.term {
    font-family: New Courier, courier, serif;
}

.sep {
    height: 12px;
    border-bottom: 1px solid #999;
}

.api {
    padding: 4px;
    font-weight: bold;
    width: 20%
}

code {
    font-size: 11px;
    padding: 5px;
    margin: 3px;
    white-space: pre;
    background-color: #FCC;
    display: block;
}

h3 {
    margin-top: 45px;
    border-top: 2px solid #444;
    padding-left: 7px;
}
</style>
<h1 >Sample Pool API 2.0 [BETA]</h1>
<p >The Sample Pool API allows two media hosting servers to keep track of when one uses samples from 
another. It is a RESTful API meaning URL goes in and XML comes out. The returning 
XML is in the form of a feed (RSS or Atom) with enclosures for downloading media.</p>
<h3 >Passive Sample Pools</h3>
<p >To be a 'passive' sample pool means that you make sources available for sampling,
but you don't care to keep track of how those samples are being used. In that case
you only need to implement two URLs:</p>
<p >ccHost installations (like ccMixter.org) will keep a detailed sample history of your material with links back to your site (<a  href="http://ccmixter.org/magnatune/pools/item/319">Example</a>.)</p>
<table  id="doctable" cellspacing="0">
<tr ><th >URL</th><th  colspan="2">Query string</th><th >Returns</th></tr>
<tr ><th ></th>
<th >key</th><th >value</th><th ></th></tr>
<tr >
<td  style="vertical-align: top;" class="api">/info</td><td  style="vertical-align: top;"></td><td ></td>
<td  style="vertical-align: top;">The general info for a pool. Just return a channel info without any items.
     (Example: <a  href="http://ccmixter.org/api/pool/info">http://ccmixter.org/api/pool/info</a>) </td>
</tr>
<tr ><td  colspan="5" class="sep">&nbsp;</td></tr>
<tr >
<td  style="vertical-align: top;" class="api">/search</td>
<td  style="vertical-align: top;"><span  class="term">query</span></td>
<td  style="vertical-align: top;" class="v">search terms</td>
<td  style="vertical-align: top;" rowspan="2">
    RSS or Atom feed of matching results. The 'guid' (or 'id') element in these are 
    used for further inquiries like '/file' and '/ubeensampled'. Default type of search is 'any' if none
    is specified. (Example: <a  href="http://ccmixter.org/api/pool/search?query=mustang">http://ccmixter.org/api/pool/search?query=mustang</a>)</td>
</tr>
<tr >
<td ></td>
<td  style="vertical-align: top;"><span  class="term">type</span></td>
<td  style="vertical-align: top;">One of: 
  <table >
   <tr ><td  style="vertical-align: top;" class="term">any</td><td  style="vertical-align: top;">Match any terms</td></tr>
   <tr ><td  style="vertical-align: top;" class="term">all</td><td  style="vertical-align: top;">Match all terms</td></tr>
   <tr ><td  style="vertical-align: top;" class="term">phrase</td><td  style="vertical-align: top;">Match exact phrase</td></tr>
  </table>
</td>
</tr>

<tr >
<td ></td>
<td  style="vertical-align: top;"><span  class="term">limit<span style="color:red">*</span></span></td>
<td  style="vertical-align: top;">Limits the number of items returned (e.g. <i>limit=20</i>)
</td>
</tr>

<tr >
<td ></td>
<td  style="vertical-align: top;"><span  class="term">offset<span style="color:red">*</span></span></td>
<td  style="vertical-align: top;">Return items starting at a given zero-based offset (e.g. <i>offset=40</i>)
</td>
</tr>

<tr >
<td ></td>
<td  style="vertical-align: top;"><span  class="term">format<span style="color:red">*</span></span></td>
<td  style="vertical-align: top;">One of:
  <table >
   <tr ><td  style="vertical-align: top;" class="term">rss</td><td  style="vertical-align: top;">Return a RSS 2.0 feed (default)</td></tr>
   <tr ><td  style="vertical-align: top;" class="term">atom</td><td  style="vertical-align: top;">Return an ATOM 1.0 feed</td></tr>
   <tr ><td  style="vertical-align: top;" class="term">count</td><td  style="vertical-align: top;">Return a count of times</td></tr>
  </table>
</td>
</tr>



</table>

<div style="color:red;font-weight:bold">*Proposed for version 2.0</div>


<h3 >Tracking Sample Pools</h3>
<p >If you do want to keep track of who is remixing material on your site, then you need to implement two more:</p>
<table  id="doctable" cellspacing="0">
<tr ><th >URL</th><th  colspan="2">Query string</th><th >Returns</th></tr>
<tr ><th ></th>
<th >key</th><th >value</th><th ></th></tr>
<tr ><td  colspan="5" class="sep">&nbsp;</td></tr>
<tr >
<td  style="vertical-align: top;" class="api">/file</td>
<td  style="vertical-align: top;"><span  class="term">guid</span></td>
<td  style="vertical-align: top;" class="v">file_guid</td>
<td  style="vertical-align: top;">An RSS feed with a single item in it as identified by 'guid'
(Example: <a  href="http://ccmixter.org/api/pool/file?guid=http://ccmixter.org/files/RinkyD/3964">http://ccmixter.org/api/pool/file?guid=http://ccmixter.org/files/RinkyD/3964</a>)
    </td>
</tr>
<tr ><td  colspan="5" class="sep">&nbsp;</td></tr>
<tr >
<td  style="vertical-align: top;" class="api">/ubeensampled</td>
<td  style="vertical-align: top;"><span  class="term">guid</span></td>
<td  style="vertical-align: top;" class="v">source_guid</td>
<td  style="vertical-align: top;" rowspan="3">
   Returns nothing (or an 'OK' xml status if you like) -- This is strictly a notification 
        to let you know that something on your site has been remixed. The ccHost implementation
        will call back to the pool site to get information about the remix so it can display
        this information in the 'Sampled from this have been used in' user interface.
        <ul >
<li >
        'guid' refers to an upload on your server</li>
<li >'remixguid' refers to the remote remix</li>
<li >'poolsite' base API URL of the remote pool.</li>
</ul>
        Use the form <span  class="v">poolsite</span>/file?guid=<span  class="v">remixguid</span> to find out more about the remix.
        
        </td>
</tr>
<tr >
<td  style="vertical-align: top;"></td>
<td  style="vertical-align: top;"><span  class="term">remixguid</span></td>
<td  style="vertical-align: top;" class="v">remix_guid</td>
</tr>
<tr >
<td  style="vertical-align: top;"></td>
<td  style="vertical-align: top;"><span  class="term">poolsite</span></td>
<td  style="vertical-align: top;" class="v">url to calling pool</td>
</tr>
</table>
<h3 >Example</h3>
<p >Assume that example1.com has source media for an artist called 'Brad Sucks' that a user at example2.com has downloaded, 
remixed and wants to upload to example2.com. Assume that base API url for both is at /api/pool.</p>
<p >example2.com calls to example1.com to query for the exact information:</p>
<code >
   http://example1.com/api/pool/search?query=brad+sucks&type=phrase
</code>
<p >example1.com responds with an RSS feed with all sources matching the phrase 'brad sucks'.</p>
<p >The user at example2.com selects the matching source and example2.com extracts the guid for 
that source, combines it with the new guid for the user's upload and notifies example1.com of
that the remix has occured (URL has been broken across lines for readability):</p>
<code >
  http://example2.com/api/pool/ubeensampled
             ?guid=http://example1.com/files/49565
              &remixid=http://example2.com/files/43923
              &pootsite=http://example2.com/api/pool
</code>
<p >The actual guids are extracted from the RSS feed results from '/search' so it
does not matter what format they are. In this case both servers use URLs but that
is totally up to each implementation.</p>
<h3 >Sample Feed</h3>
<p >When exchanging media information a standard, validing RSS or ATOM is expected (the
same thing you're already putting out for podcasts in the case of music sites). Creative
Commons license information should be included:</p>
<code >
&lt;?xml version="1.0" encoding="utf-8" ?&gt;
&lt;rss version="2.0" 
  xmlns:content="http://purl.org/rss/1.0/modules/content/" 
  xmlns:cc="http://creativecommons.org/ns#" 
  xmlns:dc="http://purl.org/dc/elements/1.1/" 
  xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"&gt;

  &lt;channel&gt;
    &lt;title&gt;ccMixter (spoken_word)&lt;/title&gt;
    &lt;link&gt;http://cchost.org&lt;/link&gt;
    &lt;description&gt;Download, Sample, Cut-up, Share.&lt;/description&gt;
    &lt;language&gt;en-us&lt;/language&gt;

    &lt;pubDate&gt;Mon, 06 Feb 2006 19:34:55 PST&lt;/pubDate&gt;
    &lt;lastBuildDate&gt;Mon, 06 Feb 2006 19:34:55 PST&lt;/lastBuildDate&gt;

    &lt;item&gt;
      &lt;title&gt;Generation Defects&lt;/title&gt;
      &lt;link&gt;http://cchost.org/files/aerosolspray/3374&lt;/link&gt;
      &lt;pubDate&gt;Tue, 20 Dec 2005 21:32:04 PST&lt;/pubDate&gt;

      &lt;dc:creator&gt;aerosolspray&lt;/dc:creator&gt;
      &lt;description&gt; A piece of music by me.&lt;/description&gt;
      &lt;content:encoded&gt;&lt;![CDATA[A piece of music by me.]]&gt;&lt;/content:encoded&gt;
      &lt;enclosure 
         url="http://cchost.org/people/aerosolspray/aerosolspray_-_Generation_Defects.wma" 
         length="952466" 
         type="audio/x-ms-wma"&gt;&lt;/enclosure&gt;
      &lt;category&gt;media&lt;/category&gt;
      &lt;category&gt;remix&lt;/category&gt;
      &lt;category&gt;non_commercial&lt;/category&gt;
      &lt;category&gt;audio&lt;/category&gt;
      &lt;category&gt;wma&lt;/category&gt;
      &lt;category&gt;44k&lt;/category&gt;
      &lt;category&gt;stereo&lt;/category&gt;
      &lt;category&gt;CBR&lt;/category&gt;
      &lt;category&gt;chill&lt;/category&gt;
      &lt;category&gt;drums&lt;/category&gt;
      &lt;category&gt;electronic&lt;/category&gt;
      &lt;category&gt;spoken_word&lt;/category&gt;
      &lt;category&gt;synth&lt;/category&gt;

      &lt;guid&gt;http://cchost.org/files/aerosolspray/3374&lt;/guid&gt;
      &lt;cc:license&gt;http://creativecommons.org/licenses/by-nc/2.5/&lt;/cc:license&gt;
    &lt;/item&gt;
  &lt;/channel&gt;
&lt;/rss&gt;
</code>
<p >The ATOM version is still being tested but this is the current format that should work:</p>
<code >
&lt;?xml version="1.0" encoding="utf-8"?&gt;
&lt;feed xmlns="http://www.w3.org/2005/Atom"&gt;

  &lt;title&gt;ccMixter (spoken_word) [BETA]&lt;/title&gt;
  &lt;link rel="self" href="http://cchost.org/feed/atom/spoken_word"&gt;&lt;/link&gt;
  &lt;link rel="alternate" href="http://cchost.org/tags/spoken_word"&gt;&lt;/link&gt;
  &lt;updated&gt;2006-02-06T19:34:55-08:00&lt;/updated&gt;
  &lt;id&gt;http://cchost.org/tags/spoken_word&lt;/id&gt;

  &lt;entry&gt;
      &lt;id&gt;http://cchost.org/files/aerosolspray/3374&lt;/id&gt;
      &lt;title&gt;Generation Defects&lt;/title&gt;
      &lt;author&gt;
        &lt;name&gt;aerosolspray&lt;/name&gt;
      &lt;/author&gt;
      &lt;link rel="alternate" 
        href="http://cchost.org/files/aerosolspray/3374" type="text/html"&gt;
      &lt;/link&gt;
      &lt;link rel="enclosure" 
            href="http://cchost.org/people/aerosolspray/aerosolspray_-_Generation_Defects.wma" 
            length="952466" 
            type="audio/x-ms-wma"&gt;
        &lt;/link&gt;
      
         &lt;category term="media"&gt;&lt;/category&gt;
         &lt;category term="remix"&gt;&lt;/category&gt;
         &lt;category term="non_commercial"&gt;&lt;/category&gt;
         &lt;category term="audio"&gt;&lt;/category&gt;
         &lt;category term="wma"&gt;&lt;/category&gt;
         &lt;category term="44k"&gt;&lt;/category&gt;
         &lt;category term="stereo"&gt;&lt;/category&gt;
         &lt;category term="CBR"&gt;&lt;/category&gt;
         &lt;category term="chill"&gt;&lt;/category&gt;
         &lt;category term="drums"&gt;&lt;/category&gt;
         &lt;category term="electronic"&gt;&lt;/category&gt;
         &lt;category term="spoken_word"&gt;&lt;/category&gt;
         &lt;category term="synth"&gt;&lt;/category&gt;
      
      &lt;updated&gt;2005-12-20T21:32:04-08:00&lt;/updated&gt;

      &lt;content type="text/plain"&gt; A piece of music by me.&lt;/content&gt;

      &lt;link rel="license" 
            href="http://creativecommons.org/licenses/by-nc/2.5/" 
            type="text/html"&gt;
      &lt;/link&gt;
  
  &lt;/entry&gt;
&lt;/feed&gt;
</code>
