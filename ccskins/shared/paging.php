<?

function _t_paging_prev_next_links(&$T,&$A) 
{
    if( empty($A['paging_stats']) )
        return;
        
    $stats = $A['paging_stats'];
    
    if( empty($stats['paging']) )
        return;
        
    print '<table id="cc_prev_next_links"><tr >';

    if ( !empty($stats['prev_link'])) 
        print "<td ><a class=\"cc_gen_button\" href=\"{$stats['prev_link']}\"><span >{$T->String('str_pagination_prev_link')}</span></a></td>\n";

    print '<td  class="cc_list_list_space">&nbsp</td>';

    if ( !empty($stats['next_link'])) 
        print "<td ><a class=\"cc_gen_button\" href=\"{$stats['next_link']}\"><span >{$T->String('str_pagination_next_link')}</span></a></td>\n";

    print '</tr></table>';

} // END: function prev_next_links

function _t_paging_google_nostyle(&$T,&$A) 
{
    _goog_style($A,$T,false,true,false);
}

function _t_paging_google_style_paging(&$T,&$A) 
{
    _goog_style($A,$T,true,true,true);
}

function _t_paging_google_style_paging_ul(&$T,&$A) 
{
    _goog_style($A,$T,true,false,true);
}

function _goog_style($A,$T,$with_buttons,$is_table,$incstyle)
{
    if( empty($A['paging_stats']) )
        return;
        
    $stats = $A['paging_stats'];
    
    if( empty($stats['paging']) )
        return;
    
    /*
    [paging] => 1
    [limit] => 15
    [all_row_count] => 14360
    [current_page] => 957
    [num_pages] => 958
    [current_url] => http://cchost.org/api/query
    [prev_link] => http://cchost.org/api/query?offset=0
    [prev_offs] => offset=0
    [next_link] => http://cchost.org/api/query?offset=30
    [next_offs] => offset=30
     */

    $mod = $stats['all_row_count'] % $stats['limit'];
    if( !$mod )
        $mod = $stats['limit'];
        
    $url                = url_args($stats['current_url'],'offset=');
    $first_page         = $url . '0';
    $last_page          = $url . ($stats['all_row_count'] - $mod);
    $pagesgroup         = 10;
    $full_numb_of_pages = $stats['num_pages'];
    $page               = $stats['current_page'];
    
    if( $is_table )
    {
        $open_block_tag = '<table id="%s"><tr>';
        $close_block_tag = '</tr></table>';
        $open_tag = '<td>';
        $close_tag = '</td>';
    }
    else
    {
        $open_block_tag = '<ul id="%s">';
        $close_block_tag = '</ul>';
        $open_tag = '<li>';
        $close_tag = '</li>';
    }
    
    if( $with_buttons )
    {
        $sm_button = 'class = "small_button"';
        $button = 'class = "cc_gen_button"';
        $pagination = '';
    }
    else
    {
        $sm_button = '';
        $button = '';
        $pagination = '';
    }

    if( $incstyle )
    {
        $pagination .= '<link rel="stylesheet" type="text/css" href="' . $T->URL('css/paging.css'). '" />';
    }
    
    $pagination .= sprintf($open_block_tag,'page_buttons');
    
    if( !empty($stats['prev_link']) ) {
        $text = $T->String('str_pagination_prev_link');
        $pagination .= "{$open_tag}<a {$button} href=\"{$stats['prev_link']}\"><span >{$text}</span></a>${close_tag} ";
    }
    $first = ($stats['limit'] * $page);
    $last  = $first + $stats['limit'];
    if( $last > $stats['all_row_count'] )
        $last = $stats['all_row_count'];
    
    $pagination .= $open_tag . 
                   '<span class="page_viewing">' . 
                   $T->String(array('str_pagination_prompt',
                             number_format($first + 1),
                             number_format($last),
                             number_format($stats['all_row_count']))) . 
                   '</span>' . 
                    $close_tag;
                    
    if ( !empty($stats['next_link'])) {
        $text2 = $T->String('str_pagination_next_link');
        $pagination .= " {$open_tag}<a {$button} href=\"{$stats['next_link']}\"><span >{$text2}</span></a>{$close_tag}";
    }
    $pagination .= $close_block_tag;
    
    $pagination .= sprintf($open_block_tag,'page_links');

    if( $page ) {
        $pagination .= "{$open_tag}<a {$sm_button} href=\"$first_page\">{$T->String('str_pagination_first')}</a>{$close_tag}";
    }
    $numpages = $pagesgroup + $page;
    if ($page > ($pagesgroup / 2)){
        $pages_to_display = $page - (int)($pagesgroup / 2);
        $numpages =  $numpages - (int)($pagesgroup / 2);
    }else{
        $pages_to_display = 0;
    }
    if ($numpages > $full_numb_of_pages){
        $numpages = $full_numb_of_pages;
    }
    if( $pages_to_display > 0 ) {
        $pagination .= $open_tag . '<span> ... </span>' . $close_tag;  
    }              
    for ($i=$pages_to_display; $i <$numpages; $i++)
    {
        $y = $i+1;
        if ($i == $page){
            $cls = 
            $pagination .= "{$open_tag}<a href=\"\" class=\"{$sm_button} selected_page_link\"><b>{$y}</b></a>" . $close_tag;
        }else{
            $next_link = $url . ($stats['limit'] * $i);
            $pagination .= "{$open_tag}<a {$sm_button} href=\"{$next_link}\">{$y}</a>{$close_tag}";
        }
    }
    if( $i < $full_numb_of_pages ) {
        $pagination .= $open_tag . '<span> ... </span>' . $close_tag;  
    }           
    if( $page != ($numpages-1) ) {
        $pagination .= "\n{$open_tag}<a {$sm_button} href=\"{$last_page}\">{$T->String('str_pagination_last')}</a>{$close_tag}";
    }
 
    $pagination .= $close_block_tag;
    
    print $pagination;
    
    
} // END: function google_style_paging

?>
