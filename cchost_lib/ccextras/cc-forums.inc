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
* $Id: cc-forums.inc 12631 2009-05-20 20:32:21Z fourstones $
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
require_once('cchost_lib/ccextras/cc-topics.inc');
require_once('cchost_lib/cc-page.php');

/**
* Wrapper for cc_tbl_forums table
*/
class CCForum extends CCTable
{
    function CCForum()
    {
        $this->CCTable('cc_tbl_forums','forum_id');
        $this->AddJoin( new CCForumGroups(), 'forum_group' );
        $this->SetSort( 'forum_group_weight,forum_weight', 'ASC' );
    }

    public static function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCForum();
        return $_table;
    }
}

/**
* Wrapper for cc_tbl_forums_groups
*
* String a UI artifact to group things on the make forum page
*/
class CCForumGroups extends CCTable
{
    function CCForumGroups()
    {
        $this->CCTable('cc_tbl_forum_groups','forum_group_id');
        $this->SetSort('forum_group_weight','ASC');
    }

    public static function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCForumGroups();
        return $_table;
    }
}

/**
* Wrapper for cc_tbl_forum_threads table
*
* Keeps forum topics organized by threads, remembers the
* first topic (that defines the thread), the forum, and
* the latest post in the thread (that defines the age of 
* the thread.
*/
class CCForumThreads extends CCTable
{
    function CCForumThreads()
    {
        $this->CCTable('cc_tbl_forum_threads','forum_thread_id');
        $this->SetSort('forum_thread_sticky DESC, forum_thread_date', 'DESC');
    }

    public static function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCForumThreads();
        return $_table;
    }
}

/**
* Form for posting topics to the current forum
class CCForumPostForm extends CCTopicForm
{
    function CCForumPostForm()
    {
        $this->CCTopicForm( _('New Topic Text'), _('Submit Topic'), true );
    }
}
*/

/**
* Forums API
*
*/
class CCForumAPI
{
    function Index($forum_id='')
    {
        $forum_id = sprintf("%0d",$forum_id);

        if( !empty($forum_id) )
        {
            $this->_show_forum($forum_id);
        }
        else
        {
            $this->_show_forum_home();
        }

        $this->_build_bread_crumb_trail($forum_id);
        $this->_add_feed_links();
    }

