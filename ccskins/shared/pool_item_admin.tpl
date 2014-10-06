<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

/*
[meta]
    type = template_component
    desc = _('Pool item (admin)')
    dataview = pool_item_admin
    embedded = 1
    datasource = pool_items
[/meta]
[dataview]
function pool_item_admin_dataview()
{
    $ccl = ccl('files') . '/';
    $cce = ccl('admin','poolitem','edit') . '/';
    $ccp = ccl('pools','item') . '/';

    $sql =<<<EOF
        SELECT pool_item_id, pool_item_url, pool_item_name, pool_item_download_url, pool_item_extra, 
               pool_item_artist, 
               CONCAT( '$cce', pool_item_id ) as item_edit_url,
               CONCAT( '$ccp', pool_item_id ) as item_view_url
        FROM cc_tbl_pool_item
        JOIN cc_tbl_pools ON pool_item_pool=pool_id
        %where% AND (pool_short_name = '%match%')
        %order%
        %limit%
EOF;

        $sql_count =<<<EOF
        SELECT COUNT(*)
        FROM cc_tbl_pool_item
        JOIN cc_tbl_pools ON pool_item_pool=pool_id
        %where% AND (pool_short_name = '%match%')
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                  'e'   => array( ) );
}
[/dataview]
*/

$tr = array( '<' => '&lt;', '>' => '&gt' );

?>
<!-- template pool_approvals -->
<style>
#inner_content {
    width: 750px;
    margin: 0px auto;
}
.cc_pool_approval_list table td {
    vertical-align: top;
    border-right: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
    padding: 1px;
}

.cc_pool_approval_list table th {
    border-bottom: 1px solid #444;
}

.em_code {
    color: #777;
    font-family: Courier New, serif;
}
.poster {
    float: right;
    text-align: right;
}
.fl {
    float: left;
    margin-right: 10px;
}
.pool_item_rec {
    margin-bottom: 4px;
    padding-top: 4px;
    border-top: 1px solid #999;
}
.butts {
    margin: 3px;
    float: left;
}

</style>

<div class="cc_pool_approval_list">
  %loop(records,r)%
  <?= $extra = unserialize($r['pool_item_extra']); ?>
  <div class="pool_item_rec" id="rec_%(#r/pool_item_id)%">
      <div class="butts">
        %if(is_admin)%
        <a href="%(#r/item_edit_url)%" id="edit_link_%(#r/pool_item_id)%" class="small_button"><span>edit</span></a>
        <a href="javascript://del item" id="del_link_%(#r/pool_item_id)%" class="small_button del_link"><span>delete</span></a>
        %end_if%
        <a class="small_button" href="%(#r/item_view_url)%"><span>view</span></a>
     </div>
     <div class="fl">%(#r/pool_item_artist)%</div>
     <div class="fl"><a href="%(#r/pool_item_url)%" target="_blank" >%(#r/pool_item_name)%</a></div>
     %if_not_null(#extra/ttype)%
         <div class="fl poster">
            <?= _('Poster') ?>: <a href="mailto:%(#extra/email)%">%(#extra/poster)%</a>
         </div>
         <div class="fl">
            <i><?= _('Type') ?>: %(#extra/ttype)%</i>
         </div>
     %end_if%
      <div style="clear:both">&nbsp;</div>
  </div>
  %end_loop%
</div>

%call(prev_next_links)%
<script type="text/javascript">
poolItemAdmin = Class.create();

poolItemAdmin.prototype = {

    initialize: function() {
        var me = this;
        $$('.del_link').each( function(e) {
            var id = e.id.match(/[0-9]+$/);
            Event.observe(e,'click',me.onDelete.bindAsEventListener(me,id));
        });
    },

    onDelete: function(event,id) {
        var e = $('rec_' + id);
        e.style.display = 'none';
        var url = home_url + 'admin/poolitem/delete/' + id;
        new Ajax.Request( url, { method: 'get' , onComplete: this._req_delete.bind(this) } );
    },

    _req_delete: function(json) {
    }
}

new poolItemAdmin();

</script>
