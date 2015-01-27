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
* $Id: cc-search.php 12624 2009-05-18 15:47:40Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');


class CCSearch
{
    function Search($ignore_request=false)
    {
        if( !empty($_REQUEST['search_text']) )
        {
            $search_text = CCUtil::StripText($_REQUEST['search_text']);

            if( !empty($search_text) )
            {
                $this->do_results($search_text);
                return;
            }
        }

        $this->do_search();
    }

    function do_search()
    {
        global $CC_GLOBALS;

        $search_meta = array();
        CCEvents::Invoke( CC_EVENT_SEARCH_META, array(&$search_meta) );

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_search');
        $form = new CCSearchForm($search_meta,'normal');
        $page->AddForm( $form->GenerateForm() );
        if( !empty($CC_GLOBALS['show_google_form']) )
            $page->AddMacro('google_search');

    }

    function OnSearchMeta(&$search_meta)
    {
        $user_meta =             array(
                'template'   => 'search_users',
                'title'      => 'str_search_users',
                'datasource' => 'user',
                'fields'     => array(),
                'group'      => 'user',
                'match'      => 'user_name,user_real_name,user_description',
            );
            
        if( empty($search_meta) )
        {
            $search_meta = array( $user_meta );
        }
        else
        {
            array_unshift($search_meta, $user_meta );
        }
        
        array_unshift($search_meta,
            array(
                'template'   => 'search_uploads',
                'datasource' => 'uploads',
                'title'      => 'str_search_uploads',
                'fields'     => array(),
                'group'      => 'uploads',
                'match'      => 'upload_name,upload_description,upload_tags',
            ));
        array_unshift($search_meta,
            array(
                'template' => '*',
                'title'    => 'str_search_site',
                'datasource' => '*',
                'group'    => 'all'
            ));
    }

    function Results()
    {
        $search_text = CCUtil::StripText($_REQUEST['search_text']);

        if( empty($search_text) )
        {
            $this->do_search();
            return;
        }

        $this->do_results($search_text);
    }

    function do_results($search_text)
    {
        global $CC_GLOBALS;

        if( empty($_REQUEST['search_in']) )
            die('missing "search in" field'); // I think this is a hack attempt

        $maxview = empty($CC_GLOBALS['max_search_overview']) ? 5 : $CC_GLOBALS['max_search_overview'];
        
        $what = CCUtil::StripText($_REQUEST['search_in']);
        $search_meta = array();
        CCEvents::Invoke( CC_EVENT_SEARCH_META, array(&$search_meta) );
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        require_once('cchost_lib/cc-query.php');

        if( empty($CC_GLOBALS['use_text_index']) )
        {
            $values['search_type'] = $search_type = CCUtil::StripText($_REQUEST['search_type']);
        }

        $form = new CCSearchForm( $search_meta, 'horizontal' );
        $values['search_in'] = $what;
        $values['search_text'] = htmlentities($search_text);
        $form->PopulateValues($values);
        $gen = $form->GenerateForm();
        // ack
        $gen->_template_vars['html_hidden_fields'] = array();
        //d($gen);
        $page->AddForm($gen);

        if( $what == 'all')
        {
            $grand_total = 0;
            $results = array();
            foreach( $search_meta as $meta )
            {
                if( $meta['group'] == 'all' )
                    continue;
                $query = new CCQuery();
                $grp_type = $meta['datasource'] == $meta['group'] ? '' : '&type=' . $meta['group'];
                $qs = "search=$search_text&datasource={$meta['datasource']}{$grp_type}&t={$meta['template']}"; 
                $q = $qs . "&limit={$maxview}&f=html&noexit=1&nomime=1";
                if( empty($search_type) )
                {
                    $search_type_arg = '';
                }
                else
                {
                    $search_type_arg = '&search_type=' . $search_type;
                    $q .= '&search_type=' . $search_type;
                }
                $args = $query->ProcessAdminArgs($q);
                ob_start();
                $query->Query($args);
                $html = ob_get_contents();
                ob_end_clean();
                $link = (count($query->records) == $maxview) 
                    ? url_args(ccl('search'),"search_text=$search_text&search_in={$meta['group']}{$search_type_arg}") : '';
                $total = $query->dataview->GetCount();
                $grand_total += $total;
                $results[] = array( 
                    'meta' => $meta, 
                    'results' => $html, 
                    'total' => $total,
                    'more_results_link' => $link,
                    'query' => $qs );
            }

            $page->SetTitle('str_search_results');
            $page->PageArg('search_results_meta',$results,'search_results_all');

            if( !$grand_total )
                $this->_eval_miss($search_text);
        }
        else
        {
            foreach( $search_meta as $meta )
            {
                if( $meta['group'] != $what )
                    continue;
                $page->AddMacro('search_results_head');
                // heaven bless global variables
                global $CC_GLOBALS;
                $result_limit = 30; // todo: option later
                $page->SetTitle( array( 'str_search_results_from', $meta['title']) );
                $grp_type = $meta['datasource'] == $meta['group'] ? '' : '&type=' . $meta['group'];
                $q = "search={$search_text}&datasource={$meta['datasource']}{$grp_type}&t={$meta['template']}&limit={$result_limit}";
                if( !empty($search_type) )
                    $q .= '&search_type=' . $search_type;
                $query = new CCQuery();
                $args = $query->ProcessAdminArgs($q);
                $query->Query($args);
                $total = $query->dataview->GetCount();
                if( empty($total) )
                {
                    $this->_eval_miss($search_text);
                }
                else
                {
                    $msg = array( 'str_search_viewing', 
                        $query->args['offset'], $query->args['offset'] + count($query->records), '<span>' . $total . '</span>' );
                    $page->PageArg('search_result_viewing',$msg);
                }
                break;
            }
        }
    }

