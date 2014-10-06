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
* $Id: cc-access.php 12647 2009-05-24 22:34:24Z fourstones $
*
*/

/**
* Base classes and general user admin interface
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

/**
 *
 */
class CCAdminSuperForm extends CCEditConfigForm
{
    /**
     * Constructor
     *
     * @param string $config_type The name of the settings group (i.e. 'menu')
     * @param string $scope CC_GLOBAL_SCOPE or a specific vroot (blank means current)
     */
    function CCAdminSuperForm()
    {
        $this->CCEditConfigForm('config');

        $fields = array( 
                    'supers' =>
                       array(  'label'      => _('Super Admins'),
                               'form_tip'   => _('Comma separated list of super site admins'),
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED,
                            ),
            );

        $this->AddFormFields($fields);
        $this->SetModule( ccs(__FILE__) );
    }

}

/**
* Displays global configuration options.
*
*/
class CCAccessEditForm extends CCForm
{
    /**
    * Constructor
    * 
    */
    function CCAccessEditForm()
    {
        $this->CCForm();
    
        $map = cc_get_url_map();
        
        $cg = count($map);
        $gkeys = array_keys($map);
        for( $i = 0; $i < $cg; $i++ )
        {
            $group =& $map[ $gkeys[$i] ];
            $cu = count($group);
            $cukeys = array_keys($group);
            for( $n = 0; $n < $cu; $n++ )
            {
                $ua =& $group[$cukeys[$n]];
                $ua->opts = $this->_gen_select($ua->pmu,$ua->url);
                //CCDebug::PrintVar($ua);
            }
        }

        $fields = array(
            'map' => array( 'label'      => '', // _('Access Map'),
                           'formatter'  => 'metalmacro',
                           'flags'      => CCFF_POPULATE,
                           'macro'      => 'access',
                           'access_map' => $map,
                            )
            );

        $this->AddFormFields($fields);

        $help = _('Use this form to decide who has acces to which commands. (This does not affect which menu items are shown, use the Manage Site/Menu form for controlling that.)');
        
        $this->SetFormHelp($help);
    }

    function _gen_select($pm,$url)
    {
        static $roles;
        if( !isset($roles) )
            $roles = cc_get_roles();

        $html = "\n<select id=\"acc[$url]\" name=\"acc[$url]\">";
        foreach( $roles as $V => $T )
        {
            $sel = ( $V == $pm ) ? 'selected="selected"' : '';
            
            $html .= "<option $sel value=\"$V\">$T</option>";
        }
        $html .= '</select>';
        return $html;
    }
}


/**
* Basic admin access API and system event watcher.
* 
*/
class CCAccess
{
    function Super()
    {
        $title = _('Edit Super Admins');
        require_once('cchost_lib/cc-admin.php');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);
        $form = new CCAdminSuperForm();
        CCPage::AddForm($form->GenerateForm());
    }

    function CommandTest()
    {
        /*
    [admin/super] => CCAction Object
        (
            [cb] => Array
                (
                    [0] => CCAccess
                    [1] => Super
                )

            [pm] => 4096
            [md] => cchost_lib/cc-access.php
            [dp] => 
            [ds] => Edit list of super admins
            [dg] => _mad
            [url] => admin/super
        )
        */
        $map = cc_get_url_map(0,0);
        print '<html><body>';
        foreach( $map as $cmd => $obj )
        {
            if( ($obj->pm & (CC_ADMIN_ONLY|CC_SUPER_ONLY|CC_MUST_BE_LOGGED_IN)) != 0 )
                continue;
            $url = ccl($cmd);
            $link = "<a href=\"{$url}\">{$cmd}</a><br />";
            print $link;
        }
        print '</body></html>';
        exit;
    }

    function CommandDump($undocced=1)
    {
        if( $undocced == 'test' )
        {
            return $this->CommandTest();
        }
        
        $title = _('ccHost Commands');
        require_once('cchost_lib/cc-admin.php');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);

        $user_only   = false;
        $public_only = false;
        if( $undocced == 'user' )
        {
            $undocced = 1;
            $user_only = true;
        }
        elseif( $undocced == 'public' )
        {
            $undocced = 1;
            $public_only = true; 
        }

        $map = cc_get_url_map(1,$undocced);
        $html =<<<EOF
<style>
#cmd_table td {
    vertical-align: top;
    padding-right: 5px;
    border: 1px solid #CCC;
}
.group_name {
      font-weight: bold;
    }
.ckey {
    font-wieght: bold;
    }
.arg {
    color: green;
    }
.command_key {
    width: 150px;
    text-align: right;
    }
.func {
    color: #666;
    }
</style>

