<?

global $CC_GLOBALS;

if( empty($CC_GLOBALS['install_done']) )
{
    ?><h3>Not yet done installing...</h3><?

    $login_url = ccl('login');
    if( empty($A['is_logged_in']) )
    {
        ?><p>
            You're not quite done installing, but you need to log in as admin to finish,
             please <a class="small_button" href="<?=$login_url?>">click here to login</a>
           </p>
        <?
    }
    else
    {
        if( empty($A['is_admin']) )
        {
            ?><p>
                You're logged in, but you don't have admin access, which you will need
                in order to finish the installation. You'll need to log out and then
                log back in as someone with administrator access.
            </p>
            <p>
                <a class="small_button">click here to log out</a>
            </p>
            <?
        }
        else
        {
            if( empty($_GET['update']) )
            {
                $update_url = url_args( ccl(), 'update=1' );
                
                ?><p>
                    You're <i>almost there...</i>
                </p>
                <p>
                    <a class="small_button" href="<?=$update_url?>">click to finish installation</a>
                </p>
                <?
            }
        }
    }
}
else
{
    $configs =& CCConfigs::GetTable();
    $settings = $configs->GetConfig('settings');
    if( $settings['homepage'] == 'docs/new_install' )
    {
        $settings['homepage'] = 'view/media/home';
        $configs->SaveConfig('settings',$settings,'',false);
    }
    
    ?><h2><?= CC_APP_NAME ?> <?= CC_HOST_VERSION ?> installation is complete</h2>
        <p>
            <a class="small_button" href="<?=ccl()?>">Enjoy your new site! <?=ccl()?></a>
        </p>
    <?
    
}

return "ok";

?>
