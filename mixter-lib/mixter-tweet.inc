<?php

/*
  $Id%
*/


function tweet_onfiltertopics(&$topics)
{
    if( !CCUser::IsAdmin() )
        return;

    foreach( $topics as $K => $topic )
    {
        $topics[$K]['topic_links'][] = array( 'url' => ccl('admin','tweet','topic',$topic['topic_id']),'text'=>'Tweet');
    }
}

function tweet_post_to_twitter( $status )
{
    global $CC_GLOBALS;
    
    $user = $CC_GLOBALS['tweet-account'];
    $pw   = $CC_GLOBALS['tweet-password'];
    
    if( empty($user) || empty($pw) )
    {
        return false;
    }
    
    require_once( 'cchost_lib/snoopy/Snoopy.class.php');
    $snoopy = new Snoopy();
    
    // snoopy will use basic auth with user/pw
    
    $url = "http://{$user}:{$pw}@twitter.com/statuses/update.xml";
    $vars['status'] = $status;
    
    $snoopy->submit($url,$vars);
    
    return $snoopy->results;
    
}


function tweet_api($type='',$arg='')
{
    $type = CCUtil::Strip($type);
    $arg = sprintf('%0d',CCUtil::Strip($arg));
    
    if( empty($type) )
        die('No type arg in tweet api');
    if( empty($arg) )
        die('Missing arg arg in tweet api');
    
    if(  empty($_POST) )
    {
        $url = $_SERVER['HTTP_REFERER'];
        switch( $type )
        {
            case 'upload':
                {
                    $qr = CCDatabase::QueryRow(
                        'SELECT upload_name,user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id = '.$arg,false);
                    list( $title, $user ) = $qr;
                    $url = ccl('files',$user,$arg);
                    if( empty($title) )
                        die('Unknown upload id in tweet api');
                }
                break;
                
            case 'topic':
                {
                    $title = CCDatabase::QueryItem('SELECT topic_name FROM cc_tbl_topics WHERE topic_id = '.$arg);
                    if( empty($title) )
                        die('Unknown topic id in tweet api');
                    
                }
                break;
                
            case 'playlist':
                {
                    $title = CCDatabase::QueryItem('SELECT cart_name FROM cc_tbl_carts WHERE cart_id = '.$arg);
                    if( empty($title) )
                        die('Unknown collection id in tweet api');                    
                }
                break;
            
            default:
                die('unknown type in tweet api');
        }
        
        $status = $title . ' ' . $url;
    }
    else
    {
        $status = '';
    }
    require_once('cchost_lib/cc-page.php');
    require_once('cchost_lib/cc-form.php');
    
    $page =& CCPage::GetPage();
    $page->SetTitle('Send this item to Twitter');
    
    $form = new CCForm();
    
    $fields = array(
        'status' => array(
            'form_label' => 'Status',
            'value' => $status,
            'formatter' => 'textarea',
            'flags' => CCFF_POPULATE
            ),
                
    );
    $form->AddFormFields( $fields );
    $form->SetFormHelp('Send this to Twitter');
    
    if( empty($_POST) || !$form->ValidateFields() )
    {
        $page->AddForm( $form->GenerateForm() );
    }
    else
    {
        $form->GetFormValues($values);
        $ret = tweet_post_to_twitter($values['status']);
        if( $ret === false )
        {
            $page->Prompt('Post to twitter failed');
        }
        else
        {
            $page->Prompt('Post sent to twitter');
        }
    }
}


function tweet_config_fields($scope,&$fields)
{
    if( $scope == CC_GLOBAL_SCOPE )
    {
        $fields['tweet-account'] =
           array(  'label'      => _('Twitter account'),
                   'form_tip'   => _('Used by the tweet command'),
                   'value'      => '',
                   'formatter'  => 'textedit',
                   'flags'      => CCFF_POPULATE);
        $fields['tweet-password'] =
           array(  'label'      => _('Twitter password'),
                   'form_tip'   => '',
                   'value'      => '',
                   'class'      => 'cc_form_input_short',
                   'formatter'  => 'textedit',
                   'flags'      => CCFF_POPULATE);
    }
}

function tweet_onmapurls()
{
    CCEvents::MapUrl( ccp('admin','tweet'), 'tweet_api',  CC_ADMIN_ONLY, ccs(__FILE__), '[type]/{upload_id}', 
        _('Tweet to known account') , CC_AG_USER );
}

function tweet_onuploadmenu(&$menu,&$record)
{
    if( CCUser::IsAdmin() )
    {
        $menu['tweet'] = 
                     array(  'menu_text'  => 'Tweet',
                             'weight'     => 1090,
                             'group_name' => 'admin',
                             'id'         => 'tweetcommand',
                             'access'     => CC_ADMIN_ONLY );


        $menu['tweet']['action']  = ccl('admin','tweet', 'upload', $record['upload_id']);
    }
}
    


?>