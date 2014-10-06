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
* $Id: cc-topics-hook.inc 12618 2009-05-14 04:30:46Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

class CCTopicHook
{
    function DoHook()
    {
         $ids = CCUtil::Strip($_GET['ids']);
         $thread_id = empty($_GET['thread']) ? 0 : CCUtil::Strip($_GET['thread']);

        $sql =<<<EOF
            SELECT topic_id, user_real_name, topic_can_xlat, topic_locked, 
                IF( (topic_right - topic_left) > 1, 1, 0) as has_children,
                topic_user, topic_thread
            FROM cc_tbl_topics
            JOIN cc_tbl_user ON topic_user=user_id
            WHERE topic_id IN ($ids)
EOF;
        $ret      = array();
        $recs     = CCDatabase::QueryRows($sql);
        $user_id  = CCUser::CurrentUser();
        $is_admin = CCUser::IsAdmin();

        if( !empty($_GET['type']) && $_GET['type'] == 'review' )
        {
            // yikes
            require_once('cchost_lib/ccextras/cc-reviews.php');
            if( CCReviewsHV::_can_review($_GET['upload']) )
            {
                $review_cmd = array( 'href' => ccl('reviews','post',$_GET['upload']) . '#edit',
                                     'text' => 'str_review_write'  );
            }
        }
        
        $first = false;

        foreach( $recs as $R )
        {
            $rec = array();

            $is_topic_owner = $user_id == $R['topic_user'];

            if( $R['topic_can_xlat'] )
            {
                $rec['cmds'][] = array( 
                                            'href' => ccl('topics','translate',$R['topic_id'])
                                                             . '#edit',
                                            'text' => 'str_topic_translate' );
            }

            if( !empty($_GET['xlat']) )
            {
                /*
                $rec['cmds'][] = array( 'href' => ccl('topics','delete',$R['topic_id']),
                                            'id' => 'delete_link_' . $R['topic_id'],
                                            'text' => 'str_topic_delete' );
                */
                $rec['cmds'][] = array( 'href' => '',
                                            'id' => 'edit_link_' . $R['topic_id'],
                                            'hidden' => true,
                                            'text' => 'str_topic_edit' );
            }
            elseif( $is_topic_owner || $is_admin )
            {
                $rec['cmds'][] = array( 'href' => ccl('topics','delete',$R['topic_id']),
                                            'text' => 'str_topic_delete' );
                $rec['cmds'][] = array( 'href' => ccl('topics','edit',$R['topic_id']),
                                            'text' => 'str_topic_edit' );

                if( $is_admin )
                {
                    if( empty($R['topic_locked']) )
                        $text = _('Lock');
                    else
                        $text = _('Unlock');

                    $rec['cmds'][] = array( 'href' => ccl('topics','lock',$R['topic_id']),
                                                      'text' => $text );

                    $rec['cmd'][] = array( 'href' => ccl('forum','fork',$R['topic_id']),
                                                       'text' => _('Fork') );
                                                       
                    if( $R['has_children'] )
                    {
                        $rec['cmds'][] 
                            = array( 'href' => ccl('topics','delete',$R['topic_id'],'branch'),
                                                          'text' => _('Delete branch') );
                    }

                    if( $is_admin && !empty($R['topic_thread']) )
                    {  
                        if( $first )
                        {                          
                          $rec['cmds'][] 
                             = array( 'href' => ccl('admin','forums', 'fork', $R['topic_id'] ),
                                    'text' => _('Fork' )  );
                        }
                        else
                        {
                            $first = true;
                        }
                    }
                }

            }

            if( empty($_GET['noreply']) && (empty($R['topic_locked']) || $is_admin) )
            {
                if( $is_topic_owner )
                {
                    $text = 'str_topic_reply_to_yourself';
                }
                else
                {
                    /************
                    * temp hack for preventing garbage on the screen
                    * what is the alternative to sprintf for int'l chars?
                    */
                    $asc2uni = array();
                    for($i=128;$i<256;$i++){
                      $asc2uni[chr($i)] = '?';
                    }
                    if( strtr($R['user_real_name'],$asc2uni) != $R['user_real_name'] )
                        $text = 'str_topic_reply';
                    else
                        $text = array('str_topic_reply_to_sn',$R['user_real_name']);
                }

                $rcmd = $thread_id ? 'thread' : 'topics';
                
                $rec['cmds'][] = array( 'href' => ccl($rcmd,'quote',$R['topic_id']),
                                            'text' => 'str_topic_reply_with_quote' );
                
                $rec['cmds'][] = array( 'href' => ccl($rcmd,'reply',$R['topic_id']),
                                            'text' => $text );
            }

            if( !empty($review_cmd) )
            {
                $rec['cmds'][] = $review_cmd;
            }

            $rec['id'] = $R['topic_id'];
            $ret['topic_cmds'][] = $rec;
        }

        CCUtil::ReturnAjaxData($ret,false);
   }

}
?>
