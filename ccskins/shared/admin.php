<?

function _t_admin_print_admin_menu(&$T,&$_TV)
{
    $menu = $_TV['admin_menu'];

    if( empty($menu['do_local']) )
    {
        $_TV['client_menu_help'] = $menu['global_help'];
        $_TV['client_menu'] = $menu['global_items'];
    }
    else
    {
        $help = $menu['local_help'] . ' <select id="vroot_selector">';
        $vroots = $menu['config_roots'];
        foreach( $vroots as $VR )
        {
            $selected = $VR['selected'] ? 'selected="selected" ' : '';
            $help .= "<option value=\"{$VR['cfg']}\" $selected>{$VR['text']}</option>\n";
        }
        if( empty($menu['delete_url']) )
        {
            $del_button = '';
        }
        else
        {
            $del_button = '<br /><br /><a class="small_button" href="' . $menu['delete_url'] . '">' . _('Delete this virtual root') . '</a>';
        }
        $_TV['client_menu_help'] = $help . "</select>" . $del_button;
        $_TV['client_menu_hint'] = $menu['local_hint'];
        $_TV['client_menu']      = $menu['local_items'];
        $_TV['end_script_blocks'][] = 'admin.php/print_admin_menu_hook';
    }

    $T->Call('print_client_menu');
}

function _t_admin_print_admin_menu_hook()
{
    ?>
<script type="text/javascript">
function vroot_hook()
{
    var e = $('vroot_selector');
    var cfg = e.options[ e.selectedIndex ].value;
    window.location.href = root_url + cfg + '/admin/site/local';
}
Event.observe( 'vroot_selector', 'change',  vroot_hook );

</script>
    <?
}


function _t_admin_show_activity_menu($T,&$A) {
  
?><table >
<tr >
<td><a class="cc_gen_button" href="<?= $A['home-url']?>activity"><span ><?= _('View Current')?></span></a></td>
<td><a class="cc_gen_button" href="<?= $A['home-url']?>activity/archive"><span ><?= _('View Archive')?></span></a></td>
<?

  if ( !empty($A['activity_menu']['full'])) {
  
?><td><a class="cc_gen_button" href="<?= $A['home-url']?>activity/export"><span ><?= _('Export')?></span></a></td>
<td><a class="cc_gen_button" href="<?= $A['home-url']?>activity/clear"><span ><?= _('Export and Clear')?></span></a></td>
<?
} // END: if
    
?><td><a class="cc_gen_button" href="<?= $A['home-url']?>activity/admin"><span ><?= _('Options')?></span></a></td>
<td style="width: 300px;"><form  method="get" action="<?= $A['home-url']?>activity" id="actsearch" style="position:relative;left:1px;top:1px;border: 1px solid black;height: 30px;width:250px; background-color: #CCC;">
<?

  if( !empty($A['act-searchterm']) ) { $A['st'] = $A['act-searchterm']; } else {  $A['st'] = null; } 
?><input  name="user" id="user" value="<?= $A['st']?>" style="font-size:11px;font-family:Verdana;position:absolute; left: 6px; top:6px; width: 100px;"></input>
<a style="position:absolute; left: 120px; top: 4px; width:100px;" class="cc_gen_button" href="javascript: $('actsearch').submit();"><span ><?= _('Search')?></span></a></form></td>
</tr>
</table>
<?
} // END: function show_activity_menu
  

//------------------------------------- 
function _t_admin_show_activity_log($T,&$A) {
  
?><style >
  .cc_log_view td
  {
     white-space: nowrap; 
     padding-right: 1.4em;
  }
  
  td.vlinktd {
    border-top: 1px solid white;
    border-left: 1px solid white;
    border-right: 3px solid white;
    border-bottom: 1px solid white;
    background: #99F;
    padding: 0px;
  }
  
  a.vlink {
    color: white;
    text-decoration: none;
    padding: 0px;
    margin: 0px;
  }
  
  a.vlink:hover {
    text-decoration: none;
    background: black;;
  }
  
  </style>
<table  class="cc_log_view" cellspacing="0" cellpadding="0">
<tr ><th >Date</th><th  colspan="2">IP</th><th  colspan="2">User</th><th >Event</th><th ></th><th ></th></tr>
<?
    $ipurl = empty($A['ip_lookup_url']) ? '' : $A['ip_lookup_url'];

    foreach( $A['activity_log'] as $e )
    { 
?><tr >
<td><?= CC_datefmt($e['activity_log_date'],'m-d-y h:ia')?></td>
<td class="vlinktd">
     <?
        if( empty($ipurl)) {
            print '&nbsp;&nbsp;';
        }
        else {
            $thisipurl = str_replace( '%IP%', $e['activity_log_ip'], $ipurl );
            print "<a href=\"{$thisipurl}\">IP</a>";
        }
    ?>
</td>
<td>
<a href="<?= $A['activity-url']?>?ip=<?= $e['activity_log_ip']?>"><?= $e['activity_log_ip']?></a></td>
<td class="vlinktd"><?

    if ( !empty($e['activity_log_user_name'])) {
    
?><a class="vlink" href="<?= $A['home-url']?>people/<?= $e['activity_log_user_name']?>">view</a><?
} // END: if
      
?></td><td><?

    if ( !empty($e['activity_log_user_name'])) {
    
?><a href="<?= $A['activity-url']?>?user=<?= $e['activity_log_user_name']?>"><?= $e['activity_log_user_name']?></a><?
} // END: if
      
?></td>
<td><b ><?= $e['activity_log_event']?></b></td>
<td><?= $e['activity_log_param_2']?></td>
<td><?= $e['activity_log_param_1']?></td>
<td><?= $e['activity_log_param_3']?></td>
</tr><?
} // END: for loop
    
?></table>
<?
$T->Call($A['prev_next_links']);
  } // END: function show_activity_log

?>