<?

/*
$Id: cc-tal-parser.php 8961 2008-02-11 22:17:33Z fourstones $

*/
error_reporting(E_ALL);

require_once('cchost_lib/htmlparser/htmlparser.inc');

if( !defined( 'TC_PRETTY' ) )
    define( "TC_PRETTY", 1 );

if( TC_PRETTY )
    define( "TCR_LF", "\n" ); 
else
    define( "TCR_LF", "" ); 

class CCTALCompiler
{
    
    function CCTALCompiler($use_compat=true)
    {
        $this->inPHP = false;
        $this->outputSuspended = 0;
        $this->var_count = 100;
        $this->use_compat = $use_compat;
        $this->if_count = 0;
        $this->func_block = 0;
        $this->loop_stack = array();
    }

    function echo_brackets($bracket)
    {
        if( $bracket && !$this->inPHP )
        {
            $pre = '<';
            $pre .= '?= ';
            $post = '?';
            $post .= '>';
        }
        else
        {
            $pre = $post = '';
        }

        return array($pre,$post);
    }

    function get_arr_name($t,$bracket=false)
    {
        list( $pre, $post ) = $this->echo_brackets($bracket);
        if( $t == "''" || $t == 'null' )
        {
            $text = $t;
        }
        else
        {
            $parts = cc_split('/',$t);
            $text = '$A';
            for( $i = 0; $i < count($parts); $i++ )
            {
                $P = trim($parts[$i]);
                $text .= "['" . $P . "']";
            }
        }
        return $pre . $text . $post;
    }

    function parse_tal_expr($v, $bracket=true, $condition=false)
    {
        $parts = cc_split(' \| ',$v);
        if( count($parts) > 1 )
        {
            $text = '';
            $this->php_bracket(true,$text);
            $this->do_OR("echo ",$text,$parts);
            return $text;
        }

        list( $pre, $post ) = $this->echo_brackets($bracket);
        if( $v == 'nothing' )
            return $pre . "null" . $post;

        if( preg_match( '/^(\'\'|string|php|not):(.*)$/', $v, $m ) )
        {
            switch( $m[1] )
            {
                case 'nothing':
                    return "null";
                case 'string':
                    $str = addslashes($m[2]);
                    return "'$str'";
                case 'not': 
                    return $pre . '!(' . $this->parse_value('${' . trim($m[2]). '}',false ) . ') ' . $post;
                case 'php':
                    return $pre . $this->parse_value(trim($m[2])) . $post;
            }
            
            return null;
        }

        $v = trim($v);
        if( $v{0} != '$' )
            $v = '${' . $v . '}';
        if( preg_match( '/^\$\{[^}]+\}$/U', $v ) )
        {
            if( $condition )
            {
                $pre .= '!empty(';
                $post = ')' . $post;
            }
        }
        return $pre . $this->parse_value( $v, false) . $post;
    }

    function parse_value($v, $bracket=false) 
    {
        list( $pre, $post ) = $this->echo_brackets($bracket);
        $curr_loop_offset = isset($this->loop_stack[0][0]) ? $this->loop_stack[0][0] : 0;
        $q = empty($this->inScript) ? '\'?' : '';
        $t = preg_replace( array( 
                                '#\sLT\s#',
                                '#CC_lang#',
                                '#\${repeat/[^/]+/index}#' ,
                                '#\${repeat/[^/]+/key}#' ,
                                '#\${repeat/[^/]+/end}#' ,
                                '#'.$q.'\${([^}\s]+)}'.$q.'#e',
                                '#\$\$#'
                           ),
                           array(
                                ' < ',
                                '_',
                                $pre . '\$ci' . $curr_loop_offset . $post,
                                $pre . '\$ck' . $curr_loop_offset . '[' . '\$ci' . $curr_loop_offset . ']'  . $post,
                                $pre . '\$ci' . $curr_loop_offset . ' == (\$cc' . $curr_loop_offset . '-1)'  . $post,
                                "\$this->get_arr_name('\\1', '$bracket')" ,
                               '$'
                            ),
                          $v
                );

        return $t;
    }

