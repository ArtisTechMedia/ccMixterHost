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
* $Id: cc-query.php 13108 2009-07-27 18:59:51Z fourstones $
*
*/

/**
* Query API
*
* The request is handled in 3 main phases:
*
*   PHASE 1
*     Interpret the parameters to determine the data source and data view (see _validate_sources)
*   
*   
*   - Perform the SQL query
*   - Format the output and return it to caller
*
* 
*
* @package cchost
* @subpackage api
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-tags.php');

/**
*
*
*/
class CCQuery 
{
    function CCQuery()
    {
        $this->sql = '';
        $this->sql_p = array(   
                        'columns' => '',
                        'joins' => array(),
                        'where' => '',
                        'order' => '',
                        'limit' => '', 
                        'group_by' => '' );
        $this->where = array();
        $this->args = array();
        $this->records = array();
        $this->columns = array();
    }

    /**
    * Entry point for api/query
    *
    */
    function QueryURL()
    {
        $this->ProcessUriArgs();

        list( $value, $mime ) = $this->Query(); // This method MAY exit the session... 

        if( $value === true ) // handled elsewhere 
            return;           // return and show the page

        if( empty($value) ) 
            CCUtil::Send404(true);  // We didn't find anything, slap back a 404

        if( !empty($mime) )
            header( "Content-type: $mime" );

        print($value);
        exit;
    }

    /**
    * Use this when calling from php (call ProcessAdminArgs first to clean the args)
    *
    */
    function Query($args=array())
    {
        if( !empty($args) )
            $this->args = $args;

        if( !empty($this->_sql_squeeze) )
           $this->sql_p = array_merge($this->sql_p,$this->_sql_squeeze);

        $this->_validate_sources();

        if( $this->args['datasource'] == 'uploads' )
            $this->_gen_visible();

        if( empty($this->args['cache']) )
        {
            $this->_generate_records();
        }
        else
        {
            $this->_generate_records_from_cache();
        }

        //
        // Process the resulting records here
        //
        if( $this->args['format'] == 'count' )
        {
            return( array( '[' . $this->records . ']', 'text/plain' ) );
        }
        elseif( $this->args['format'] == 'ids' )
        {
            $text = empty($this->records) ? '-' : join(';',$this->records );

            return( array( $text, 'text/plain' ) );
        }

        // Do NOT return at this point if records are empty, 
        // an empty feed is still valid

        if( !empty($this->records) && !empty($this->args['nosort']) && !empty($this->args['ids']) )
        {
            $ids = is_string($this->args['ids']) ? $this->_split_ids($this->args['ids']) : $this->args['ids'];
            $i = 0;
            foreach($ids as $id)
                $sort_order[$id] = $i++;
            $this->_resort_records($this->records,$sort_order,'upload_id');
        }

        switch( $this->args['format'] )
        {
            case 'undefined':
            case 'php':
                break;

            case 'phps':
                return array( serialize($this->records), 'text/plain' );

            default:
            {
                $results = '';
                $results_mime = '';
                $this->args['queryObj'] = $this;

                CCEvents::Invoke( CC_EVENT_API_QUERY_FORMAT, 
                                    array( &$this->records, &$this->args, &$results, &$results_mime ) );
                return array( $results, $results_mime );
            }
        } // end switch

        return array( &$this->records, '' );
    }

    /**
    * Call this from php when you need to hack in some SQL (call ProcessAdminArgs first on $qargs)
    *
    */
    function QuerySQL($qargs,$sqlargs)
    {
        $this->_sql_squeeze =  $sqlargs;
        return $this->Query($qargs);
    }

    /**
    * Call this to fetch and clean args that were passed in through the browser
    */
    function ProcessUriArgs($extra_args = array())
    {
        global $CC_GLOBALS;

        $this->_from_url = true;

        $req = !empty($_POST) ? $_POST : $_GET;

        // some bots have been passing in empty args
        // like user= without values
        $keys = array_keys($req);
        for( $i = 0; $i < count($keys); $i++ )
        {
            $k = $keys[$i];
            if( empty($req[$k]) && ($req[$k] !== '0') )
                unset($req[$k]);
        }

        if( empty($req) )
            return $extra_args;

        if( !empty($req['ccm']) )
            unset($req['ccm']);

        CCUtil::Strip($req);

        $this->_arg_alias_ref($req); // convert short to long
        $this->_uri_args = $req;     // store for later

        $defargs = $this->GetDefaultArgs();
        $this->args = array_merge($defargs,$req,$extra_args);
        
        // get the '+' out of the tag str
        if( !empty($this->args['tags']) )
            $this->args['tags'] = str_replace( ' ', ',', urldecode($this->args['tags']));

        // queries might need decoding
        $search_key = '';
        if( !empty($this->args['search']) )
            $search_key = 'search';
        else if( !empty($this->args['searchp']) )
            $search_key = 'searchp';
        if( !empty($search_key) )
            $this->args[$search_key] = urldecode($this->args[$search_key]);

        $k = array_keys($this->args);
        $n = count($k);
        for( $i = 0; $i < $n; $i++)
        {
            $tt = $this->args[$k[$i]];
            if( is_string($tt) && preg_match('/(\'\"|\')$/', $tt) )
                die('Illegal value in query');
        }

        $this->_validate_sources();
        $this->_validate_args();

        return $this->args;
    }
    
    function _clean_uri_arg($arg)
    {
        // Unfortunately some templates pass parameters directly
        // into embed and html formats so we can't assume that
        // _form_url is accurate
        
        //if( !empty($this->_from_url) )
        if( !empty($this->args[$arg]) )
            return CCUtil::CleanUrl($this->args[$arg]);
    }
    
    /**
    * Helper function for formats during CC_EVENT_QUERY_SETUP
    *
    */
    function GetURIArg($arg,$value=null)
    {
        return (empty($this->_uri_args) || empty($this->_uri_args[$arg])) ? $value : $this->_uri_args[$arg] ;
    }


