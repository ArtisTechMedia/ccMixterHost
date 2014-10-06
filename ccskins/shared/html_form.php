<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template html_form -->
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/form.css') ?>" title="Default Style" />
<script type="text/javascript" src="<?= $T->URL('js/form.js') ?>"></script>

<?
function _t_html_form_html_form(&$T,&$A) 
{
    $F =& $A['curr_form'];

    if ( !empty($F['form_id']))
        print "<script type=\"text/javascript\">form_id = '{$F['form_id']}';</script>\n";

    $onsubmit = ''; // empty($F['hide_on_submit']) ? '' : 'onsubmit="return the_formMask.dull_screen();" ';
    $enctype  = empty($F['form-data'])      ? '' : 'enctype="' . $F['form-data'] . '"';
    $html =<<<EOF
    <form  action="{$F['form_action']}" 
             method="{$F['form_method']}" 
             class="cc_form" 
             name="{$F['form_id']}" id="{$F['form_id']}" 
              {$onsubmit} {$enctype} >
EOF;
    
    print $html;

    if ( !empty($F['form_macros']))
        foreach( $F['form_macros'] as $macro )
           $T->Call($macro);

    if ( !empty($F['html_form_grid_columns'])) 
    {
        if( empty($F['form_fields_macro']) )
            $T->Call('grid_form_fields');
        else
            $T->Call($F['form_fields_macro']);
    }

    if ( !empty($F['html_form_fields']))
    {
        if( empty($F['form_fields_macro']) )
            $T->Call('form_fields');
        else
            $T->Call($F['form_fields_macro']);
    }

    if ( !empty($F['submit_text'])) 
    {
        $submit_text = $T->String($F['submit_text']);
        ?><input  type="submit" name="form_submit" id="form_submit" class="cc_form_submit" value="<?= $submit_text ?>"></input><?
    }

    if ( !empty($F['html_hidden_fields'])) 
    {
        foreach( $F['html_hidden_fields'] as $H )
            print "\n<input  type=\"hidden\" name=\"{$H['hidden_name']}\" id=\"{$H['hidden_name']}\" value=\"{$H['hidden_value']}\" />";
    }

    print "</form>\n";

    if( !empty($A['post_form_goo']) )
    {
        $T->Call('post_form_goo');
        unset($A['post_form_goo']);
    }

    if( !empty($F['form_submit_trap']) )
    {
        ?>
<script type="text/javascript">
    // ajax trapper
    new <?= $F['form_submit_trap']?>(form_id);
</script>
        <?
    }

} // END: function html_form



//------------------------------------- 
function _t_html_form_submit_forms(&$T,&$A) 
{
    $results = cc_query_fmt('f=php&dataview=links&limit=3&user=' . $A['user_name']);

    print '<div class="cc_submit_forms_outer">';

    if( !empty($results) )
    {
        $manage_url = url_args(ccl('api','query'),'t=manage_files&user=' . $A['user_name']);
        $manage_link = '<a class="manage_link" href="' . $manage_url . '">';
        foreach( $results as $k => $res )
            $results[$k] = $res['upload_name'];
        $names = '<i>' . join('</i>, <i>',$results) . '</i>';
?>
       <div  id="manage_box" style="display:;">
            <div class="cc_submit_forms box">
                <img  src="<?= $T->URL('images/submit-manage.png') ?>" />
                <h2><?= $T->String('str_file_manage') ?></h2>
                <div  class="cc_submit_form_help" style="font-size:1.3em;line-height:1.7em;"><?= $T->String(array('str_files_manage_text',
                                                                        $manage_link, '</a>',
                                                                        $manage_link, '</a>',
                                                                        $manage_link, '</a>',
                                                                        $names )) ?>
                </div>
                <div  class="cc_submit_form_url">
                    <a  href="<?= $manage_url ?>" > <?= $T->String('str_file_manage')  ?></a>
                </div>
            </div>
      </div>
<?
    }
   
    foreach($A['submit_form_infos'] as $SI )
    {
        ?><div  class="cc_submit_forms box"><?

        if ( !empty($SI['logo'])) 
        {
            ?><img  src="<?= $T->URL($SI['logo']) ?>" /><?
        }

        ?><h2 ><?= $T->String($SI['text']) ?></h2>
        <div  class="cc_submit_form_help"><?= $T->String($SI['help']) ?></div>
        <div  class="cc_submit_form_url"><?
            if ( !($SI['quota_reached']) )
                { ?><a  href="<?= $SI['action']?>"><?= $T->String($SI['text']) ?></a><? }
            else
                { ?><span  class="cc_quota_message"><?= $T->String($SI['quota_message']) ?></span><? }

        ?></div>
        </div><?
    } 

    ?></div><?
} // END: function submit_forms


//------------------------------------- 
function _t_html_form_add_type_stuffer(&$T,&$A) 
{
?> 
<script>
Event.observe( 'file_type', 'change', function() 
    { 
        var ft = $('file_type');
        var sel = ft.options[ ft.selectedIndex ];
        var text = sel.value ? sel.text : '';
        $('type_hint_target').innerHTML = '<b>' + text + '</b>';
    }
    );
</script>
<?

}

//------------------------------------- 
function _t_html_form_show_form_about(&$T,&$A) 
{
    ?><div id="cc_form_help_container"><div class="box"><?
    foreach( $A['curr_form']['form_about'] as $FA )   
    {
        ?><div  class="cc_form_about"><?= $T->String($FA) ?></div><?
    }
    
    ?></div></div><?

} // END: function show_form_about

?>
