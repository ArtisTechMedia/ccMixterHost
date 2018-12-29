<?

function _cc_tpl_flip_prefix($prefix)
{
    // typing an extra char '!' for the majority case was
    // stupid. Now '!' supress output (not sure what that's for lol)
    //
    return $prefix == '<?=' ? '<?' : '<?=';
}

function cc_tpl_parse_echo($prefix,$var,$postfix)
{
    if( $prefix )
    {
        if( $prefix == '!' )
            $prefix = '<?=';
    }

    $postfix = ';' . $postfix;

    return '<!-- -->' . cc_tpl_parse_var( $prefix, $var, $postfix );
}

function cc_tpl_parse_var($prefix,$var,$postfix)
{
    if( $prefix )
        $prefix = _cc_tpl_flip_prefix($prefix);
    $parts = explode('/',$var);
    if( $parts[0]{0} == '#' )
    {
        $v = '$' . substr($parts[0],1);
        array_shift($parts);
        if( empty($parts) )
            return $prefix . $v . $postfix;
    }
    else
    {
        $v = '$A';
    }
    return $prefix . $v . '[\'' . join( "']['", $parts ) . '\']' . $postfix;
}

function cc_tpl_parse_t($var)
{
    $parts = explode('/',$var);
    if( $parts[0]{0} == '#' )
    {
        $var = '$' . substr($parts[0],1);
        array_shift($parts);
        if( empty($parts) )
            $var_name = $var;
        else
            $var_name = $var . '[\'' . join( "']['", $parts ) . '\']';
    }
    else
    {
        $var_name = "'$var'";
    }
    
    return "<?= \$T->String($var_name); ?>";
}

function cc_tpl_parse_loop($arr, $item)
{
    $arr_name = cc_tpl_parse_var('',$arr,'');
    return "<? if( !empty($arr_name) ) { \$c_$item = count($arr_name); \$i_$item = 0; ".
           "foreach( $arr_name as \$k_$item => \$$item) { \$i_$item++; ?>";
}

function cc_tpl_parse_last($bang, $item)
{
    $item = preg_replace('/(#|\$)/','',$item);
    $bang = empty($bang) ? '' : '!';

    return "<? if( {$bang}(\$i_{$item} == \$c_{$item}) ) { ?>";
}

function cc_tpl_parse_first($bang, $item)
{
    $item = preg_replace('/(#|\$)/','',$item);
    $bang = empty($bang) ? '' : '!';

    return "<? if( {$bang}(\$i_{$item} == 0) ) { ?>";
}

function cc_tpl_parse_call_macro($prefix, $mac)
{
    $prefix = _cc_tpl_flip_prefix($prefix);
    if( $mac{0} != "'" )
        $mac = cc_tpl_parse_var('',$mac,'');

    return "$prefix \$T->Call($mac); ?>";
}

function cc_tpl_parse_if_null( $is_null, $var )
{
    $varname = cc_tpl_parse_var('',$var,'');
    $bang = $is_null == 'not_' ? '!' : '';
    return "<? if( {$bang}empty($varname) ) { ?>";
}

function cc_tpl_parse_define($left,$right)
{
    $left  = cc_tpl_parse_var('',$left,'');
    if( $right{0} != "'" )
        $right = cc_tpl_parse_var('',$right,'');

    return "<? $left = $right; \n?>";
}

function cc_tpl_parse_file($filename,$bfunc)
{
    //print "parsing: $filename\n<br />";
    return cc_tpl_parse_text(file_get_contents($filename),$bfunc);
}

function cc_tpl_parse_chop($prefix,$varname,$amt)
{
    $prefix = _cc_tpl_flip_prefix($prefix);
    $var = cc_tpl_parse_var('',$varname,'');
    if( $amt == 'chop' )
    {
        $amt = "\$A['chop']";
    }

    return "$prefix cc_strchop($var,$amt,true); ?>"; /* isset(\$A['dochop']) ? \$A['dochop'] : true); ? >*/
}

function cc_tpl_parse_if_attr($varname,$attr)
{
    $var = cc_tpl_parse_var('',$varname,'');
    return "<?= empty($var) ? '' : \"$attr=\\\"\" . $var . '\"'; ?>";
}

function cc_tpl_parse_if_class($bang,$varname,$class)
{
    $bang = empty($bang) ? '' : '!';
    $var = cc_tpl_parse_var('',$varname,'');
    return "<?= {$bang}empty($var) ? '' : \"class=\\\"$class\\\"\"; ?>";
}

function cc_tpl_parse_date($prefix,$varname,$fmt)
{
    $prefix = _cc_tpl_flip_prefix($prefix);
    $var = cc_tpl_parse_var('',$varname,'');
    return "$prefix cc_datefmt($var,'$fmt'); ?>";
}

function cc_tpl_parse_inspect($varname)
{
    $var = cc_tpl_parse_var('',$varname,'');
    return "<? CCDebug::Enable(true); CCDebug::PrintVar($var,false); ?>";
}

function cc_tpl_parse_switch($varname)
{
    $var = cc_tpl_parse_var('',$varname,'');
    return "<? switch($var) {  ?>";
}

function cc_tpl_parse_query_sql($args,$sql_where)
{
    $code =<<<EOF
    require_once('cchost_lib/cc-query.php');
    \$query = new CCQuery();
    \$args = \$query->ProcessAdminArgs("$args");
    \$sqlargs['where'] = "$sql_where";
    \$query->QuerySQL(\$args,\$sqlargs);
EOF;
    return "<? $code ?>";
}

function cc_tpl_parse_url($prefix,$varname)
{
    $prefix = _cc_tpl_flip_prefix($prefix);

    if( $varname{0} == '#' )
        $v = '$' . substr($varname,1);
    elseif( $varname{0} == "'" )
        $v = $varname;
    else
        $v = "'$varname'";

    return "$prefix \$T->URL($v); ?>";
}

