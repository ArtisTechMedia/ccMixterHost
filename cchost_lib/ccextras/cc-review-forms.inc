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
* $Id: cc-review-forms.inc 11338 2008-11-28 19:26:05Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
require_once('cchost_lib/ccextras/cc-topics-forms.inc');


class CCReviewForm extends CCTopicForm
{
    function CCReviewForm()
    {
        $this->CCTopicForm(_('Review'),'Submit Review', CC_TTF_NOT_VISIBLE );
//       $this->SetTemplateMacro('review_post')
    }
}


class CCReviewFormAPI
{
    /**
    * Handles reviews/post URL
    *
    * @param integer $upload_id Upload ID to review
    */
    function PostReview($upload_id)
    {
        global $CC_GLOBALS, $CC_CFG_ROOT;

        if( empty($CC_GLOBALS['reviews_enabled']) )
        {
            CCPage::Prompt(_('Review integration is not enabled.'));
            return;
        }

        $uploads =& CCUploads::GetTable();
        if( !$uploads->KeyExists($upload_id) )
        {
            CCPage::Prompt(_('Cannot find that upload.'));
            CCUtil::Send404(false);
            return;
        }

        $form = new CCReviewForm();

        if( !empty($_GET['ajax']) )
        {
            $form->RemoveFormField( 'user_confirm' );
            $form->RemoveFormField( 'user_mask' );
            $form->SetTemplateVar('form_submit_trap', 'ccReviewFormHook' ); 
        }

        require_once('cchost_lib/ccextras/cc-reviews.inc');

        list( $user_real_name, $upload_name, $user_name ) = CCDatabase::QueryRow(
            'SELECT user_real_name, upload_name, user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='
            . $upload_id, false);

        if( empty($_POST['review']) || !$form->ValidateFields() )
        {
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();

            if( empty($_GET['ajax']) )
            {
                CCReview::_build_bread_crumb_trail(array($user_name,$user_real_name),
                                          array( $upload_id, $upload_name), '',_('create new'));

                CCPage::SetTitle(array('str_review_write_s',$upload_name));

                $args = $query->ProcessAdminArgs('t=list_files&ids=' . $upload_id); 
            }
            else
            {
                $args = $query->ProcessAdminArgs('t=reviews_summary&paging=off&upload=' . $upload_id); 
            }

            $query->Query($args);

            CCPage::PageArg('review_upload',$upload_id);
            
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            if( !CCReviewsHV::_can_review($upload_id) )
            {
                // submit was hit twice
                require_once('cchost_lib/cc-page.php');
                CCPage::Prompt(_("You have already reviewed this upload."));
            }
            else
            {
                $form->GetFormValues($values);
                $reviews =& CCReviews::GetTable();
                $values['topic_id'] = $reviews->NextID();
                $values['topic_upload'] = $upload_id;
                $values['topic_date'] = date('Y-m-d H:i:s',time());
                $values['topic_user'] = CCUser::CurrentUser();
                $values['topic_type'] = 'review';
                $user_real = CCUser::CurrentUserField('user_real_name');
                $values['topic_name'] = sprintf(_("Review of '%s' by '%s'"), 
                                                $upload_name, $user_real);
                $reviews->Insert($values,0);
                CCReview::Sync($upload_id,$values['topic_user']);

                $row = $reviews->QueryKeyRow($values['topic_id']);
                $row['user_real_name'] = $user_real;
                $row['topic_permalink'] = ccl('reviews',$user_name, $upload_id ) . '#' . $values['topic_id'];

                CCEvents::Invoke( CC_EVENT_REVIEW, array( &$row ) );

                if( empty($_GET['ajax']) )
                {
                    $url = ccl( 'reviews', $user_name, $upload_id );
                    CCUtil::SendBrowserTo($url);
                }
                else
                {
                    $ret['upload_id']   = $upload_id;
                    $ret['reviews_url'] = ccl('reviews',$user_name, $upload_id );
                    $ret['num_reviews'] = CCDatabase::QueryItem(
                                            'SELECT COUNT(*) FROM cc_tbl_topics WHERE topic_type = \'review\' AND topic_upload = '.$upload_id);
                    CCUtil::ReturnAjaxData($ret);
                }
            }

        }
    }

}

?>
