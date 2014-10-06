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
* $Id: cc-pools-forms.php 12403 2009-04-24 05:41:25Z fourstones $
*
*/

/**
* Module for admin management of sample pools
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to ccHost');

/**
*/
require_once('cchost_lib/cc-form.php');

/**
* Form for editing the properties of a known pool
*
*/
class CCAdminEditPoolForm extends CCForm
{
    function CCAdminEditPoolForm()
    {
        $this->CCForm();
        $fields = array( 
            'pool_name' =>  
               array(  'label'      => _('Name'),
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),
            'pool_short_name' =>  
               array(  'label'      => _('Internal Name'),
                       'formatter'  => 'statictext',
                       'flags'      => CCFF_POPULATE | CCFF_NOUPDATE | CCFF_STATIC ),
            'pool_description' =>
               array(  'label'      => _('Description'),
                       'formatter'  => 'textarea',
                       'flags'      => CCFF_POPULATE ),
            'pool_api_url' =>  
               array(  'label'      => _('API URL'),
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE ),
            'pool_site_url' =>  
               array(  'label'      => _('Site URL'),
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE ),
            'pool_banned' =>  
               array(  'label'      => _('Banned'),
                       'form_tip'   => _('Ignore communications from this pool'),
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE ),
            'pool_auto_approve' =>  
               array(  'label'      => _('Auto-approve Remote Remixes'),
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE ),
            'pool_search' =>  
               array(  'label'      => _("Allow to be searched remotely"),
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE ),
            );

        $this->AddFormFields($fields);
    }
}

class CCAdminPoolsForm extends CCForm
{
    function CCAdminPoolsForm()
    {
        $this->CCForm();

        $fields = array( 
            /*
            'allow-pool-search' =>  
                   array(  'label'      => 'Allow users to search remote pools',
                           'formatter'  => 'checkbox',
                           'flags'      => CCFF_POPULATE  ),

            'pool-push-hub' =>  
                   array(  'label'      => 'Request to be a pool at:',
                           'form_tip'   => 'Must the URL to the site\'s pool API',
                           'formatter'  => 'doitnow',
                           'nowbutton'  => 'Request Now',
                           'flags'      => CCFF_POPULATE ),
            */
            'pool-remix-throttle' =>
                   array(  'label'      => _('Remote Remix Throttle'),
                           'form_tip'   => _('Maximum remote unnapproved remixes.'),
                           'formatter'  => 'textedit',
                           'class'      => 'cc_form_input_short',
                           'flags'      => CCFF_POPULATE  ),

            'pool-pull-hub' =>  
                   array(  'label'      => _('Add a sample pool to your site:'),
                           'form_tip'   => _("This must be the URL to the site's pool API (e.g. http://ccmixter.org/media/api)."),
                           'formatter'  => 'doitnow',
                           'nowbutton'  => 'Add Now',
                           'flags'      => CCFF_POPULATE ),
/*
            'allow-pool-register' =>  
                   array(  'label'      => 'Allow remote pools to register here',
                           'formatter'  => 'checkbox',
                           'flags'      => CCFF_POPULATE  ),
*/
               );
        $this->AddFormFields($fields);
    }

    function generator_doitnow($varname,$value='',$class='')
    {
        $html = $this->generator_textedit($varname,$value,$class);
        $caption = $this->GetFormFieldItem($varname,'nowbutton');
        $html .= " <input type='submit' id=\"doitnow_$varname\" name=\"doitnow_$varname\" value=\"$caption\" />";
        return( $html );
    }

    function validator_doitnow($fieldname)
    {
        return( $this->validator_textedit($fieldname) );
    }

    /**
     * Overrides base class in order to populate fields with current contents of environment's config.
     *
     */
    function GenerateForm($hiddenonly = false)
    {
        $configs =& CCConfigs::GetTable();
        $values = $configs->GetConfig('config');
        $this->PopulateValues($values);
        return( parent::GenerateForm($hiddenonly) );
    }
}

class CCAddPoolWrapperForm extends CCForm
{
    function CCAddPoolWrapperForm()
    {
        $this->CCForm();

        $fields = array( 
            'pool_name' =>
                   array(  'label'      => _('Display Name'),
                           'form_tip'   => '',
                           'formatter'  => 'textedit',
                           'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),
            'pool_short_name' =>
                   array(  'label'      => _('Internal (Short) Name'),
                           'form_tip'   => _('Used in Query API'),
                           'formatter'  => 'textedit',
                           'class'      => 'cc_form_input_short',
                           'flags'      => CCFF_POPULATE  ),
            'pool_description' =>
                   array(  'label'      => _('Description'),
                           'form_tip'   => '',
                           'formatter'  => 'textedit',
                           'flags'      => CCFF_POPULATE  ),
            'pool_site_url' =>
                   array(  'label'      => _('Site Address'),
                           'form_tip'   => _('For display only, the site will not be contacted'),
                           'formatter'  => 'textedit',
                           'flags'      => CCFF_POPULATE  ),
            'pool_search' =>  
                   array(  'label'      => 'Allow searches in remix forms',
                           'formatter'  => 'checkbox',
                           'flags'      => CCFF_POPULATE  ),
               );
        $this->AddFormFields($fields);
        $help = 'Use this form to create a wrapper for a remote Sample Pool that does not implement the Sample Pool API. Items in this wrapper pool will be entered by "hand" by you instead of remotely searched from another site.';
        $this->SetFormHelp($help);
    }
}

class CCAddPoolItemsForm extends CCGridForm
{
    function CCAddPoolItemsForm()
    {
        $this->CCGridForm();
        $this->SetTemplateVar('form_fields_macro','flat_grid_form_fields');

        $heads = array( 
            _('Name'),
            _('Author'), 
            _('URL'), 
            _('License'), 
         );
        
        $this->SetColumnHeader($heads);
        
        $lics = $this->_get_lics();
        $a = $this->_make_row('pi[1]', $lics, 'select');
        $this->AddGridRow( 1, $a );
        $a = $this->_make_row('pi[%i%]',$lics, 'raw_select');
        $this->AddMetaRow($a, _('Add new item') );        
    }
    
    function _get_lics()
    {
        $lic_rows = CCDatabase::QueryRows('SELECT license_id,license_name FROM cc_tbl_licenses ORDER by license_name');
        $lics = array();
        foreach( $lic_rows as $R )
            $lics[ $R['license_id'] ] = $R['license_name'];
        return $lics;
    }
    
    function _get_column_order()
    {
        return array( 'pool_item_name', 'pool_item_artist',
                  'pool_item_url', 'pool_item_license' );
    }
    
    function _make_row($S,$lics,$lictype)
    {
        // These MUST line up with _get_column_order above
        return array(
              array(
                'element_name'  => $S . '[pool_item_name]',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . '[pool_item_artist]',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . '[pool_item_url]',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . '[pool_item_license]',
                'options'     => $lics,
                'formatter'  => $lictype,
                'flags'      => CCFF_NONE ),
            );
    }
}
?>
