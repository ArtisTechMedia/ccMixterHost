<style>
.qouter {
    width: 720px;
}
.urlexample {
    font-size: 15px;
}
div.urlexample {
    white-space: nowrap;
}
.urlexample a {
    font-weight: normal;
}
.qexample, .codexample, .urlexample, .codesnippet, .exq {
    font-family: Courier New, courier, serif;
}

.exq_desc, .exq {
    padding-bottom: 7px;
    border: solid #AAA;

}
.exq_desc {
    vertical-align: top;
    padding: 3px 10px 3px 3px;
    background-color: #CCC;
    border-width: 2px 0px 2px 4px;
}
.exq {
    white-space: nowrap;
    padding: 0px 3px 0px 3px;
    border-width: 2px 4px 2px 0px;
}

.qexample, .codexample, .urlexample {
    margin-left: 2em;
}
.codexample {
    white-space: pre;
}
.qtable {
    margin-left: 2em;
}
.qtable th, .qtable td {
    vertical-align: top;
}
.qtable th {
    white-space: nowrap;
    border-bottom: 1px solid black;
}
.qtable td {
    padding-right: 1.5em;
    border-bottom: 1px dotted #999;
}
.qtable td.key, span.key {
    font-weight: bold;
}

td.value, span.value {
    font-style: italic;
}

.tmptitle {
    font-size: 1.1em;
    padding: 4px;
    font-weight: bold;
}
.tmptitle, .tmpdesc  {
    background-color: #555;
    color: white;
}
.tmpdesc {
    font-size: 11px;
    font-weight: normal;
}
</style>
<h1>Query API 2.0 (beta)</h1>
<div class="qouter">
<p>
The Query API is how you get data from a ccHost site installation. This information can used as widgets in a blog or
other web page, a feed or raw data for programmatic manipulation.
</p>

<h2>Parameter Passing</h2>

<p>
No matter what the calling context, the information in the results and how they are formatted are
controlled by setting up parameters and values using the URL query parameter syntax:
</p>

<div class="qexample"><span class="key">name</span>=<span class="value">value</span>&<span class="key">another_name</span>=<span class="value">another_value</span></div>

<p>
For example, to return uploads that have been tagged with either foo or bar you would say:
</p>

<div class="qexample"><span class="key">tags</span>=<span class="value">foo</span>+<span class="value">bar</span>&<span class="key">type</span>=<span class="value">any</span></div>

<p>
To see the results in a RSS feed format, you would add the <span class="value">rss</span> value as a <span class="key">format</span>
(<span>f</span> for short) parameter:
</p>

<div class="qexample"><span class="key">tags</span>=<span class="value">foo</span>+<span class="value">bar</span>&<span class="key">type</span>=<span class="value">any</span>&<span class="key">f</span>=<span class="value">rss</span></div>

<p>
Unlike the RSS format which has a pre-defined output format, the HTML format can be further 
controlled by specifying a <span class="key">template</span> (<span class="key">t</span> for short) to be used. 
For example, to embed the same results into a web page with links and attribution:

<div class="qexample"><span class="key">tags</span>=<span class="value">foo</span>+<span class="value">bar</span>&<span class="key">type</span>=<span class="value">any</span>&<span class="key">format</span>=<span class="value">html</span>&<span class="key">t</span>=<span class="value">links_by</span></div>

<p>
See the <a href="#param_ref">parameter reference</a> for a detailed explanation of what parameters are available.
</p>


<h2>Calling Context</h2>

<p>
The Query API can invoked from several contexts:
</p>
<ul>
  <li>Remotely via a URL</li>
  <li>Directly from PHP in a ccHost installation</li>
  <li>Embedded in a template in a ccHost installation</li>
  <li>Embedded into a topic using ccHost formatting</li>
  <li>Embedded into ccHost navigation tabs</li>
</ul>

<h3>URL Remote invocation</h3>
<p>
  The base URL for the Query API is http://example.com/api/query where 'example.com' is the base of the
  ccHost site installation. Parameters and values are passed URL query parameters. In the following example
  the <span class="key">user</span> (<span class="key">u</span> for short) is used to limit the results
  to uploads by a specific uploader:
