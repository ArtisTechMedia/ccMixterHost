<table class="cc_macro_patterns">
<?
    foreach( $A['curr_form']['macro_patterns'] as $pattern_key => $pattern )
    {
        print "<tr><td class=\"cc_macro_pattern_label\">{$pattern_key}</td><td class=\"cc_macro_pattern\">{$pattern}</td></tr>\n";
    }
?>
</table>
