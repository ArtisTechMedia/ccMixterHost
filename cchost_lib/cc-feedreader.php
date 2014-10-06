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
* $Id: cc-feedreader.php 11321 2008-11-25 02:28:42Z fourstones $
*
*/

/**
* Module to read and parse XML RSS and Atom feeds
*
* @package cchost
* @subpackage api
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Abstract base class for downloading and parsing XML
*
* Derived classes need to impelement the follwing methods:
*
* <code>
* function cb_element_start ( $parser, $name, $attribs )
* {   
* }
* 
* function cb_element_data($parser, $data) 
* {   
* }
* 
* function cb_element_end ( $parser, $name)
* {
* }
* </code>
* @package cchost
* @subpackage api
*/
class CCXmlReader
{
    /**
    * @var string Error string
    */
    var $ERROR;

    /**
    * Initialize a parse session
    *
    * This is internal, you probably want {@link parse}
    *
    * @param string &$data XML data that will be parsed
    */
    function cc_xml_parser_create(&$data) 
    {
        // Default XML encoding is UTF-8
        $encoding = 'utf-8';
        $bom = false;

        // Check for UTF-8 byte order mark (PHP5's XML parser doesn't handle it).
        if (!strncmp($data, "\xEF\xBB\xBF", 3)) 
        {
            $bom = true;
            $data = substr($data, 3);
        }

        // Check for an encoding declaration in the XML prolog if no BOM was found.
        if (!$bom && ereg('^<\?xml[^>]+encoding="([^"]+)"', $data, $match)) 
        {
            $encoding = $match[1];
        }

        // Unsupported encodings are converted here into UTF-8.
        $php_supported = array('utf-8', 'iso-8859-1', 'us-ascii');
        if (!in_array(strtolower($encoding), $php_supported)) 
        {
            if (function_exists('iconv')) {
                $data = @iconv($encoding, 'utf-8', $data);
            }
            else if (function_exists('mb_convert_encoding')) {
                $data = @mb_convert_encoding($data, 'utf-8', $encoding);
            }
            else if (function_exists('recode_string')) {
                $data = @recode_string($encoding .'..utf-8', $data);
            }
            else {
                CCDebug::Log("Could not convert XML encoding '$encoding' to UTF-8.");
                return 0;
            }
        }

        $xml_parser = xml_parser_create($encoding);
        xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, 'utf-8');
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
        return $xml_parser;
    }

    /**
    * Download and parse a URL
    *
    * URL will be connected and downloaded (through Snoopy) and the contents
    * returned will be parsed. 
    *
    * @param string $url URL to download
    * @param array $headers Optional raw HTTP headers to send along with URL
    */
    function cc_parse_url($url, $headers = "" ) 
    {
        require_once('cchost_lib/snoopy/Snoopy.class.php');

        $snoopy = new Snoopy();
        $snoopy->read_timeout = 10;
        if (is_array($headers) ) {
            $snoopy->rawheaders = $headers;
        }
        
        $ok = @$snoopy->fetch($url);

        //d($snoopy); exit;

        if( $ok )
            return $this->parse($snoopy->results);

        return 0;
    }

    /*
    * Parse XML using PHP Sax style callbacks
    *
    * @param string $data XML data to parse
    * @return boolean $ok 
    */
    function parse($data)
    {
        // parse the data:
        $xml_parser = $this->cc_xml_parser_create($data);

        xml_set_element_handler($xml_parser, array($this,'cb_element_start'), 
                                             array($this,'cb_element_end'));
        xml_set_character_data_handler($xml_parser, 
                                             array($this,'cb_element_data'));

        if (!xml_parse($xml_parser, $data, 1)) 
        {
            $error_msg = 'Feed reader returns error: ' .
                            xml_error_string(xml_get_error_code($xml_parser)) .
                            ' (' . xml_get_current_line_number($xml_parser) . ')';

            CCDebug::Log($error_msg);
            $this->ERROR = $error_msg;
            return 0;
        }
        xml_parser_free($xml_parser);
        return 1;
    }
}


/**
* internal: Struct used as global trapper
*
* the xml parser in php plays around with instances
* of the call back class; it seems to like the 
* global variables so comply.
*
* @package cchost
* @subpackage api
*/
class CCFeedStatusReaderData
{
    /** 
    * @var array 
    * expected:
    *   status['status']
    *   status['message']
    */
    var $status;

