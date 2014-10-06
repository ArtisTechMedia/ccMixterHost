<?

function _t_util_empty($T,$A)
{
}

function _t_util_format_signature($T,$A)
{
    print $T->String('str_from'). " <a href=\"{$A['root-url']}\">{$A['site-title']}</a>";
}

function _t_util_patch_stream_links(&$T,&$A)
{
    $stream_fg = $T->URL('images/player/hear-button-fg.gif');
    if( empty($stream_fg) )
    {
        $str = $T->String('str_stream');
        ?>
<script type="text/javascript">
$$('.cc_streamlink').each( function(e) { e.innerHTML = '<?=$str?>'; } );
</script>
        <?
    }
    else
    {
        $stream_bg = $T->URL('images/player/hear-button-bg.gif');
        ?>
<style type="text/css"> 
div.cc_list span { display:block; float: left; }
a.cc_streamlink { float: left; width: 23px; height: 23px; display:block; background: url('<?= $stream_bg ?>') top left no-repeat; }
a.cc_streamlink:hover { background: url('<?= $stream_fg ?>') top left no-repeat; text-decoration:none;}
</style>
        <?
    }
}

function _t_util_print_prompts(&$T,&$A)
{
    foreach( $A['prompts'] as $prompt )
    {
        ?><div class="cc_<?= $prompt['name'] ?>"><?= $T->String($prompt['value']) ?></div><?
    }
}


function _t_util_print_html_content(&$T,&$A)
{
    if( empty($A['html_content']) )
        return;

    foreach( $A['html_content'] as $html )
        eval( '?>' . $html); //print $html;
}

function _t_util_print_forms(&$T,&$A)
{
    foreach( $A['forms'] as $form_info )
    {
        $form = $form_info[1];
        $A['curr_form'] = $form;
        $T->Call($form_info[0]);
    }
}

function _t_util_disable_submit_button(&$T,&$A)
{
    ?>
<script type="text/javascript">
    if( $('form_submit') )
    {
        var do_disable = true;
        CC$$('.remix_checks').each( function(rc) {
                if( rc.checked )
                {
                    do_disable = false;
                }
            });
        $('form_submit').disabled = do_disable;
    }
</script>
    <?
}

function _t_util_hide_upload_form(&$T,&$A)
{
    $msg = '<h2 id="form_mask_msg">' . str_replace("\n", ' ', addslashes($T->String('str_uploading_msg'))) . '</h2>';
    $title = $T->String('str_uploading');
?>
<script type="text/javascript">
    var the_formMask = new ccFormMask(form_id,'<?= $msg ?>',true,'<?= $title ?>');
</script>
<?
}


function _t_util_print_bread_crumbs(&$T,&$A)
{
    if( empty($A['bread_crumbs']) )
        return;

    ?><div  class="cc_breadcrumbs"><?

    $carr103 = $A['bread_crumbs'];
    $cc103= count( $carr103);
    $ck103= array_keys( $carr103);
    for( $ci103= 0; $ci103< $cc103; ++$ci103)
    { 
        $A['crumb'] = $carr103[ $ck103[ $ci103 ] ];

        if ( !($ci103 == ($cc103-1)) ) 
        {
            ?><a  href="<?= $A['crumb']['url']?>"><span ><?= $T->String($A['crumb']['text'])?></span></a>  &raquo; <?
        }

        if ( $ci103 == ($cc103-1) )
        {
            ?><span ><?= $T->String($A['crumb']['text'])?></span><?
        }
    } 

    ?></div><?

}


function _t_util_print_client_menu(&$T,&$A)
{
    $btn = empty($A['use_buttons']) ? '' : 'class="small_button"';
    
    ?><link rel="stylesheet" type="text/css" href="<?= $T->URL('css/client_menu.css'); ?>" title="Default Style" /><?

    if( !empty($A['client_menu_help']) )
    {
        ?><div class="client_menu_help box"><?= $A['client_menu_help'] ?></div><?
    }
    print '<table class="client_menu_table">';

    foreach( $A['client_menu'] as $I )
    {        
        ?>
<tr><th>
        <? if( !empty($I['actions']) ) {
                foreach( $I['actions'] as $act )
                {
                    ?>
                      <a <?=$btn?> href="<?= $act['action'] ?>"><?= $T->String($act['menu_text']) ?></a>
                    <?
                }
            }
        
            if( !empty($I['action']) ) {
        ?>
            <a <?=$btn?> href="<?= $I['action'] ?>"><?= $T->String($I['menu_text']) ?></a>
        <?
            }
        ?>
    </th>
    <td>
        <?
            if( !empty($I['help']) ) {
                ?> <span class="hint"><?= $T->String($I['help']) ?></span> <?
            }
        ?>
    </td></tr>
<?    
    }

    print '</table>';
    if( !empty($A['client_menu_hint']) )
    {
        ?><div class="client_menu_hint"><?= $T->String($A['client_menu_hint']) ?></div><?
    }
}

