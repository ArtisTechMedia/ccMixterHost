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
* $Id: cc-upload-forms.php 12624 2009-05-18 15:47:40Z fourstones $
*
*/

/**
* @package cchost
* @subpackage upload
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
 * Base class for forms that upload media files.
 * 
 */
class CCUploadMediaForm extends CCUploadForm 
{
    /**
     * Constructor.
     * 
     * Sets up basic editing fields for name, tags, description and the
     * file upload itself. Invokes the CC_UPLOAD_VALIDATOR 
     * to get a list of valid file types allowed for upload.
     *
     * @access public
     * @param integer $user_id This id represents the 'owner' of the media
     */
    function CCUploadMediaForm($user_id,$file_field = true,$upload_id=0)
    {
        global $CC_CFG_ROOT;

        $this->CCUploadForm();
        $this->SetSubmitText(_('Upload'));
        $this->SetHiddenField('upload_user', $user_id);
        $this->SetHiddenField('upload_config', $CC_CFG_ROOT);
        if( $upload_id )
            $this->SetHiddenField('upload_id', $upload_id );

        $fields['upload_name'] =
                        array( 'label'      => 'str_name',
                               'formatter'  => 'textedit',
                               'form_tip'   => 'str_display_name_for_file',
                               'flags'      => CCFF_POPULATE );

        if( $file_field )
        {
            require_once('cchost_lib/cc-upload.php');
            CCUpload::GetUploadField($fields,'upload_file_name');
        }

        $fields['upload_description'] =
                        array( 'label'      => 'str_description',
                               'want_formatting' => true,
                               'formatter'  => 'textarea',
                               'flags'      => CCFF_POPULATE );
        
        $this->AddFormFields( $fields );

        CCUpload::GetTagFields( $this, 'upload_tags', 'before', 'upload_description' );

        $this->_extra = array();
    }

    function AddSuggestedTags($suggested_tags)
    {
        CCUpload::AddSuggestedTags($this,$suggested_tags);
    }

}

/**
 * Extend this class for forms that upload new media to the system.
 *
 */
class CCNewUploadForm extends CCUploadMediaForm
{
    /**
     * Constructor.
     *
     * Tweaks the bass class state to be in line with
     * new uploads, original or remixes.
     *
     * @access public
     * @param integer $userid The upload will be 'owned' by this user
     * @param integer $show_lic Set this to display license choices
     */
    function CCNewUploadForm($userid, $show_lic = true, $avail_lics='')
    {
        $this->CCUploadMediaForm($userid);

        $this->SetHiddenField('upload_date', date( 'Y-m-d H:i:s' ) );

        if( $show_lic && !empty($avail_lics) )
        {
            $lics = split(',',$avail_lics);
            $lics = "'" . join( "','",$lics) . "'";
            $lics = CCDatabase::QueryRows("SELECT *, 0 as license_checked FROM cc_tbl_licenses WHERE license_id IN ({$lics})");
            $count    = count($lics);
            if( $count == 1 )
            {
                $this->SetHiddenField('upload_license',$lics[0]['license_id']);
            }
            elseif( $count > 1 )
            {
                $fields = array( 
                    'upload_license' =>
                                array( 'label'      => 'str_license',
                                       'formatter'  => 'metalmacro',
                                       'flags'      => CCFF_POPULATE,
                                       'macro'      => 'license_choice',
                                       'license_choice' => $lics
                                )
                            );
                
                $this->AddFormFields( $fields );
            }
        }
        
    }

}

class CCConfirmDeleteForm extends CCForm
{
    function CCConfirmDeleteForm($pretty_name)
    {
        $this->CCForm();
        $this->SetHelpText(_('This action can not be reversed...'));
        $this->SetSubmitText(sprintf(_("Are you sure you want to delete '%s'?"),$pretty_name));
    }
}

/**
* @package cchost
* @subpackage admin
*/
class CCAdminUploadForm extends CCForm
{
    function CCAdminUploadForm(&$record)
    {
        $this->CCForm();

        require_once('cchost_lib/cc-tags.inc');
        $tags =& CCTags::GetTable();
        $where['tags_type'] = CCTT_SYSTEM;
        $tags->SetOrder('tags_tag','ASC');
        $sys_tags = $tags->QueryKeys($where);

        $lics = CCDatabase::QueryRows('SELECT license_id,license_name FROM cc_tbl_licenses');
        $options = array();
        foreach( $lics as $lic )
            $options[ $lic['license_id'] ] = $lic['license_name'];

        $fields = array(
            'ccud' => array(
                'label'     => _('Internal Tags'),
                'form_tip'  => _("Be careful when editing these, it is easy to confuse the system"),
                'value'     => $record['upload_extra']['ccud'],
                'formatter' => 'textedit',
                'flags'     => CCFF_REQUIRED | CCFF_POPULATE
                ),
            'popular_tags'  =>
                        array( 'label'      => _('System Tags'),
                               'target'     => 'ccud',
                               'tags'       => $sys_tags,
                               'formatter'  => 'metalmacro',
                               'macro'      => 'popular_tags',
                               'form_tip'   => _('Click on these to automatically add them.'),
                               'flags'      => CCFF_STATIC | CCFF_NOUPDATE 
                ),
            'upload_date'  =>
                        array( 'label'      => _('Upload Date'),
                               'formatter'  => 'textedit',
                               'form_tip'   => _('For anti-bumping format: YYYY-MM-DD HH:MM:SS (24hr clock)'),
                               'value'      => $record['upload_date'],
                               'flags'      => CCFF_POPULATE,
                ),
            'upload_license'  =>
                        array( 'label'      => _('Upload License'),
                               'formatter'  => 'select',
                               'form_tip'   => '',
                               'options'    => $options,
                               'value'      => $record['upload_license'],
                               'flags'      => CCFF_POPULATE,
                ),
            );

        $this->AddFormFields($fields);
        //CCPage::AddScriptBlock('popular_tags_script');

    }
}

?>