function cc_tpl_parse_text($text,$bfunc)
{
    static $ttable;

    $w   = '(?:\s+)?';      // optional whitespace
    $op  = '\(' . $w;       // open paren
    $cp  = $w . '\)';       // close paren
    $c   = $w . ',' . $w;   // comma
    $ac  = '([^,]+)' . $c;  // arg followed by comma
    $a   = '([^\)]+)';      // final arg
    $qa  = "'([^']+)'";     // quoted arg
    $aoq = "'?([^\)']+)'?"; // arg, optional quotes

    if( !isset($ttable) )
    {
       $ttable = array(
        
        '#\[(meta|dataview)\].*\[/\1\]#Us' => '',         // trim out metas
        '/((?:\s|^)+%%[^%]+%%)/' => '',         // trim out comments

        '/^\s+/'  => '',                         // trim out all spaces
        '/\s+$/'  => '',
        '/%\s+%/' => '%%',

        "/%(!?)(?:var)?{$op}{$a}{$cp}%/e"  =>   "cc_tpl_parse_echo('$1 ','$2', ' ?>');",

        '/%!/'           => '<?= ',
        '/%([a-z\(])/'   => '<? $1',

        "/<\? loop{$op}{$ac}{$a}{$cp}%/e"                 =>   "cc_tpl_parse_loop('$1','$2');",

        "/(<\?=?) call(?:_macro)?{$op}{$a}{$cp}%/e"       =>   "cc_tpl_parse_call_macro('$1 ','$2');",
        "/<\? if_(not_)?(?:empty|null){$op}{$a}{$cp}%/e"  =>   "cc_tpl_parse_if_null('$1','$2');"  ,
        "/<\? (?:define|map){$op}{$ac}{$a}{$cp}%/e"       =>   "cc_tpl_parse_define('$1','$2');",
        "/(<\?=?) chop{$op}{$ac}{$a}{$cp}%/e"             =>   "cc_tpl_parse_chop('$1', '$2','$3');",
        "/(<\?=?) date{$op}{$ac}{$qa}{$cp}%/e"            =>   "cc_tpl_parse_date('$1', '$2','$3');",
        "/<\? switch{$op}{$a}{$cp}%/e"                    =>   "cc_tpl_parse_switch('$1');",
        "/<\? inspect{$op}{$a}{$cp}%/e"                   =>   "cc_tpl_parse_inspect('$1');",
        "/<\? if_(not_)?first{$op}{$a}{$cp}%/e"           =>   "cc_tpl_parse_first('$1','$2');",  
        "/<\? if_(not_)last{$op}{$a}{$cp}%/e"             =>   "cc_tpl_parse_last('$1','$2');",  
        "/(<\?=?) url{$op}{$a}{$cp}%/e"                   =>   "cc_tpl_parse_url('$1','$2');",
        "/<\?=? if_attr{$op}{$ac}{$a}{$cp}%/e"            =>   "cc_tpl_parse_if_attr('$1','$2');",
        "/<\?=? if_(not_)?class{$op}{$ac}{$a}{$cp}%/e"    =>   "cc_tpl_parse_if_class('$1','$2','$3');",
        "/<\? text{$op}{$a}{$cp}%/e"                      =>   "cc_tpl_parse_t('$1');", 
        "/<\? query_sql{$op}{$ac}{$a}{$cp}%/e"            =>   "cc_tpl_parse_query_sql('$1','$2');", 

        "/<\? else%/"                           =>   "<? } else { ?>",
        "/<\? end_(?:macro|if|switch)%/"   =>   "<?\n } ?>",
        "/<\? end_case%/"                       =>   "<?\n } break;\n; ?>",
        "/<\? end_loop%/"                       =>   "<?\n } } ?>",

        "/<\? if\(([^\)]+)\)%/"                           =>   "<? if( !empty(\$A['$1']) ) { ?>",
        "/<\? if_not\(([^\)]+)\)%/"                       =>   "<? if( empty(\$A['$1']) ) { ?>",
        "/<\? prepend{$op}{$ac}{$aoq}{$cp}%/"              =>   "<? array_unshift(\$A['$1'],'$2'); ?>",
        "/<\? append{$op}{$ac}{$aoq}{$cp}%/"              =>   "<? \$A['$1'][] = '$2'; ?>",
        "/<\? import_skin{$op}{$aoq}{$cp}%/"              =>   "<? \$T->ImportSkin('$1'); ?>",
        "/<\? case{$op}{$a}{$cp}%/"                       =>   "<? case $1: { ?>",
        "/<\? query{$op}{$aoq}{$cp}%/"                    =>   "<?= cc_query_fmt('f=html&noexit=1&nomime=1&' . '$1'); ?>",
        "/<\? customize%/"                                =>   "<? \$T->AddCustomizations(); ?>",
        "/<\? return%/"                                   =>   "<? return 'ok'; ?>",
        "/<\? settings{$op}{$ac}{$a}{$cp}%/"              =>   "<? \$A['$2'] = CC_get_config('$1'); ?>",
        "/<\? un(?:define|map){$op}{$a}{$cp}%/"           =>   "<? unset(\$A['$1']); ?>",
        );
    }

    $ttable["/<\? macro\(([^\)]+)\)%/"] = "<? \nfunction $bfunc$1(&\$T,&\$A) { ?>"; 

    $text = preg_replace( array_keys($ttable), array_values($ttable), $text );

    return preg_replace( array( '/\?>(\s+)?<\?=?/', '/<!-- -->/'), array( '', ''),  $text ) . '<? return "ok"; ?>';  
}

/*
*/

?>
