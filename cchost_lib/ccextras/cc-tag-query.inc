<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $id$
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP, 'cc_tag_query_OnApiQuerySetup' );

function cc_tag_query_OnApiQuerySetup( &$args, &$queryObj, $requiresValidation )
{
    extract($args);

    if( !empty($dataview) )
    {
        if($dataview == 'passthru')
            return;

        if( empty($datasource) )
        {
            $queryObj->GetSourcesFromDataview($dataview); // <-- this writes to $args...
            $datasource = $args['datasource'];            //     ...but not result of expand()
        }
    }
    
    if( $datasource == 'tags' )
    {
        if( empty($dataview) || ($dataview == 'default') )
            $dataview = 'tags';
    
        if( empty($sort) )
        {
            $sort = 'name';
        }
        switch( $sort )
        {
            case 'date':
                $args['sort'] = 'count';
                // fall thru
            case 'count':
                $queryObj->sql_p['order'] = 'tags_count';
                $deford = 'DESC';
                break;
            
            case 'name':
            default:
            {
                $queryObj->sql_p['order'] = 'tags_tag';
                // this can't be right...
                if( !empty($queryObj->_uri_args) &&
                    empty($queryObj->_uri_args['ord']) )
                {
                    $ord = 'ASC';
                }
                $deford = 'ASC';
                break;
            }
        }
        if( empty($ord) )
            $args['ord'] = $ord = $deford;
            
        $queryObj->sql_p['order'] .= ' ' . $ord;
        
        // this can't be right...
        if( !empty($queryObj->_uri_args) &&
            !empty($queryObj->_uri_args['limit']) &&       
            !empty($limit) &&
            ($queryObj->_uri_args['limit'] != $limit)
           )
        {
            $queryObj->sql_p['limit'] = $queryObj->_uri_args['limit'];
            $queryObj->_limit_is_valid = true;
        }
        
        if( !empty($category) )
            $cat = $category;
            
        if( !empty($cat) )
        {
            $queryObj->where[] = "tags_category = '{$cat}'";
        }
    }
    elseif( $datasource == 'tag_cat')
    {
        if( empty($dataview) || ($dataview == 'default') )
            $dataview = 'tag_cat';
    
        $queryObj->sql_p['order'] = 'tag_category ASC';
        
        $cattotal = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_tag_category');
        $queryObj->sql_p['limit'] = $cattotal;
        $queryObj->_limit_is_valid = true;
        
        if( !empty($category) )
            $cat = $category;
            
        if( !empty($cat) )
        {
            $queryObj->where[] = "tag_category_id = {$cat}";
        }
        
    }
}
?>
