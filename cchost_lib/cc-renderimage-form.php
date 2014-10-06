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
* $Id: cc-renderimage-form.php 12642 2009-05-24 00:44:39Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

class CCAdminThumbnailForm extends CCEditConfigForm
{
    function CCAdminThumbnailForm()
    {
        $this->CCEditConfigForm('config');

        $fields['thumbnail-on'] = 
           array( 'label'       => _('Generate and Display Thumbnails'),
                   'formatter'  => 'checkbox',
                   'form_tip'   => _('Display thumbnails for image uploads'),
                   'flags'      => CCFF_POPULATE);

        $fields['thumbnail-exec'] = 
           array( 'label'       => _('Thumbnail Command Line'),
                   'formatter'  => 'textedit',
                   'form_tip'   => array( _('Parameterized command to execute to create thumbnail.<br />ex: %s'),
                                '<span style="white-space:pre;">/usr/bin/convert %file_in% -resize 100x120 -compress JPEG -quality 75 %file_out%</span>' ),
                   'flags'      => CCFF_POPULATE);

        $fields['thumbnail-mime'] = 
           array( 'label'       => _('Thumbnail MIME Type'),
                   'formatter'  => 'textedit',
                   'form_tip'   => array( 'ex: image/jpeg' ),
                   'flags'      => CCFF_POPULATE);

        $fields['thumbnail-ext'] = 
           array( 'label'       => _('Thumbnail File Extension'),
                   'formatter'  => 'textedit',
                   'form_tip'   => array( 'ex: jpeg' ),
                   'flags'      => CCFF_POPULATE);

        $help = _('In order to create the thumbnail your PHP system must allow calling third party command line executables.');
        $this->SetFormHelp($help);
        $this->AddFormFields($fields);
        $this->SetModule(ccs(__FILE__));
    }
}


?>
