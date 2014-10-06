<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_file_macros_print_howididit_link(&$T,&$A)
{
    ?>
        <table><tr><td><a class="cc_gen_button" href="<?= ccl('howididit',$A['record']['upload_id']) ?>"><span><?= $T->String('str_how_i_did_it') ?></span></a></td></tr></table><?
}

function _t_file_macros_print_num_playlists(&$T,&$A)
{
    $ccb = url_args( ccl('playlist','browse'), 'upload=' .$A['record']['upload_id'] );
    $text = $T->String( array('str_pl_found_in_d',
                              '<a href="' . $ccb . '">',
                              $A['record']['upload_num_playlists'],
                              '</a>' ) );

    ?>
    <div class="upload_num_playlists_link" style="text-align: right;"><?= $text ?></div>
    <?
}


function _t_file_macros_license_rdf(&$T,&$A)
{
  ?><!-- 
<rdf:RDF xmlns="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
<Work rdf:about="">
  <dc:title><?= str_replace('--','',$A['record']['upload_name']) ?></dc:title>

  <dc:date><?= $A['record']['year'] ?></dc:date>
  <dc:description><?= htmlentities($A['record']['upload_description_html']) ?></dc:description>
  <dc:creator><Agent><?= $A['record']['user_real_name']?></Agent></dc:creator>
  <dc:rights><Agent><?= $A['record']['user_real_name']?></Agent></dc:rights>
<?  if ( !empty($A['record']['dcmitype'])) 
    {
          ?>  <dc:type rdf:resource="http://purl.org/dc/dcmitype/<?= $A['record']['dcmitype']?>"></dc:type>
<?  }
    if ( !empty($A['record']['remix_parents'])) 
    {
        foreach( $A['record']['remix_parents'] as $parent )
        {
            ?>  <dc:source resource="<?= $parent['file_page_url']?>"></dc:source> 
<?
        }
    }
    
    ?>  <license rdf:resource="<?= $A['record']['license_url']?>"></license>
</Work>
<?
        if ( !empty($A['record']['files']['0']['file_extra']['sha1'])) 
        {
            ?><Work rdf:about="urn:sha1:<?= $A['record']['files']['0']['file_extra']['sha1']?>">
  <license rdf:resource="<?= $A['record']['license_url']?>"></license>
</Work>
<?
        }
?><License rdf:about="<?= $A['record']['license_url']?>">
<?

    if ( !empty($A['record']['license_permits'])) 
    {
        $pts = CC_split_tags($A['record']['license_permits']);
        foreach( $pts as $pt )
        {
            ?>  <permits rdf:resource="http://creativecommons.org/ns#<?= $pt ?>"></permits>
<?
        }
    }
    if ( !empty($A['record']['license_required'])) 
    {
        $pts = CC_split_tags($A['record']['license_required']);
        foreach( $pts as $pt )
        {
            ?>  <requires rdf:resource="http://creativecommons.org/ns#<?= $pt ?>"></requires>
<?
        }
    }
    
    if ( !empty($A['record']['license_prohibits'])) 
    {
        $pts = CC_split_tags($A['record']['license_prohibits']);
        foreach( $pts as $pt )
        {
            ?>  <prohibits rdf:resource="http://web.resource.org/cc/<?= $pt ?>"></prohibits>
<?
        }
    }
?></License> 
</rdf:RDF>
--><?
}

function _t_file_macros_show_nsfw(&$T,&$A)
{
    print '<p id="nsfw">' . $T->String(array('str_nsfw_t','<a href="http://en.wikipedia.org/wiki/NSFW">','</a>')) . '</p>';
}


function _t_file_macros_show_zip_dir(&$T,&$A)
{
    $R =& $A['record'];
    foreach( $R['zipdirs'] as $zip )
    {
        ?><p class="zipdir_title"><?= $T->String('str_zip_title') ?>: <span><?= $zip['name'] ?></span></p>
            <ul class="cc_zipdir"><?
        foreach( $zip['dir']['files'] as $F )
        {
            ?><li><?=$F?></li><?
        }
        ?></ul><?
    }
}

function _t_file_macros_request_reviews(&$T,&$A)
{

    ?><div id="requested_reviews"><?
        cc_query_fmt('noexit=1&nomime=1&f=html&t=reviews_preview&sort=topic_date&match=' . $A['record']['upload_id'] );
    ?></div><?

    $ratings_opts = cc_get_config('chart');
    if( !empty($ratings_opts['thumbs_up']) )
    {
        ?><div id="recommended_by"><?
            cc_query_fmt('noexit=1&nomime=1&f=html&t=recc&match=' . $A['record']['upload_id'] );
        ?></div><?
    }

}

function _t_file_macros_print_recent_reviews(&$T,&$A)
{
    ?>
        <p class="recent_reviews"><?= $T->String('str_recent_reviews') ?></p>
          <ul id="recent_reviews">
    <?
    foreach( $A['posts'] as $post )
    {
        $text = CC_strchop($post['post_text'],50);
        print "<li><span class=\"poster_name\">{$post['username']}</span> <a href=\"{$post['post_url']}\">{$text}</a></li>\n";
    }
    ?></ul>
    <a class="cc_gen_button" style="width: 25%" href="<?= $A['view_topic_url'] ?>"><span><?= $T->String('str_read_all') ?></span></a><?
}

function _t_file_macros_upload_not_published(&$T,&$A)
{
    print "<div class=\"unpublished\">{$A['record']['publish_message']}</div>";
}

function _t_file_macros_upload_banned(&$T,&$A) 
{
    print "<div class=\"upload_banned\">{$A['record']['banned_message']}</div>";
}

?>
