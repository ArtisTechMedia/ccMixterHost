<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: ajax_menu.php 12990 2009-07-19 21:19:00Z fourstones $
*
*/
/*

[meta]
    type = ajax_component
    desc  = _('Return a menu for an upload')
    dataview = upload_menu
    valid_args = ids
[/meta]

This is used for an ajax callback for just a menu on a record

no download/play/show stuff, just actions (review, edit, share, etc.)

N.B. Originally, the URLs (actions) in this structure were used directly (and
in several cases, still, are used that way. However, there are now several
other cases where the presense of a URL is simply used as a flag to engage
the AJAX version of the feature.

----------------------------------
    Menu layout ?
------------------------------------

    [play] => Array
            [stream] => Array
                    [menu_text] => Stream
                    [weight] => -1
                    [group_name] => play
                    [id] => cc_streamfile
                    [access] => 4
                    [action] => http://cch5.org/media/files/stream/Transistor_Karma/11706.m3u
                    [type] => 

    [download] => Array
            [1] => Array
                    [action] => http://cch5.org/people/
                    [menu_text] => mp3  (3.44MB)
                    [group_name] => download
                    [type] => audio/mpeg
                    [weight] => 1
                    [tip] => Transistor_Karma_-_The_Waterpipe_Aria_from_Ariane_and_Barbecue_a_remix_opera.mp3
                    [access] => 4
                    [id] => cc_downloadbutton

    [share] => Array
            [share_link] => Array
    [comment] => Array
            [comments] (Write Review)
    [owner] => Array
            [editupload] => Array
            [managefiles] => Array
            [manageremixes] => Array
    [admin] => Array
            [publish] => Array  (could be under owner)
            [deleteupload] => Array
            [howididit] => Array
            [editorial] => Array
            [ban] => Array
            [uploadadmin] => Array
    [playlist] => Array
            [playlist_menu] => Array

*/
?>
<script src="/ccskins/shared/js/prototype.js" ></script>

<!-- template ajax_menu -->
<?

if( !empty($A['records']) )
    $R =& $A['records'][0];
else
    if( !empty($A['record']) )
        $R =& $A['record'];
    else
        return;

$menu =& $R['local_menu'];

?>

<table><tr>

<td style="vertical-align:top;padding-right:12px;">

<?
    /** SHARE ******/
    
    if( !empty($menu['share']['share_link']) )
    {
        ?>
          <div class="box">
        <?
          $menu['share']['share_link']['menu_text'] = '<img src="'. $T->URL('images/share-link.gif') . '" />';
          helper_ajax_menu_item($menu['share']['share_link'],$T,false);
        ?>
          </div><!-- .box share -->
        <?
    }
    

/* INSTA REVIEW */

    if( !empty($R['upload_extra']['num_reviews']) || !empty($menu['comment']['comments']) )
    {
        ?>
            <div class="box">
               <h3 style="margin: 0 auto 3px auto"><?= $T->String('str_reviews') ?></h3>
               <br />
               <div id="review_<?= $R['upload_id']; ?>">
                   <?
                       if( !empty($menu['comment']['comments']) )
                       {
                           ?>
                               <span id="instareview_btn_<?= $R['upload_id']; ?>"></span>
                           <?
                       }
                   
                       if( !empty($R['upload_extra']['num_reviews']) )
                       {
                           $revurl = ccl('reviews',$R['user_name'],$R['upload_id']);
                         ?>
                           <a class="small_button" href="<?= $revurl ?>"><?= $T->String('str_read_all') ?> (<?= ($R['upload_extra']['num_reviews']); ?>)</a> 
                         <?
                       }
                   ?>
                &nbsp;<br style="clear:both" />
                </div><!-- #review_[upload_id] -->
            </div> <!-- box -->
        <?

    } // insta review
    

    /* RECOMMENDS/RATINGS */


    if( $R['ratings_on'] ) {
        print '<div class="box" id="action_recommends">';
        
        $A['record'] = $R;
        if( $R['is_thumbs_up'] ) {
            $T->Call('util.php/recommends');
        }
        else {
            $T->Call('util.php/ratings_stars_small_user');
        }
        if( !empty($A['logged_in_as']) )
        {
            ?>
            <script type="text/javascript">
                null_star = '<?= $T->URL('images/stars/star-empty-s.gif'); ?>';
                full_star = '<?= ('images/stars/star-red-s.gif') ?>';
                rate_return_t = 'ratings_stars_small_user';
                recommend_return_t = 'recommends';
                new userHookup('upload_list', 'ids=<?= $R['upload_id'] ?>&limit=1','action_recommends');
            </script>
            <?
        }
        
        print '&nbsp;<br style="clear:both" /></div><!-- box -->';
        
    } // ratings enabled

   
    if( !empty($menu['owner']) || !empty($menu['admin']) || !empty($menu['editorial']) )
    {
        ?>
           <div class="box" id="ownermenubox">
            <ul>
        <?

            /** OWNER stuff *****/
            
            if( !empty($menu['owner']) )
            {
                foreach( $menu['owner'] as $mi )
                    helper_ajax_menu_item($mi,$T);
            }
            
            
            /** ADMIN menu *****/
            
            if( !empty($menu['admin']) )
            {
                foreach( $menu['admin'] as $mi )
                    helper_ajax_menu_item($mi,$T);
            }
            
            /** Editors menu *****/
            
            if( !empty($menu['editorial']) )
            {
                foreach( $menu['editorial'] as $mi )
                    helper_ajax_menu_item($mi,$T);
            }
    
            ?>
              </ul>
              </div><!-- .box #ownmenubox -->
            <?
    }
    ?>
    </td>

    <td style="vertical-align:top;padding-right:12px;width:30%">