    /**
    * @var string Next element
    */
    var $waiting_for;

    /**
    * @var string Error string
    */
    var $ERROR;
}

/**
* Global helper (see {@link CCFeedStatusReaderData})
* @package cchost
* @subpackage api
*/
$ccFSR = new CCFeedStatusReaderData();

/**
* Feed status return reader
*
* Quick and dirty parser for reading status returns from RESTful 
* type APIs
*
* @package cchost
* @subpackage api
*/
class CCFeedStatusReader extends CCXmlReader
{
    /**
    * Parse status XML
    *
    * @param string $data XML data
    * @return object $feedStatusReaderData CCFeedStatusReaderData
    */
    function parse($data)
    {
        parent::parse($data);
        global $ccFSR;
        $ccFSR->ERROR = $this->ERROR;
        return $ccFSR;
    }

    /**
    * Start element callback
    * 
    * As specified in PHP {@link http://us2.php.net/manual/en/function.xml-set-element-handler.php cb_element_start})
    *
    */
    function cb_element_start ( $parser, $name, $attribs )
    {   
        global $ccFSR;
        $name = strtolower($name);
        if( $name == 'error' || $name == 'message' )
            $ccFSR->waiting_for = $name;
    }

    /**
    * Character data callback
    * 
    * As specified in PHP {@link http://us2.php.net/manual/en/function.xml-set-element-handler.php cb_element_data})
    *
    */
   function cb_element_data($parser, $data) 
    {
        global $ccFSR;
        if( !empty($ccFSR->waiting_for) )
        {
            $ccFSR->status[ $ccFSR->waiting_for ] = $data;
            $ccFSR->waiting_for = null;
        }
    }
}

/**
* Global struct helper for parsing an RSS or Atom feed
*
* Returned by {@link CCFeedReader::parse()} 
*
* @package cchost
* @subpackage api
*/
class CCFeedReaderData
{
    /** 
    * @var array $items Results
    */
    var $items;

    /**#@+
    * @var boolean 
    */
    var $is_atom;
    var $is_rss;
    /**#@-*/

    /**#@+
    * @access private
    */
    var $current_item;
    var $channel;
    var $waiting_for;
    var $wait_for_special;
    /**#@-*/
}

/**
* @package cchost
* @subpackage api
*/
$ccFR = new CCFeedReaderData();

/**
* @package cchost
* @subpackage api
*/
class CCFeedReader extends CCXmlReader
{
    function parse($data)
    {
        global $ccFR;

        $ccFR->items = array();
        $ccFR->channel = array(
                              'title' => '',
                              'link' => '', 
                              'date_timestamp' => '',
                              'description' => '' );
        $ccFR->is_atom = false;
        $ccFR->is_rss = false;
        $ccFR->waiting_for = null;
        $ccFR->wait_for_special = null;

        $ok = parent::parse($data);

        $ccFSR->ERROR = $this->ERROR;

        if( $ok )
        {
            $count = count($ccFR->items);
            $keys = array_keys($ccFR->items);
            for( $i = 0; $i< $count; $i++ )
            {
                $I =& $ccFR->items[ $keys[$i] ];
                
                if( !empty($I['tags']) )
                    $I['category'] = join(', ',$I['tags']);
                unset($I['tags']);

                if( !isset($I['artist']) )              $I['artist'] = '';
                if( !isset($I['enclosure']['url']) )    $I['enclosure']['url'] = '';
                if( !isset($I['enclosure']['length']) ) $I['enclosure']['length'] = '';
                if( !isset($I['enclosure']['type']) )   $I['enclosure']['type'] = '';
                if( !isset($I['category']) )            $I['category'] = '';
                if( !isset($I['guid']) )                $I['guid'] = '';
                if( !isset($I['license_url']) )         $I['license_url'] = '';
                if( !isset($I['description']) )         $I['description'] = '';
                if( !isset($I['date_timestamp']) )      $I['date_timestamp'] = '';
            }
        }

        return $ccFR;
    }