/* this is deprecated - don't call */
function _t_util_prev_next_links(&$T,&$A) 
{
    print '<table id="cc_prev_next_links"><tr >';

    if ( !empty($A['prev_link'])) 
        print "<td ><a class=\"cc_gen_button\" href=\"{$A['prev_link']}\"><span >{$A['back_text']}</span></a></td>\n";

    print '<td  class="cc_list_list_space">&nbsp</td>';

    if ( !empty($A['next_link'])) 
        print "<td ><a class=\"cc_gen_button\" href=\"{$A['next_link']}\"><span >{$A['more_text']}</span></a></td>\n";

    print '</tr></table>';

} // END: function prev_next_links


function _t_util_ratings_stars(&$T,&$A)
{
    $R =& $A['record'];
    if( !empty($R['ratings']) )
    {
        foreach( $R['ratings'] as $rsize ) // 'half' 'full'
        {
            $src = 'images/stars/star-' . $rsize . '.gif';
            print '<img style="width:17px;height:17px;margin:0px;" src="' . $T->URL($src) . '" />';
        }
        print ' ' . $R['ratings_score'];
    }
}

function _t_util_ratings_stars_user(&$T,&$A)
{
    $R =& $A['record'];
    if( !empty($R['ratings']) )
    {
        $id = $R['upload_id'];
        if( $A['ajax'] )
            print '<div>'; 
        else
            print '<div id="rate_block_' . $id . '">';
        $i = 1; 
        if( empty($R['ratings']) )
        {
            // there are no existing, put out editable blank stars...

            print $T->String('str_rate') . ': ';
            for( $i = 1; $i < 6; $i++ )
            {
                $url = $T->URL('images/stars/star-empty.gif');
                $info = 'id="rate_star_' . $i . '_' . $id . '" class="rate_star"';
                print '<img '.$info.' style="width:17px;height:17px;margin:0px;" src="' . $url . '" />';
            }
        }
        else
        {
            // there's already a rating, put out stars, editable if legal to do so...

            foreach( $R['ratings'] as $rsize ) // 'half' 'full'
            {
                $src = 'images/stars/star-' . $rsize . '.gif';
                $url = $T->URL($src);
                $info = 'id="rate_star_' . $i . '_' . $id . '" class="rate_star"';
                print '<img '.$info.' style="width:17px;height:17px;margin:0px;" src="' . $url . '" />';
                ++$i;
            }
            print ' ' . $R['ratings_score'];
        }
        print '</div>';
    }
}

function _t_util_ratings_stars_small(&$T,&$A)
{
    $R =& $A['record'];
    if( !empty($R['ratings']) )
    {
        if( $A['ajax'] )
            print '<div>'; 
        else
            print '<div class="small_stars" id="rate_block_' . $R['upload_id'] . '">';
        foreach( $R['ratings'] as $rsize ) // 'half' 'full'
        {
            $src = 'images/stars/star-' . $rsize . '-s.gif';
            $url = $T->URL($src);
            print '<img style="width:10px;height:10px;margin:0px;" src="' . $url . '" />';
        }
        print ' ' . $R['ratings_score'];
        print '</div>';
    }
}

function _t_util_ratings_stars_small_user(&$T,&$A)
{
    $R =& $A['record'];
    if( !empty($R['ratings']) )
    {
        $id = $R['upload_id'];
        if( $A['ajax'] )
            print '<div>'; 
        else
            print '<div class="small_stars" id="rate_block_' . $id . '">';
        $i = 1; 
        if( empty($R['ratings']) )
        {
            // there are no existing, put out editable blank stars...

            print $T->String('str_rate') . ': ';
            for( $i = 1; $i < 6; $i++ )
            {
                $url = $T->URL('images/stars/star-empty-s.gif');
                $info = 'id="rate_star_' . $i . '_' . $id . '" class="rate_star"';
                print '<img '.$info.' style="width:10px;height:10px;margin:0px;" src="' . $url . '" />';
            }
        }
        else
        {
            // there's already a rating, put out stars, editable if legal to do so...

            foreach( $R['ratings'] as $rsize ) // 'half' 'full'
            {
                $src = 'images/stars/star-' . $rsize . '-s.gif';
                $url = $T->URL($src);
                $info = 'id="rate_star_' . $i . '_' . $id . '" class="rate_star"';
                print '<img '.$info.' style="width:10px;height:10px;margin:0px;" src="' . $url . '" />';
                ++$i;
            }
            print ' ' . $R['ratings_score'];
        }
        print '</div>';
    }
}

function _t_util_recommends(&$T,&$A)
{
    $R =& $A['record'];

    if( empty($A['ajax']) )
    {
        ?><div class="rated recommend_block" id="recommend_block_<?= $R['upload_id'] ?>"><?
    }
    
    print $T->String('str_recommends') . ' <span>(' . sprintf('%d',$R['upload_num_scores']) . ')</span>';

    if( empty($A['ajax']) )
    {
      ?></div><?
    }
}

?>
