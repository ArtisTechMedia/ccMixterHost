<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?><!-- template publicize --><?


//------------------------------------- 
function _t_publicize_publicize(&$T,&$A) 
{

    print '<link rel="stylesheet"  type="text/css" title="Default Style" href="'.$T->URL('css/publicize.css').'" />';

  
    if ( !empty($A['PUB']['intro'])) 
    {
  
        ?><div  id="pubintro"><?

        if ( !empty($A['PUB']['user_avatar_url'])) 
        {
            ?><img  src="<?= $A['PUB']['user_avatar_url']?>" /><?
        } // END: if
          
        ?><div ><?= $T->String($A['PUB']['intro']) ?></div>
        </div><?
    } // END: if
    
    ?><div id="pubinstructions">
        <p><?= $T->String($A['PUB']['step1']) ?></p>
     </div>
    <form  id="puboptions_form">
    <table  id="puboptions1">
<?

    foreach( $A['PUB']['combos'] as $combo )
    { 
        ?><tr><th><?= $T->String($combo['title']) ?><?

        if ( !empty($combo['help'])) 
        {
            ?><span class="pubhelp"><?= $T->String($combo['help']) ?></span><?
        } // END: if
      
        ?></th><td><select  id="<?= $combo['id']?>" name="<?= $combo['name']?>" class="<?= $combo['class']?>"><?

        foreach( $combo['opts'] as $opt )
        {
            if ( !empty($opt['selected'])) 
            {
                ?><option  value="<?= $opt['value']?>" selected="selected"><?= $T->String($opt['text']) ?></option><?
            } // END: if
            else
            {
                ?><option  value="<?= $opt['value']?>"><?= $T->String($opt['text']) ?></option><?
            } // END: if
        } // END: for loop
      
        ?></select></td></tr><?
    } // END: for loop
    
    ?></table><?

    foreach( $A['PUB']['hiddens'] as $hide )
    { 
        ?><input  type="hidden" value="<?= $hide['value']?>" name="<?= $hide['name']?>" id="<?= $hide['name']?>"></input><?
    } // END: for loop
    
    ?><span  id="type_target"></span>
    </form>

    <div  id="pubinstructions">
        <p><?= $T->String($A['PUB']['step2']) ?></p>
    </div>

    <p id="target_text_p">
        <textarea  id="target_text" name="target_text">
        </textarea>
    </p>

    <div  id="preview_container" class="light_color med_bg dark_border">
        <table  id="seehtml">
        <tr ><td ><a  href="javascript://toggle preview" id="preview_button_link" class="cc_gen_button">
        <span  id="preview_button"><?= $T->String($A['PUB']['seehtml']) ?></span></a></td></tr>
        </table>

        <b ><?= $T->String($A['PUB']['preview']) ?>:</b>
        <p  id="preview_warn" style="display:block"><?= $T->String($A['PUB']['previewwarn']) ?></p>
        <p  id="html_warn" style="display:none"><?= $T->String($A['PUB']['htmlwarn']) ?></p>
        <div  id="preview_block">
            <div  id="preview" style="display:block"><span >&nbsp;</span>
            </div>
            <div  id="src_preview" style="display:none;">
            </div>
        </div>
    </div>
<script  type="text/javascript" src="<?= $T->URL('js/publicize.js') ?>"></script>
<script type="text/javascript">
  //<!--
  embed_templates = [ '<?= join("', '",$A['PUB']['embedded_templates']) ?>' ];
  seeHTML = '<?= addslashes($T->String($A['PUB']['seehtml'])) ?>';
  showFormatted = '<?= addslashes($T->String($A['PUB']['showformatted'])) ?>';
  username = '<?= $A['PUB']['user_name']?>';
  new ccPublicize('<?= $A['PUB']['user_name']?>');
  //-->
</script>
<?
} // END: function publicize
  
?>