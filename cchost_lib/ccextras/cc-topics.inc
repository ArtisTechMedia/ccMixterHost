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
* $Id: cc-topics.inc 12531 2009-05-04 17:27:51Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-extras-events.php'); // for EVENT_TOPIC stuff
require_once('cchost_lib/cc-page.php');

/**
*/
// Topic Delete Flags
define('CCTDF_MARK',            'mark');
define('CCTDF_SHALLOW',         'shallow');
define('CCTDF_DEEP',            'deep');

class CCTopicsi18n extends CCTable
{
    function CCTopicsi18n()
    {
        $this->CCTable('cc_tbl_topic_i18n', 'topic_i18n_topic');
        $this->AddJoin( new CCTopics(), 'topic_i18n_xlat_topic' );
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $table;
        if( !isset($table) )
            $table = new CCTopicsi18n();
        return $table;
    }

    function & GetTranslations($topic_id)
    {
        $w['topic_i18n_topic'] = $topic_id;
        $rows = $this->QueryRows($w);
        $keys = array_keys($rows);
        $topics =& CCTopics::GetTable();
        foreach( $keys as $key )
        {
            $R =& $rows[$key];
            if( empty($R['topic_id']) )
            {
                // now featuring: Insta-sync!
                $delw['topic_i18n_topic'] = $topic_id;
                $delw['topic_i18n_xlat_topic'] = $R['topic_i18n_xlat_topic'];
                $this->DeleteWhere($delw);
                unset($rows[$key]);
                continue;
            }
            // force xlat off so we don't recurse
            $R['topic_can_xlat'] = 0;
            $topics->GetRecordFromRow($R);
            if( empty($R['topic_permalink']) )
                $R['topic_permalink'] = ccl('topics','view',$R['topic_id']);
        }
        return $rows;
    }

    function DeleteTranslationsOf($topic_id)
    {
        $w['topic_i18n_xlat_topic'] = $topic_id;
        $this->DeleteWhere($w);
    }

    function GetTranslationOf($topic_id,$topics)
    {
        $w['topic_i18n_xlat_topic'] = $topic_id;
        $id = $this->QueryKey($w);
        $row = $topics->QueryKeyRow($id);
        $row['topic_can_xlat'] = 0;
        return $topics->GetRecordFromRow($row);
    }
}

/**
* Base class wrapper for topics table
*
*/
class CCTopics extends CCTable
{
    var $_type_limit;
    var $_show_deleted;

    function CCTopics($show_deleted=false)
    {
        $this->CCTable('cc_tbl_topics', 'topic_id');
        $this->_show_deleted = $show_deleted;
    }

    function ShowDeleted($show=true)
    {
        $old = $this->_show_deleted;
        $this->_show_deleted = $show;
        return $old;
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $table;
        if( !isset($table) )
            $table = new CCTopics();
        return $table;
    }

    function LimitType($type)
    {
        $this->_type_limit = $type;
    }

    /**
    * In this orverride we actually delete an entire branch under the topic
    */
    function DeleteKeyRow($topic_id)
    {
        // Locking broken when added if-modified-since
        $this->Lock();
        list( $left, $right ) = CCDatabase::QueryRow('SELECT topic_left,topic_right FROM cc_tbl_topics WHERE topic_id='.$topic_id,false);
        $wid = ($right - $left) + 1;
        $sql[] = "DELETE FROM cc_tbl_topics WHERE topic_left BETWEEN $left AND $right";
        $sql[] = "UPDATE cc_tbl_topics SET topic_right = topic_right - $wid WHERE topic_right > $right";
        $sql[] = "UPDATE cc_tbl_topics SET topic_left  = topic_left - $wid  WHERE topic_left  > $right";
        CCDatabase::Query($sql);
        $this->Unlock();
    }