    /**
    * Helper function for formats during CC_EVENT_QUERY_SETUP
    *
    */
    function AddJoin($sql)
    {
        return $this->sql_p['joins'][] = $sql;
    }

    /** 
    * Helper function for formats during CC_EVENT_QUERY_SETUP
    *
    */
    function GetSourcesFromDataview($dataview_name)
    {
        if( empty($this->dataview) )
        {
            require_once('cchost_lib/cc-dataview.php');
            $this->dataview = new CCDataView();
        }
        $this->dataviewProps = $this->dataview->GetDataview($dataview_name);
        $this->args['dataview'] = $dataview_name;
        $this->args['datasource'] = empty($this->dataviewProps['datasource']) ? 'uploads' : $this->dataviewProps['datasource'];
    }

    /** 
    * Helper function for formats during CC_EVENT_QUERY_SETUP
    *
    */
    function GetSourcesFromTemplate($template_name)
    {

        if( empty($this->dataview) )
        {
            require_once('cchost_lib/cc-dataview.php');
            $this->dataview = new CCDataView();
        }

        require_once('cchost_lib/cc-template.php');
        $template = $this->template = new CCSkinMacro($template_name);
        $this->templateProps = $template->GetProps();
        $this->dataviewProps = $this->dataview->GetDataviewFromTemplate($template);

        if( empty($this->dataviewProps) )
        {
            if( empty($this->templateProps['dataview_param']) || 
                ($this->templateProps['dataview_param'] != 'ok') ||
                (empty($this->args['dataview']))
              )
            {
                $dvname = 'default';
            }
            else
            {
                $dvname = $this->args['dataview'];
            }

            $this->templateProps['dataview'] = $dvname;
            $this->dataviewProps = $this->dataview->GetDataview($dvname);
        }

        $this->args['dataview'] = $this->templateProps['dataview'];

        if( empty($this->templateProps['datasource']) )
        {
            $this->args['datasource'] = empty($this->dataviewProps['datasource']) ? 'uploads' : $this->dataviewProps['datasource'];
        }
        else
        {
            //
            // The datasource is right there in the template
            //
            $this->args['datasource'] = $this->templateProps['datasource'];
        }
    }

    function _trigger_setup_event()
    {
        CCEvents::Invoke( CC_EVENT_API_QUERY_SETUP, array( &$this->args, &$this, !empty($this->_from_url)) );

        // ugh, sql_p['columns'] is a string, it should have been an array
        // all along. We hack it into a string here so we don't break
        // anybody else that depends on it being a string.
        if( !empty($this->columns) )
        {
            $cols = join( ',' , $this->columns );
            if( !empty($this->sql_p['columns']) ) {
                $cols = ',' . $cols;
            }
            $this->sql_p['columns'] .= $cols;
        }
    }
    
    /** 
    * Query phase 1 processing
    *
    * At the end of this data source and data view MUST have been initialized
    *
    */
    function _validate_sources()
    {
        if( !empty($this->_validated_sources) )
            return;

        $A =& $this->args;

        $this->_do_ds_ensure($A);
        
        //
        // This is the default return type from dataview::perform
        //
        $A['rettype'] = CCDV_RET_RECORDS;

        //
        // every query must have a format
        //
        if( empty($A['format']) )
            $A['format'] = 'page';

        switch( $A['format'] )
        {
            case 'count':
                $A['rettype'] = CCDV_RET_ITEM;
                $dv_name = 'count';
                if( !empty($A['datasource']) ) {
                    if( $A['datasource'] == 'pool_items') 
                       $dv_name = 'count_pool_items';
                    else if( $A['datasource'] == 'user' )
                        $dv_name = 'count_users';
                    else if( $A['datasource'] == 'cart' ) {
                        $dv_name = $A['dataview'];
                        $A['rettype'] = CCDV_RET_COUNT;
                    }
                }   
                $this->GetSourcesFromDataview($dv_name);
                $this->_trigger_setup_event();
                $this->sql_p['limit'] = '';
                break;
            case 'ids':
                $A['rettype'] = CCDV_RET_ITEMS;
                $this->GetSourcesFromDataview('ids');
                break;
            case 'php':
            case 'phps':
                if( empty($A['dataview']) )
                    $A['dataview'] = 'default';
                $this->GetSourcesFromDataview($A['dataview']);
                // fall through
            default:
                $this->_trigger_setup_event();
                break;
        }

        if( empty($A['datasource']) )
            die('Could not determine datasource');

        if( $A['dataview'] == 'passthru' )
            $this->dead = true;

        // do it again, sigh
        $this->_do_ds_ensure($A);

        $this->_validated_sources = true;
    }

    function _do_ds_ensure(&$A)
    {
        if( empty($A['datasource']) )
            return;
        if( $A['datasource'] == 'users' )
            $A['datasource'] = 'user';
        elseif( $A['datasource'] == 'pool_item' )
            $A['datasource'] = 'pool_items';
    }

    /** 
    * Validate args from HTTP request
    *
    */
    function _validate_args()
    {
        if( !empty($this->templateProps['valid_args']) )
        {
            $valid = array_unique(preg_split('/([^a-z_]+)/',$this->templateProps['valid_args'],0,PREG_SPLIT_NO_EMPTY));
            if( !empty($valid) )
            {
                $skip = array('format','template','dataview','datasource','offset','limit','sort','ord','dpreview','_cache_buster');
                $diff = array_diff(array_diff(array_keys($this->_uri_args),$skip),$valid);
                if( !empty($diff) )
                {
                    $msg = sprintf(_('Invalid query args: "%s"  Valid args are: "%s" '),join( ', ',$diff),$this->templateProps['valid_args']);
                    print $msg;
                    exit;
                }
            }
        }
        if( !empty($this->templateProps['required_args']) )
        {
            $req = array_unique(preg_split('/([^a-z]+)/',$this->templateProps['required_args'],0,PREG_SPLIT_NO_EMPTY));
            if( !empty($req) )
            {
                $gotcha = array_intersect(array_keys($this->args),$req); // not _url_args!
                if( array_diff($req,$gotcha) )
                {
                    $msg = sprintf(_('Missing required query args: "%s" '),$this->templateProps['required_args']);
                    print $msg;
                    exit;
                }
            }
        }
        if( !empty($this->templateProps['formats']) )
        {
            $valid = array_unique(preg_split('/([^a-z]+)/',$this->templateProps['formats'],0,PREG_SPLIT_NO_EMPTY));
            if( !empty($valid) && empty($this->args['format']) || !in_array( $this->args['format'], $valid ) )
            {
                $this->args['format'] = $valid[0];
            }
        }
    }

