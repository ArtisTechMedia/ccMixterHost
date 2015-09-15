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
* $Id: cc-tag-query.php 14287 2010-03-31 09:10:29Z fourstones $
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
    }

    if( empty($datasource) )
    {
        if( empty($dataview) )
            return;
        
        $queryObj->GetSourcesFromDataview($dataview); // <-- this writes to $args...
        $datasource = $args['datasource'];            //     ...but not result of extract()
    }
        
    if( !empty($tagexp) )
    {
        $tagexp = '(' . $tagexp . ')';   
        
        $tagexp = preg_replace( '/\s+/', '', $tagexp );
        
        if( preg_match( '/([^a-z0-9_\(\)\*\|-])/', $tagexp, $m ) )
        {
            die('Invalid character in tagexp: ' . $m[1] );
        }
        
        $open  = '(\(|^)';
        $close = '(\)|$)';
        $op    = '[\*\|]';
        $opm   = '[\*\|-]';
        
        if( preg_match( "/({$open}{$op}|{$opm}{$close}|{$op}{$op}|{$opm}{$op}|{$close}{$open})/", $tagexp, $m ) )
        {
            die('malformed tagexp: ' . $m[1]);
        }
        
        if( !preg_match("/\(([^()]+|(?R))*\)/",$tagexp,$m) || ($m[0] != $tagexp) )
        {
            die('Mismatched parenthesis in tagexp');
        }


        if( $datasource == 'tags' )
            $f = 'tags_tag';
        else
            $f = $queryObj->_make_field('tags');
            
        $col = "CONCAT(',',{$f},',')";
        
        $replaces = array(
            '/(-?[a-z0-9_]+)/' => " {$col} LIKE '%,$1,%' \n",
            '/\*/'          => ' AND ',
            '/\|/'          => ' OR ',
            "/LIKE '%,-/"   => "NOT LIKE '%",
        );
        
        $queryObj->where[] = preg_replace(array_keys($replaces),$replaces,$tagexp);
        
    }

    if( $datasource == 'tag_alias')
    {
        if( empty($search) )
            return;
        
        $text_words = preg_split( '/[_\s+]/', $search, -1, PREG_SPLIT_NO_EMPTY );
        if( empty($text_words) )
        {
            return;
        }   
    
        $text_words[] = join('_',$text_words);
        
        $soundex = array();
        foreach( $text_words as $T )
        {
            $T = addslashes($T);
            $soundex[] = "(tag_alias_tag SOUNDS LIKE '{$T}')";
            $soundex[] = "(tag_alias_tag LIKE '{$T}%')";
        }

        $queryObj->where[] = join(' OR ', $soundex);
        $slashed = addslashes($search);
        $queryObj->where[] = "tag_alias_alias != '{$slashed}'";
        $queryObj->where[] = "tag_alias_alias != '" . str_replace(' ','_',$slashed) . "'";

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
                $ord = $queryObj->GetURIArg('ord','ASC');
                $deford = 'ASC';
                break;
            }
        }
        if( empty($ord) )
            $args['ord'] = $ord = $deford;
            
        $queryObj->sql_p['order'] .= ' ' . $ord;

        if( (integer)($limit) === 0 ) // e.g. 'default'
            $limit = 1200;
        $queryObj->sql_p['limit'] = $limit;
        
        if( !empty($category) )
            $cat = $category;
            
        if( !empty($cat) )
        {
            $queryObj->where[] = "tags_category = '{$cat}'";
        }
        
        if( !empty($subtype) )
        {
            $pair = $subtype;
        }
        
        if( empty($pair) )
        {
            if( !empty($min) )
            {
                $queryObj->where[] = "tags_count >= {$min}";
            }
            
            $queryObj->columns[] = 'tags_count';
        }
        else
        {
            if( !empty($min) )
            {
                $queryObj->where[] = "tag_pair_count >= {$min}";
            }
            
            $pairs = $opairs = CCTag::TagSplit($pair);
            
            foreach( $pairs as $K => $V )
                $pairs[$K] = "(tag_pair = '{$V}')";
            $pairs = join( ' OR ', $pairs);
            $queryObj->where[] = $pairs;
            $queryObj->AddJoin('cc_tbl_tag_pair ON tags_tag = tag_pair_tag');
            if( count($opairs) == 1 )
            {
                $queryObj->columns[] = 'tag_pair_count as tags_count';                
            }
            else
            {
                $queryObj->columns[] = 'SUM(tag_pair_count) as tags_count';
                $queryObj->sql_p['group_by'] = 'tag_pair_tag';
            }
        }
        
        if( !empty($ids) ) 
        {
            $queryObj->where[] = "tags_tag LIKE '{$ids}%'";
        }
    }
    elseif( $datasource == 'tag_cat')
    {
        if( empty($dataview) || ($dataview == 'default') )
            $dataview = 'tag_cat';
    
        $queryObj->sql_p['order'] = 'tag_category ASC';
        
        $cattotal = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_tag_category');
        $queryObj->sql_p['limit'] = $cattotal;
        
        if( !empty($category) )
            $cat = $category;
            
        if( !empty($cat) )
        {
            $queryObj->where[] = "tag_category_id = {$cat}";
        }
        
    }
}
?>