    function compile_phptal_file($infile,$outfile)
    {
        $text = file_get_contents($infile);
        $basename = preg_replace( '/[^a-zA-Z0-9]+/', '_', basename($infile,'.xml') );
        $text = $this->compile_phptal_text($text,$basename);
        $f = fopen($outfile,'w');
        fwrite($f,$text);
        fclose($f);
    }

    function setup_loop($value,&$OUT)
    {
        $i = ++$this->var_count;
        array_unshift($this->loop_stack, array( $i ));

        $arr_name = '$carr' . $i;
        $args = cc_split( 'php:', $value );
        if( count($args) == 1 )
        {
            $args = cc_split( ' ',  $value);
            if( empty($args[1]) ) { print("Lousy value: $value"); exit; }
            $arr_expr = $this->get_arr_name($args[1]);
        }
        else
        {
            $arr_expr = $this->parse_value($args[1],false);
        }
        $var_name = '$A[\'' . trim($args[0]) . '\']';
        $this->php_bracket(true, $OUT);
        $this->o($OUT , TCR_LF .
                $arr_name . ' = ' . $arr_expr . ';' . TCR_LF .
                '$cc' . $i . '= count( ' .     $arr_name . ');' . TCR_LF .
                '$ck' . $i . '= array_keys( '. $arr_name . ');' . TCR_LF .
                'for( $ci' . $i . '= 0; $ci' . $i . '< $cc' . $i . '; ++$ci' . $i . ')' . TCR_LF .
                '{ '  . TCR_LF .
                '   ' . $var_name . ' = ' . $arr_name . '[ $ck' . $i . '[ $ci' . $i . ' ] ];' . TCR_LF .
                '   ' );

    }

    function setup_condition($value,&$OUT)
    {
        $this->php_bracket(true, $OUT);
        if( preg_match( '#^repeat/#', $value ) )
        {
            $this->o($OUT,TCR_LF . "if ( " . $this->parse_tal_expr($value) . " ){" . TCR_LF );
        }
        elseif( preg_match( '/^php:(.*)$/', $value, $m ) )
        {
            $this->o($OUT,TCR_LF . "if ( " . $this->parse_tal_expr($m[1]) . " ){" . TCR_LF );
        }
        else if( preg_match( '/^not: exists:(.*)$/', $value, $m ) )
        {
            $this->o($OUT,TCR_LF . "if ( !isset(" . $this->parse_tal_expr($m[1],false) . ") ) {" . TCR_LF );
        }
        else if( preg_match( '/^exists:(.*)$/', $value, $m ) )
        {
            $this->o($OUT,TCR_LF . "if ( isset(" . $this->parse_tal_expr($m[1],false) . ") ) {" . TCR_LF );
        }
        else
        {
            $this->o($OUT,TCR_LF . "if ( " . $this->parse_tal_expr($value,false,true) . ") {" . TCR_LF );
        }
        ++$this->if_count;
    }

    function parse_define($value,&$OUT)
    {
        $defines = cc_split(';',$value);
        $this->php_bracket(true, $OUT);
        foreach( $defines as $define )
        {
            $define = trim($define);
            if( empty($define) )
                continue;

            preg_match('/^([^\s+]+)\s+(.*)$/',$define,$m);
            $name = $m[1];
            $args = cc_split(' \| ',$m[2]);
            if( count($args) == 1 )
            {
                $OUT .= "\$A['$name'] = ";
                if( $args[0] == "null" )
                    $OUT .= "'';" . TCR_LF;
                else if( intval($args[0]) || $args[0] === '0')
                    $OUT .= $args[0] . ";" . TCR_LF;
                else
                    $OUT .= $this->parse_tal_expr($args[0],false) . ";" . TCR_LF;
            }
            else
            {
                $this->do_OR("\$A['$name'] =",$OUT,$args);
            }
        }
    }