<table id="cmd_table" cellspacing="0" cellpadding="0">
EOF;
        foreach( $map as $group_name => $group )
        {
            $html .= "\n" . '<tr><td><span class="group_name">' . $group_name . '</span></td>';
            
            $html .= '<td><table>';

            foreach( $group as $command_name => $C )
            {
                if( $user_only || $public_only)
                {
                    if( ($C->pmu & (CC_ADMIN_ONLY|CC_SUPER_ONLY)) != 0 )
                        continue;

                    if( $public_only )
                    {
                        if( ($C->pmu & CC_MUST_BE_LOGGED_IN) != 0 )
                            continue;
                    }
                }
                
                $html .= "\n" . '<tr><td class="command_key">' . $command_name . '</td>';
                
                $html .= "\n" . '   <td><table>';

                $html .= "\n" . '<tr><td class="ckey">URL</td>';
                
                $html .= '<td>';
                if( is_array($C->dp) )
                {
                    foreach( $C->dp as $url )
                        $html .= $url . '<br />';
                }
                else
                {
                    $html .= $C->url;
                    if( !empty($C->dp) )
                    {
                        $html.= '/<span class="arg">' . $C->dp . '</span>';
                    }
                }
                $html .= '</td></tr>';
                
                $html .= "\n" . '<tr><td class="ckey">Source</td>';
                
                $html .= '<td>' . $C->md . ' <span class="func">(';
                if( is_array($C->cb) )
                {
                    $html .= join('::',$C->cb);
                }
                else
                {
                    $html .= $C->cb;
                }

                $html .= ')</span></td></tr>';
                
                $html .=  "\n" . '<tr><td class="ckey">Desc</td><td>' . $C->ds . '</td></tr>';

                $html .= "\n" . '<tr><td class="ckey">Access</td><td>' . $C->pmd . '</td></tr>';
                $html .= "\n" . '</table></td></tr>';

            }
                           

            $html .= '</table></td></tr>';
        }

    $html .=<<<EOF
</table>
EOF;

        CCPage::AddContent($html);
    }

    function Access()
    {
        $title = _('Restrict Access Rights');
        require_once('cchost_lib/cc-admin.php');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);
        $form = new CCAccessEditForm();
        if( empty($_POST['accessedit']) )
        {
            CCPage::AddForm($form->GenerateForm());
        }
        else
        {
            $acc = $_POST['acc'];
            $map =& CCEvents::GetUrlMap();
            $accmap = array();
            foreach( $acc as $url => $pm )
            {
                if( $map[$url]->pm != $pm )
                    $accmap[$url] = $pm;
            }

            $configs =& CCConfigs::GetTable();
            $configs->SaveConfig( 'accmap', $accmap, CC_GLOBAL_SCOPE, false );
            CCPage::Prompt(_('Access map changes saved'));
        }
    }

    function OnAdminMenu( &$items, $scope )
    {
        if( !CCUser::IsSuper() || $scope == CC_LOCAL_SCOPE )
            return;

        $items += array( 
            'superusers'   => array( 'menu_text'  => _('Super Users'),
                             'menu_group' => 'configure',
                             'help'      => _('Edit list of super users'),
                             'access' => CC_SUPER_ONLY,
                             'weight' => 20,
                             'action' =>  ccl('admin','super')
                             ),
            'access'   => array( 'menu_text'  => _('Restrict Access'),
                             'menu_group' => 'configure',
                             'help' => _('Restrict access to commands'),
                             'access' => CC_SUPER_ONLY,
                             'weight' => 21,
                             'action' =>  ccl('admin','access')
                             ),
            );
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/super',     array('CCAccess', 'Super'),       
            CC_SUPER_ONLY, ccs(__FILE__), '', _('Edit list of super admins'), CC_AG_ADMIN_MISC );
        CCEvents::MapUrl( 'admin/access',     array('CCAccess', 'Access'), 
            CC_SUPER_ONLY, ccs(__FILE__), '', _('Edit URL access levels'), CC_AG_ADMIN_MISC );
        CCEvents::MapUrl( 'commands',     array('CCAccess', 'CommandDump'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{0|user|public}', _('This screen...'), CC_AG_ADMIN_MISC );
    }

}

function cc_get_url_map($doconly=1,$doconly2=1)
{
    $roles = cc_get_roles();
    $group_names = cc_get_access_groups();

    $configs =& CCConfigs::GetTable();
    $accmap = $configs->GetConfig('accmap');

    $map = CCEvents::GetUrlMap();
    if( $doconly )
    {
        $groups = array();
        foreach( $map as $K => $V )
        {
            if( $doconly2 ) 
            {
                if( empty($V->dg) ) // no doc group?
                    continue;       // never mind
            }
            else
            {
                if( empty($V->dg) ) // no doc group?
                    $V->dg = '*UN-DOCCED';
            }
            $pm = empty($accmap[$K]) ? $V->pm : $accmap[$K];
            $V->pmu = $pm;
            $V->url = $K;
            $V->pmd = $roles[ $pm ];
            if( array_key_exists($V->dg,$group_names) )
                $gn = $group_names[$V->dg];
            else
                $gn = $V->dg;
            $groups[ $gn ][$K] = $V;
        }
        ksort($groups);
        foreach( $groups as $G => $V )
        {
            ksort($groups[$G]);
        }
        return $groups;
    }
    return $map;
}



?>