</p>

<div class="urlexample"><a href="<?= $A['query-url']?>tags=hip_hop&sort=name&u=teru"><?= $A['query-url']?><span class="key">tags</span>=<span class="value">hip_hop</span>&<span class="key">sort</span>=<span class="value">name</span>&<span class="key">u</span>=<span class="value">teru</span></a></div>
<p></p>
<div class="urlexample">
<a href="<?= $A['query-url']?>format=rss&datasource=topics&type=review&u=victor"><?= $A['query-url']?><span class="key">f</span>=<span class="value">rss</span>&<span class="key">datasource</span>=<span class="value">topics</span>&<span class="key">type</span>=<span class="value">review</span>&<span class="key">u</span>=<span class="value">victor</span></a></div>

<p>This type of query request can be used anywhere an URL is used, however only certain formats make sense 
in some URL contexts. For example, when typing in an URL in your browser, only the <span class="key">f</span>=<span class="value">page</span> is useful. When
embedding into a blog use <span class="key">f</span>=<span class="value">html</span> or if used in the SRC parameter of a <span class="codesnippet">&lt;script&gt;</span>
then <span class="key">f</span>=<span class="value">docwrite</span> should be used. </p>

<h3>Directly from PHP</h3>
<p>
  Developers that write extensions to ccHost use the URL query syntax to call the CCQuery object:
</p>
<div class="codexample">
require_once('cchost_lib/cc-query.php');
$query = new CCQuery();
$args = $query->ProcessAdminArgs('tags=hip_hop&sort=name&user=teru');
$query->Query($args);
</div>
<p>
  In order to facilitate using query parameters passed to a URL other than api/query, either 
  via GET or POST:
</p>

<div class="codexample">
require_once('cchost_lib/cc-query.php');
$query = new CCQuery();
$args = $query-><b>ProcessUriArgs</b>();
$query->Query($args);
</div>

<p>
  Instead of outputting the results into the page directly, the results can be returned as a PHP array
  by using <span class="key">f</span>=<span class="value">php</span>. The following is an example of 
  using a <span class="key">dataview</span> to retrieve the upload_ids of all uploads by <span class="value">teru</span> tagged
  as <span class="value">hip_hop</span>
</p>

<div class="codexample">
require_once('cchost_lib/cc-query.php');
$query = new CCQuery();
$args = $query->ProcessAdminArgs('tags=hip_hop&u=teru&<b>dataview=ids&b=php</b>');
$results = $query->Query($args);
foreach( $results[0] as $row )
{
    $id = $row['upload_id'];
    //...
}
</div>

<p>The <span class="codesnippet">$results</span> variable now contains an array that contains the results</p>

<h3>Embedded in a template in a ccHost installation</h3>
<p>
  Template developers use the URL query sytax but in a more concise way:
</p>

<div class="qexample">%query('tags=hip_hop&t=links_by_ul&limit=5')%</div>
<p></p>     
<h3>Embedded into a topic using ccHost formatting</h3>
<p>
  Queries can be embedded into topic content posts in a ccHost site using the following
  syntax:
</p>

<div class="qexample">[query=template=mplayer&playlist=340][/query]</div>
<p></p>     

<h3>Embedded into ccHost navigation tabs</h3>
<p>
  ccHost site admins can embed queries into the navigation tabs by selecting 'Query' for 
  the tab type and entering the query. Format is always <span class="value">page</span>.
</p>

<h2>Concepts and Definitions </h2>

<a name="format"></a>
<h3>Formats</h3>
<p>
  Every query must have a <span class="key">format</span> parameter (or <span class="key">f</span> for short).
</p>
<p>
  The <span class="key">f</span> parameter determines how the final data is returned. There are several
  categories:
</p>