    function _show_forum_home()
    {
        //
        // Set up forum group table
        //
        $forum_groups =& CCForumGroups::GetTable();
        $groups = $forum_groups->QueryRows('');
        $num_groups = count($groups);
        $visible_groups = array();
    
        //
        // Forums
        //
        $forums =& CCForum::GetTable();
        $mask = CCMenu::GetAccessMask();

        //
        // Threads
        //
        $threads =& CCForumThreads::GetTable();
        $this->_add_last_topic_join($threads);
        $threads->SetOffsetAndLimit(0,1);
        $threads->SetSort('forum_thread_date', 'DESC');

        //
        // and finally Topics
        //
        $topics =& CCTopics::GetTable();

        for( $i = 0; $i < $num_groups; $i++ )
        {
            $G =& $groups[$i];
            $where['forum_group'] = $G['forum_group_id'];
            $forum_rows = $forums->QueryRows($where);
            $num_forums_in_groups = count($forum_rows);
            $visible_forums = array();

            for( $x = 0; $x < $num_forums_in_groups; $x++ )
            {
                $F =& $forum_rows[$x];
                if( ($F['forum_read_access'] & $mask) != 0 )
                {
                    $forum_id = $F['forum_id'];

                    $wf['forum_thread_forum'] = $forum_id;
                    $tf['topic_forum'] = $forum_id;

                    $F['num_posts']     = $topics->CountRows($tf);
                    $F['num_threads']   = $threads->CountRows($wf);

                    $latest_post = $threads->QueryRow($wf);

                    if( !empty($latest_post) )
                    {
                        $url = ccl('thread',$latest_post['forum_thread_id']) . '#' . 
                                                $latest_post['topic_id'];
                        $latest_post['permalink'] = $url;
                        $latest_post['user_url'] = ccl('people',$latest_post['user_name']);
                    }

                    $F['latest_post']   = $latest_post;
                    $F['link']          = ccl('forums', $forum_id );
                    $visible_forums[] = $F;
                }
            }

            if( !empty($visible_forums) )
            {
                $G['forums'] = $visible_forums;
                $visible_groups[] = $G;
            }
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_forums');
        $page->PageArg('forums',$visible_groups,'forum_index');
    }

    function _add_last_topic_join(&$threads)
    {
        $jtopic = $threads->AddJoin( new CCTopics(), 'forum_thread_newest' );
        $threads->AddJoin( new CCUsers(),  "$jtopic.topic_user" );
        return $jtopic;
    }

    function _show_forum($forum_id)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        //
        // Get the forums row and check this
        // user's access rights
        //
        $forums =& CCForum::GetTable();
        $forum_row = $forums->QueryKeyRow($forum_id);
        $mask = CCMenu::GetAccessMask();
        if( ($mask & $forum_row['forum_read_access']) == 0 )
        {
            return $this->_show_forum_home();
        }

        // 
        // Set up the commands for this forum
        //
        $commands = array();
        if( ($mask & $forum_row['forum_post_access']) != 0 )
        {
            $commands[] = array( 'url' => ccl('forums','post',$forum_id),
                                 'text' => 'str_forum_post_new_topic' );
        }

        //
        // Get the relevant thread info for this forum
        //
        $threads =& CCForumThreads::GetTable();
        $newest     = $threads->AddJoin( new CCTopics(), 'forum_thread_newest' );
        $newestuser = $threads->AddJoin( new CCUsers(),  "{$newest}.topic_user" );
        $oldest     = $threads->AddJoin( new CCTopics(), 'forum_thread_oldest' );
        $oldestuser = $threads->AddJoin( new CCUsers(),  "{$oldest}.topic_user" );

        $columns =<<<EOF
        cc_tbl_forum_threads.*,
        {$oldestuser}.user_name      AS author_user_name, 
        {$oldestuser}.user_real_name AS author_real_name,   
        {$oldest}.topic_name         AS oldest_topic_name, 
        {$newestuser}.user_real_name AS newest_real_name, 
        {$newestuser}.user_name      AS newest_user_name, 
        DATE_FORMAT({$newest}.topic_date,'%a %b %e, %Y')         AS newest_topic_date, 
        {$newest}.topic_id           AS newest_topic_id
EOF;

        $wf['forum_thread_forum'] = $forum_id;
        $page->AddPagingLinks($threads,$wf,30);
        $forum_threads =& $threads->QueryRows($wf,$columns);

        $topic_counter = new CCTable('cc_tbl_topics','topic_id');

        //
        // Massage the forum data for display 
        //
        $count = count($forum_threads);
        for( $i = 0; $i < $count; $i++ )
        {
            $R =& $forum_threads[$i];
            $thread_id = $R['forum_thread_id'];
            $R['thread_url'] = ccl('thread',$thread_id);
            $w = array();
            $w['topic_thread'] = $thread_id;
            $R['num_topics'] = $topic_counter->CountRows($w) - 1;
            $R['newest_topic_url'] = ccl('thread',$thread_id) . '#' . $R['newest_topic_id'];
            $R['newest_user_url'] = ccl('people',$R['newest_user_name']);
            $R['author_url']  = ccl('people',$R['author_user_name']);
        }

        //
        // Build the page
        //
        $page->SetTitle($forum_row['forum_name']);
        $page->PageArg( 'forum_cmds', $commands );
        $page->PageArg( 'threads', $forum_threads , 'forum_listing' );
    }