    function cb_element_start ( $parser, $name, $attribs )
    {   
        global $ccFR;

        $name = strtolower($name);

        /* ATOM:
            author       category       content
            entry        feed           id
            link         name           title     updated
        */

        /* RSS 
            category          cc:license    channel 
            content:encoded   dc:creator    description 
            enclosure         guid          item 
            language          lastBuildDate link 
            pubDate           rss           title 
        */

        switch($name)
        {
            case 'feed':
                $ccFR->is_atom = true;
                break;

            case 'rss':
                $ccFR->is_rss = true;
                break;

            case 'item':
            case 'entry':
                $ccFR->current_item = array();
                break;

            case 'name':
            case 'dc:creator':
            case 'dc:author':  // early ccHosts spit this
                $ccFR->waiting_for = 'artist';
                break;
            
            case 'id':
            case 'guid':
                $ccFR->waiting_for = 'guid';
                break;

            case 'category':
                if( $ccFR->is_atom )
                    $ccFR->current_item['tags'][] = $attribs['term'];
                else
                    $ccFR->wait_for_special = 'tag';
                break;

            case 'content':
            case 'description':
                $ccFR->waiting_for = 'description';
                break;

            case 'content:encoded':
                $ccFR->waiting_for = 'encoded_description';
                break;

            case 'link':
                $this->wait_for_link($attribs);
                break;

            case 'cc:license':
            case 'creativecommons:license':
                if( empty($attribs['rdf:about']) ) // cchost 1.0 emitted this
                    $ccFR->waiting_for = 'license_url';
                else
                    $ccFR->current_item['license_url'] = $attribs['rdf:about'];
                break;

            case 'enclosure':
                $ccFR->current_item['enclosure'] = $attribs;
                break;

            case 'summary':
            case 'title':
                $ccFR->waiting_for = $name;
                break;

            case 'updated':
            case 'created':
            case 'modified':
            case 'pubdate':
            case 'lastbuilddate':
                $ccFR->wait_for_special = 'date_timestamp';
                break;
        }
    }

    function wait_for_link($attribs)
    {
        global $ccFR;

        if( empty($attribs['rel']) )
        {
            $ccFR->waiting_for = 'link';
        }
        else
        {
            switch( $attribs['rel'] )
            {
                case 'license':
                    $ccFR->current_item['license_url'] = $attribs['href'];
                    break;
                case 'enclosure':
                    $attribs['url'] = $attribs['href'];
                    unset($attribs['href']);
                    unset($attribs['rel']);
                    $ccFR->current_item['enclosure'] = $attribs;
                    break;
                case 'alternate':
                default:
                    if( !isset($ccFR->current_item) )
                        $ccFR->channel['link'] = $attribs['href'];
                    else
                        $ccFR->current_item['link'] = $attribs['href'];
                    break;
            }
        }
    }

    function cb_element_end ( $parser, $name)
    {
        global $ccFR;

        $name = strtolower($name);

        switch( $name )
        {
            case 'item':
            case 'entry':
                $ccFR->items[] = $ccFR->current_item;
                unset($ccFR->current_item);
                break;
        }
        $ccFR->waiting_for = null;
    }

    function cb_element_data($parser, $data) 
    {
        global $ccFR;

        if( isset($ccFR->wait_for_special) )
        {
            $wfs = $ccFR->wait_for_special;
            $ccFR->wait_for_special = null;

            switch( $wfs )
            {
                case 'date_timestamp':
                    $time = strtotime($data);
                    if( ($time === false) || ($time < 0) )
                    {
                        $time = CCUtil::ParseW3cdtfDate($data);
                        if( $time === false )
                            $time = time();
                    }
                    $data = date( 'Y-m-d H:i:s', $time );
                    $ccFR->waiting_for = 'date_timestamp';
                    break;

                 case 'tag':
                    $ccFR->current_item['tags'][] = $data;
                    return;
            }

        }

        if( isset($ccFR->waiting_for) )
        {
            if( isset($ccFR->current_item) )
            {
                $wfitem =& $ccFR->current_item[ $ccFR->waiting_for ];
                if( empty($wfitem) )
                    $wfitem = '';
                $wfitem .= $data;
            }
            else
            {
                $ccFR->channel[ $ccFR->waiting_for ] = $data;
            }
        }
    }

}


?>