    /**
     *  Create a new top level topic from an existing branch (reply)
     *
     */
    function ForkBranch($topic_id)
    {
        $this->Lock();
        // before:
        //  7             18
        //    8       9
        //    10      13  <-- remove this
        //       11 12
        //    14      15  
        //    16      17
        // ----------
        //
        $right_sql = 'SELECT MAX(topic_right) FROM cc_tbl_topics';
        $max_right = CCDatabase::QueryItem($right_sql);
        // max_right = 18
        list( $left, $right ) = CCDatabase::QueryRow('SELECT topic_left,topic_right FROM cc_tbl_topics WHERE topic_id='.$topic_id,false);
        // ( 10, 13 )
        $diff = ($max_right+1) - $left;
        // diff = 9
        $sql[] = "UPDATE cc_tbl_topics SET topic_right = topic_right + $diff " .
                    "WHERE topic_left BETWEEN $left AND $right";
        $sql[] = "UPDATE cc_tbl_topics SET topic_left  = topic_left  + $diff " .
                    "WHERE topic_left BETWEEN $left AND $right";
        // ----------
        //  7             18
        //    8       9
        //    19      22  <-- 
        //       20 21
        //    14      15  
        //    16      17
        // ----------
        $wid = ($right - $left) + 1;
        //  4
        ++$right;
        // 14
        $sql[] = "UPDATE cc_tbl_topics SET topic_right = topic_right - $wid ".
                    "WHERE topic_right BETWEEN $right AND $max_right";
        $sql[] = "UPDATE cc_tbl_topics SET topic_left  = topic_left - $wid "  .
                    "WHERE topic_left BETWEEN $right AND $max_right";
        //
        // right/left -= 4 BETWEEN 14 AND 18
        // ----------
        //  7             14
        //    8       9
        //    10      11  
        //    12      13
        //  19            22  <-- 
        //       20 21
        // ----------
        CCDatabase::Query($sql);
        $this->Unlock();        
    }
    
    function InsertNewTopic(&$values,$parent_id)
    {
        // we do a variation of the nested set model discussed here
        // see: http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
        //
        if( $parent_id === 0 )
        {
            //
            // We're appending a top level topic (e.g. a review or new forum post)
            //
            $right_sql = 'SELECT MAX(topic_right) FROM cc_tbl_topics';
            $right = CCDatabase::QueryItem($right_sql);
            $values['topic_left'] = $right + 1;
            $values['topic_right'] = $right + 2;
            if( empty($values['topic_tags']) )
                $values['topic_tags'] = '';
            $ret = parent::Insert($values);
            return $ret;
        }

        //
        // We're appending (inserting) a topic the end of a parent's top level list (e.g. a reply)
        //
        // The parent's 'right' becomes the new topic's left
        //
        // The parent's new 'right' and everything to the right of it is shifted by
        // by 2
        //
        $right_sql = "SELECT topic_right FROM cc_tbl_topics WHERE topic_id = {$parent_id}";
        $this->Lock();
        $right = CCDatabase::QueryItem($right_sql);
        $sql[] = "UPDATE cc_tbl_topics SET topic_right = topic_right + 2 WHERE topic_right >= {$right}";
        $sql[] = "UPDATE cc_tbl_topics SET topic_left  = topic_left + 2  WHERE topic_left >   {$right}";
        CCDatabase::Query($sql);
        $values['topic_left'] = $right;
        $values['topic_right'] = $right + 1;
        if( empty($values['topic_tags']) )
            $values['topic_tags'] = '';
        $ret = parent::Insert($values);
        $this->Unlock();

        return $ret;
    }

    // overwrite parent's version to add type limit
    function _where_to_string($where)
    {
        $where = parent::_where_to_string($where) . '';

        if( !empty($this->_type_limit) )
        {
            $w = "topic_type = '" . $this->_type_limit . "'";

            if( empty($where) )
            {
                $where = $w;
            }
            else
            {
                $where .= " AND ($w)";
            }
        }

        if( empty($this->_show_deleted) )
        {
            $w = 'topic_deleted < 1';

            if( empty($where) )
            {
                $where = $w;
            }
            else
            {
                $where .= " AND ($w)";
            }
        }

        return $where;
    }

    // helper
    function GetParentTopic($topic_id)
    {
        // I think this is how to get the immediate parent
        list( $left, $right ) = CCDatabase::QueryRow('SELECT topic_left,topic_right FROM cc_tbl_topics WHERE topic_id='.$topic_id,false);
        $sql = "SELECT topic_id FROM cc_tbl_topics WHERE topic_left < $left AND topic_right > $right ORDER BY topic_left DESC LIMIT 1";
        return array( CCDatabase::QueryItem($sql), $left, $right );
    }


}

class CCTopic
{
    function _verify_topic($topic_id,&$topics,&$record,$is_translate=false)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        $topic_id = intval(CCUtil::Strip($topic_id));
        if( empty($topic_id) )
        {
            $page->Prompt(_('no topic specified.'));
            CCUtil::Send404(false,__FILE__,__LINE__); // just a hack
            return false;
        }