    /**
    * Call this before calling Query or QuerySQL
    */
    function ProcessAdminArgs($args,$extra_args=array())
    {
        if( is_string($args) )
        {
            parse_str($args,$args);
            CCUtil::StripSlash($args);
        }

        // alias short to long
        $this->_arg_alias_ref($args);

        $this->args = array_merge($this->GetDefaultArgs($args),$args,$extra_args);

        if( !empty($this->args['tags']) )
        {
            // clean up tags 
            require_once('cchost_lib/cc-tags.php');
            $this->args['tags'] = join(',',CCTag::TagSplit($this->args['tags']));
        }

        return $this->args;
    }

    function SerializeArgs($args=array())
    {
        if( empty($args) )
            $args =& $this->args;
        $keys = array_keys($args);
        $default_args = $this->GetDefaultArgs();
        $str = '';

        // alias short to long
        $this->_arg_alias_ref($args);

        $badargs = array( 'qstring', 'ccm', 'format', 'template', 'dataview', 'datasource', '_cache_buster' ); 

        foreach( $keys as $K )
        {
            if(  in_array( $K, $badargs ) || empty($args[$K]) || !is_string($args[$K]) ||
                ( array_key_exists($K,$default_args) && ($args[$K] == $default_args[$K]) ) )
            {
                continue;
            }

            if( !empty($str) )
                $str .= '&';

            $str .= $K . '=' . urlencode($args[$K]);
        }
        return $str;
    }

    function GetDefaultArgs()
    {
        global $CC_GLOBALS;

        $args = array(
                    'sort' => 'date', 
                    'ord'  => 'DESC', 
                    'limit' => $CC_GLOBALS['querylimit'],
                    'offset' => 0,
                    'format' => 'page',
                    );
        return $args;
    }

    function _generate_records()
    {
        foreach( array( 'sort', 'date', ) as $arg )
        {
            $method = '_gen_' . $arg;
            $this->$method();
        }

        foreach( array( '*search', '*searchp', 'tags', 'type', 'ids', 'user', 'remixes', 
                        'sources', 'trackbacksof',
                         'remixesof', 'score', 'lic', 'remixmax', 'remixmin', 'reccby',  
                         'upload', 'thread',
                         'reviewee', '*match', 'reqtags','rand', 'recc', 'collab', 'topic', 
                         'minitems', 'oneof', 'pool', 'uploadmin', 'digrank', 'dynamic'
                        ) as $arg )
        {
            if( strpos($arg,'*',0) === 0 )
                $arg = substr($arg,1);
            else
                $this->_clean_uri_arg($arg);
            if( isset($this->args[$arg]) )
            {
                $method = '_gen_' . $arg;
                $this->$method();
            }
        }

        $this->_gen_limit();

        if( !empty($this->reqtags) )
        {
            $tagfield = $this->_make_field('tags');
            $this->where[] = $this->dataview->MakeTagFilter($this->reqtags,'all',$tagfield);
        }

        if( !empty($this->oneof) )
        {
            $tagfield = $this->_make_field('tags');
            $this->where[] = $this->dataview->MakeTagFilter($this->oneof,'any',$tagfield);
        }


        if( !empty($this->tags) )
        {
            $tagfield = $this->_make_field('tags');
            if( empty($this->args['type']) )
                $this->args['type'] = 'all';
            $this->where[] = $this->dataview->MakeTagFilter($this->tags,$this->args['type'],$tagfield);
        }

        if( !empty($this->sql_p['where']) )
            $this->where[] = $this->sql_p['where'];

        $this->sql_p['where'] = empty($this->where) ? '' : '(' . join( ') AND (', $this->where ) . ')' ;

        if( empty($this->dead) )
        {
            $this->dataviewProps['dataview'] = $this->args['dataview'];
            $this->records =&  $this->dataview->Perform( $this->dataviewProps, $this->sql_p, $this->args['rettype'], $this );
            $this->sql = $this->dataview->sql;
        }


        // ------------- DUMP RESULTS ---------------------

        if( !empty($this->args['dump_query']) && CCUser::IsAdmin() )
        {
            CCDebug::Enable(true);
            $x = CCDebug::IsEnabled();
            CCDebug::PrintVar($this,false);
        }

        if( !empty($_REQUEST['dump_rec']) && CCUser::IsAdmin() )
        {
            CCDebug::Enable(true);
            CCDebug::PrintVar($this->records[0],false);
        }

    }

    function _generate_records_from_cache()
    {
        $cname = cc_temp_dir() . '/query_cache_' . $this->args['cache'] . '.txt';
        if( file_exists($cname) )
        {
            include($cname);
            $this->records =& $_cache_rows;
            //$this->_generate_records();
        }
        else
        {
            $this->_generate_records();

            $data = serialize($this->records);
            $data = str_replace("'","\\'",$data);
            $text = '<? /* This is a temporary file created by ccHost. It is safe to delete. */ ' .
                     "\n" . '$_cache_rows = unserialize(\'' . $data . '\'); ?>';
            CCUtil::MakeSubDirs(dirname($cname));
            $f = fopen($cname,'w+');
            fwrite($f,$text);
            fclose($f);
            chmod($cname,cc_default_file_perms());
        }
    }

