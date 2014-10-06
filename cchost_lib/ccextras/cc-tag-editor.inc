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
* $Id: cc-tag-editor.inc 9784 2008-05-09 20:04:30Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');
require_once('cchost_lib/cc-page.php');

class CCTagEditorForm extends CCGridForm
{
    function CCTagEditorForm($config,$add_text,$default_tags=array())
    {
        $this->CCGridForm();
        $this->SetTemplateVar('form_fields_macro','flat_grid_form_fields');

        $heads = array( 
            _('Delete'),
            _('Tags'), 
            _('Caption'), 
         );
        
        $this->SetColumnHeader($heads);

        $configs =& CCConfigs::GetTable();
        if( is_string($config) )
        {
            $channels =  $configs->GetConfig($config,CC_GLOBAL_SCOPE);
        }
        else
        {
            $chansgrop = $configs->GetConfig($config[0],CC_GLOBAL_SCOPE);
            if( !empty($chansgrop[$config[1]]) )
                $channels = $chansgrop[$config[1]];
        }
        if( empty($channels) )
        {
            if( empty($default_tags) )
            {
                $default_tags = array(
                            array( 'tags' => '*',
                                   'text' => 'str_filter_all'),
                            array( 'tags' => 'remix',
                                    'text' => 'remix' ),
                            array( 'tags' => 'sample',
                                   'text'   => 'sample' )
                        );
            }
            $channels = $default_tags;
        }
        $count = 0;
        foreach( $channels as $channel  )
        {
            $S = 'S[' . ++$count . ']';
            $a = array(
                  array(
                    'element_name'  => $S . '[delete]',
                    'value'      => false,
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_POPULATE ),
                  array(
                    'element_name'  => $S . '[tags]',
                    'value'      => $channel['tags'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE ),
                  array(
                    'element_name'  => $S . '[text]',
                    'value'      => $channel['text'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE ),
                );

            $this->AddGridRow( $count, $a );
        }

        $S = 'new[%i%]';
        $a = array(
              array(
                'element_name'  => $S . '[delete]',
                'value'      => false,
                'formatter'  => 'checkbox',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[tags]',
                'value'      => 'remix',
                'formatter'  => 'textedit',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[text]',
                'value'      => '',
                'formatter'  => 'textedit',
                'flags'      => CCFF_POPULATE ),
            );

        $this->AddMetaRow($a, $add_text );
    }
}

?>
