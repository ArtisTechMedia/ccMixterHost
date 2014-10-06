<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- remix_search -->
<style type="text/css">
div#remix_search_controls {
}

#pool_select_contaner {
	display: inline;
}
#search_picks {
    border-top: 1px solid black;
    margin-top: 1em;
}
.remix_source_selected {
    color: green;
    font-weight: bold;
}
.remix_no_match {
    color: red;
    margin: 0.1em;
    font-style: italic;
}
#remix_search_toggle {
    margin: 5px;
}
#license_info a {
    font-weight: bold;
}

#remix_search_results {
}

</style>
<div style="width:40em;">
    <div id="remix_search_toggle" style="display:none">%if_not_null(field/close_box)%<a href="javascript: //toggle" 
        id="remix_toggle_link" class="small_button"><span>%text(str_remix_close)%</span></a>%end_if%</div>
    <div id="remix_search_controls" style="display:block">
        <select id="remix_search_type">
            %if_not_null(use_text_index)%
                <option value="search_remix_artist" selected="selected">%text(str_remix_artist)%</option>
                <option value="search_remix_title" >%text(str_remix_title)%</option>
                <option value="search_remix" >%text(str_remix_full_search)%</option>
            %else%
                <option value="search_remix_gen_artist" selected="selected">%text(str_remix_artist)%</option>
                <option value="search_remix_gen_title" >%text(str_remix_title)%</option>
                <option value="search_remix_gen" >%text(str_remix_full_search)%</option>
            %end_if%
        </select>
        <input type="edit" id="remix_search" />
        <div id="pool_select_contaner"></div>
        <a href="javascript://do search" class="small_buttton" id="do_remix_search"><span>%text(str_remix_do_search)%</span></a>
        <div class="remix_no_match" id="remix_no_match"></div>
    </div>
    <div id="remix_search_results">
    %if_not_null(field/remix_id)%
        <?  
            cc_query_fmt("dataview=links_by&t=remix_checks&f=html&noexit=1&nomime=1&ids=" . $A['field']['remix_id']); 
        ?>
    %end_if%
    %if_not_null(field/sourcesof)%
        <? 
            $qurl = "dataview=links_by&t=remix_checks&f=html&noexit=1&nomime=1&sources=" . $A['field']['sourcesof'];
            cc_query_fmt($qurl); 
            $qurl = "dataview=pool_item&t=remix_pool_checks&f=html&sort=&noexit=1&nomime=1&sources=" .$A['field']['sourcesof'];
            cc_query_fmt($qurl); 
        ?>
    %end_if%
    </div>
    <div id="remix_search_picks">
    </div>
    <div id="license_info_container">
        <div id="license_info" class="box">
        </div>
    </div>
</div>

<script src="%url('js/remix_search.js')%" type="text/javascript"></script>
<script type="text/javascript"> 
var pools = %query('t=pools&f=js')%;
%if_null(field/pool_id)%
var pool_id = 0;
%else%
var pool_id = %(field/pool_id)%;
%end_if%
new ccRemixSearch(<?= empty($A['use_text_index']) ? 'false' : 'true' ?>);
</script>