    /********************************
    * Generators
    *********************************/
    function _gen_collab()
    {
        if( ($this->args['datasource'] == 'collabs') || ($this->args['datasource'] == 'collab_users') )
        {
            $field = $this->_make_field('collab');
            $this->where[] = sprintf( "($field = '%0d')", $this->args['collab'] );
        }
        elseif( $this->args['datasource'] == 'uploads' )
        {
            $collab_id = sprintf('%0d',$this->args['collab'] );
            if( !empty($collab_id) )
            {
                $ids = CCDatabase::QueryItems('SELECT collab_upload_upload FROM cc_tbl_collab_uploads WHERE collab_upload_collab='.$collab_id);
                if( empty($ids) )
                {
                    $this->dead = true;
                }
                else
                {
                    $this->where[] = '(upload_id IN (' . join(',',$ids) . '))';
                }
            }
        }
    }

    function _gen_date()
    {
        // Check for date limits
        $this->_date_helper('since');
        $this->_date_helper('before');
    }
    
    function _gen_digrank() 
    {
        if( $this->args['digrank'] == -1 ) {
            $this->args['sort'] = 'date';
            $this->args['ord'] = 'desc';
            $this->_gen_sort();
            return;
        }
        /*            
            cooling factor: 
                1     - all time greatest hits
                280   - new-ish 
                10000 - new-ish-er
                
            First thing we do is normalize the scores. (The average at ccMixter
            is 9.7 but there are 10 uploads with well over 100 scores.) We use
            the standard deviance to smooth it out so the order is the (roughly)
            the same but the disparity between the scores is no longer there.
            
            Then we do the Newton cooling thing against the number of days
            since today that the upload happened. That's the:
            
                normalized_score * exp( -cool_factor * time_diff )
                
            the result of that (temperature) is what you sort (desc) 
        */
    $sql1 =<<<EOF
SELECT 
  ROUND(STD(upload_num_scores)) AS std, 
  ROUND(AVG(upload_num_scores) + STD(upload_num_scores)) AS hi, 
  ROUND(AVG(upload_num_scores) - STD(upload_num_scores)) AS lo 
  FROM cc_tbl_uploads WHERE upload_num_scores > 0;  
EOF;
        
        $r = CCDatabase::QueryRow($sql1);
        $std = $r['std'];
        $hi = $r['hi'];
        $lo = (integer)$r['lo'] < 2 ? 2 : $r['lo'];
        $cool =  (float)sprintf("%f",$this->args['digrank']) / 1000000.0;

        $this->sql_p['columns'] = 
          "(@sc:=upload_num_scores) as sc," .
          "(@score:=FLOOR(IF( @sc >  ${hi}, ${hi} + (@sc / ${std}), IF( @sc < ${lo}, ${lo} - (@sc / ${std}), @sc)))) as score," .
          "(@score * exp(-(${cool}) * datediff(now(),upload_date))) as temperature";
        $this->sql_p['order'] = 'temperature desc';
    }

    function _date_helper($pivot)
    {
        // Check for date limit

        $pivot_val = 0;

        if( !empty($this->args[$pivot . 'd']) )     // text date
        {
            $pivot_val = strtotime($this->args[$pivot . 'd']);
            if( $pivot_val < 1 )
                die('invalid date string');
        }
        elseif( !empty($this->args[$pivot . 'u']) ) // unix time
        {
            if( $this->args[$pivot . 'u']{0} === '_' )
                $this->args[$pivot . 'u'] = substr($this->args[$pivot . 'u'],1);
            $pivot_val = sprintf('%0d',$this->args[$pivot . 'u']);
        }

        if( !empty($pivot_val) )
        {
            // yup, another hack
            if( $this->args['datasource'] == 'pool_items' )
            {
                $pivot_date = $pivot_val;
            }
            else
            {
                $pivot_date = date( 'Y-m-d H:i', $pivot_val );
            }
            $field = $this->_make_field('date');
            $op = $pivot == 'since' ? '>' : '<'; // or 'before'
            $this->where[] = "($field $op '$pivot_date')";
        }

    }

    function _gen_dynamic()
    {
        if( $this->args['datasource'] == 'cart' && !empty($this->args['dynamic']) )
        {
            $this->where[] = 'LENGTH(cart_dynamic) > 0';
        }
    }

    function _gen_ids()
    {
        $ids = array();
        
        if( empty($this->args['dataview']) || $this->args['dataview'] != 'tags' ) {
            $ids = $this->_split_ids($this->args['ids']);
        }
        if( $ids )
        {
            $field = $this->_make_field('id');
            $this->where[] = "($field IN (" . join(',',$ids) . '))';
        }
    }

    function _split_ids($ids)
    {
        return array_unique(preg_split('/([^0-9]+)/',$ids,0,PREG_SPLIT_NO_EMPTY));
    }

    function _gen_lic()
    {
        if( empty($this->args['lic']) || ($this->args['lic'] == 'all') )
        {
            return;
        }

        $T = array(
            'by'       =>  array('attribution','attribution_3'),
            'sa'       =>  array('share-alike','share-alike_3'),
            'nd'       =>  array('noderives','noderives_3'),
            's'        =>  array('sampling'),
            'splus'    =>  array('sampling+'),
            'nc'       =>  array('noncommercial','noncommercial_3'),
            'ncsa'     =>  array('by-nc-sa','by-nc-sa_3'),
            'ncnd'     =>  array('by-nc-nc','by-nc-nd_3'),
            'ncsplus'  =>  array('nc-sampling+'),
            'pd'       =>  array('publicdomain','cczero') ,
            'zero'     =>  array('cczero') ,
            );

        // available for commercial use, even ads
        $T['open']  = array_merge( $T['by'], $T['pd'], $T['nd'], $T['sa'], $T['zero'] );

        // available for commercial use, except ads
        $T['safe']  = array_merge( $T['open'], $T['s'], $T['splus'] );

        // requires supra-nc for commercial use
        $T['allnc'] = array_merge( $T['nc'], $T['ncsa'], $T['ncnd'], $T['ncsplus'] );

        $lics =explode(',',trim($this->args['lic']));

        $license_ids = array();

        foreach( $lics as $lic )
        {
            if( !array_key_exists( $lic, $T ) )
                die('invalid license argument');
            $license_ids = array_merge($license_ids,$T[$lic]);
        }
        
        $field = $this->_make_field('license');
        $license_ids = join( "', '", $license_ids );
        $this->where[] = "($field IN ('$license_ids'))";
    }