    function _add_feed_links( $thread = '', $user = '', $user_real_name='', $thread_name ='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        if( !empty($thread) )
        {
            $q = '&thread='.$thread;
            if( empty($thread_name) )
                $thread_name = CCDatabase::QueryItem('SELECT forum_thread_name FROM cc_tbl_forum_threads WHERE forum_thread_id='.$thread);
            $title = empty($thread_name) ? $page->String('str_topic') : $thread_name;
        }
        elseif( !empty($user) )
        {
            $q = '&thread=-1&user='.$user;
            $title = $user_real_name . ' (' . _('Topics') . ')';
        }
        else
        {
            $q = '&thread=-1'; // yea, magic hack
            $title = $page->String('str_forums');
        }

        $query = 'datasource=topics' . $q .'&title=' . urlencode($title);
        $page->AddFeedLink($query, $title, $title, 'feed_topics', 'topics' );
    }

    function _build_bread_crumb_trail($forum_id='',$topic_name='')
    {
        $trail = array();
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('forums'), 'text' => 'str_forums' );

        if( $forum_id )
        {
            $forum_name = CCDatabase::QueryItem('SELECT forum_name FROM cc_tbl_forums WHERE forum_id='.$forum_id);
            $trail[] = array( 'url' => ccl('forums',$forum_id), 'text' => $forum_name );

            if( $topic_name )
            {
                $trail[] = array( 'url' => 'dummy', 'text' => $topic_name );
            }
        }
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function _build_bread_crumb_trail2($topic_id, $text)
    {
        $sql = "SELECT forum_id, forum_name, forum_thread_id, topic_name  FROM cc_tbl_topics " .
                 "JOIN cc_tbl_forum_threads ON topic_thread = forum_thread_id ".
                 "JOIN cc_tbl_forums        ON forum_thread_forum = forum_id " .
                 "WHERE topic_id=" .$topic_id;
        $row = CCDatabase::QueryRow($sql,false);
        list( $forum_id, $forum_name, $thread_id, $thread_name  ) = $row;
        $trail = array();
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('forums'), 'text' => 'str_forums' );
        $trail[] = array( 'url' => ccl('forums',$forum_id), 'text' => $forum_name );
        $trail[] = array( 'url' => ccl('thread',$thread_id), 'text' => $thread_name );
        $trail[] = array( 'url' => '', 'text' => $text );
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    // The topic database record is actually gone at this point
    // the $topic argument is a shadow of that record
    // Here we update the 'newest' topic in the thread. If there
    // are no more topics in the thread, the thread is destroyed.
    function OnPostTopicDelete($deltype,$topic)
    {
        if( empty($topic['topic_thread']) )
            return;

        $topic_threads = new CCForumThreads();

        $thread_id = $topic['topic_thread'];

        $sql =<<<EOF
            SELECT topic_id, topic_date FROM cc_tbl_topics 
            WHERE topic_thread={$thread_id} AND topic_deleted = 0
            ORDER BY topic_date DESC 
            LIMIT 1
EOF;
        $row = CCDatabase::QueryRow($sql,false);
        list( $newest, $topic_date ) = $row;
        if( empty($newest) )
        {
            $topic_threads->DeleteKey( $thread_id );
        }
        else
        {
            $tf['forum_thread_newest'] = $newest;
            $tf['forum_thread_date']   = $topic_date;
            $tf['forum_thread_id']     = $thread_id;
            $topic_threads->Update($tf);
        }
    }

    // This topic_id is about to be deleted from the database
    // all we are doing here is updating the users' forum
    // post count
    function OnTopicDelete($deltype,$topic_id)
    {
        $deep = $deltype == CCTDF_DEEP;

        if( $deep )
        {
            list( $left, $right ) = CCDatabase::QueryRow('SELECT topic_left, topic_right FROM cc_tbl_topics WHERE topic_id='.$topic_id,false);
            if( empty($left) || empty($right) ) // orphaned
            {
                $where = "topic_id = $topic_id";
            }
            else
            {            
                $where = "topic_left >= $left AND topic_right <= $right";
            }
        }
        else
        {
            $where = "topic_id = $topic_id";
        }

        $sql = "SELECT topic_user FROM cc_tbl_topics WHERE {$where}";
        $user_ids = CCDatabase::QueryItems($sql);
        $this->UpdateUserPostCounts($user_ids);
    }