    function do_OR($target,&$OUT,$args)
    {
        $vals = array();
        $cbraces = 0;

        foreach( $args as $A )
        {
            if( intval($A) || $A === '0')
            {
                $OUT .= " $target $A;";
            }
            elseif( $A == 'nothing' )
            {
                $OUT .= " $target null; ";
            }
            elseif( preg_match( '/^php:(.*);?$/', $A, $m ) )
            {
                $OUT .= " $target {$m[1]}; ";
            }
            elseif( preg_match( '/^string:(.*);?$/', $A, $m ) )
            {
                $value = addslashes(trim($m[1]));
                $OUT .= " $target '$value'; ";
            }
            else
            {
                $val = $this->parse_tal_expr($A,false);
                $this->o($OUT, TCR_LF . "if( !empty($val) ) { $target $val; } else { ");
                $cbraces++;
            }
        }
        do{ $OUT .= '} '; } while( --$cbraces );
    }

    function php_bracket($open, &$OUT)
    {
        if( $open )
        {
            if( !$this->inPHP )
            {
                $OUT .= '<';
                $OUT .= '?';
                if( TC_PRETTY )
                    $OUT .= "\n";
                $this->inPHP = true;
            }        
        }
        else
        {
            if( $this->inPHP )
            {
                if( TC_PRETTY )
                    $OUT .= "\n";
                $OUT .= '?';
                $OUT .= '>';
                $this->inPHP = false;
            }
        }
    }

    function parse_tal_attr($value,&$OUT)
    {
        $args = preg_split('/\s+/',$value);
        $name = $args[0];
        $val1 = $this->parse_tal_expr($args[1],false);
        if( empty($args[2]) )
        {
            $str = "$name=\"<" . "?= $val1 ?" . ">\"";
        }
        else
        {
            if( $args[1] == 'php:' )
            {
                $value = preg_replace( '/^(.*php:)/','',$value);
                $str = "$name=\"<" . "?= $value ?" . ">\"";
            }
            else
            {
                if( $args[2] != '|' || empty($args[3]) )
                {
                    print_r($args);
                    die("don't know tal::attribute expression: '$value'");
                }
                
                $val2 = $this->parse_tal_expr($args[3],false);
                $str = "$name=\"<" . "?= empty($val1) ? $val2 : $val1; ?" . ">\"";
            }
        }
        $OUT .= $str;
        //print("Attr: $str\n");
    }

    function is_singleton($name)
    {
        return in_array( $name, array( 'br', 'hr', 'img' ) );
    }

    function o(&$OUT,$text)
    {
        if( preg_match("/\n+/",$text,$m) )
        {
            $margin = str_repeat('  ',count($this->loop_stack) + $this->if_count + $this->func_block );
            $text = str_replace("\n","\n$margin",$text);
        }

        $OUT .= $text;
    }