    function _gen_limit()
    {
        $A =& $this->args;

        if( !empty($this->sql_p['limit']) )
        {
            $A['limit'] = $this->sql_p['limit'];
        }

        if( !empty($A['limit']) )
        {
            if( preg_match("/[a-zA-Z]/", $A['limit']) )
            {
                global $CC_GLOBALS;
                
                // alias:
                if( $A['limit'] == 'query' || !array_key_exists($A['limit'], $CC_GLOBALS) )
                    $A['limit'] = 'querylimit';                 
                    
                $A['limit'] = $CC_GLOBALS[$A['limit']];
            }
            
            if( !empty($A['offset']) )
                $A['offset'] = sprintf('%0d',$A['offset'] );

            if( empty($A['offset']) || ($A['offset'] <= 0) )
                $A['offset'] = '0';

            $this->sql_p['limit'] = $A['limit'] . ' OFFSET ' . $A['offset'];
        }
        
    }

    function _gen_match()
    {
        // this only works for specific dataviews (see search_remix_artist.php)
        $this->sql_p['match'] = addslashes(trim($this->args['match']));
    }

    function _gen_minitems()
    {
        if( $this->args['datasource'] == 'cart' && !empty($this->args['minitems']) )
        {
            $this->where[] = 'cart_num_items >= ' . $this->args['minitems'];
        }
    }

    function _gen_minup() 
    {
        $num =  CCUtil::CleanNumber( $this->args['minup'] );
        if( $num > 0 )
        {
            $this->where[] = 'user_num_uploads >= ' . $num;
        }
    }

    function _gen_minrx() 
    {
        $num =  CCUtil::CleanNumber( $this->args['minrx'] );
        if( $num > 0 )
        {
            $this->where[] = 'user_num_remixes >= ' . $num;
        }
    }
    
    function _gen_oneof()
    {
        $this->oneof = preg_split('/[\s,+]+/',$this->args['oneof'],-1,PREG_SPLIT_NO_EMPTY);
    }

    function _gen_pool()
    {
        if( $this->args['datasource'] == 'pools') 
            $f = 'pool_id';
        
        if( $this->args['datasource'] == 'pool_items' )
            $f = 'pool_item_pool';
            
        if( !empty($f) )
        {
            if( !(intval($this->args['pool']) > 0 ) )
            {
                $pool_id = CCDatabase::QueryItem(
                    "SELECT pool_id FROM cc_tbl_pools WHERE pool_short_name = '{$this->args['pool']}'");
            }
            else
            {
                $pool_id = sprintf('%0d',$this->args['pool']);
            }

            if( !empty($pool_id) )
            {
                $this->where[] = $f . ' = ' . $pool_id;
            }
        }
    }

    function _gen_rand()
    {
        $this->sql_p['order'] = 'RAND()';
    }

    function _gen_reccby()
    {
        $user_id = CCDatabase::QueryItem("SELECT user_id FROM cc_tbl_user WHERE user_name= '{$this->args['reccby']}'");
        if( !empty($user_id) && $this->args['datasource'] == 'uploads')
        {
            $this->sql_p['joins'][] = 'cc_tbl_ratings ON ratings_upload=upload_id';
            $this->where[] = 'ratings_user = ' . $user_id;
            if( $this->args['format'] != 'count' )
            {
                $this->sql_p['order'] = 'ratings_id DESC'; // er, ....
            }
        }
    }

    function _gen_remixmax()
    {
        $field = $this->_make_field('num_remixes');
        $this->where[] = "(${field} <= '{$this->args['remixmax']}')";
    }

    function _gen_remixmin()
    {
        $field = $this->_make_field('num_remixes');
        $this->where[] = "(${field} >= '{$this->args['remixmin']}')";
    }        

    function _gen_uploadmin()
    {
        $field = $this->_make_field('num_uploads');
        $this->where[] = "(${field} >= '{$this->args['uploadmin']}')";
    }        

    /*
    * List the remixes of an upload (see also remix filter in cc-filter.php)
    */
    function _gen_remixes()
    {
        $this->_heritage_helper('remixes','tree_child','cc_tbl_tree','tree_parent','upload_id');
    }

    function _gen_reqtags()
    {
        $this->reqtags = preg_split('/[\s,+]+/',$this->args['reqtags'],-1,PREG_SPLIT_NO_EMPTY);
    }

    
    /*
    * List the remixes of a PERSON
    */
    function _gen_remixesof()
    {
        $user_id = CCUser::IDFromName($this->args['remixesof']);
        if( empty($user_id) )
        {
            $this->dead = true;
            return;
        }
        $sql = "SELECT tree_child FROM cc_tbl_tree JOIN cc_tbl_uploads ON tree_parent = upload_id WHERE upload_user = " . $user_id;
        $ids = CCDatabase::QueryItems($sql);
        if( empty($ids) )
        {
            $this->dead = true;
            return;
        }
        $this->where[] = 'upload_id IN (' . join(',',$ids) . ')';
    }

    /*
    * Reviews left FOR a person
    */
    function _gen_reviewee()
    {
        if( $this->args['datasource'] == 'topics' )
        {
            /*
                Assumes this is dataview !!!:
            $this->sql_p['joins'] = ' cc_tbl_uploads ups      ON topic_upload = ups.upload_id ' .
                                    'JOIN cc_tbl_user    reviewee ON ups.upload_user = reviewee.user_id';
            */
            $this->where[] = "(reviewee.user_name = '{$this->args['reviewee']}')";
        }
    }

