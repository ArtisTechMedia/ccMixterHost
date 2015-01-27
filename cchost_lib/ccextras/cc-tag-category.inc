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
* $Id: cc-tag-category.inc 13921 2010-01-14 22:27:26Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

class CCTagCat 
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/tagcat', array('CCTagCat','AdminTags'), CC_ADMIN_ONLY, ccs(__FILE__) );
        CCEvents::MapUrl( 'admin/tagcat/cats', array('CCTagCat','AdminCats'), CC_ADMIN_ONLY, ccs(__FILE__) );
    }
    
    function _get_paging(&$offset,$show_all=false,$where='',$search_term='')
    {
        /*
            CONDITIONS:
            
            axis 1:
                Inital form show
                On submit
                
            axis 2:
                Showing all
                Showing unassigned only:
                Showing search results
         
        */
        
        $page_size = 50;

        $showflag = $show_all ? '&showall=1' : '';

        $help = '';

        if( $show_all )
        {
            $text = '<p>Results for all tags</p>';
        }
        else
        {
            if( $search_term )
            {
                $text = "<p>Results for search term: <i>{$search_term}</i></p>";
            }
            else
            {
                $text = '<p>Results for all unassigned tags</p>';
            }
        }
        
        $help .= $text;
        
        if(empty($_REQUEST['offset']))
        {
            $offset = 0;
            $prev = 0;
            $next = $page_size;
        }
        else
        {
            $offset = $_REQUEST['offset'];
            $prev = $offset - $page_size;
            $next = $offset + $page_size;
            
        }

        $link = ccl('admin','tagcat');
        $edit_url = $link . '/';
        $search_url = $link . '?search=';

        $help .=<<<EOF
<div style="float:right;margin-right:10%;">
search for: <input id="search_term" style="width:10em" /><a class="small_button" href="javascript://" id="get_search"
onclick="document.location = '{$search_url}' + $('search_term').value;">find</a>
</div>
EOF;
        $ord = empty($_REQUEST['order']) ? '' : 'order=' . urlencode($_REQUEST['order']);
        $xord = $ord ? '&' . $ord : '';

        
        $buttons = array();
        
        $total = CCDatabase::QueryItem("SELECT COUNT(*) FROM cc_tbl_tags {$where}");
        $help .= sprintf("<p>This range of tags: %d to %d (of %d)</p>",$offset,min($total,$next),$total);

        $search_param = $search_term ? '&search=' . urlencode($search_term) : '';
        
        if( $total > $page_size )
        {
            if( !empty($offset) )
            {
                $plink = url_args( $link, 'offset='.$prev . $showflag . $xord . $search_param );
                $buttons[] = "<a href=\"{$plink}\" class=\"small_button\">prev</a>";
            }
        }

        if( !empty($_POST) )
        {
            if( $show_all || $search_term )
            {
                $text = 're-display';
            }
            else
            {
                $text = 'next batch';
            }
            $plink = url_args( $link, 'offset='.$offset . $showflag . $xord . $search_param );
            $buttons[] = "<a href=\"{$plink}\" class=\"small_button\">{$text}</a>";                
        }
        
        if( $total > $page_size && ($next < $total) )
        {
            if( !empty($next) && (empty($_POST) || $show_all || $search_term) )
            {
                $nlink = url_args( $link, 'offset='.$next . $showflag .  $xord . $search_param );
                $buttons[] = "<a href=\"{$nlink}\" class=\"small_button\">next</a>";
            }
        }
    
        if( $show_all || $search_term )
        {
            $buttons[] = "<a class=\"small_button\" href=\"{$link}\">show unassigned tags</a>";
        }
        
        if( !$show_all || $search_term )
        {
            $surl = url_args($link,'showall=1' . $xord );
            $buttons[] = "<a class=\"small_button\" href=\"{$surl}\">show all tags</a>";
        }

        if( empty($ord) )
        {
            $ourl = url_args($link, ($show_all ? 'showall=1&' : '') . 'order=tags_tag%20asc' . $search_param );
            $olink = "<a class=\"small_button\" href=\"{$ourl}\">sort by tag</a>";
        }
        else
        {
            $ourl = url_args($link, ($show_all ? 'showall=1&' : '') . $search_param  );
            $olink = "<a class=\"small_button\" href=\"{$ourl}\">sort by count</a>";            
        }

        $buttons[] = $olink;
        
        $td = '<td style="padding-right:3px">';
        $help .= '<table><tr>' . $td . join( '</td>' . $td, $buttons ) . '</td></tr></table>';
        
        $help .= '<div style="text-align:right;">Type (<b>T</b>) is either System (<b>S</b>), Admin (<b>A</b>) or User (<b>U</b>)</div>';
        
        return $help;    
    }
    
    function AdminTags()
    {
        $show_all = !empty($_REQUEST['showall']);
        
        $where = '';
        $search_term = '';
        
        if( $show_all )
        {
            $where = '';
        }
        else
        {
            if( !empty($_REQUEST['search']) )
            {
                $search_term = CCUtil::StripText($_REQUEST['search'] );
                if( !empty($search_term) ) 
                {
                    $where = "WHERE tags_tag LIKE '%{$search_term}%'";
                }
            }
            
            if( empty($where) )
                $where = 'WHERE (tags_count > 0) AND (tags_category = "0" OR ISNULL(tags_category) OR tags_category = "" )';   
        }
        

        $GLOBALS['skip_form_word_hack'] = true; // sigh. go global search for this if you care
      
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-form.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _('Assign Tag Categories');
        $admin = new CCAdmin();
        $admin->BreadCrumbs(true,array('url'=>ccl('admin','tags'),'text'=>_('Manage Tags')),array('url'=>'','text'=>$title));
        $page =& CCPage::GetPage();
        $page->SetTitle($title);
        
        
        $form = new CCGridForm();
        $cats = CCDatabase::QueryRows('SELECT * FROM cc_tbl_tag_category');

        $heads = array(_('Tag'), _('Category'),_('T'),_('Convert to...') );
        $form->SetColumnHeader($heads);

        $opts = array();
        $opts[''] = '(none)';
        foreach( $cats as $cat )
            $opts[ $cat['tag_category_id'] ] = cc_strchop($cat['tag_category'],6) . '&nbsp;&nbsp;';

        $order = 'tags_count DESC, tags_tag ASC';
        if( !empty($_REQUEST['order']) )
        {
            $order = $_REQUEST['order'];
        }

        $offset = 0;    
        $help = $this->_get_paging($offset,$show_all,$where,$search_term);
        if( empty($_POST['tagsnapshot']) )
        {
            $sql =<<<EOF
SELECT tags_tag,tags_category,tags_count,tags_type, tag_alias_alias as rule
    FROM cc_tbl_tags 
    LEFT OUTER JOIN cc_tbl_tag_alias ON tags_tag=tag_alias_tag
    {$where}
    ORDER by {$order}
    LIMIT 50 OFFSET {$offset}
EOF;

            $tags = CCDatabase::QueryRows($sql);
        }
        else
        {
            // things get horked if a user updates the tags while
            // we are editing, so we take a snapshot between now
            // when we hit submit...
            
            $tags = unserialize( urldecode($_POST['tagsnapshot']) );
        }
        
        $count = count($tags);
        
        $types = array(
            CCTT_ADMIN =>  'A',
            CCTT_SYSTEM => 'S',
            CCTT_USER =>   'U'
        );
        
        for( $i = 0; $i < $count; $i++ )
        {
            $T =& $tags[$i];
            $id = $T['tags_tag'];
            $pre = "mi[{$id}]";
            
            $a = array();
            $a[] = 
                array(
                    'element_name'  => $pre . '[tags_tagstatic]',
                    'value'      => $id . ' (' . $T['tags_count'] . ')',
                    'formatter'  => 'statictext',
                    'flags'      => CCFF_STATIC);

            $a[] = 
                array(
                    'element_name'  => $pre . '[tags_category]',
                    'value'      => $T['tags_category'],
                    'options'    => $opts,
                    'nobr'       => true,
                    'formatter'  => 'radio',
                    'flags'      => CCFF_REQUIRED 
                );
                    
            $a[] = 
                array(
                    'element_name'  => $pre . '[tags_type]',
                    'value'      => $T['tags_type'],
                    'options'    => $opts,
                    'formatter'  => 'select',
                    'options'    => $types,
                    'flags'      => CCFF_REQUIRED 
                );
            $a[] = 
                array(
                    'element_name'  => $pre . '[rule]',
                    'value'      => $T['rule'],
                    'formatter'  => 'textedit',
                    'class'      => 'vedit',
                    'flags'      => CCFF_NONE
                );
                    
            $form->SetHiddenField($pre . '[tags_tag]',$id);
            $form->AddGridRow($id,$a);
        
        }
        
        $form->SetHiddenField('tagsnapshot', urlencode(serialize($tags)) );

        $help .=<<<EOF
<style>
.form_row_0 {
    background-color: #DEF;
}
.vedit {
    font-size: 9px;
    width: 60px;
}
</style>
EOF;

        $form->SetFormHelp($help);

        if( empty($_POST) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $mi = $_POST['mi'];
            $aliases = array();            
            $table = new CCTable('cc_tbl_tags','tags_tag');
            foreach( $mi as $tag )
            {
                if( !empty($tag['rule']) )
                {
                    $aliases[] = array( $tag['tags_tag'], $tag['rule'] );
                }

                if( isset($tag['rule']))
                    unset($tag['rule']);
                    
                $table->Update($tag);
            }
            
            foreach( $aliases as $A )
            {
                $sql = "INSERT INTO cc_tbl_tag_alias SET tag_alias_tag='{$A[0]}', tag_alias_alias ='{$A[1]}'" .
                          " ON DUPLICATE KEY UPDATE tag_alias_alias = '{$A[1]}'";
                CCDatabase::Query($sql);
            }

            // heh, here's how to show just the form help
            // this is bound to break somewhere down the line...
            
            $page->PageArg('curr_form',$form->GetTemplateVars(),'html_form.php/show_form_about');
        }        
        
    }
    
    function AdminCats()
    {
        $GLOBALS['skip_form_word_hack'] = true; // sigh
        
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-form.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _('Manage Tag Categories');
        $admin = new CCAdmin();
        $admin->BreadCrumbs(true,array('url'=>ccl('admin','tags'),'text'=>_('Manage Tags')),array('url'=>'','text'=>$title));
        $page =& CCPage::GetPage();
        $page->SetTitle($title);
    
        $form = new CCGridForm();
        $heads = array(_('Delete'), _('Name'), _('ID') );
        $form->SetColumnHeader($heads);
        $cats = CCDatabase::QueryRows('SELECT * FROM cc_tbl_tag_category');
        $count = count($cats);
        
        for( $i = 0; $i < $count; $i++ )
        {
            $id = $cats[$i]['tag_category_id'];
            $pre = "mi[{$id}]";
            
            $a = array(  
                array(
                    'element_name'  => $pre . "[delete]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),
                array(
                    'element_name'  => $pre . '[name]',
                    'value'      => $cats[$i]['tag_category'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED),
                array(
                    'element_name'  => $pre . '[id]',
                    'value'      => $cats[$i]['tag_category_id'],
                    'class'      => 'cc_form_input_short',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED ),
                );

            $form->AddGridRow($id,$a);
        
        }

        $pre = 'new[%i%]';
        
        $a = array(  
            array(
                'element_name'  => $pre . "[delete]",
                'formatter'  => 'checkbox',
                'flags'      => CCFF_NONE ),
            array(
                'element_name'  => $pre . '[name]',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED),
            array(
                'element_name'  => $pre . '[id]',
                'class'      => 'cc_form_input_short',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
            );
        
        $form->AddMetaRow($a, _('Add Category') );

        if( empty($_POST) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $mi = array();
            if( !empty($_POST['mi']) )
                $mi = $_POST['mi'];
            if( !empty($_POST['new']) )
                $mi += $_POST['new'];
            CCUtil::StripSlash($mi);
            $keys = array_keys($mi);
            $count = count($keys);
            $tabs = array();
            for( $i = 0; $i < $count; $i++ )
            {
                $tab =& $mi[$keys[$i]];
                
                if( array_key_exists('delete',$tab ) )
                    continue;

                $tabs[] = $tab;
            }
            $table = new CCTable('cc_tbl_tag_category','tag_category_id');
            $table->DeleteWhere('1');
            $table->InsertBatch(array( 'tag_category', 'tag_category_id'), $tabs );
            
        }

    }

}

?>