        $record = CCDatabase::QueryRow('SELECT topic_locked, topic_can_xlat FROM cc_tbl_topics WHERE topic_id='.$topic_id);
        if( empty($record) )
        {
            $page->Prompt(_('Cannot find that topic.') . ' ' . 
                           _('It might have been deleted by the author.'));
            CCUtil::Send404(false,__FILE__,__LINE__);
            return false;
        }

        $is_logged_in = CCUser::IsLoggedIn();
        $can_reply = ($is_logged_in && empty($record['topic_locked'])) || CCUser::IsAdmin();
            
        if( !$can_reply )
        {
            if( !$is_translate || empty($record['topic_can_xlat']) || !$is_logged_in )
            {
                $page->Prompt(_('You are not authorized to post here.'));
                return false;
            }
        }

        return true;
    }

    function Translate($topic_id='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        if( empty($topic_id) )
        {
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $args = $query->ProcessAdminArgs('t=topic_xlat_list&xlat=1&title=str_topics_for_translation&sort=date&ord=desc');
            $query->Query($args);
            return;
        }

        $topics = '';
        $record = array();

        if( !$this->_verify_topic($topic_id,$topics,$record,true) )
            return;

        $topic_name = CCDatabase::QueryItem('SELECT topic_name FROM cc_tbl_topics WHERE topic_id='.$topic_id);

        $page->SetTitle( sprintf(_('Translation of "%s"'), $topic_name) );

        require_once('cchost_lib/ccextras/cc-topics-forms.inc');

        $form = new CCTopicTranslateForm($topic_name);
        if( empty($_POST['topictranslate']) || !$form->ValidateFields() )
        {
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $args = $query->ProcessAdminArgs('t=topic_reply&ids='.$topic_id);
            $query->Query($args);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);

            $topics =& CCTopics::GetTable();
            $next_id = $topics->NextID();

            $xlat_args['topic_i18n_language'] = $values['topic_i18n_language'];
            $xlat_args['topic_i18n_topic'] = $topic_id;
            $xlat_args['topic_i18n_xlat_topic'] = $next_id;
            $translations =& CCTopicsi18n::GetTable();
            $translations->Insert($xlat_args);
            
            unset($values['topic_i18n_language']);

            $values['topic_id']    = $next_id;
            $values['topic_date']  = date('Y-m-d H:i:s',time());
            $values['topic_user']  = CCUser::CurrentUser();
            $values['topic_type']  = 'xlat';
            $values['topic_forum'] = 
            $values['topic_thread'] = 0;
            $topics->InsertNewTopic($values,0);

            CCUtil::SendBrowserTo(); // this will send back to topic
         }
    }

    function Reply($topic_id='',$is_quote = false)
    {
        $topics = null;
        $record = null;

        if( !$this->_verify_topic($topic_id,$topics,$record) )
            return;

        // display the topic we are repyling to...

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=topic_reply&ids='.$topic_id);
        $query->Query($args);

        require_once('cchost_lib/ccextras/cc-topics-forms.inc');
        $page =& CCPage::GetPage();

        // this is a round about way of doing form validation
        // but we do this in case we have to shove a 'quote' 
        // into the field, we only want to do that the very
        // first time the user comes into the form

        $form = new CCTopicReplyForm();
        $did_validation = false;
        $validated = false;
        if( !empty($_POST['topicreply']) )
        {
            $validated = $form->ValidateFields();
            $did_validation = true;
        }

        $row = CCDatabase::QueryRow('SELECT topic_user,topic_text as format_text_topic_text FROM cc_tbl_topics WHERE topic_id='.$topic_id);
        $user_real_name = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_id='.$row['topic_user']);

        if( empty($_POST['topicreply']) || ($did_validation && !$validated) )
        {
            if( CCUser::CurrentUser() == $row['topic_user'] )
            {
                $title = 'str_topic_reply_to_yourself';
            }
            else
            {
                $title = array('str_topic_reply_to_s',$user_real_name);
            }
            $page->SetTitle($title);

            if( $is_quote === true && !$did_validation)
            {
                require_once('cchost_lib/cc-dataview.php');
                $dv = new CCDataView();
                $info = array( 'e' => array( CC_EVENT_FILTER_FORMAT ) );
                $r = array(&$row);
                $dv->FilterRecords( $r, $info );
                $form->SetFormValue( 'topic_text', "[quote={$user_real_name}]{$row['topic_text_plain']}[/quote]");
            }

            /*
            CCPage::PageArg('topic_id',$topic_id);
            CCPage::PageArg('topic',$record,'topic_reply' );
            */
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $record = CCDatabase::QueryRow('SELECT topic_name,topic_forum,topic_thread,topic_user,topic_type,topic_upload,topic_id FROM cc_tbl_topics WHERE topic_id='.$topic_id);

            $replang = _('Reply');
            if( strstr($record['topic_name'],$replang) === false )
                $name = sprintf( '%s (%s)', $record['topic_name'], $replang );
            else
                $name = $record['topic_name'];
            $form->GetFormValues($values);
            $topics = new CCTopics();
            $next_id = $topics->NextID();
            $values['topic_id']    = $next_id;
            $values['topic_date']  = date('Y-m-d H:i:s',time());
            $values['topic_user']  = CCUser::CurrentUser();
            $values['topic_type']  = 'reply';
            $values['topic_forum'] = $record['topic_forum'];
            $values['topic_thread'] = $record['topic_thread'];
            $values['topic_upload'] = $record['topic_upload'];
            $values['topic_name']   = $name;
            $topics->InsertNewTopic($values,$topic_id);

            if( !empty($values['topic_thread']) )
            {
                // err, this should be in forums.inc no?
                require_once('cchost_lib/ccextras/cc-forums.inc');
                $threads =& CCForumThreads::GetTable();
                $tvalues['forum_thread_id']   = $record['topic_thread'];
                $tvalues['forum_thread_newest'] = $next_id;
                $tvalues['forum_thread_date'] = $values['topic_date'];
                $threads->Update($tvalues);
            }

            CCEvents::Invoke( CC_EVENT_TOPIC_REPLY, array( &$values, &$record ) );

            CCUtil::SendBrowserTo();
        }
    }

    function Quote($topic_id)
    {
        $this->Reply($topic_id,true);
    }

    function CheckTopicAccess($topic_id)
    {
        if( CCUser::IsLoggedIn() )
        {
            if( CCUser::IsAdmin() )
                return;
            $topics =& CCTopics::GetTable();
            $user_id = $topics->QueryItemFromKey('topic_user',$topic_id);
            if( $user_id == CCUser::CurrentUser() )
                return;
        }
        cc_exit();
    }

    function Delete($topic_id,$branch=false)
    {
        if( $branch && !CCUser::IsAdmin() )
            cc_exit();
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->CheckTopicAccess($topic_id);
        $topics =& CCTopics::GetTable();
        $page->SetTitle(_("Deleting Topic"));
        if( empty($_POST['confirmtopicdelete']) )
        {
            $topics =& CCTopics::GetTable();
            $pretty_name = $topics->QueryItemFromKey('topic_name',$topic_id);
            if( empty($pretty_name) )
                $pretty_name = _('Topic');
            require_once('cchost_lib/ccextras/cc-topics-forms.inc');
            $form = new CCConfirmTopicDeleteForm($pretty_name);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $this->DeleteTopic($topic_id,$branch);
            //CCPage::Prompt(_("Topic has been deleted"));
            CCUtil::SendBrowserTo();
        }
    }

    function DeleteTopic($topic_id,$branch=false)
    {
        $translations =& CCTopicsi18n::GetTable();
        $topics       =& CCTopics::GetTable();

        $x = $topics->GetParentTopic($topic_id);
        list( $parent_id, $left, $right ) = $x;
        $depth = '';
        $delrecord = $topics->QueryKeyRow($topic_id);

        if( $parent_id && !$branch )
        {
            if( $right - $left > 1 )
            {
                // has children and a parent
                // mark as deleted
                $args4['topic_id'] = $topic_id;
                $args4['topic_deleted'] = 1;
                $topics->Update($args4);
                $depth = CCTDF_MARK;
                CCEvents::Invoke( CC_EVENT_TOPIC_DELETE, array( $depth, $topic_id ));
            }
            else
            {
                // one parent, no children:
                // delete from tree

                $depth = CCTDF_SHALLOW;
                CCEvents::Invoke( CC_EVENT_TOPIC_DELETE, array( $depth, $topic_id ));
                // delete from topics
                $topics->DeleteKey($topic_id);
                $translations->DeleteTranslationsOf($topic_id);
                // If the parent is marked as deleted, nuke it
                $old_show_val = $topics->ShowDeleted(true);
                $mark = $topics->QueryItemFromKey('topic_deleted',$parent_id);
                $topics->ShowDeleted($old_show_val);
                if( $mark )
                    $this->DeleteTopic($parent_id); // recurse 'up'
            }
        }
        else
        {
            // top level topic or branch flag is set, wipe it's tree...
            $depth = CCTDF_DEEP;
            CCEvents::Invoke( CC_EVENT_TOPIC_DELETE, array( $depth, $topic_id ));
            $topics->DeleteKeyRow($topic_id);
            $translations->DeleteTranslationsOf($parent_id);
        }

        CCEvents::Invoke( CC_EVENT_POST_TOPIC_DELETE, array( $depth, $delrecord ) );
    }

    function Edit($topic_id)
    {
        $page =& CCPage::GetPage();
        $this->CheckTopicAccess($topic_id);
        
        global $CC_GLOBALS;

        require_once('cchost_lib/ccextras/cc-topics-forms.inc');

        $form =  new CCTopicEditForm();
        $topics =& CCTopics::GetTable();
        $record = $topics->QueryKeyRow($topic_id);
        
        $inpost = !empty($_POST['topicedit']);

        if( !$inpost )
            $form->PopulateValues($record);

        if( !$inpost || !$form->ValidateFields() )
        {
            $page->SetTitle(array('str_topic_edit_s',$record['topic_name']));
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $values['topic_id']   = $topic_id;
            $values['topic_edited'] = date('Y-m-d H:i:s',time());
            $topics->Update($values);
            CCUtil::SendBrowserTo();
        }
    }

    function View($topic_id='')
    {
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('datasource=topics&t=ccskins/shared/pages/content_page.tpl&ids='.$topic_id);
        $query->Query($args);
    }

    function Lock($topic_id)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Locking and Unlocking Topic Replies'));

        $row =
            CCDatabase::QueryRow('SELECT topic_left, topic_right, topic_locked FROM cc_tbl_topics WHERE topic_id='.$topic_id,false);

        if( empty($row) )
        {
            $page->Prompt(_('Topic does not exist'));
            return;
        }
        list( $left, $right, $topic_locked  ) = $row;
        $lock = $topic_locked ? '0' : '1';
        $sql = "UPDATE cc_tbl_topics SET topic_locked = {$lock} WHERE topic_left >= {$left} AND topic_right <= {$right}";
        CCDatabase::Query($sql);

        $prompt = $lock ? _('Topic is now locked') 
                        : _('Topic is now unlocked');

        if( !empty($_SERVER['HTTP_REFERER']) )
        {
            $prompt .= ' <a href="' . $_SERVER['HTTP_REFERER'] . '">' .
                       _('Return to previous page') . '</a>';   
        }

       $page->Prompt($prompt);
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('topics','reply'),  array( 'CCTopic', 'Reply'),   
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{topic_id}/[isquote]', 
            _('Display topic reply form') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('topics','translate'),  array( 'CCTopic', 'Translate'),   
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{topic_id}', 
            _('Display topic translate form') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('topics','quote'),  array( 'CCTopic', 'Quote'),   
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{topic_id}', 
            _('Display quote reply form') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('topics','delete'), array( 'CCTopic', 'Delete'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{topic_id}', 
            _('Display delete topic form') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('topics','edit'),   array( 'CCTopic', 'Edit'),    
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{topic_id}', 
            _('Display an edit topic form') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('topics','view'),   array( 'CCTopic', 'View'),    
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{topicid}', 
            _('Display a topic thread') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('topics','lock'),   array( 'CCTopic', 'Lock'),    
            CC_ADMIN_ONLY, ccs(__FILE__), '{topicid}', 
            _('Lock a topic thread from replies') , CC_AG_FORUMS );

        CCEvents::MapUrl( ccp('user_hook','topic_cmds'),   array( 'CCTopicHook', 'DoHook'),    
            CC_MUST_BE_LOGGED_IN, dirname(__FILE__) . '/cc-topics-hook.inc', '', 
            _('Ajax callback for topic hook'), CC_AG_FORUMS );
    }

}
?>