    function _gen_score()
    {
        $this->args['score'] = sprintf('%0d',$this->args['score']);
        if( $this->args['datasource'] == 'user' )
            $this->where[] = 'user_num_scores >= ' . $this->args['score'];
        elseif( $this->args['datasource'] == 'uploads' )
            $this->where[] = 'upload_num_scores >= ' . $this->args['score'];
    }

    function _search_helper($columns,$term)
    {
        if( $term{0} == '-' )
        {
            $neg = 'NOT';
            $term = substr($term,1);
        }
        else
        {
            $neg = '';
        }
        return "LOWER(CONCAT({$columns})) {$neg} LIKE '%{$term}%'";
    }

    function _search_term_parser($term)
    {
        preg_match_all('/((-?"([^"]+)")|(?<=^|\s)([^"\s]+)(?=\s|$))/',$term,$m);
        $res = array();
        foreach($m[0] as $mx)
        {
            if( $mx{0} == '-' )
            {
                if( strlen($mx) > 1 )
                {
                    if( $mx{1} == '"' )
                        $mx = str_replace('"','',$mx);
                }
                else
                {
                    continue;
                }
            }
            elseif( $mx{0} == '"' )
            {
                $mx = str_replace('"','',$mx);
            }

            $res[] = $mx;
        }
        return $res;
    }

    function _gen_search()
    {
        $this->_gen_search_helper('search');
    }

    function _gen_searchp()
    {
        $this->_gen_search_helper('searchp');
    }
    
    function _gen_search_helper($argtype)
    {
        $search_meta = array();
        CCEvents::Invoke( CC_EVENT_SEARCH_META, array(&$search_meta) );
        $is_searchp = $argtype == 'searchp';
        $ds = $this->args['datasource'];
        
        if( $is_searchp ) {
            $grp = 'searchp';   
        } else {        
            $grp = empty($this->args['group']) ? 0 : $this->args['group'];
            if( empty($grp) ) {
                $grp = empty($this->args['type']) ? 0 : $this->args['type'];
            }
        }

        // added uploads_alt group just for query searches (probably belongs somewhere else)
        //
        // usage: api/query?type=uploads_alt&s=QUERY_TEXT
        //
        // This will add the user name fields to the search, otherwise only upload info
        // is searched where type=uploads (which is the default)
        //
        $search_meta[] = array
                    (
                        'group' => 'uploads_alt',
                        'template' => 'search_uploads',
                        'datasource' => 'uploads',
                        'match' => 'user_name,user_real_name,upload_name,upload_description,upload_tags',
                        'join_user_on_count' => 1
                    );

        // added these for more precise dig-style searches (without descriptions)
        $search_meta[] = array
                    (
                        'group' => 'searchp',
                        'template' => 'search_uploads',
                        'datasource' => 'uploads',
                        'match' => 'upload_name,upload_tags',
                        'join_user_on_count' => 1
                    );
        $search_meta[] = array
                    (
                        'group' => 'searchp',
                        'datasource' => 'user',
                        'match' => 'user_name,user_real_name',
                        'dataview' => 'user_basic'
                    );
        
        foreach( $search_meta as $meta )
        {
            if( (($grp === 0) || ($grp == $meta['group'])) && ($ds == $meta['datasource']) )
            {
                $search = str_replace("'","\\'",(trim($this->args[$argtype])));
                $strlow = strtolower($search);
                global $CC_GLOBALS;
                if( empty($CC_GLOBALS['use_text_index']) )
                {
                    $stype = empty($this->args['search_type']) ? 'any' : $this->args['search_type'];
                    switch( $stype )
                    {
                        case 'match':
                        {
                            $this->where[] = $this->_search_helper($meta['match'],$strlow);
                            break;
                        }

                        case 'all':
                        {
                            $terms = $this->_search_term_parser($strlow);
                            foreach( $terms as $term )
                                $this->where[] = $this->_search_helper($meta['match'],$term);
                            break;
                        }

                        case 'any':
                        {
                            $terms = $this->_search_term_parser($strlow);
                            $ors = array();
                            foreach( $terms as $term )
                            {
                                if( $term{0} == '-' )
                                    $this->where[] = $this->_search_helper($meta['match'],$term);
                                else
                                    $ors[] = $this->_search_helper($meta['match'],$term);
                            }
                            if( !empty($ors) )
                                $this->where[] = join( ' OR ', $ors );
                            break;
                        }
                    }
                }
                else
                {
                    $this->where[] = "MATCH({$meta['match']}) AGAINST( '$search' IN BOOLEAN MODE )";
                }
                
                if( $this->args['format'] == 'count' ) {
                    if( !empty($meta['join_user_on_count'])   ) {
                        $this->AddJoin( 'cc_tbl_user ON upload_user = user_id' );
                    }
                } else if( !empty($meta['dataview']) ) {
                    $this->GetSourcesFromDataview($meta['dataview']);
                }
                break;
            }
        }
    }

    function _gen_sort()
    {
        if( !empty($this->sql_p['order']) )
        {
            // this can happen when a formatter hacks in during ApiQuerySetup
            return;
        }

        $args =& $this->args;
        if( !empty($args['ids']) || !empty($args['nosort'])  )
        {
            $this->sql_p['order'] = '';
            return;
        }

        if( ($args['datasource'] == 'uploads') && ($args['sort'] == 'rank') )
        {
            $configs =& CCConfigs::GetTable();
            $ratings = $configs->GetConfig('chart');
            $formula = empty($ratings['rank_formula']) ?
                          '((upload_num_scores*4) + (upload_num_playlists*2))' :
                          $ratings['rank_formula'];
            $comma = empty($this->sql_p['columns']) ? '' : ',';
            $this->sql_p['columns'] =  $comma . $formula . ' AS qrank';
        }

        $sorts = $this->GetValidSortFields();

        if( !empty($sorts[$args['sort']]) )
        {
            $args['ord'] = empty($args['ord']) || (strtoupper($args['ord']) == 'DESC') ? 'DESC' : 'ASC';

            $this->sql_p['order'] = $sorts[$args['sort']][1] . ' ' . $args['ord'];
        }

    }
    
