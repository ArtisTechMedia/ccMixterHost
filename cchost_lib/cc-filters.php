<?


if( !defined('IN_CC_HOST') )
    die( 'Welcome to ccHost' );

function cc_filter_std(&$records,&$dataview_info)
{
    global $cc_dont_eat_std_filters;
    global $CC_GLOBALS;
    
    $c = count($records);
    $k = array_keys($records);
    require_once('cchost_lib/cc-tags.php');
        
    foreach( array( CC_EVENT_FILTER_DOWNLOAD_URL,
                    CC_EVENT_FILTER_EXTRA,
                    CC_EVENT_FILTER_FILES,
                    CC_EVENT_FILTER_NUM_FILES,
                    CC_EVENT_FILTER_RATINGS_STARS,
                    CC_EVENT_FILTER_REMIXES_FULL,
                    CC_EVENT_FILTER_REMIXES_SHORT,
                    CC_EVENT_FILTER_UPLOAD_MENU,
                    CC_EVENT_FILTER_UPLOAD_TAGS,
                    CC_EVENT_FILTER_UPLOAD_USER_TAGS,
                     ) as $e )
    {

        if( !in_array( $e, $dataview_info['e'] ) )
            continue;

        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];

            switch( $e )
            {
                case CC_EVENT_FILTER_FILES:
                {
                    if( !isset($R['files']) ) 
                        cc_filter_files($R);
                    break;
                }

                case CC_EVENT_FILTER_NUM_FILES:
                {
                    $R['num_files'] = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_files WHERE file_upload = ' .$R['upload_id'] );
                    break;
                }

                case CC_EVENT_FILTER_EXTRA:
                {
                    if( is_string($R['upload_extra']) )
                        $R['upload_extra'] = unserialize($R['upload_extra']);
                    
                    if( isset($R['upload_contest']) )
                    {
                        if( $R['upload_contest'] )
                        {
                            // todo: this shouldn't be here
                            if( empty($CC_GLOBALS['contests']) )
                                cc_fill_contests();

                            $R['upload_extra']['relative_dir'] = ccp($CC_GLOBALS['contest-upload-root'],
                                    $CC_GLOBALS['contests'][$R['upload_contest']],$R['user_name']);
                        }
                        else
                        {
                            $R['upload_extra']['relative_dir'] = ccp($CC_GLOBALS['user-upload-root'],$R['user_name']);
                        }
                    }
                    break;
                }

                case CC_EVENT_FILTER_DOWNLOAD_URL:
                {
                    if( !isset($R['files']) )
                        cc_filter_files($R);

                    $r_user = $R['user_name'];
                    $f_name = $R['files'][0]['file_name'];

                    if( $R['upload_contest'] )
                    {
                        if( empty($CC_GLOBALS['contests']) ) // todo: this shouldn't be here
                            cc_fill_contests();

                        $contest_name = $CC_GLOBALS['contests'][$R['upload_contest']];

                        $R['download_url'] = ccd($CC_GLOBALS['contest-upload-root'],$contest_name,$r_user,$f_name);
                    }
                    else
                    {
                        $R['download_url'] = ccd($CC_GLOBALS['user-upload-root'],$r_user,$f_name);
                    }
                    break;
                }

                case CC_EVENT_FILTER_UPLOAD_TAGS:
                {
                    require_once('cchost_lib/cc-tags.inc');
                    $tags = CCTag::TagSplit($R['upload_tags']);
                    $baseurl = ccl('tags') . '/';
                    foreach( $tags as $tag )
                        $R['upload_taglinks'][] = array( 'tagurl' => $baseurl . $tag, 'tag' => $tag );
                    break;
                }

                case CC_EVENT_FILTER_UPLOAD_USER_TAGS:
                {
                    if( is_string($R['upload_extra']) )
                        $R['upload_extra'] = unserialize($R['upload_extra']);

                    require_once('cchost_lib/cc-tags.inc');
                    $tags = CCTag::TagSplit($R['upload_extra']['ccud']);
                    $tags = array_merge($tags,CCTag::TagSplit($R['upload_extra']['usertags']));
                    $baseurl = ccl('tags') . '/';
                    foreach( $tags as $tag )
                        $R['usertag_links'][] = array( 'tagurl' => $baseurl . $tag, 'tag' => $tag );
                    break;
                }

                case CC_EVENT_FILTER_RATINGS_STARS:
                {
                    if( $R['ratings_enabled'] && !$R['thumbs_up'] )
                    {
                        $average = $R['upload_score'] / 100;
                        $count = $R['upload_num_scores'];
                        $stars = floor($average);
                        $half = ($R['upload_score'] % 100) > 25;
                        for( $ri = 0; $ri < $stars; $ri++ )
                            $R['ratings'][] = 'full';

                        if( $half )
                        {
                            $R['ratings'][] = 'half';
                            $ri++;
                        }
                        
                        for( ; $ri < 5; $ri++ )
                            $R['ratings'][] = 'empty';

                        $R['ratings_score'] = number_format($average,2) . '/' . $count;
                    }
                    break;
                }

                case CC_EVENT_FILTER_REMIXES_FULL:
                {
                    cc_filter_remixes_full($R,$dataview_info);
                    break;
                }

                case CC_EVENT_FILTER_REMIXES_SHORT:
                {
                    cc_filter_remixes_short($R,$dataview_info);
                    break;
                }

                case CC_EVENT_FILTER_UPLOAD_MENU:
                {
                    // note: this is 
                    $allmenuitems = array();
                    $r = array( &$allmenuitems, &$R );
                    CCEvents::Invoke(CC_EVENT_UPLOAD_MENU, $r );

                    // sort the results
                    
                    uasort($allmenuitems ,'cc_weight_sorter');

                    // filter the results based on access permissions
                    require_once('cchost_lib/cc-menu.php');
                    $mask = CCMenu::GetAccessMask();

                    $menu = array();
                    $count = count($allmenuitems);
                    $keys = array_keys($allmenuitems);
                    $grouped_menu = array();
                    for( $i = 0; $i < $count; $i++ )
                    {
                        $key    = $keys[$i];
                        $item   =& $allmenuitems[$key];
                        $access = $item['access'];
                        if( !($access & CC_DISABLED_MENU_ITEM) && (($access & $mask) != 0) )
                        {
                            $grouped_menu[$item['group_name']][$key] = $item;
                        }
                    }
                    $R['local_menu'] =& $grouped_menu;
                }
            } // end switch on event

        } // for each record

        if( empty($cc_dont_eat_std_filters) )
            $dataview_info['e'] = array_diff( $dataview_info['e'], array( $e ) );

    } // foreach event sent in

}

