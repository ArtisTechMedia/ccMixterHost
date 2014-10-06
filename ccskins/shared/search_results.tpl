<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template search_results -->
%macro(search_results_all)%
<link rel="stylesheet" type="text/css" href="%url(css/search.css)%" title="Default Style" />
%if_not_null(search_miss_msg)%
    <div class="search_miss">%text(search_miss_msg)%</div>
%end_if%
%loop(search_results_meta,M)%
<div class="search_result_block">

    <div class="search_result_title">%text(str_results_from)%: %text(#M/meta/title)%</div>
    %if_not_null(#M/total)%
        <div class="search_result_count"><?= $T->String(array('str_search_found_d','<span>'.$M['total'].'</span>')); ?></div>
        %(#M/results)%
        %if_not_null(#M/more_results_link)%
            <div class="search_more_links"><a href="%(#M/more_results_link)%">%text(str_search_more)%<!-- --> %text(#M/meta/title)%</a>...</div>
        %end_if%
    %else%
        <div class="search_result_count"><!-- -->%text(str_search_no_matches)% <!-- -->%text(#M/meta/title)%</div>
    %end_if%
</div>
%end_loop%
%end_macro%


%macro(search_results_head)%
<link rel="stylesheet" type="text/css" href="%url(css/search.css)%" title="Default Style" />
%if_not_null(search_miss_msg)%
    <div class="search_miss">%text(search_miss_msg)%</div>
%end_if%
%if_not_null(search_result_viewing)%
    <div class="search_result_count">%text(search_result_viewing)%</div>
%end_if%
%end_macro%