<table class="qtable">
 <tr><th>Category</th><th>Format Values</th></tr>
 <tr><td class="key">HTML       </td><td>   <span class="value">page</span>, <span class="value">html</span></td><tr>
 <tr><td class="key">Feeds/XML  </td><td>   <span class="value">atom</span>, <span class="value">rss</span>, <span class="value">xspf</span>, <span class="value">xml</span>    </td><tr>
 <tr><td class="key">Javascript </td><td>   <span class="value">js</span>, <span class="value">json</span>, <span class="value">docwrite</span> </td><tr>
 <tr><td class="key">Plain text </td><td>   <span class="value">csv</span>, <span class="value">textfile</span>      </td><tr>
 <tr><td class="key">Special    </td><td>   <span class="value">m3u</span>, <span class="value">ids</span>, <span class="value">count</span>      </td><tr>
</table>

<p>
  The default <span class="key">format</span> is <span class="value">page</span> which will embed the results into a full HTML page based
  on the current skin of the ccHost installation. For HTML without the full page use <span class="key">format</span>=<span class="value">html</span>
</p>

<h3>Templates</h3>
<p>
  For the HTML <span class="key">format</span>s <span class="value">page</span> and <span class="value">html</span> there are many different specialized templates
  that return the requested in specific HTML snippets. 
</p>

<p>
  See the <a href="#templates">Templates Appendix</a> for details.
</p>


<h3>Data Views</h3>
<p>
<p>
  For non HTML queries a <span class="key">dataview</span> is used to return rows of data. The <span class="key">dataview</span> 
  acts as a column selector while the other query parameters (such as <span class="key">tags</span> and <span class="key">user</span>) 
  determine which rows to return and <span class="key">sort</span> will determine the order.
</p>

<p>
  See the <a href="#dataviews">Dataview Appendix</a> section for a list of <span class="key">dataview</span> values in the system.
</p>

<h3>Specifying Parameters</h3>
<p>
  Use parameters to select which records, how many of them and in which order.
</p>

<p>
  For example in order to return the 10 latest uploads that are tagged as 
  <span class="value">sample</span> with an Attribution license we use the follow query:
</p>

<div class="urlexample">
<a href="<?= $A['query-url']?>limit=10&tags=sample&lic=by"><span class="key">limit</span>=<span class="value">10</span>&<span class="key">tags</span>=<span class="value">sample</span>&<span class="key">lic</span>=<span class="value">by</span></a></div>

<p></p>     

<h2>Usage Cookbook</h2>

<h3>Combining Parameters That Make Sense</h3>
<p>When queries for a set of uploads it might be helpful to group the parameters so the results are not too limited. 
For example you probably never want to combine the following parameters: 
<span class="key">collab</span>, 
<span class="key">ids</span>, 
<span class="key">playlist</span>, 
<span class="key">remixes</span>, 
<span class="key">remixesof</span>, 
and <span class="key">sources</span> because they each return a very small set of records.</p>

<p>But things get interesting when you refine the results with some set parameters like 
<span class="key">lic</span>, 
<span class="key">reccby</span>, 
<span class="key">remixmax</span>, 
<span class="key">remixmin</span>, 
<span class="key">reqtags</span>, 
<span class="key">score</span>, 
<span class="key">sinceu</span>, 
<span class="key">sinced</span>, 
<span class="key">tags</span>, 
or <span class="key">user</span>. </p>

<p>Suppose that you know of a playlist (986) and you want to know which songs are available under an
Attribution license:</p>

<div class="urlexample"><a href="<?= $A['query-url']?>f=html&t=links&playlist=986&lic=by"><span class="key">f</span>=<span class="value">html</span>&<span class="key">t</span>=<span class="value">links</span>&<span class="key">playlist</span>=<span class="value">986</span>&<span class="key">lic</span>=<span class="value">by</span></a></div>

<p>You want to know of a cappellas by calendargirl that have been remixed less than 5 times...</p>

<div class="urlexample"><a href="<?= $A['query-url']?>f=html&t=links&user=calendargirl&tags=acappella&remixmax=5"><span class="key">f</span>=<span class="value">html</span>&<span class="key">t</span>=<span class="value">links</span>&<span class="key">user</span>=<span class="value">calendargirl</span>&<span class="key">tags</span>=<span class="value">acappella</span>&<span class="key">remixmax</span>=<span class="value">5</span></a></div>
<p></p>
<a name="examples"></a>
<h3>Example Queries</h3>