function _filter_history_helper($sql,$col)
{
    /*
        note: I couldn't get inner selects to work
    */
    $values = CCDatabase::QueryItems($sql);
    if( empty($values) )
        return null;
    return array( 'where' => $col . ' IN (' . join(',',$values) . ')' );
}

function cc_filter_remixes_short(&$R)
{
    if( !empty($R['upload_num_sources']) )
    {
        $args = _filter_history_helper(
                    "SELECT tree_parent FROM cc_tbl_tree WHERE tree_child={$R['upload_id']} LIMIT 4",'upload_id');
        if( $args )
        {
            $dv = new CCDataView();
            $R['remix_parents'] = $dv->PerformFile('links_by_chop',$args);
        }
    }

    if( !empty($R['upload_num_pool_sources']) )
    {
        if( empty($R['remix_parents']) || (count($R['remix_parents']) < 3) )
        {
            $count = empty($R['remix_parents']) ? 4 : 4 - count($R['remix_parents']);
            $args = _filter_history_helper(
                "SELECT pool_tree_pool_parent FROM cc_tbl_pool_tree WHERE pool_tree_child={$R['upload_id']} LIMIT {$count}",
                'pool_item_id');
            if( $args )
            {
                $dv = new CCDataView();
                $pool_parents = $dv->PerformFile('links_by_pool',$args);
                if( empty($R['remix_parents']) )
                    $R['remix_parents'] = $pool_parents;
                else
                    $R['remix_parents'] = array_merge( $R['remix_parents'],  $pool_parents );
            }
        }
    }

    if( !empty($R['remix_parents']) && (count($R['remix_parents']) > 3) )
    {
        $R['more_parents_link'] = $R['file_page_url'];
        unset($R['remix_parents'][3]);
    }

    if( !empty($R['upload_num_remixes']) )
    {
        $args = _filter_history_helper(
                    "SELECT tree_child FROM cc_tbl_tree WHERE tree_parent={$R['upload_id']} LIMIT 4",'upload_id');
        if( $args )
        {
            $dv = new CCDataView();
            $R['remix_children'] = $dv->PerformFile('links_by_chop',$args);
        }
    }

    if( !empty($R['upload_num_pool_remixes']) )
    {
        if( empty($R['remix_children']) || (count($R['remix_children']) < 3) )
        {
            $count = empty($R['remix_children']) ? 4 : 4 - count($R['remix_children']);
            $args = _filter_history_helper(
                "SELECT pool_tree_pool_child FROM cc_tbl_pool_tree WHERE pool_tree_parent={$R['upload_id']} LIMIT {$count}",
                'pool_item_id');
            if( $args )
            {
                $dv = new CCDataView();
                $pool_children = $dv->PerformFile('links_by_pool',$args);
                if( empty($R['remix_children']) )
                    $R['remix_children'] = $pool_children;
                else
                    $R['remix_children'] = array_merge( $R['remix_children'],  $pool_children );
            }
        }
    }

    if( !empty($R['remix_children']) && (count($R['remix_children']) > 3) )
    {
        $R['more_children_link'] = $R['file_page_url'];
        unset($R['remix_children'][3]);
    }

}

