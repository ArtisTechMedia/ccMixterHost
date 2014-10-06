<?
// Call this template: api/query?t=user_feeds&datasource=user&user=USER_NAME
//
// where USER_NAME is the login name of the user
?>
%%
[meta]
    desc = _('User Feeds')
    page_title = _('User Feeds')
    type = template_component
    breadcrumbs = home,user,text(User Feeds)
    embedded = 1
    dataview = user_feeds
    datasource = user
[/meta]
[dataview]
function user_feeds_dataview()
{
    $fancy_name = cc_fancy_user_sql();

    $sql =<<<EOF
    SELECT user_name, user_id, user_real_name, {$fancy_name}
    %columns%
    FROM cc_tbl_user
    %where%
EOF;

    return array( 'e' => array(),
                  'sql' => $sql 
                );
}
[/dataview]
%%
%if_empty(records)%
    %return%
%end_if%

<? 

global $urec, $rssfeed, $rssimg, $feed, $atomfeed, $atomimg, $xspffeed, $xspfimg, $podimg;

$urec = $A['records']['0'];
$urec['fancy_user_name'] = '<b>' . $urec['fancy_user_name'] . '</b>';
$rssfeed  = $A['query-url'] . 'f=rss&';
$rssimg   = '<img src="' . $T->URL('images/feed-icon16x16.png') . '" />';
$podimg   = '<img src="' . $T->URL('images/menu-podcast.png') . '" />';
$atomfeed = $A['query-url'] . 'f=atom&';
$atomimg  = '<img src="' . $T->URL('images/feed-atom16x16.png') . '" />';
$xspffeed = $A['query-url'] . 'f=xspf&';
$xspfimg  = '<b>XSPF</b>';

?>
<style>
div.user_feeds {
    width: 690px;
    margin: 0px auto;
}

.keyhead {
    margin: 1px;
    font-weight: bold;
}
table.keytable {
    margin: 12px;
    border: 1px solid #777;
}
table.keytable td.keyimg {
    text-align: right;
}
table.keytable td {
    height: 13px; 
    color: #777;
}
table.linkstable td {
    padding-left: 5px;
}
</style>
<h1><?= $T->String(array('str_user_feed_title', $urec['fancy_user_name'])) ?></h1>
<div class="user_feeds">

<?
function gen_ufl($q,$title,$dopod=true)
{
    global $urec, $rssfeed, $rssimg, $atomfeed, $atomimg, $xspffeed, $xspfimg,$podimg;

    $utitle = urlencode( preg_replace('#</?b>#', '', $title ) );

    if( $GLOBALS['strings-profile'] == 'audio' )
    {
        $pod  = empty($dopod) ? '' : "<a href=\"{$rssfeed}{$q}{$urec['user_name']}&title={$utitle}\">{$podimg}</a>";
        $xspf = empty($dopod) ? '' : "<a class=\"small_button\" href=\"{$xspffeed}{$q}{$urec['user_name']}&title={$utitle}\"><span>{$xspfimg}</span></a>";
    }
    else
    {
        $pod = $xspf = '';
    }
    $atom = $dopod ? "<a href=\"{$atomfeed}{$q}{$urec['user_name']}&title={$utitle}\">{$atomimg}</a>" : '';

    $html =<<<EOF
 <tr>
    <td>{$pod}</td>
    <td>{$atom}</td>
    <td>{$xspf}</td>
    <td><a href="{$rssfeed}{$q}{$urec['user_name']}&title={$utitle}">{$rssimg}</a></td>
    <td>{$title}</td>
 </tr>
EOF;
    print $html;
}

print '<table class="linkstable">';
gen_ufl('u=', $T->String(array('str_user_feed_all_ups_by',$urec['fancy_user_name'])));
gen_ufl('sort=last_edit&u=', $T->String(array('str_user_feed_last_edit',$urec['fancy_user_name'])));
gen_ufl('tags=remix&u=', $T->String(array('str_user_feed_remixes_by',$urec['fancy_user_name'])));
gen_ufl('remixesof=', $T->String(array('str_user_feed_remixes_of',$urec['fancy_user_name'])));
gen_ufl('tags=trackback&u=', $T->String(array('str_user_feed_trackbacks',$urec['fancy_user_name'])));
gen_ufl('tags=trackback&remixesof=', $T->String(array('str_user_feed_remix_trackbacks',$urec['fancy_user_name'])));
gen_ufl('reccby=', $T->String(array('str_user_feed_reccby',$urec['fancy_user_name'])));
gen_ufl('datasource=topics&type=review&u=', $T->String(array('str_user_feed_reviews_by',$urec['fancy_user_name'])),false);
gen_ufl('datasource=topics&type=review&reviewee=', $T->String(array('str_user_feed_reviews_for',$urec['fancy_user_name'])),false);
gen_ufl('datasource=topics&thread=-1&u=',  $T->String(array('str_user_feed_topics',$urec['fancy_user_name'])), false);
print '</table>';

?>

<table class="keytable">
    <tr><td class="keyimg"><?= $rssimg ?></td><td><b>RSS</b> %text(str_syndication_feed)%</td></tr>
    <tr><td class="keyimg"><?= $atomimg ?></td><td><b>ATOM</b> %text(str_syndication_feed)%</td></tr>
<? if( $GLOBALS['strings-profile'] == 'audio' ) { ?>
    <tr><td class="keyimg"><?= $podimg ?></td><td><b>Podcast</b> %text(str_drag_this_link)%</td></tr>
    <tr><td class="keyimg"><a href="javascript://" class="small_button"><?= $xspfimg ?></a></td><td><b>XSPF</b> %text(str_playlist)%</td></tr>
<? } ?>
</table>
</div>

