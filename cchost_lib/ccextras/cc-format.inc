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
* $Id: cc-format.inc 13251 2009-08-09 17:24:09Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
require_once('cchost_lib/ccextras/cc-topics.php');

function _generator_cc_format($form, $fieldname, $value, $class )
{
    $textarea = $form->generator_textarea($fieldname,$value,$class);

    $url = ccl('format','preview') . '/';
    $html = 
        '<div class="cc_box format_preview_outer" style="display:none" id="format_preview_' . $fieldname . '">
            <h3>    <input type="button" onclick="cc_hide_preview(\'' . $fieldname . '\')" value="x" class="cc_close_preview" />
' . _('Preview') . ':</h3>
        <div class="cc_format_preview" id="format_inner_preview_' . $fieldname . '"></div>
            </div>';

    require_once('cchost_lib/cc-page.php');
    $page = CCPage::GetPage();
    $thumbs_up_tiny = ccd( $page->Search( 'images/thumbs_up_tiny.gif' ) );
    

    if( $form->GetFormFieldItem($fieldname,'show_img') )
    {
        $mona = ccd( $page->Search( 'images/mona.gif' ) );
        $img_button =<<<END
        <a href="javascript://heya!" onclick="cc_apply_format('$fieldname','img'); return false;" style="" ><img src="$mona" /></a>
END;
    }
    else
    {
        $img_button = '';
    }

    $html .=<<<END
        <div class="cc_ed_buttons">
    <input type="button" onclick="cc_apply_format('$fieldname','b');" value="b" style="font-weight: bold;" />
    <input type="button" onclick="cc_apply_format('$fieldname','i');" value="i" style="font-style:italic;" />
    <input type="button" onclick="cc_apply_format('$fieldname','u');" value="u" style="text-decoration:underline;" />
    <input type="button" onclick="cc_apply_format('$fieldname','url');" value="link" style="text-decoration:underline;font-weight:bold;" />
    <input type="button" onclick="cc_apply_format('$fieldname','red');" value="R" style="color:red" />
    <input type="button" onclick="cc_apply_format('$fieldname','green');" value="G" style="color:green" />
    <input type="button" onclick="cc_apply_format('$fieldname','blue');" value="B" style="color:blue" />
    <input type="button" onclick="cc_apply_format('$fieldname','big');" value="+" style="" />
    <input type="button" onclick="cc_apply_format('$fieldname','small');" value="-" style="" />
    {$img_button}
    <a href="javascript://heya!" onclick="cc_apply_format('$fieldname','up'); return false;" style="" ><img src="$thumbs_up_tiny" /></a>
    <input type="button" onclick="cc_format_preview('$fieldname')" id="preview_$fieldname" value="preview" style="font-size:smaller;" />
    </div>
        $textarea
END;

    return $html;
}




/**
*
*
*/
class CCFormat
{
    function Preview()
    {
        if( !_cc_is_formatting_on() || empty($_GET['ptext']) )
            exit;

        $text = CCUtil::StripSlash(urldecode($_GET['ptext']));
        print _cc_format_format($text);
        exit;
    }

    function OnFilterFormat(&$rows)
    {
        if( !_cc_is_formatting_on() )
            return;

        $c = count($rows);
        if( !$c )
            return;
        $k = array_keys($rows);

        // dig out fields
        $first_row =& $rows[ $k[0] ];
        $col_keys = array_keys($first_row);
        $col_cnt = count($col_keys);
        $format_needed = array();
        for( $i = 0; $i < $col_cnt; $i++ )
        {
            $pieces = explode('_',$col_keys[$i]);
            if( $pieces[0] != 'format' )
                continue;
            $db_col = join('_',array_splice($pieces,2));
            $format_needed[$col_keys[$i]] = array(
                'name' => $db_col . '_' . (($pieces[1] == 'text') ? 'plain' : 'html'),
                'format' => $pieces[1] );
        }
        /*
            In the end, $format_needed looks something like:
            
                Array
                (
                    [format_html_topic_text] => Array
                        (
                            [name] => topic_text_html
                            [format] => html
                        )
                
                    [format_text_topic_text] => Array
                        (
                            [name] => topic_text_plain
                            [format] => text
                        )
                
                )
                
            The incoming rows have fields with names that look
            the keys in that array and assume they will be
            updated with versions of [format] named [name]
            
        */

        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $rows[$k[$i]];

            foreach( $format_needed as $F => $meta )
            {
                if( !isset($R['topic_format']) )
                    $R['topic_format'] = CC_TOPIC_FORMAT_BB;

                switch( $R['topic_format'] )
                {
                    case CC_TOPIC_FORMAT_BB:
                    {
                        if( $meta['format'] == 'text' )
                            $R[$meta['name']] = _cc_format_unformat($R[$F]);
                        elseif( $meta['format'] == 'html' )
                            $R[$meta['name']] = _cc_format_format($R[$F]);
                        break;
                    }
                    case CC_TOPIC_FORMAT_HTML:
                    {
                        if( $meta['format'] == 'text' )
                            $R[$meta['name']] = cc_format_html_to_text($R[$F]);
                        elseif( $meta['format'] == 'html' )
                            $R[$meta['name']] = $R[$F]; // it's already HTML
                        break;
                    }
                    case CC_TOPIC_FORMAT_PLAIN:
                    {
                        $R[$meta['name']] = $R[$F]; // it's all plain
                        break;
                    }
                }
                unset($R[$F]);
            }
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('format'), array('CCFormat','CallBack'), CC_DONT_CARE_LOGGED_IN,
                ccs(__FILE__), '/preview?ptext=', _('Ajax callback to return formatted text'), CC_AG_FORUMS);
    }

    function CallBack($cmd='')
    {
        switch( $cmd )
        {
            case 'preview':
                return $this->Preview();
        }
    }


    /**
    * Event handler for {@link CC_EVENT_FORM_FIELDS}
    *
    * @param object &$form CCForm object
    * @param object &$fields Current array of form fields
    */
    function OnFormFields(&$form,&$fields)
    {
        if( !_cc_can_format_edit() )
            return;

        $help = _('Select text and use the buttons to apply format');

        $count = count($fields);
        $keys = array_keys($fields);
        for( $i = 0; $i < $count; $i++ )
        {
            $K =& $keys[$i];

            if( !empty($fields[$K]['want_formatting'] ) )
            {
                $F =& $fields[ $K ];
                if( $F['formatter'] == 'textarea' )
                {
                    $F['formatter'] = 'cc_format';
                    if( empty($F['form_tip']) )
                        $F['form_tip'] = $help;
                    else
                        $F['form_tip'] .= '<br /><br />' . $help;
                }
            }
        }
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
            $fields['format'] =
               array(  'label'      => _('Allow User Text Formatting'),
                       'form_tip'   => _('Allow users to format text (bold, italic, etc.)'),
                       'value'      => 0,
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE | CCFF_REQUIRED );

            $fields['adminformat'] =
               array(  'label'      => _('Allow Admin Text Formatting'),
                       'form_tip'   => _('Allow admins to format text'),
                       'value'      => 0,
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE | CCFF_REQUIRED );
        }
    }
}



?>