function cc_filter_remixes_full(&$R,&$dataview_info)
{
    if( $dataview_info['queryObj']->args['datasource'] == 'pool_items' )
    {
        // We are listing a sample pool item(s)
        $args = _filter_history_helper(
                   "SELECT pool_tree_child FROM cc_tbl_pool_tree WHERE pool_tree_pool_parent={$R['pool_item_id']}", 'upload_id');
        if( $args )
        {
            $dv = new CCDataView();
            $R['remix_children'] = $dv->PerformFile('links_by',$args);
        }
        if( !empty($R['remix_children']) && (count($R['remix_children']) > 14) )
            $R['children_overflow'] = true;

        $args = _filter_history_helper(
                   "SELECT pool_tree_parent FROM cc_tbl_pool_tree WHERE pool_tree_pool_child={$R['pool_item_id']}", 'upload_id');
        if( $args )
        {
            $dv = new CCDataView();
            $R['remix_parents'] = $dv->PerformFile('links_by',$args);
        }
        if( !empty($R['remix_parents']) && (count($R['remix_parents']) > 14) )
            $R['parents_overflow'] = true;

    }
    else
    {
        // We are listing local upload

        /* local remix sources */

        $args = _filter_history_helper("SELECT tree_parent FROM cc_tbl_tree WHERE tree_child={$R['upload_id']}",'upload_id');
        if( $args )
        {
            $dv = new CCDataView();
            $R['remix_parents'] = $dv->PerformFile('links_by_chop',$args);
        }
        
        /* pool remix sources */

        $args = _filter_history_helper(
                    "SELECT pool_tree_pool_parent FROM cc_tbl_pool_tree WHERE pool_tree_child={$R['upload_id']}",
                    'pool_item_id');
        if( $args )
        {
            $dv = new CCDataView();
            $pool_parents = $dv->PerformFile('links_by_pool',$args);
            if( empty($R['remix_parents']) )
                $R['remix_parents'] = $pool_parents;
            else
                $R['remix_parents'] = array_merge( $R['remix_parents'],  $pool_parents );
        }
        if( !empty($R['remix_parents']) && (count($R['remix_parents']) > 14) )
            $R['parents_overflow'] = true;

        /* local remixes of this upload */

        $args = _filter_history_helper(
                    "SELECT tree_child FROM cc_tbl_tree WHERE tree_parent={$R['upload_id']}",'upload_id');
        if( $args )
        {
            $dv = new CCDataView();
            $R['remix_children'] = $dv->PerformFile('links_by_chop',$args);
        }

        /* remote remixes and trackbacks of this upload */

        $args = _filter_history_helper(
                        "SELECT pool_tree_pool_child FROM cc_tbl_pool_tree WHERE pool_tree_parent={$R['upload_id']}",
                        'pool_item_id');
        if( $args )
        {
            $dv = new CCDataView();
            $pool_children = $dv->PerformFile('links_by_pool',$args);
            if( empty($R['remix_children']) )
                $R['remix_children'] = $pool_children;
            else
                $R['remix_children'] = array_merge( $R['remix_children'],  $pool_children );
        }
        if( !empty($R['remix_children']) && (count($R['remix_children']) > 14) )
            $R['children_overflow'] = true;
    }
}