<?        

/** TRACKBACK menu *****/

$str = sprintf($T->String('str_list_i_saw_this'), '"' . $R['upload_name'] . '"');

?>
<div id="trackbackbox">

 <div class="box">
  <h2 style="margin-top:0px;"><?= $T->String('str_list_trackback') ?></h2>

  <p id="trackback_caption"><?= $str ?></p>
  <ul>
  
  <?
        $mi = array();
        $mi['action'] = 'javascript:// noted';
        $saws = array( array( 'remix',    $T->String('str_trackback_type_remix')),
                       array( 'video',    $T->String('str_trackback_type_video')),
                       array( 'web',      $T->String('str_trackback_type_web')),
                       array( 'album',    $T->String('str_trackback_type_album'))
                        );
        
        if( !empty($GLOBALS['strings-profile']) && ($GLOBALS['strings-profile'] == 'audio' ) )
        {
            $saws[] = array( 'podcast',  $T->String('str_podcast'));
        }
        
        $url = "upload_trackback('{$R['upload_id']}', '";
        foreach( $saws as $saw )
        {
            $mi['menu_text'] = $saw[1];
            $mi['onclick'] = $url . $saw[0] . "');";
            helper_ajax_menu_item($mi,$T);
        }
    ?>

    </ul>
  </div><!-- box -->
</div><!-- trackback box -->
    
</td>
    
<td style="vertical-align:top;padding-right:12px;">
    
<?

/** PLAYLIST menu *****/

    if( !empty($menu['playlist']['playlist_menu']) )
    {
        // actually we're going to embed the thing right here...

        ?>
          <div class="box plblock" id="am_pl_menu" style="float:left">
            <h2 style="margin-top:0px;"><?= $T->String('str_playlists'); ?></h2>
        <?
            $A['args'] = $menu['playlist']['playlist_menu']['mi'];
            $T->Call('playlist_2_menu');
        ?>
          </div>
    
    </td>
</tr>
</table>

&nbsp;
<br style="clear:both" />

<script type="text/javascript">
    function pl_item_cb(resp,json)
    {
        this.parentNode.innerHTML = json.message ? eval(json.message) : eval(json);
    }
    function pl_item_action(event,url)
    {
        new Ajax.Request( url, { method: 'get', onComplete: pl_item_cb.bind(this) } );
        Event.stop(event);
        return false;
    }
    CC$$('a.pl_menu_item',$('am_pl_menu')).each( function(e) {
        var url = e.href;
        e.href = 'javascript:// playlist goo';
        Event.observe( e, 'click', pl_item_action.bindAsEventListener(e,url) );
    });
</script>

<?

}



function helper_ajax_menu_item(&$item,&$T,$use_li=true) 
{
    if( $use_li )
    {
        if( empty($item['parent_id']) )
            print '<li>';
        else
            print "<li id=\"{$item['parent_id']}\">";
    }
    
    if( !empty($item['pre']) )
        print $item['pre'];

    print '<a ';

    $attrs = array( 'action' => 'href', 
                    'tip'    => 'title',
                    'id'     => 'id',
                    'class'  => 'class',
                    'type'   => 'type',
                    'onclick'=> 'onclick' );

    foreach( $attrs as $K => $V )
        if( !empty($item[$K]) )
            print "$V=\"{$item[$K]}\" ";

    print '>';
    
    if( !empty($item['menu_text']) )
        print $T->String($item['menu_text']);
    
    print '</a>';
    
    if( $use_li )
       print "</li>\n";
}


?>