    function UpdateUserPostCounts($user_ids)
    {
        if( !empty($user_ids) ) // topics were orphaned if null
        {
            $user_ids = join( ', ', $user_ids );
            $sql =<<<EOF
                SELECT COUNT(*)-1 as user_num_posts, topic_user as user_id 
                FROM cc_tbl_topics 
                WHERE topic_user IN ({$user_ids}) AND topic_thread > 0 
                GROUP BY topic_user
EOF;
            $rows = CCDatabase::QueryRows($sql);
            $users =& CCUsers::GetTable();
            foreach( $rows as $row )
                $users->Update($row);
        }
    }
    
    function OnTopicReply(&$reply, &$original)
    {
        $this->_on_new_topic($reply);
    }

    function _on_new_topic(&$topic)
    {
        if( empty($topic['topic_thread']) )
            return;

        $users =& CCUsers::GetTable();
        $num_posts = $users->QueryItemFromKey('user_num_posts',$topic['topic_user']);
        $args['user_num_posts'] = $num_posts + 1;
        $args['user_id'] = $topic['topic_user'];
        $users->Update($args);
    }

    function Reply($topic_id)
    {
        $this->_build_bread_crumb_trail2($topic_id, 'str_topic_reply');
        $api = new CCTopic();
        $api->Reply($topic_id,false);
    }

    function Quote($topic_id)
    {
        $this->_build_bread_crumb_trail2($topic_id, 'str_topic_reply');
        $api = new CCTopic();
        $api->Reply($topic_id,true);
    }
    