    function compile_phptal_text($text,$basename)
    {
        $OUT = '';
        $this->inPHP = false;
        $this->php_bracket(true,$OUT);
        $tname = '_t_' . $basename . '_init';
        $OUT .= "if( !defined('IN_CC_HOST') )\n    die('Welcome to ccHost');\n";

        if( $this->use_compat )
            $OUT .= "\n \$T->CompatRequired(); \n\n";

        // catch
        // <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        // and
        // <?xml here

        if( preg_match( '@^<\?[^\?]+\?>@U', $text, $m ) ||
            preg_match( '@^<\!DOCTYPE[^>]+>@U', $text, $m ) )
        {
            $this->php_bracket(true,$OUT);
            $this->o($OUT,"print('{$m[0]}' . \"\\n\")\n");
            $text = substr($text, strlen($m[0]));
        }

        $parser = new HtmlParser($text);
        $node_stack = array();
        $this->loop_stack = array();
        while ($parser->parse()) {

            switch( $parser->iNodeType )
            {
                case NODE_TYPE_ELEMENT:
                    $attrs =& $parser->iNodeAttributes;
                    array_unshift( $node_stack, array( $parser->iNodeName, empty($attrs) ? null : $attrs ) );
                    //print( "Push: {$parser->iNodeName}\n" );
                    break;
                case NODE_TYPE_ENDELEMENT:
                    $stack_top = array_shift( $node_stack );
                    //print("Pop: {$parser->iNodeName} == {$stack_top[0]}\n");
                    break;
            }

            if( $this->outputSuspended )
            {
                switch( $parser->iNodeType )
                {
                    case NODE_TYPE_ELEMENT:
                        $this->outputSuspended++;
                        break;
                    case NODE_TYPE_ENDELEMENT:
                        $this->outputSuspended--;
                        break;
                }
                continue;
            }


            switch( $parser->iNodeType )
            {
                case NODE_TYPE_ELEMENT:
                    {
                        $this->check_space($OUT);
                        $parts = cc_split(':',$parser->iNodeName);
                        if( empty($parts[1]) || in_array($parts[0],array('rdf','dc') ) )
                         {
                            switch( $parts[0] )
                            {
                                case 'script':
                                case 'style':
                                    $this->inScript = true;
                            }

                            $tag = '<' . $parser->iNodeName . ' ';
                            $content = '';

                            if( !empty($attrs) )
                            {
                                // condition must come first
                                if( array_key_exists('tal:condition',$attrs) )
                                {
                                    $value = $attrs['tal:condition'];
                                    $this->setup_condition($value,$OUT);
                                    $node_stack[0]['condition'] = true;
                                }

                                if( array_key_exists('tal:define',$attrs) )
                                {
                                    $value = $attrs['tal:define'];
                                    $this->parse_define($value,$OUT);
                                }

                                foreach( $attrs as $attr => $value )
                                {
                                    switch( $attr )
                                    {
                                        case 'tal:define':
                                        case 'tal:on-error':
                                        case 'tal:condition':
                                            break;
                                        case 'tal:repeat':
                                            $this->setup_loop($value,$OUT);
                                            $node_stack[0]['looping'] = true;
                                            break;
                                        case 'tal:content':
                                            $value = preg_replace('/^structure:?\s+/','',$value);
                                            $content = $value; 
                                            break;
                                        case 'tal:attributes':
                                            $oldPhp = $this->inPHP;
                                            $this->inPHP = false;
                                            $tag .= ' ';
                                            $this->parse_tal_attr($value,$tag);
                                            $this->inPHP = $oldPhp;
                                            break;
                                        default:
                                            if( strstr($attr,'tal:') !== false )
                                                die("unhandled tal attribute: $attr");
                                            $oldPhp = $this->inPHP;
                                            $this->inPHP = false;
                                            $value = $this->parse_value($value,true,true);
                                            $this->inPHP = $oldPhp;
                                            $tag .= ' ' . $attr . '="' . $value . '"';
                                            break;
                                    }
                                }
                                $parser->iNodeAttributes = array();
                            }

                            if( !$this->is_singleton($parts[0]) )
                                $tag .= '>';

                            $this->php_bracket(false, $OUT);
                            $this->o($OUT,$tag);
                            if( !empty($content) )
                            {
                                $parsed_content = $this->parse_tal_expr($content);
                                $this->o($OUT,$parsed_content);
                            }
                        }
                        else
                        {
                            switch( $parts[0] )
                            {
                                case 'phptal':
                                {
                                    if( isset($attrs['include']) )
                                    {
                                        $value = substr($attrs['include'],strlen('string:'));
                                        $this->php_bracket(true, $OUT);
                                        $this->o($OUT, "\$T->Call('$value');" . TCR_LF);
                                        unset($attrs['include']);
                                    }
                                    if( count($attrs) )
                                    {
                                        print_r($attrs);
                                        die('unhandled phptal:block attributes');
                                    }
                                    break;
                                }

                                case 'tal':
                                {
                                    if( isset($attrs['replace']) )
                                    {
                                        // currently we only support replace=''
                                        $this->outputSuspended++;
                                        unset($attrs['replace']);
                                    }
                                    if( isset($attrs['condition']) )
                                    {
                                        $this->setup_condition($attrs['condition'],$OUT);
                                        $node_stack[0]['condition'] = true;
                                        unset($attrs['condition']);
                                    }

                                    if( isset($attrs['define']) )
                                    {
                                        $this->parse_define($attrs['define'],$OUT);
                                        unset($attrs['define']);
                                    }
                                    if( isset($attrs['repeat']) )
                                    {
                                        $this->setup_loop($attrs['repeat'],$OUT);
                                        $node_stack[0]['looping'] = true;
                                        unset($attrs['repeat']);
                                    }

                                    if( isset($attrs['content']) )
                                    {
                                        $value = preg_replace('/^structure:?\s+/','',$attrs['content']);
                                        $this->o($OUT,$this->parse_tal_expr($value,true));
                                        unset($attrs['content']);
                                    }
                                    if( isset($attrs['on-error']) )
                                    {
                                        unset($attrs['on-error']);
                                    }
                                    if( count($attrs) )
                                    {
                                        print_r($attrs);
                                        die('unhandled tal:block attributes');
                                    }
                                    break;
                                }

                                case 'metal':
                                {
                                    if( isset($attrs['define-macro']) )
                                    {
                                        $this->php_bracket(true, $OUT);
                                        if( TC_PRETTY )
                                            $OUT .= "\n\n//------------------------------------- \n";
                                        else
                                            $OUT .= "\n";
                                        $tname = '_t_' . $basename . '_' . $attrs['define-macro'];
                                        $OUT .= "function $tname(&\$T,&\$A) {\n  ";
                                        $node_stack[0]['funcblock'] = true;
                                        ++$this->func_block;
                                    }
                                    elseif( isset($attrs['use-macro']) )
                                    {
                                        $value = $attrs['use-macro'];
                                        if( $value{0} == '$' )
                                            $value = $this->parse_tal_expr($value,false);
                                        else
                                            $value = "'$value'";
                                        $this->php_bracket(true, $OUT);
                                        $this->o($OUT,"\$T->Call($value);\n");
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    break;

                case NODE_TYPE_COMMENT:
                    if( empty($this->inScript) )
                        break;
                    // fall thru

                case NODE_TYPE_TEXT:
                    {
                        $ttext = trim($parser->iNodeValue);
                        if( empty($ttext) )
                        {
                            $this->hasSpace = true;
                        }
                        else
                        {
                            $this->check_space($OUT);
                            $this->php_bracket(false, $OUT);
                            $this->o($OUT,$this->parse_value($parser->iNodeValue,true));
                        }
                    }
                    break;

                case NODE_TYPE_ENDELEMENT:
                    {
                        $this->check_space($OUT);
                        $name = $stack_top[0];
                        if( ( $name != $parser->iNodeName) && $parser->iNodeName != '{singleton}' )
                            die("Misatch tags expecting {$name} got {$parser->iNodeName}  (Stack level:" . count($node_stack) . ")\n");
                        $parts = cc_split(':',$name);
                        if( empty($parts[1]) )
                        {
                            if( $this->is_singleton($name)  )
                            {
                                $OUT .= ' />';
                            }
                            else
                            {
                                $this->php_bracket(false, $OUT);
                                $OUT .= '</' . $name. '>';
                            }
                        }

                        if( ($name == 'script') || ($name == 'style') )
                            $this->inScript = false;

                        if( !empty($stack_top['looping']) )
                        {
                            $this->close_brace($OUT, 'for loop');
                            array_shift($this->loop_stack);
                        }

                        if( !empty($stack_top['condition']) )
                        {
                            $this->close_brace($OUT, 'if');
                            --$this->if_count;
                        }

                        if( !empty($stack_top['funcblock']) )
                        {
                            $this->close_brace($OUT, 'function ' . $stack_top[1]['define-macro']);
                            --$this->func_block;
                        }
                    }
                    break;
            }
        }
        
        $this->php_bracket(false,$OUT);

        return trim($OUT);
    }

    function check_space(&$OUT)
    {
        if( empty($this->hasSpace) )
            return;
        if( empty($this->inPHP) )
        {
            $this->php_bracket(false,$OUT);
            $OUT .= "\n";
        }
        $this->hasSpace = false;
    }

    function close_brace(&$OUT, $type)
    {
        $this->php_bracket(true, $OUT);
        if( TC_PRETTY )
            $this->o($OUT,"} // END: $type\n");
        else
            $OUT .= "}" . TCR_LF;
    }

}


?>