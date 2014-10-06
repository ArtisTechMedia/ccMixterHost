<?


if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,      array( 'CCQueryBrowser', 'OnMapUrls') );

class CCQueryBrowser
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('browse'),     array( 'CCQueryBrowser', 'Browse'),    CC_DONT_CARE_LOGGED_IN,
              ccs(__FILE__),'',_('Display Query (upload) Browser'), CC_AG_UPLOADS );
    }

    function Browse()
    {
        if( isset($_GET['user_lookup']) )
        {
            $user_mask = trim(CCUtil::Strip($_GET['user_lookup']));
            if( !$user_mask )
                CCUtil::ReturnAjaxMessage('not a valid user lookup',CC_AJAX_WARNING);

            $sql =<<<EOF
                SELECT 
                    CONCAT( '<p class="cc_autocomp_line" id="_ac_', user_name, '">', 
                        IF(user_name = user_real_name,user_name,CONCAT(user_real_name,' (',user_name,')')),
                        '</p>' 
                        ) as t
                    FROM cc_tbl_user 
                    WHERE  ((user_name LIKE '{$user_mask}%') OR (user_real_name LIKE '{$user_mask}%')) AND 
                           user_num_uploads > 0 ORDER BY user_name ASC
EOF;

            $users = CCDatabase::QueryItems($sql);
            $args['count'] = count($users);
            $args['html'] = join('',$users);
            CCUtil::ReturnAjaxData($args,false);
        }
        else if( isset($_GET['tag_lookup']) )
        {
            $tag_mask = trim(CCUtil::Strip($_GET['tag_lookup']));
            if( !$tag_mask )
                CCUtil::ReturnAjaxMessage('not a valid tag lookup',CC_AJAX_WARNING);
            
            $limit = '';
            if( !empty($_GET['limit']) )
            {
                $limit = sprintf("%d",$_GET['limit']);
                if( !empty($limit) )
                    $limit = "LIMIT 0, $limit";
            }

            $type = empty($_GET['type']) ? CCTT_USER : sprintf('%d',$_GET['type']);
            if( empty($type) )
                $type = CCTT_USER;
            $where = "WHERE ( (tags_type & $type) <> 0 ) ";

            if( $tag_mask != '*' )
                $where .= " AND (tags_tag LIKE '{$tag_mask}%')";

            if( !empty($_GET['min']) )
            {
                $min = sprintf("%d",$_GET['min']);
                if( !empty($min) )
                    $where .= " AND (tags_count >= $min)";
            }

            $sql =<<<EOF
            SELECT CONCAT( '<p class="cc_autocomp_line" id="_ac_', tags_tag, '">', tags_tag, ' (', tags_count,')</p>' ) as t
                   FROM cc_tbl_tags
                   {$where}
                   ORDER BY tags_tag ASC {$limit}
EOF;

            $tags = CCDatabase::QueryItems($sql);
            $args['count'] = count($tags);
            $args['html'] = join('',$tags);
            CCUtil::ReturnAjaxData($args,false);
        }
        else if( isset($_GET['related']) )
        {
            $tag_mask = trim(CCUtil::Strip($_GET['related']));
            if( !$tag_mask )
                CCUtil::ReturnAjaxMessage('not a valid tag lookup',CC_AJAX_WARNING);
            require_once('cchost_lib/cc-dataview.php');
            $dv = new CCDataView();
            $filter = $dv->MakeTagFilter($tag_mask);
            $tags = CCDatabase::QueryItems("SELECT DISTINCT upload_tags FROM cc_tbl_uploads WHERE $filter");

            $tags = array_unique(preg_split('/[\s,]?,[\s]?/',join(',',$tags),-1,PREG_SPLIT_NO_EMPTY));
            sort($tags);
            $these = preg_split('/[\s,]+/',$tag_mask,-1,PREG_SPLIT_NO_EMPTY);
            $tags = array_merge( $these, array_diff($tags,$these));
            array_walk($tags,'cc_wrap_user_tags');
            $args['count'] = count($tags);
            $args['html'] = join('',$tags);
            CCUtil::ReturnAjaxData($args,false);
        }
        else if( isset($_GET['user_tags']) )
        {
            $user_name = trim(CCUtil::Strip($_GET['user_tags']));
            if( !$user_name )
                CCUtil::ReturnAjaxMessage('not a valid user lookup',CC_AJAX_WARNING);

            $tags = CCDatabase::QueryItems('SELECT DISTINCT upload_tags FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id ' .
                                              "WHERE user_name='$user_name'");

            $tags = array_unique(preg_split('/[\s]?,[\s]?/',join(',',$tags),-1,PREG_SPLIT_NO_EMPTY));
            sort($tags);
            array_walk($tags,'cc_wrap_user_tags');
            $args['count'] = count($tags);
            $args['html'] = join('',$tags);
            CCUtil::ReturnAjaxData($args,false);
        }
        else
        {
            require_once('cchost_lib/cc-page.php');
            CCPage::SetTitle('str_browse_remixes');
            CCPage::AddMacro('query_browser');
        }
    }
}

?>