    /*
    * List the sources of an upload (see also remix filter in cc-filter.php)
    */
    function _gen_sources()
    {
        switch( $this->args['datasource'] )
        {
            case 'uploads':
                $this->_heritage_helper('sources','tree_parent','cc_tbl_tree','tree_child','upload_id');
                break;
            case 'pool_items':
                $this->_heritage_helper('sources','pool_tree_pool_parent','cc_tbl_pool_tree','pool_tree_child','pool_item_id');
                break;
            default:
                die('invalid datasource for "sources"');
                break;
        }
    }

    function _gen_tags()
    {
        $this->tags = preg_split('/[\s,+]+/',$this->args['tags'],-1,PREG_SPLIT_NO_EMPTY);
    }

    function _gen_thread()
    {
        if( $this->args['datasource'] == 'topics' )
        {
            $thread = $this->args['thread'];
            if( $thread == -1 )
                $this->where[] = "topic_thread > 0";
            else
                $this->where[] = "topic_thread = $thread";
        }
    }

    function _gen_topic()
    {
        if( $this->args['datasource'] == 'topics' )
        {
            $topic = trim($this->args['topic']);
            if( strlen($topic) )
            {
                $slug_sql = cc_get_topic_name_slug();
                $this->where[] = "{$slug_sql} = '{$this->args['topic']}'";
            }
        }
    }

    function _gen_trackbacksof()
    {        
        $id = sprintf('%0d',$this->args['trackbacksof']);

        $sql = "SELECT pool_tree_pool_child as pool_item_id FROM cc_tbl_pool_tree " .
                 "JOIN cc_tbl_pool_item ON pool_tree_pool_child = pool_item_id " .
                 "WHERE pool_item_approved > 0 AND pool_tree_parent = " . $id . ' ' .
                 "ORDER BY pool_item_id DESC " ;

        $rows = CCDatabase::QueryItems($sql);
        if( empty($rows) )
        {
            //$this->where[] = $kf . 'pool_item_id IN (' . $sql . ')';
            $this->dead = true;
        }
        else
        {
            $this->where[] = 'pool_item_id IN (' . join(',',$rows) . ')';
        }
    }
    

    function _gen_type()
    {
        // 'type' for uploads (as applied to tags) are handled elsewhere (see 
        // call to MakeTagFilter in this file)

        if( $this->args['datasource'] == 'topics' )
        {
            $this->where[] = "topic_type = '{$this->args['type']}'";
        }
    }

    function _gen_upload()
    {
        if( $this->args['datasource'] == 'cart' )
        { 
            $this->sql_p['joins'] = 'cc_tbl_cart_items on cart_item_cart=cart_id';
            $field = 'cart_item_upload';
        }
        elseif( $this->args['datasource'] == 'topics' || ($this->args['datasource'] == 'ratings'))
        {
            $field = $this->_make_field('upload');
        }

        if( !empty($field) )
            $this->where[] = $field ." = '{$this->args['upload']}'";
    }

    function _gen_user()
    {
        $user = $this->args['user'];
        if( $user{0} == '-' )
        {
            $user = substr($user,1);
            $op = '<>';
        }
        else
        {
            $op = '=';
        }

        if( $this->args['datasource'] == 'pool_items' )
        {
            $field = 'pool_item_artist';
            $user_id = $user;
        }
        else
        {
            $w['user_name'] = $user;
            $users =& CCUsers::GetTable();
            $user_id = $users->QueryKey($w);
            if( $this->args['datasource'] == 'user' )
                $field = 'user_id';
            else
                $field = $this->_make_field('user');
        }
        $this->where[] = "($field $op '{$user_id}')";
    }

    function _gen_visible()
    {
        if( !empty($this->_ignore_visible) )
            return;

        $need_user = false; // if the user is not an admin they
                            // should only see their uploads.
                            // this flag controls that filter

        $banned = 0; // default for banned upload = off

        if( !empty($this->args['mod']) )
        {
            // user requested banned uploads 
            if( CCUser::IsAdmin() )
            {
                $banned = 1;
            }
            else
            {
                if( CCUser::IsLoggedIn() )
                {
                    $banned = 1;
                    $need_user = true;
                }
            }
        }

        $published = 1; // default for published upload = on
        
        if( !empty($this->args['unpub']) )
        {
            // user requested unpublished uploads

            if( CCUser::IsAdmin() )
            {
                $published = 0;
            }
            else
            {
                if( CCUser::IsLoggedIn() )
                {
                    $published = 0;
                    $need_user = true;
                }
            }
        }

        // we special case for when both mod=1 and unpub=1
        // to make sure we return both 

        $op = ( $banned && !$published ) ? ' OR ' : ' AND ';

        $this->where[] = "(upload_banned = $banned $op upload_published = $published)";

        if( $need_user )
            $this->where[] = '(upload_user='.CCUser::CurrentUser().')';

    }

    function _heritage_helper($key,$f1,$t,$f2,$kf)
    {
        $id = sprintf('%0d',$this->args[$key]);
        // sigh, I can't get subqueries to work.
        $sql = "SELECT $f1 as $kf FROM $t WHERE $f2 = $id";
        $rows = CCDatabase::QueryItems($sql);
        
        if( empty($rows) )
        {
            //$this->where[] = $kf . ' IN (' . $sql . ')';
            $this->dead = true;
        }
        else
        {
            $this->where[] = $kf . ' IN (' . join(',',$rows) . ')';
        }
    }


    function _arg_alias()
    {
        $this->_arg_alias_ref($this->args);
    }

    function _arg_alias_ref(&$args)
    {
        $aliases = array( 'f'      => 'format',
                          't'      => 'template',
                          'm'      => 'template',
                          'macro'  => 'template',
                          'tmacro' => 'template',
                          'u'      => 'user',
                          'q'      => 'search',
                          'query'  => 'search',
                          's'      => 'search',
                       );

        foreach( $aliases as $short => $long )
        {
            if( isset($args[$short]) )
            {
                $args[$long] = $args[$short];
                unset( $args[$short] );
            }
        }
    }