   function PostNew($forum_id)
    {
        $api = new CCForums();
        $forums =& CCForum::GetTable();
        $row = $forums->QueryKeyRow($forum_id);
        $mask = CCMenu::GetAccessMask();
        if( empty($row) || (($row['forum_post_access'] & $mask) == 0) )
        {
            $url = ccl('forums');
            CCUtil::SendBrowserTo($url);
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle( 'str_forum_post_new_topic' );
        $this->_build_bread_crumb_trail($forum_id,'str_forum_post_new_topic' );

        require_once('cchost_lib/ccextras/cc-topics-forms.inc');
        $form = new CCTopicForm( 'str_forum_new_topic_text', 'str_forum_submit_topic', CC_TTF_EDIT );
        if( empty($_POST['topic']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            list( $thread_id, $topic_id ) = $this->_post_new_topic($values,$forum_id);
            $url = ccl('thread',$thread_id);
            CCUtil::SendBrowserTo($url);
        }
    }

    function _post_new_topic($values,$forum_id)
    {
        $topics =& CCTopics::GetTable();
        $topic_id = $topics->NextID();
        $threads =& CCForumThreads::GetTable();
        $thread_id = $threads->NextID();

        $values['topic_forum'] = $forum_id;
        $values['topic_date'] = date('Y-m-d H:i:s',time());
        $values['topic_user'] = CCUser::CurrentUser();
        $values['topic_id'] = $topic_id;
        $values['topic_thread'] = $thread_id;
        $values['topic_tags'] = '';
        $topics->InsertNewTopic($values,0);

        $ftvalues = array();
        $ftvalues['forum_thread_id'] = $thread_id;
        $ftvalues['forum_thread_forum'] = $forum_id;
        $ftvalues['forum_thread_user'] = CCUser::CurrentUser();
        $ftvalues['forum_thread_oldest'] = 
        $ftvalues['forum_thread_newest'] = $topic_id;
        $ftvalues['forum_thread_date'] = $values['topic_date'];
        $ftvalues['forum_thread_name'] = $values['topic_name'];
        if( empty($ftvalues['forum_thread_extra']) )
            $ftvalues['forum_thread_extra'] = '';
        $threads->Insert($ftvalues);

        $this->_on_new_topic($values);

        CCEvents::Invoke( CC_EVENT_FORUM_POST, array( &$values ) );
        
        return array( $thread_id, $topic_id );

    }
    
    function ViewThread($thread_id='')
    {
        global $CC_GLOBALS;
        
        $page =& CCPage::GetPage();

        $api = new CCForums();

        $threads =& CCForumThreads::GetTable();
        if( empty($thread_id) || !$threads->KeyExists($thread_id) )
        {
            $this->_build_bread_crumb_trail(1);
            $page->SetTitle( 'str_forum_thread_missing' );
            $page->Prompt('str_forum_thread_not_there');
            return;
        }

        $sql = <<<EOF
        SELECT topic_name, topic_id, topic_locked, topic_user,
                forum_thread_forum, forum_read_access,                 
                forum_post_access, forum_thread_closed, forum_thread_sticky, forum_id, forum_name
            FROM cc_tbl_topics 
            JOIN cc_tbl_forum_threads ON forum_thread_oldest = topic_id 
            JOIN cc_tbl_forums        ON forum_thread_forum = forum_id
            WHERE forum_thread_id = $thread_id
EOF;
        
        $row = CCDatabase::QueryRow($sql);
        
        $mask = CCMenu::GetAccessMask();
        if( ($mask & $row['forum_read_access']) == 0 )
        {
            if( ($row['forum_read_access'] & CC_ADMIN_ONLY) != 0 )
                $msg = 'str_forum_admin_required';
            elseif( ($row['forum_read_access'] & CC_MUST_BE_LOGGED_IN) != 0 )
                $msg = 'str_forum_login_required';
            else
                $msg = 'str_forum_perm_denied';
            require_once('cchost_lib/cc-page.php');
            $page->SetTitle('str_forum_restricted_access');
            $page->Prompt($msg);
            return;
        }

        require_once('cchost_lib/cc-page.php');

        if( ($mask & $row['forum_post_access']) != 0 )
            $this->_thread_commands($row,$thread_id);

        $page->PageArg('topic_thread_id',$thread_id);
        
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('limit=0&sort=left&paging=off&f=page&t=topic_thread&&title=' . 
            urlencode($row['topic_name']) . '&match='. $thread_id );

        $query->Query($args);                        
        
        $name = $row['forum_name'] . '::' . $row['topic_name'];
        $this->_add_feed_links( $thread_id, '', '', $name );
        $this->_build_bread_crumb_trail($row['forum_id'],$row['topic_name']);
    }

    function _thread_commands($row,$thread_id)
    {
        $commands = array();
        $closed = $row['forum_thread_closed'];

        if( empty($closed) && empty($row['topic_locked']) )
        {
            $commands[] = array( 'url' => ccl('topics','reply',$row['topic_id']) . '#edit',
                             'text' => 'str_forum_post_topic_reply', 'msg' => 0 );
        }

        if( CCUser::IsAdmin() )
        {
            $commands[] = array( 'url' => ccl('admin','forums', 'move', $thread_id ),
                                 'text' => _('Move thread' ), 'msg' => 0  );

            $ctext = $closed ? _('Open') : _('Close');

            $commands[] = array( 'url' => ccl('admin','forums', 'close', $thread_id ),
                                 'text' => $ctext, 'msg' => 0  );

            $ctext = $row['forum_thread_sticky'] ? _('Unstick') : _('Stick');

            $commands[] = array( 'url' => ccl('admin','forums', 'stick', $thread_id ),
                                 'text' => $ctext, 'msg' => 0  );
        }

        $page =& CCPage::GetPage();
        $page->PageArg('thread_commands',$commands);
    }

    function User($user_name='')
    {
        if( !empty($user_name) )
        {
            list( $user_real_name, $user_id ) = 
                CCDatabase::QueryRow('SELECT user_real_name, user_id FROM cc_tbl_user WHERE user_name = \'' . $user_name . '\'', false);
        }
        if( empty($user_id) )
        {
            CCUtil::Send404();
            return;
        }
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-query.php');
        $page =& CCPage::GetPage();
        $query = new CCQuery();
        $title = $page->String(array( 'str_forum_topics_by', $user_real_name ));
        $args = $query->ProcessAdminArgs('sort=date&ord=desc&t=topics_user&limit=20&title'.$title.'&user='.$user_name);
        $query->Query($args);
        $this->_add_feed_links( '', $user_name,$user_real_name);
    }
}

?>