function cc_filter_files(&$R)
{
    global $CC_GLOBALS;

    // todo: this shouldn't be here
    if( empty($CC_GLOBALS['contests']) )
        cc_fill_contests();

    $sql = 'SELECT * FROM cc_tbl_files where file_upload = ' . $R['upload_id'];
    $R['files'] = CCDatabase::QueryRows($sql);
    $fk = array_keys($R['files']);
    $tags = '';
    for( $fi = 0; $fi < count($fk); $fi++ )
    {
        $F =& $R['files'][$fk[$fi]];
        $F['file_extra'] = unserialize($F['file_extra']);
        $F['file_format_info'] = unserialize($F['file_format_info']);

        $r_user = $R['user_name'];

        if( $R['upload_contest'] )
        {
            $contest_name = $CC_GLOBALS['contests'][$R['upload_contest']];

            if( empty($tags) )
            {
                if( empty($R['upload_tags']) )
                    $tags = CCDatabase::QueryItem('SELECT upload_tags FROM cc_tbl_uploads WHERE upload_id='.$R['upload_id']);
                else
                    $tags = $R['upload_tags'];
            }

            $is_source = preg_match('/,contest_(source|sample),/', ',' . $tags . ',' );

            if( $is_source )
            {
                $F['download_url'] = ccd($CC_GLOBALS['contest-upload-root'],$contest_name,$F['file_name']);
                $F['local_path']   = cca($CC_GLOBALS['contest-upload-root'],$contest_name,$F['file_name']);
            }
            else
            {
                $F['download_url'] = ccd($CC_GLOBALS['contest-upload-root'],$contest_name,$r_user,$F['file_name']);
                $F['local_path']   = cca($CC_GLOBALS['contest-upload-root'],$contest_name,$r_user,$F['file_name']);
            }
        }
        else
        {
            $F['download_url'] = ccd($CC_GLOBALS['user-upload-root'],$r_user,$F['file_name']);
            $F['local_path']   = cca($CC_GLOBALS['user-upload-root'],$r_user,$F['file_name']);
        }

        $fs = $F['file_filesize'];
//        if( $fs )
        {
            $F['file_rawsize'] = $fs;
            if( $fs > CC_1MG )
                $fs = number_format($fs/CC_1MG,2) . 'MB';
            else
                $fs = number_format($fs/1024) . 'KB';
            $F['file_filesize'] = " ($fs)";
        }
    }
}

function cc_fill_contests()
{
    global $CC_GLOBALS;
    static $looked_up;

    if( !isset($looked_up) )
    {
        $sql = 'SELECT contest_id, contest_short_name FROM cc_tbl_contests'; 
        $crows = CCDatabase::QueryRows($sql);
        foreach( $crows as $crow )
            $CC_GLOBALS['contests'][ $crow['contest_id'] ] = $crow['contest_short_name'];
        $looked_up = true;
    }
}

?>