    /**
     * @access private
     */
    function _make_field($field)
    {
        // yes, special case hacks go here
        if( $field =='date')
        {
            if( $this->args['datasource'] == 'user' ) 
              return 'user_registered';
            elseif( $this->args['datasource'] == 'pool_items' ) 
              return 'pool_item_timestamp';
        }
        else
        {
            if( $this->args['datasource'] == 'tags' ) 
              return( 'tags_tag' );
        }
        

        return preg_replace('/s?$/', '', $this->args['datasource']) . '_' . $field;
    }

    function _resort_records(&$records,&$sort_order,$sort_key)
    {
        if( !empty($sort_order) )
        {
            $sorted = array();
            $count = count($records);
            for( $i = 0; $i < $count; $i++ )
            {
                $sorted[ $sort_order[ $records[$i][$sort_key] ] ] = $records[$i];
            }
            $records = $sorted;
            $sorted = null;
            ksort($records);
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','query'),   array( 'CCQuery', 'QueryURL'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Query API'), CC_AG_QUERY );

        cc_tcache_kill(); // this is probably an ?update=1 so kill the cache...
    }

    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['querylimit'] =
               array(  'label'      => _('Limit Queries'),
                       'form_tip'   => _("Limit the number of records returned from api/query (0 or blank means unlimited - HINT: that's a bad idea)"),
                       'value'      => 20,
                       'class'      => 'cc_form_input_short',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE );
        }
    }

    function GetValidSortFields()
    {
        // this method is a little shaky...

        if( $this->args['datasource'] == 'cart' )
        {
            return array( 'name' => array( _('Cart (playlist) name'), 'cart_name' ),
                          'date' => array( _('Cart (playlist) date'), 'cart_date' ),
                        );
        }


        if( $this->args['datasource'] == 'pool_items' )
        {
            return array( 'name' => array( _('Pool item name'), 'pool_item_name' ),
                          'user' => array( _('Pool item artist'), 'pool_item_artist' ),
                          'date' => array( _('Pool item date'), 'pool_item_timestamp' ),
                          'id'   => array( _('Internal id'), 'pool_item_id'),
                        );
        }

        if( $this->args['datasource'] == 'topics' )
        {
            return array( 'name' => array( _('Topic name'), 'topic_name' ),
                          'date' => array( _('Topic date'), 'topic_date' ),
                          'type' => array( _('Topic type'), 'topic_type' ),
                          'left' => array( _('Topic tree'), 'topic_left' ),
                        );
        }

        $user = array( 'fullname' => array( _('Aritst display name'),  'TRIM(LOWER(user_real_name))' ),
                          'date'     => array( _('Registration date'),  'user_registered' ),
                        'user'               => array( _('Artist login name'), 'user_name'),
                        'registered'         => array( _('Artist registered'), 'user_registered'),
                        'user_remixes'       => array( _('Number of remixes'), 'user_num_remixes'),
                        'remixed'            => array( _('Number of times remixed'), 'user_num_remixed'),
                        'uploads'            => array( _('Number of uploads'), 'user_num_uploads'),
                        'userscore'          => array( _('Artists\'s average rating'), 'user_score'),
                        'user_num_scores'    => array( _('Number of ratings'), 'user_num_scores'),
                        'user_reviews'       => array( _('Reviews left by artist'), 'user_num_reviews'),
                        'user_reviewed'      => array( _('Reviews left for artist'), 'user_num_reviewed'),
                        'posts'              => array( _('Forum topics by artist'), 'user_num_posts'),
                        );

        if( $this->args['datasource'] == 'user' )
        {
            return $user;
        }

        if( ($this->args['datasource'] == 'collabs') ||
            ($this->args['datasource'] == 'collab_user') )
        {
            return array_merge( $user, 
                       array( 'name' => array( _('Collaboration name'), 'collab_name' ),
                              'date' => array( _('Collaboration  date'), 'collab_date' ),
                              'user' => array( _('Collaboration owner'), 'collab_user' ),
                        ) );
        }

        if( $this->args['datasource'] != 'uploads' )
            return '';

        
        return array_merge( $user, array(
            'name'               => array( _('Upload name'),             'TRIM(TRIM(BOTH \'"\' FROM LOWER(upload_name)))'),
            'lic'                => array( _('Upload license'),          'upload_license'),
            'date'               => array( _('Upload date'),             'upload_date'),
            'last_edit'          => array( _('Upload last edited'),      'upload_last_edit'),
            'remixes'            => array( _('Upload\'s remixes'),       '(upload_num_remixes+upload_num_pool_remixes)'),
            'sources'            => array( _('Upload\'s sources'),       '(upload_num_sources+upload_num_pool_sources)'),
            'num_scores'         => array( _('Number of ratings'),       'upload_num_scores'),
            'num_playlists'      => array( _('Number of playlists'),     'upload_num_playlists desc,upload_date'),
            'id'                 => array( _('Internal upload id'),      'upload_id'),
            'local_remixes'      => array( _('Upload\'s local remixes'), 'upload_num_remixes'),
            'pool_remixes'       => array( _('Upload\'s remote remixes'),'upload_num_pool_remixes'),
            'local_sources'      => array( _('Upload\'s local sources'), 'upload_num_sources'),
            'pool_sources'       => array( _('Upload\'s sample pool sources'), 
                                                    'upload_num_pool_sources'),

            'rank'               => array( _('Upload Rank'), 'qrank'),
            'score'              => array( _('Upload\'s ratings'), 'upload_score'),
            ));
    }


} // end of class CCQuery


/**
* @private
*/
function cc_tcache_kill()
{
    $files = glob(cc_temp_dir() . '/query_cache_*.txt');
    if( $files !== false )
        foreach( $files as $file )
            unlink($file);
}


?>