<table cellspacing="0" cellpadding="0">
<tr><td class="exq_desc">The lastest 15 uploads sorted by user's full name</td>
    <td class="exq">f=html&t=links_by&limit=15&chop=0&sort=fullname</td></tr>
<tr><td class="exq_desc">The latest 15 modified uploads</td>
    <td class="exq">f=html&t=links_by&limit=15&chop=0&sort=last_edit</td></tr>
<tr><td class="exq_desc">Playlists created in the last 3 weeks that have at least 3 items</td>
    <td class="exq">t=playlist_2_browse&since=3 weeks ago&minitems=3</td></tr>
<tr><td class="exq_desc">Playlists created by user 'teru' sorted alphabetically</td>
    <td class="exq">t=playlist_2_browse&u=teru&sort=name&ord=asc</td></tr>
<tr><td class="exq_desc">Uploads sorted by number of playlists they are included in</td>
    <td class="exq">t=playlist_2_uploads&sort=num_playlists&ord=desc</td></tr>
<tr><td class="exq_desc">Uploads by user 'teru' ordered by times included in playlists</td>
    <td class="exq">t=playlist_2_uploads&sort=num_playlists&ord=desc&u=teru</td></tr>
<tr><td class="exq_desc">Raw header information about a playlier</td>
    <td class="exq">f=html&t=playlist_2_info&ids=1651</td></tr>
<tr><td class="exq_desc">Link to the latest 5 topics on the page 'Featured Samples'</td>
    <td class="exq">f=html&t=topic_page_links&limit=5&page=featured-samples</td></tr>
<tr><td class="exq_desc">Avatar for the user 'mcjackinthebox'</td>
    <td class="exq">f=html&t=avatar&u=mcjackinthebox</td></tr>
<tr><td class="exq_desc">XML formatted search results for 'anthony' in user table.</td>
    <td class="exq">f=xml&t=search_users&limit=5&search_type=any&search=anthony</td></tr>
<tr><td class="exq_desc">Yahoo! Easy Listener Flash(tm) plugin with uploads that are tagged 'remix' and either 'ambient' or 'chill'</td>
    <td class="exq">f=html&t=easy_listener&limit=10&reqtags=remix&tags=ambient+chill&type=any</td></tr>
<tr><td class="exq_desc">Count of uploads during the month of July 2006</td>
    <td class="exq">sinced=July 2006&befored=Aug 2006&f=count</td></tr>
<tr><td class="exq_desc">Highest recommended uploads from 3 weeks ago</td>
    <td class="exq">sinced=3 weeks ago&befored=2 weeks ago&sort=num_scores</td></tr>
<tr><td class="exq_desc">XML with basic user info of the last 3 registered users</td>
    <td class="exq">dataview=user_basic&limit=3&f=xml</td></tr>
</table>

<a name="param_ref"></a>
<h2>Appendix A: Parameter Reference</h2>

<table class="qtable">
<tr><th>Parameter</th><th>Short Form</th>               <th>Description</th></tr>

