<!-- template howididit -->
<? 
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

cc_query_fmt('f=html&noexit=1&nomime=1&t=list_files&ids=' . $A['howididit_upload']); ?>
<div class="cc_howididit"><?
      $info = $A['howididit_info'];
      $arr = $A['howididit_fields'];
      $c = count($arr);
      $k = array_keys($arr);
      for( $i = 0; $i < $c; $i++ )
      {
          $fkey = $k[$i];
          $head = $arr[ $fkey ];
          if( !empty($info[$fkey]) )
          {
              ?><div class="box">
                  <h2><?= $T->String($head['label']) ?></h2>
                  <p><?= $info[$fkey] ?></p>
                </div>
              <?
          }
      }
    ?>
</div>
