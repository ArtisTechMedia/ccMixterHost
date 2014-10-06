<? 
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
    desc = _('Browse Collaborations')
    datasource = collabs
    dataview = browse_collabs
    embedded = 1
[/meta]
[dataview]
function browse_collabs_dataview()
{
    $sql =<<<EOF
        SELECT * 
        FROM cc_tbl_collabs 
        %where% AND collab_confirmed = 1
        %order%
        %limit%
EOF;

    $sql_count =<<<EOF
        SELECT COUNT(*) FROM cc_tbl_collabs WHERE collab_confirmed = 1
EOF;

    return array( 'e' => array(CC_EVENT_FILTER_COLLAB_CREDIT),
                  'sql' => $sql,
                  'sql_count' => $sql_count );
}
[/dataview]
*/ ?>
 
<!-- template collab_main -->
<style>
#inner_content {
    width: 85%;
    margin: 0px auto;
}
</style>

<? $rows = array_chunk($A['records'], 2); ?>

<link  rel="stylesheet" type="text/css" href="%url( 'css/playlist.css' )%" title="Default Style"></link>
<link  rel="stylesheet" type="text/css" href="%url( 'css/collab.css' )%" title="Default Style"></link>
<table  style="width:78%; margin: 0px auto;">
%loop(#rows,cols)%
<tr>
  %loop(#cols,R)%
  <td  style="vertical-align: top;width:50%;">
    <div  class="collab_entry">
    <span  class="collab_date"><?= CC_datefmt($R['collab_date'],'M d, Y');?></span>
    <a  class="collab_name" href="%(home-url)%collab/%(#R/collab_id)%">%chop(#R/collab_name,35)%</a>
    <br  />
    %loop(#R/collab_users,u)%
        <a  class="cc_user_link" href="%(#u/artist_page_url)%">%(#u/user_real_name)%</a>%if_not_last(#u)%, %end_if%
    %end_loop%
    <br  style="clear:both" />
    </div>
  </td>
  %end_loop%
</tr>
%end_loop%
</table>
%call('prev_next_links')%
