<?

if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template tags -->
<?
//------------------------------------- 
function _t_tags_taglinks(&$T,&$A) 
{
    $tags = $A['tag_array'];
    $c = count( $tags );
    $k = array_keys( $tags );
    for( $i = 0; $i < $c; ++$i )
    { 
       $A['tag'] = $tags[ $k[ $i ] ];
       
        ?><a href="<?= $A['tag']['tagurl']?>" rel="tag" class="taglink"><?= $A['tag']['tag']?></a><?
        if ( !($i == ($c-1)) ) { ?>, <? }
    }
}

//------------------------------------- 
function _t_tags_taglinks_str(&$T,&$A) 
{
    $tags = split(',',$A['tag_str']);
    $urlbase = $A['tag_urlbase'];
    $c = count( $tags );
    $k = array_keys( $tags );
    for( $i = 0; $i < $c; ++$i )
    { 
       $tag = $tags[ $k[ $i ] ];
       
        ?><a href="<?= $urlbase . $tag ?>" rel="tag" class="taglink"><?= $tag ?></a><?
        if ( !($i == ($c-1)) ) { ?>, <? }
    }
}

//------------------------------------- 
function _t_tags_popular_tags(&$T,&$A) 
{
    print '<span id="' . $A['field']['name'] . '">';
    $tags = $A['field']['tags'];
    $c = count( $tags );
    $k = array_keys( $tags );
    for( $i = 0; $i < $c; $i++ )
    { 
       $A['tag'] = $tags[ $k[$i] ];
       ?><a href="javascript://popular" onclick="cc_add_tag('<?= $A['tag']?>','<?= $A['field']['target']?>');" class="taglink"><?= $A['tag']?></a><?
       if ( !($i == ($c-1)) ) 
           { ?>, <? }
     }
     print '</span>';
}
  
//------------------------------------- 
function _t_tags_tag_picker(&$T,&$A) 
{
    $T->Call('tag_filter');
    $target = ccl('tags') . '/';
    $fetch = url_args( ccl('browse'), 'related='.$A['tagstr']);
?>
<script>
new ccTagFilter( { url: '<?=$fetch?>', 
                   target_url: '<?=$target?>',
                   tags: '<?=$A['tagstr']?>' } );
</script>
<?
}

function _t_tags_tags(&$T,&$A) 
{
    ?><div  class="cc_tag_switch_link"><?= $A['tag_switch_link']?></div><?

    $tags =& $A['tag_array'];
    $c = count( $tags );
    $k = array_keys( $tags );
    for( $i = 0; $i < $c; ++$i )
    { 
       $A['tag'] = $tags[ $k[ $i ] ];
       
        ?><span  class="cc_tag_count">
        <a href="<?= $A['tag']['tagurl']?>" rel="nofollow tag" class="taglink" style="line-height:110%;font-size:<?= $A['tag']['fontsize']?>px"><?= $A['tag']['tags_tag']?></a> (<?= $A['tag']['tags_count']?>)</span><?
        if ( !($i == ($c-1)) ) { ?>, <? }
    }
    
    ?><p class="cc_tag_bottom">&nbsp;</p><?
}

function _t_tags_suggested_picker(&$T,&$A)
{
    d($A);
}

?>