    function OnFilterSearch(&$records,&$info)
    {
        $k = array_keys($records);
        $c = count($k);
        $r = '("([^"]+)"|(\w+))';
        preg_match_all( "/$r/", $info['queryObj']->args['search'], $m );
        $terms = array_filter(array_merge($m[2],$m[3]));
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[ $k[$i] ];
            $R['qsearch'] = $this->_highlight_results($R['qsearch'],$terms);
        }
    }

    function _highlight_results($input,&$terms,$maxoutlen = 150)
    {
        $max = $maxoutlen;

        // stripos is only on PHP 5 so we have to fake it...
        $xcopy = strtolower($input);
        foreach( $terms as $term )
        {
            if( !$term )
                continue;
            $pos = strpos($xcopy,$term);
            if( $pos !== false )
            {
                $len   = strlen($term);
                $term  = substr($input,$pos,$len); // get mixed version of term
                $repl  = "<span>$term</span>";
                $temp  = substr_replace($input, $repl, $pos, $len);
                if( $pos + $len + 20 > $max )
                    $temp = "..." . substr($temp,$pos-20,$max-5);
                $input = $temp;
                break;
            }
        }
        if( strlen($input) > $max )
            $input = substr($input,0,$max) . '...';

        return($input);
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'search',         array('CCSearch','Search'),       
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Display search form'), CC_AG_SEARCH );
        CCEvents::MapUrl( 'search/results', array('CCSearch','Results'),     
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _("Use this for 'action' in forms"), CC_AG_SEARCH );
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
            /*
               this just doesn't work
               
            $fields['use_text_index'] =
               array(  'label'      => _('Search Method'),
                       'form_tip'   => _('Check this to use mysql TEXTINDEX searching'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );
            */
            
            $fields['show_google_form'] =
               array(  'label'      => _('Show Google(tm) Search Form'),
                       'form_tip'   => _('Check this to show users a Google search form'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );
            $fields['max_search_overview'] =
               array(  'label'      => _('Search Return Results'),
                       'form_tip'   => _('Maximum number of results in search overview'),
                       'value'      => '',
                       'class'      => 'cc_form_input_short',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE );
        }

    }


    function _eval_miss($search_text)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['use_text_index']) )
        {
            $msg = 'str_search_miss_generic';
        }
        else
        {
            // gather some stats about the search term:
            $words = str_word_count($search_text,1);
            $num_words = count($words);
            $biggest_word = 0;
            for( $i = 0; $i < $num_words; $i++ )
                $biggest_word = max(strlen($words[$i]),$biggest_word);
            $sophis = preg_match('/[+<>~(-]/',$search_text);
            $quoted = strpos($search_text,'"');
            /*
            if( ($biggest_word < 4) && $num_words == 1 )
            {
                $msg = array( 'str_search_miss_tiny', ' <span>"' . $search_text . ' joebob"</span> ', ' <span>"' . $search_text . '_joebob"</span> ' );
            }
            elseif( ($biggest_word < 4) && ($num_words > 1) && !$quoted)
            {
                $msg = array( 'str_search_miss_quote', ' <span>"' . $search_text . '"<span> ', 
                             ' <span>' . substr(str_replace(' ','_',$search_text),0,25) . '</span> ' );
            }
            else
            */
            {
                $msg = array( 'str_search_miss', 
                                 '<a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html">','</a>' );
            }
        }
        
        $page =& CCPage::GetPage();
        $page->PageArg('search_miss_msg',$msg);
    }
}

/**
*/
class CCSearchForm extends CCForm
{
    function CCSearchForm($search_meta,$mode )
    {
        global $CC_GLOBALS;

        $this->CCForm();

        foreach( $search_meta as $meta )
        {
            $field_groups = array();
            $options[$meta['group']] = $meta['title'];
            if( !empty($meta['fields']) )
            {
                foreach( $meta['fields'] as $K => $F )
                {
                    $field_groups[] = $meta['group'];
                    if( empty($F['class']) )
                        $F['class'] = $meta['group'];
                    else
                        $F['class'] .= ' ' . $meta['group'];
                    $fields[$K] = $F;
                }
           }
        }

        $fields['search_text'] =
                        array( 'label'      =>  $mode == 'horizontal' ? '' : _('Search Text'),
                               'form_tip'   => '',
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED);

        if( empty($CC_GLOBALS['use_text_index']) )
        {
            $typeops = array( 'any' => _('Match any word'), 'all' => _('Match all words'), 'match' => _('Match exact phrase') );
            $fields['search_type'] =
                    array( 'label'      => $mode == 'horizontal' ? '' : _('Match'),
                           'form_tip'   => '',
                           'formatter'  => 'select',
                           'options'    => $typeops,
                           'flags'      => CCFF_POPULATE);

        }

        $fields['search_in'] =
                        array( 'label'      => $mode == 'horizontal' ? '' : _('What'),
                               'form_tip'   => '',
                               'formatter'  => 'select',
                               'options'    => $options,
                               'flags'      => CCFF_POPULATE);

        if( $mode == 'horizontal' )
        {
            $this->SetTemplateVar('form_fields_macro','horizontal_form_fields');
        }
        else
        {
            if( empty($CC_GLOBALS['use_text_index']) )
            {
                $this->SetFormHelp( 'str_search_help_generic' );
            }
            else
            {
                $this->SetFormHelp( array( 'str_search_help',
                                 '<a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html">','</a>') );
            }
        }
        $this->AddFormFields( $fields );
        $this->SetSubmitText(_('Search'));
        $this->SetTemplateVar('form_method','GET');

        //$this->SetHandler( ccl('search', 'results') );
    }
}

?>
