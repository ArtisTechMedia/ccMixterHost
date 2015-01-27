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
* $Id: cc-db-admin.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* Handles admin user interface for database config
*
* This module sets up the url: admin/database but doesn't
* actually map it to any menu items because getting it
* wrong can break the site too easily.
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
* Configuration form for database admin
* 
*/
class CCAdminDatabaseForm extends CCForm
{
    /**
    * Constructor
    *
    */
    function CCAdminDatabaseForm()
    {
        $this->CCForm();
        $fields = array( 
                    'db-name'        => 
                       array( 'label'       => _('mysql Database Name'),
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),

                    'db-server'        => 
                       array( 'label'       => _('Location of Server '),
                               'form_tip'   => _('Typically "localhost"'),
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),

                    'db-user'        => 
                       array( 'label'       => _('mysql User Name'),
                               'form_tip'   => _('This is the name used to connect to the mysql database.'),
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),

                    'db-password'    => 
                       array( 'label'       => _('mysql User Password'),
                               'form_tip'   => _('This is the password used to connect to the mysql database.'),
                               'nomd5'      => true,
                               'formatter'  => 'password',
                               'flags'      => CCFF_POPULATE  ),
                       );

        $this->AddFormFields($fields);
    }
}

/**
* Database Admin Callbacks
*
*/
class CCDatabaseAdmin
{

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/database',  array('CCDatabaseAdmin','Admin'), CC_ADMIN_ONLY,
            ccs(__FILE__), '', _('Edit config-db file'), CC_AG_ADMIN_MISC );
    }

    /**
    * Handler for /admin/database
    *
    * Wildly dangerous, guaranteed to generate support calls.
    *.
    * @see CCAdminDatabaseForm::CCAdminDatabaseForm()
    */
    function Admin()
    {
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Database Configuration'));
        $form = new CCAdminDatabaseForm();
        $config_db = CCDatabase::_config_db();
        if( empty($_POST['admindatabase']) || !$form->ValidateFields() )
        {   
            include($config_db);
            $form->PopulateValues($CC_DB_CONFIG);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($CC_DB_CONFIG);

            $varname = "\$CC_DB_CONFIG";
            $text = "<?";
            $text .= <<<END
        
// This file is generated as part of install and config editing

if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

$varname = array (
   'db-name'     =>   '{$CC_DB_CONFIG['db-name']}',
   'db-server'   =>   '{$CC_DB_CONFIG['db-server']}',
   'db-user'     =>   '{$CC_DB_CONFIG['db-user']}',
   'db-password' =>   '{$CC_DB_CONFIG['db-password']}',
 
  ); 

END;

            $text .= "?>";

            $f = fopen($config_db, 'w+' );
            fwrite($f,$text,strlen($text));
            fclose($f);
            $perms = cc_default_file_perms();
            //CCDebug::PrintVar($perms);
            chmod($config_db, $perms);

            $page->Prompt(sprintf(_("Database configuration saved to %s (%04o)"), $config_db, $perms));
        }

    }

}

?>
