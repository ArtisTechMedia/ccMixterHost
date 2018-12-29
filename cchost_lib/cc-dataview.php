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
* $Id: cc-dataview.php 12646 2009-05-24 18:11:02Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('CCDV_RET_RECORDS',  1);
define('CCDV_RET_ITEMS',    2);
define('CCDV_RET_RESOURCE', 3);
define('CCDV_RET_ITEM',     4);
define('CCDV_RET_RECORD',   5);

class CCDataView
{
    function GetDataView($dataview_name)
    {
        global $CC_GLOBALS;
        $filename = CCUtil::SearchPath( $dataview_name . '.php', $CC_GLOBALS['dataview-dir'], 'ccdataviews', true );

        if( empty($filename) )
            return null;

        require_once('cchost_lib/cc-file-props.php');
        $fp = new CCFileProps();
        $props = $fp->GetFileProps($filename);
        $props['dataview'] = $dataview_name;
        $props['file'] = $filename;
        return $props;
    }

    function GetDataViewFromTemplate(&$skinmac)
    {
        $props = $skinmac->GetProps();

        if( empty($props['dataview']) )
            return null;

        if( empty($props['embedded']) )
        {
            global $CC_GLOBALS;
            $dvprops = $this->GetDataView($props['dataview']);
        }
        else
        {
            // we grab the template and suck out the 
            // embedded dataview
            $file = $skinmac->GetSkinFile();
            $text = file_get_contents($file);
            if( !preg_match('#\[dataview\](.*)\[/dataview\]#s',$text,$m) )
            {
                die('missing [dataview] section');
                return null;
            }
            $dvprops['code'] = $m[1];
        }
        return $dvprops;
    }

    function & PerformFile($dataview_name,$args,$ret_type = CCDV_RET_RECORDS) 
    {
        $props = $this->GetDataView($dataview_name);
        $f =& $this->Perform($props,$args,$ret_type);
        return $f;
    }

    function & Perform( $dataview, $args, $ret_type = CCDV_RET_RECORDS, $queryObj=null)
    {
        if( empty($dataview['code']) )
        {
            if( empty($dataview['file']) )
            {
                die('No code or file in ' . $dataview['dataview']);
            }
            else
            {
                if( !file_exists($dataview['file']) )
                    die("Can't find dataview file: " . $dataview['file']);

                require_once($dataview['file']);
            }
        }
        else
        {
            eval($dataview['code']);
        }
        
        $func = $dataview['dataview'] . '_dataview';
        if( !function_exists($func) )
            die("Can't find dataview function in " . $dataview['dataview']);

        if( empty($args['where']) )
            $args['where'] = '1';

        $info = $func($queryObj);

        $ret =& $this->PerformInfo( $info, $args, $ret_type, $queryObj, $dataview );

        return $ret;
    }

    function & PerformInfo( $info, $args, $ret_type = CCDV_RET_RECORDS, $queryObj=null, $dataview=null)
    {
        if( !empty($args['joins']) && is_array($args['joins']) )
            $args['joins'] = join( ' JOIN ', $args['joins'] );

        $sqlargs = array();
        foreach( array( array( 'JOIN', 'joins' ),
                        array( 'ORDER BY', 'order' ),
                        array( 'LIMIT' , 'limit' ),
                        array( ',' , 'columns' ),
                        array( 'GROUP BY', 'group_by' ),
                        array( '', 'match' ),
                        array( 'WHERE', 'where') ,
                        ) as $f )
        {
            $sqlargs[$f[1]] = !empty($args[$f[1]]) ? trim($f[0] . ' ' . $args[$f[1]]) : '';
        }


        $this->sql = preg_replace( array( '/%joins%/', '/%order%/', '/%limit%/', '/%columns%/', '/%group%/', '/%match%/', '/%where%/'  ),
                                    $sqlargs, $info['sql'] );

        $this->sql_count = empty($info['sql_count']) ? '' :
                    preg_replace( array( '/%joins%/', '/%order%/', '/%limit%/', '/%columns%/', '/%group%/', '/%match%/','/%where%/'  ),
                                    $sqlargs, $info['sql_count'] );

        if( CCUser::IsAdmin() && !empty($_GET['dpreview']) )
        {
            $x['sqlargs'] = $sqlargs;
            $x[] = $this->sql;
            $x[] = !isset($dataview) ? '*no dv*' : $dataview;
            $x[] = $queryObj;
            CCDebug::PrintVar($x);
        } 

        if( !empty($queryObj->records) )
            return $queryObj->records;

        switch( $ret_type )
        {
            case CCDV_RET_RECORD:
            {
                $record = CCDatabase::QueryRow($this->sql);
                if( !empty($record) )
                {
                    $arr = array( &$record );
                    $info['queryObj'] =& $queryObj;
                    $this->FilterRecords($arr,$info);
                }
                return $record;
            }

            case CCDV_RET_RECORDS:
            {
                $info['queryObj'] =& $queryObj;
                $records =& CCDatabase::QueryRows($this->sql); //d($records);
                if( count($records) > 0 )
                    $this->FilterRecords($records,$info);
                return $records;        
            }

            case CCDV_RET_ITEMS:
            {
                $items =& CCDatabase::QueryItems($this->sql);
                return $items;
            }

            case CCDV_RET_ITEM:
            {
                $item = CCDatabase::QueryItem($this->sql);
                return $item;
            }

            case CCDV_RET_RESOURCE:
            {
                $qr = CCDatabase::Query($this->sql);
                return $qr;
            }
        }

        die('Invalid return type for dataview: ' . $ret_type );
    }

    function GetCount()
    {
        if( empty($this->sql_count) )
        {
            print _('This template does not support paging');
            exit;
        }
        return intval( CCDatabase::QueryItem($this->sql_count) );
    }

    function FilterRecords(&$records,$info)
    {
        global $cc_dont_eat_std_filters;

        if( empty($cc_dont_eat_std_filters) )
        {        
            while( count($info['e']) )
            {
                $k = array_keys($info['e']);
                $e = $info['e'][$k[0]];
                CCEvents::Invoke( $e, array( &$records, &$info ) );
                if( in_array( $e, $info['e'] ) )
                    $info['e'] = array_diff( $info['e'], array( $e ) );
            }
            return $info['e'];
        }
        else
        {
            $events = $info['e'];
            foreach( $events as $e )
            {
                $info['e'] = array( $e );
                CCEvents::Invoke( $e, array( &$records, &$info ) );
            }
            $info['e'] = $events;
        }
    }

    function MakeTagFilter( $tags, $type='all', $tagfield='upload_tags' )
    {
        if( is_array($tags) )
            $tags = join(',',$tags);

        $tagands = array();
        require_once('cchost_lib/cc-tags.php');
        $tagsarr = CCTag::TagSplit($tags);
        foreach( $tagsarr as $tag )
        {
            if( $tag{0} == '-' )
            {
                $tag = substr($tag,1);
                $not = ' NOT ';
            }
            else
            {
                $not = '';
            }
            $tagands[] = "(CONCAT(',',$tagfield,',') $not LIKE '%,$tag,%' )";
        }
        $op = $type == 'all' ? 'AND' : 'OR';
        
        return implode( ' ' . $op . '  ', $tagands );
    }
}

?>
