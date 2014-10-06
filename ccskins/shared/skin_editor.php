<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template skin_editor -->
<?
function _t_skin_editor_edit_font_schemes(&$T,&$A) 
{
    $props = $A['field']['props'];
    $fid = $A['field']['name'];
    $value = empty($A['field']['value']) || !file_exists($A['field']['value']) ? $props[0]['id'] : $A['field']['value'];
    $scroll = empty($A['field']['scroll']) ? '' : 'overflow: scroll; height: 240px;';
    static $inst = 0;

    ++$inst;

    $class = 'skin_font_pick' . $inst;

    ?>
    <input type="hidden" name="<?= $fid ?>" id="<?= $fid ?>" value="<?= $value ?>"/>
    <div style="padding-left: 20px;border: 2px solid #999; width: 250px; <?=$scroll?>">
    <table cellpadding="0" cellspacing="0"><?
    
    $int = 1;

    foreach( $props as $P )
    {
        $file  = $P['id'];
        $id = 'efs_' . $inst . '_' . $int++;
        if( $value == $file )
            $val_id = $id;
        
        $text = file_get_contents($file);
        preg_match( '#{(.+)}#Ums', $text, $m);
        $caption = $T->String($P['desc']);
        ?>
        <tr >
            <td class="<?= $class?>" id="<?= $id?>" style="padding:0px;margin:0px;">
                <span style="padding:0px;margin:0px;font-style:normal;font-size: 14px;<?= $m[1]?>"><?= $caption?></span>
            </td>
            <script type="text/javascript">$('<?=$id?>').ref = '<?=$file?>';</script>
        </tr>
        <?
    }

    print '</table></div>';

    ?>
    </div>
    <script type="text/javascript">
        new ccSkinEditor('<?=$class?>','<?= $fid ?>','<?= $val_id ?>');
    </script>
    <?
    
}

function _t_skin_editor_edit_color_schemes(&$T,&$A) 
{
    $props = $A['field']['props'];
    $fid = $A['field']['name'];
    $value = empty($A['field']['value'])  || !file_exists($A['field']['value']) ? $props[0]['id'] : $A['field']['value'];
    $scroll = empty($A['field']['scroll']) ? '' : 'overflow: scroll; height: 240px;';
    static $inst = 1;

    $class = 'skin_colors_pick' . $inst;


    ?>
    <input type="hidden" name="<?= $fid ?>" id="<?= $fid ?>" value="<?= $value ?>"/>
    <div style="padding-left: 20px;border: 2px solid #999; width: 250px; <?=$scroll?>">
    <style type="text/css">table.ed td { height: 10px; width:20px; border-style:solid; border-width: 1px; }</style><?

    $int = 1;
    foreach( $props as $P )
    {
        $file = $P['id'];
        $text = file_get_contents($file);
        preg_match_all( '/\.([^\s{]+)[\s{]/U', $text, $m );
        $id = 'id_' . $int++;
        if( $value == $file )
            $val_id = $id;
        $markup = preg_replace( '#.*<style type="text/css">(.+)</style>#Ums', '$1', $text );
        $markup = preg_replace( '/\./', "#{$id} .", $markup);
        print '<br /><b>' . $T->String($P['desc']) . '</b><br />';
        print "<style type=\"text/css\">{$markup}</style><table class=\"{$class} ed\" id=\"{$id}\">";
        $rows = array_chunk($m[1],7);
        foreach( $rows as $row )
        {
            print '<tr>';
            foreach( $row as $col )
            {
                print "<td class=\"{$col}\">&nbsp;</td>";
            }
            print "</tr>\n";
        }        
        print '</table>';
        ?><script type="text/javascript">$('<?=$id?>').ref = '<?=$file?>';</script><?
    }

    ?>
    </div>
    <script type="text/javascript">
        new ccSkinEditor('<?=$class?>','<?= $fid ?>','<?= $val_id ?>');
    </script>
    <?
    
}

function _t_skin_editor_edit_layouts(&$T,&$A) 
{
    $props = $A['field']['props'];
    $fid = $A['field']['name'];
    $value = empty($A['field']['value'])  || !file_exists($A['field']['value']) ? $props[0]['id'] : $A['field']['value'];
    $scroll = empty($A['field']['scroll']) ? '' : 'overflow: scroll; height: 240px;';
    static $inst = 1;

    ?>
    <input type="hidden" name="<?= $fid ?>" id="<?= $fid ?>" value="<?= $value ?>"/>
    <div style="padding-left: 20px;border: 2px solid #999; width: 250px; <?=$scroll?>">
    <style type="text/css">#el td { vertical-align: top; padding:2px;} </style>
    <table>
    <?

    $class = 'skin_layout_pick_' . $inst;
    $int = 1;

    foreach( $props as $P )
    {
        $file = $P['id'];
        $id = 'esl' . $inst . '_' . $int++;
        if( $value == $file )
            $val_id = $id;
        if( empty($P['image']) )
        {
            ?><tr class="<?=$class?>" id="<?=$id?>" ><td></td><td><?= $T->String($P['desc'])?></td></tr><?
        }
        else
        {
            ?><tr class="<?=$class?>" id="<?=$id?>" >
                <td><img src="<?= $T->URL($P['image'])?>" /></td>
                <td><?= $T->String($P['desc'])?></td>
              </tr><?
        }

        ?><script type="text/javascript">$('<?=$id?>').ref = '<?=$file?>';</script>
        
        <?
    }

    ?>
    </table></div>
    <script type="text/javascript">
        new ccSkinEditor('<?=$class?>','<?= $fid ?>','<?= $val_id ?>');
    </script>
    <?

    ++$inst;
}

?>