<tr><td class="key">  beforeu          </td><td></td><td> Unix time</td></tr>
<tr><td class="key">  befored          </td><td></td><td> Date string (see php's <a href="http://us.php.net/strtotime">strtodate</a>)</td></tr>
<tr><td class="key">  chop             </td><td></td>
<td> Several of the embedding HTML templates will "chop" long names to this value.</td></tr>

<tr><td class="key">  collab           </td><td></td>
<td> Return files belonging to a given collaboration project. Value is a numeric id of the project.</td></tr>

<tr><td class="key">  datasource       </td><td></td>
<td> Set to <span class="value">topics</span> with <span class="key">format</span>=<span class="value">rss</span> to get topics related feeds. (See type parameter.)</td></tr>

<tr><td class="key">  dataview         </td><td></td><td> (see <a href="dataviews">Data View section</a>)</td></tr>
<tr><td class="key">  format           </td><td>f</td><td> (see <a href="#formats">Formats section</a>)</td></tr>
<tr><td class="key">  ids              </td><td></td><td> Comma-separated numeric ids</td></tr>
<tr><td class="key">  lic              </td><td></td><td> (See <a href="#license">License Values</a>)</td></tr>

<tr><td class="key">  limit            </td><td></td>
<td> This will tell the QAPI to return "no more than" a certain number of records. Valid values are:<p></p>
    <table>
    <tr><td><span class="value">numeric value</span> </td>
        <td> A paging system can simulated by setting a limit, combined with <span class="key">offset</span>.</td></tr>
    
    <tr><td><span class="value">page</span></td>
        <td>This tells the QAPI to return no more than the number of records shown on a typical page listing. This is the default
    value for <span class="key">f</span>=<span class="value">page</span>. This is assigned by the site's administrator, typically in the 10-15 range, and can not be surpassed in URL context.</td></tr>

    <tr><td><span class="value">feed</span></td>
     <td>This tells the QAPI to return no more than the number of records in a feed listing. This is the default
    value for any of the feed category of formats (<span class="value">rss</span>, <span class="value">atom</span>, etc.). This is assigned by the site's administrator, typically in the 15-20 range, and can not be surpassed in URL context.</td></tr>

    <tr><td><span class="value">query</span></td>
    <td>This tells the QAPI to return no more than the number of records in a feed listing. This is the default
    value for any of the non feed or page category of formats (<span class="value">html</span>, <span class="value">csv</span>, etc.). This is assigned by the site's administrator, typically in the 100-200 range, and can not be surpassed in URL context.</td></tr>

    <tr><td style="border:0px"><span class="value">default</span></td>
     <td style="border:0px">This tells the QAPI to use whatever is the admin assigned value for the current context and format. This is the same as leaving out the <span value="key">limit</span> parameter.</td></tr>
     
     </table>

</td></tr>

<tr><td class="key">  match            </td><td></td><td> Template specific, for example <span class="codexample">t=review_upload&match=%upload_id% </span> and <span class="codexample">t=topic_thread&match=%thread_id%</span></td></tr>
<tr><td class="key">  nosort           </td><td></td><td> Used with param <span class="key">ids</span> to honor the order of ids passed in.</td></tr>
<tr><td class="key">  offset           </td><td></td><td> Combine with <span class="key">limit</span> to page through results.</td></tr>
<tr><td class="key">  paging           </td><td></td><td> Used with <span class="key">format</span>s <span class="value">page</span> and <span class="value">html</span> to include prev/next buttons. Valid values are <span class="value">on</span> and <span class="value">off</span><br /><br />The default for <span class="value">page</span> is <span class="value">on</span>, for <span class="value">html</span> is <span class="value">off</span></td></tr>
<tr><td class="key">  playlist         </td><td></td><td> Return records belonging to a specific playlist. Value is the numeric playlist id</td></tr>
<tr><td class="key">  rand             </td><td></td><td> Set to <span class="value">1</span> to return records in a random order</td></tr>
<tr><td class="key">  reccby           </td><td></td><td> Return records ecommended by a user at the site. Value is the login name of the user.</td></tr>
<tr><td class="key">  remixes          </td><td></td><td> Request for remixes of a given upload id</td></tr>
<tr><td class="key">  remixesof        </td><td></td><td> Request for remixes of a given user (value is login name)</td></tr>
<tr><td class="key">  remixmax         </td><td></td><td> Uploads that have been remixed no more than <span class="value">remixmax</span> times</td></tr>
<tr><td class="key">  remixmin         </td><td></td><td> Uploads that have been rmeixed no less than <span class="value">remixmin</span> times</td></tr>
<tr><td class="key">  reqtags          </td><td></td><td> These tags must be included in upload</td></tr>
<tr><td class="key">  reviewee         </td><td></td><td> Review topics authored by reviewee</td></tr>
<tr><td class="key">  score            </td><td></td><td> Uploads that have at least <span class="value">score</span> number of ratings</td></tr>
<tr><td class="key">  search           </td><td>s</td><td>Search for text words or a phrase.</td></tr>
<tr><td class="key">  search_type      </td><td></td><td> Valid values are <span class="value">match</span> for an exact phrase, <span class="value">any</span> for matches of any of the terms, <span class="value">all</span> for matches of all of the terms.</td></tr>
<tr><td class="key">  sinceu           </td><td></td><td> Unix time</td></tr>
<tr><td class="key">  sinced           </td><td></td><td> Date string (see php's <a href="http://us.php.net/strtotime">strtodate</a>)</td></tr>
<tr><td class="key">  sort             </td><td></td><td> (See <a href="#sorts">Valid Sorts</a>)</td></tr>
<tr><td class="key">  ord              </td><td></td><td>Order of score. Valid values are <span class="value">ASC</span> and <span class="value">DESC</span>.</td></tr>
<tr><td class="key">  sources          </td><td></td><td> Sources of a given remix</td></tr>
<tr><td class="key">  tags             </td><td></td><td>Return uploads with the tags (separated by '+'). For multiple tags set the type parameter to either <span class="value">all</span> to see records with all tags or <span class="value">any</span> to see records that have any of the tags.</td></tr>
<tr><td class="key">  template         </td><td>t</td><td> (See <a href="#templates">Templates Appendix</a>)</td></tr>
<tr><td class="key">  thread           </td><td></td><td>Used with forum related templates to specify the topics associated with a given thread.</td></tr>
<tr><td class="key">  title            </td><td></td><td>Used with <span class="key">format</span>=<span class="value">page</span> and some feed formats to display a title at the top of the page or XML file.</td></tr>
<tr><td class="key">  type             </td><td></td><td> When data source is <span class="value">uploads</span> this is a modifier for the tags parameter. When data source is <span class="value">topics</span> this restricts the returning records to topics of that type (e.g. <span class="value">forum</span>, <span class="value">review</span>, <span class="value">artist_qa</span>, etc.) The exact types available are site specific.</td></tr>
<tr><td class="key">  user             </td><td>u</td><td> Return records that were uploaded by a certain user. Value is the login name.</td></tr>
</table>

<a name="license"></a>
<h2>Appendix B: License Values</h2>

<table class="qtable">
<tr><td class="key">  by</td><td>Attribution</td></tr>
<tr><td class="key">  nc</td><td>NonCommercial</td></tr>
<tr><td class="key">  sa</td><td>Share-Alike</td></tr>
<tr><td class="key">  nod</td><td>NoDerives</td></tr>
<tr><td class="key">  byncsa</td><td>NonCommercial ShareAlike</td></tr>
<tr><td class="key">  byncnd</td><td>NonCommercial NoDerives</td></tr>
<tr><td class="key">  s</td><td>Sampling</td></tr>
<tr><td class="key">  splus</td><td>Sampling+</td></tr>
<tr><td class="key">  ncsplus</td><td>NonCommercial Sampling+</td></tr>
<tr><td class="key">  pd</td><td> Public Domain</td></tr>
</table>

<a name="dataviews"></a>
<h2>Appendix C: Data Views</h2>

<p>
  The following is a list of Data Views.
</p>

<p>
  In order to peek into a Data View use he following query:
</p>

<div class="qexample"><span class="key">f</span>=csv&<span class="key">limit</span>=1&<span class="key">dataview</span>=NAME_OF_DATA_VIEW</div>

<p>
  replacing NAME_OF_DATA_VIEW with one of the following names:
</p>

<table class="qtable">
<? print_dataviews(); ?>
</table>


<h2>Appendix D: Sort values</h2>

<a name="sorts"></a>
<p>
Valid sort requests depends on the data source:
</p>

<table class="qtable">
<tr><th>Data Source</th><th>Value</th><th>Description</th></tr>
<tr><td class="key">users</td><td class="value"> fullname </td><td> Aritst display name</td></tr>
<tr><td></td><td class="value"> date     </td>              <td > Registration date</td></tr>
<tr><td></td><td class="value"> user               </td>    <td > Artist login name</td></tr>
<tr><td></td><td class="value"> 	user_remixes       </td><td > Number of remixes</td></tr>
<tr><td></td><td class="value"> 	remixed            </td><td > Number of times remixed</td></tr>
<tr><td></td><td class="value"> 	uploads            </td><td > Number of uploads</td></tr>
<tr><td></td><td class="value"> 	userscore          </td><td > Artistss average rating</td></tr>
<tr><td></td><td class="value"> 	user_num_scores    </td><td > Number of ratings</td></tr>
<tr><td></td><td class="value"> 	user_reviews       </td><td > Reviews left by artist</td></tr>
<tr><td></td><td class="value"> 	user_reviewed      </td><td > Reviews left for artist</td></tr>
<tr><td></td><td class="value"> 	posts              </td><td > Forum topics by artist</td></tr>
<tr><td class="key">uploads </td>                           <td > 	Same as user +</td></tr>
<tr><td></td><td class="value"> 	name               </td><td > Upload name</td></tr>
<tr><td></td><td class="value"> 	lic                </td><td > Upload license</td></tr>
<tr><td></td><td class="value"> 	date               </td><td > Upload date</td></tr>
<tr><td></td><td class="value"> 	last_edit          </td><td > Upload last edited</td></tr>
<tr><td></td><td class="value"> 	remixes            </td><td > Upload's remixes</td></tr>
<tr><td></td><td class="value"> 	sources            </td><td > Upload's sources</td></tr>
<tr><td></td><td class="value"> 	num_scores         </td><td > Number of ratings</td></tr>
<tr><td></td><td class="value"> 	num_playlists      </td><td > Number of playlists</td></tr>
<tr><td></td><td class="value"> 	id                 </td><td > Internal upload id</td></tr>
<tr><td></td><td class="value"> 	local_remixes      </td><td > Upload's local remixes</td></tr>
<tr><td></td><td class="value"> 	pool_remixes       </td><td > Upload's remote remixes</td></tr>
<tr><td></td><td class="value"> 	local_sources      </td><td > Upload's local sources</td></tr>
<tr><td></td><td class="value"> 	pool_sources       </td><td > Upload's sample pool sources</td></tr>
<tr><td></td><td class="value"> 	rank               </td><td > Upload Rank</td></tr>
<tr><td></td><td class="value"> score              </td>    <td > Upload's ratings</td></tr>
<tr><td class="key">topics</td><td class="value">name </td>               <td > Topic name</td></tr>
<tr><td></td><td class="value"> date </td>                  <td > Topic date</td></tr>
<tr><td></td><td class="value"> type </td>                  <td > Topic type</td></tr>
<tr><td></td><td class="value"> left </td>                  <td > Topic tree</td></tr>
<tr><td class="key">collab</td>                             <td > 	Same as user +</td></tr>
<tr><td></td><td class="value"> 	name </td>              <td > Collaboration name</td></tr>
<tr><td></td><td class="value"> 	date </td>              <td > Collaboration creation date</td></tr>
<tr><td></td><td>user </td>                                 <td > Collaboration owner (instead of every artist)</td></tr>
<tr><td class="key">pool_items</td><td class="value"> 	name </td><td> Pool item name</td></tr>
<tr><td></td><td class="value"> 	user </td><td> Pool item artist</td></tr>
</table>

<a name="templates"></a>
<h2>Appendix E: HTML Templates</h2>

<p>
The templates are grouped by specifc usage scenarios they where 
designed for.
</p>

<table class="qtable">
<? print_templates(); ?>
</table>

</div><!-- end of query doc -->
<?

function print_dataviews()
{
    _do_print_from_cache('dataviews');
}

function print_templates()
{
    _do_print_from_cache('templates');
}

function _do_print_from_cache($type)
{
    $fname = '_print_' . $type . '.tmp';

    if( !empty($_GET['clear']) )
    {
        if( file_exists($fname) )
            unlink($fname);
    }

    if( !file_exists($fname) )
    {
        ob_start();
        $func = '_print_' . $type;
        $func();
        $text = ob_get_contents();
        ob_end_clean();
        $f = fopen($fname,'w');
        fwrite($f,$text);
        fclose($f);
    }

    if( empty($text) )
        $text = file_get_contents($fname);

    print $text;
}


function _print_dataviews()
{
    traverse_dv_dir('ccdataviews');
}

function traverse_dv_dir($dir)
{
    require_once('cchost_lib/cc-file-props.php');
    $fp = new CCFileProps();

    $files = glob( $dir . '/*.*' );
    sort($files);

    foreach( $files as $filename )
    {
        $props = $fp->GetFileProps($filename);
        if( empty($props['type']) || $props['type'] != 'dataview' )
            continue;
        $filename = preg_replace('#.*/([^\.]+)\.[a-z]+(\.php)?$#i', '\1', $filename);
        print "<tr><td class=\"value\">$filename</td><td>";
        if( !empty($props['desc']) )
        {
            print $props['desc'];
            $br = '<br />';
        }
        else
        {
            $br = '';
        }
        foreach( $props as $prop_name => $prop_value  )
        {
            switch($prop_name)
            {
                case 'type':
                case 'name':
                case 'desc':
                    break;
                default:
                    if( !empty($prop_name) )
                    {
                        print "{$br}{$prop_name} - {$prop_value}\n";
                        $br = '<br />';
                    }
            }
        }
        print "</td></tr>\n";
    }

    foreach( glob( $dir . '/*' ) as $dirname )
    {
        if( is_dir($dirname) )
            traverse_dv_dir($dirname);
    }
}


function & get_valid_templates()
{
    static $valid_templates = 
    array( 
           'format'=> array(   
                    'title' => 'Formats',
                    'desc' => 'Designed for blogs and other off-site pages',
                    'templates' => array(),
                ), 
           'topic_format'=> array(   
                    'title' => 'Topic Formats',
                    'desc' => 'Designed for blogs and other off-site pages (topics)',
                    'templates' => array(),
                ), 
           'embed'=> array(   
                    'title' => 'Object Embeddings',
                    'desc' => 'Object/Flash embeddings',
                    'templates' => array(),
                ), 
           'page'=> array(   
                    'title' => 'Page',
                    'desc' => 'Designed to be used within the main site',
                    'templates' => array(),
                ), 
           'search_results'=> array(   
                    'title' => 'Search Results',
                    'desc' => 'Used by site search feature',
                    'templates' => array(),
                ), 
           'ajax_component'=> array(  
                    'title' => 'Ajax',
                    'desc' => 'Designed to be used in response to ajax requests',
                    'templates' => array(),
                ), 
           );

    return $valid_templates;
}

function _print_templates()
{
    require_once('cchost_lib/cc-file-props.php');
    global $fp;
    $fp = new CCFileProps();
    $valid_templates =& get_valid_templates();
    traverse_template_dir('ccskins');
    $types = array_keys($valid_templates);
    foreach( $types as $type )
    {
        $type_info =& $valid_templates[$type];
        print <<<EOF
<tr><td class="tmptitle" colspan = "2">{$type_info['title']} - <span class="tmpdesc">{$type_info['desc']}</span></tr>

EOF;
        ksort($type_info['templates']);
        foreach( $type_info['templates'] as $desc => $props )
        {
            print <<<EOF
              <tr><td class="value">{$props['template']}</td><td>{$desc}

EOF;
            foreach( $props as $prop_name => $prop_value )
            {
                if( $prop_name == 'template' )
                    continue;
                print "<br /><b>{$prop_name}</b> {$prop_value}\n";
            }            

            print "</td></tr>\n";
        }
    }
}

function traverse_template_dir($dir)
{
    global $fp;
    $valid_templates =& get_valid_templates();
    $types = array_keys($valid_templates);

    foreach( glob( $dir . '/*.*' ) as $filename )
    {
        $props = $fp->GetFileProps($filename);
        if( empty($props['type']) )
            continue;
        $t = $props['type'];
        if( !in_array( $t, $types ) )
            continue;
        $target =& $valid_templates[$t]['templates'];
        $filename = preg_replace('#.*/([^\.]+)\.[a-z]+(\.php)?$#i', '\1', $filename);
        $desc = empty($props['desc']) ? $filename : $props['desc'];
        $target[$desc]['template'] = $filename;
        foreach( $props as $prop_name => $prop_value  )
        {
            switch( $prop_name )
            {
                case 'type':
                case 'desc':
                case 'dataview':
                case 'embedded':
                    continue;
                default:
                    $target[$desc][$prop_name] = $prop_value;
            }
        }
    }

    foreach( glob( $dir . '/*' ) as $dirname )
    {
        if( is_dir($dirname) )
            traverse_template_dir($dirname);
    }
}

?>