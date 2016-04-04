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
* $Id: cc-form.php 13098 2009-07-25 05:50:43Z fourstones $
*
*/

/**
* Base User interface forms
*
* @package cchost
* @subpackage ui
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-page.php'); // figure might as well


/**
 * Base class for all HTML forms in the system.
 * 
 * Extend this class for basic forms that do not have any uploading
 * needs. It contains several field type handlers built in such as
 * text inputs, textareas, radio groups, checkboxes, etc.
 *
 * See {@tutorial cchost.pkg#newform CCForm tutorials}
 */
class CCForm 
{
    /**#@+
    * @access private
    * @var string 
    */
    var $_template_vars;
    var $_form_fields;
    var $_template_macro;
    var $_form_help_messages;
    var $_enable_submit_message;
    /**#@-*/

    /**
     * Constructor
     *
     * This method sets up several defaults (submit button text, form method) but also
     * creates several hidden fields on the form:
     * 
     * <b>'http_referer'</b> remembers the URL this form was originally called 
     * from. 
     * <b>-classname-</b> remembers the name of the class that created this form 
     * minus the 'cc' prefix and 'form' postfix. e.g. if the name of this class is 
     * CCMyEditingForm the name of this field is 'myediting'. It's value is always 
     * the string 'classname'. You can use this field to confirm at POST time that 
     * you are processing the right form:
     * 
     * <code>
     * if( !empty($_POST['myediting']) )
     * {
     *      // .... User hit 'submit' button
     * }
     * </code>
     *
     */
    function CCForm()
    {
        $this->_form_fields = array();
    
        $this->_form_help_messages = array();

        $this->_template_vars = array(
                                'form_method' => 'post',
                                'submit_text' => _("Submit"),
                                );

        $this->_template_macro = 'html_form';

        if( !empty($_POST['http_referer']) )
            $refer = urldecode($_POST['http_referer']);
        elseif( !empty($_SERVER['HTTP_REFERER']) )
            $refer = $_SERVER['HTTP_REFERER'];

        if( !empty($refer) )
        {
            $this->SetHiddenField( 'http_referer', 
                                   htmlspecialchars(urlencode($refer)),
                                   CCFF_HIDDEN | CCFF_NOUPDATE );

        }

        // it's conceivable that REQUEST_URI might work here...
        $this->SetHandler( $_SERVER['REQUEST_URI'] );

        $p1 = substr(get_class($this),2);
        if( strlen($p1) > 4 )
            $this->SetHiddenField( strtolower(substr($p1,0,strlen($p1)-4)), 'classname', CCFF_HIDDEN | CCFF_NOUPDATE );

        CCEvents::Invoke( CC_EVENT_FORM_INIT, array( &$this ) );
    }

    function SendToReferer()
    {
        $refer = $this->GetFormValue('http_referer');
        if( !empty($refer) )
        {
            $refer = str_replace('&amp;','&',urldecode($refer));
            CCUtil::SendBrowserTo($refer); // this will exit the session
        }
        return false;
    }

    function StorePageInfo()
    {
        $this->SetHiddenField( 'form_referer', 
                           htmlspecialchars(urlencode(cc_current_url())),
                           CCFF_HIDDEN | CCFF_NOUPDATE | CCFF_POPULATE );
                           
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page_title = $page->GetTitle();
        $this->SetHiddenField( 'page_referer', 
                           htmlspecialchars(urlencode($page_title)),
                           CCFF_HIDDEN | CCFF_NOUPDATE | CCFF_POPULATE );
        $bread_crumbs = $page->GetBreadCrumbs();
        $this->SetHiddenField( 'bread_crumb_referer', 
                           htmlspecialchars(urlencode(serialize($bread_crumbs))),
                           CCFF_HIDDEN | CCFF_NOUPDATE | CCFF_POPULATE );
    }

    function GetPageInfo()
    {
        $refer = $this->GetFormValue('form_referer');
        if( !empty($refer) )
            $args['refer'] = str_replace('&amp;','&',urldecode($refer));
        $title = $this->GetFormValue('page_referer');
        if( empty($title) )
        {
            $title = _('Back');
        }
        else
        {
            $title = str_replace('&amp;','&',urldecode($title));
        }
        $args['title'] = $title;
        if( !empty($args['title']) && !empty($args['refer']) )
            $args['ret_link'] = "<a href=\"{$args['refer']}\">{$args['title']}</a>";

        $bc = $this->GetFormValue('bread_crumb_referer');
        if( !empty($bc) )
        {
            $args['bread_crumbs'] = unserialize(urldecode($bc));
        }

        return $args;
    }
        
    function EnableUploads()
    {
        $this->_template_vars['form-data'] = 'multipart/form-data';
        $this->EnableSubmitMessage(true);
    }

    function SetSubmitFormType($submit_meta_data)
    {
        $this->_submit_meta_data = $submit_meta_data;
    }

    function GetSubmitFormType()
    {
        if( isset($this->_submit_meta_data) )
            return $this->_submit_meta_data;
        return null;
    }

    /**
     * Set the value of an html form field. 
     *
     * @param  string $fieldname The form field's name
     * @param  mixed  $value     Value for the form field. The type depends on the type of html form field. For example a textedit formatter expects text, a choice field expects an array.
     */
    function SetFormValue( $fieldname, $value )
    {
        $this->SetFormFieldItem( $fieldname, 'value', $value );
    }

    /**
     * Get the current value in a form field.
     *
     * @param  string $fieldname The form field's name
     * @return      The value. The type of the value depends on the type of field.
     */
    function GetFormValue( $fieldname )
    {
        return $this->GetFormFieldItem($fieldname,'value');
    }

    /**
     * Returns an array of all the fields in the table.
     *
     * This will skip fields marked static or no-update. The array returned can be used "as is" for CCTable functions Insert and Update.
     *
     * @param  array $values A reference to an array that receives the values.
     */
    function GetFormValues( &$values )
    {
        foreach( $this->_form_fields as $name => $ff ) 
            if( $this->_should_update($ff) )
                $values[$name] = $ff['value'];
    }


    /**
     * Creates a hidden form field and sets it's value
     *
     * @access public
     * @param  string $name The form element name
     * @param  string $value Value to to be used 
     * @param  integer $flags CCFF_* flags 
     */
    function SetHiddenField( $name, $value, $flags = CCFF_HIDDEN_DEFAULT )
    {
        $this->_form_fields[$name] = array(  'flags' => $flags,
                                             'value' => $value );

    }

    /**
    * Add meta-data for HTML form fields
    * 
    * The meta data is an array of structures that describes the field<br />
    * and determines the behavior of CCForm methods.
    *  
    * Typical usage is to call this method from the constructor of a form and looks like:
    * 
    * <code>
    * $fields = array(
    *
    *     'contest_friendly_name' => array (
    *                 'label'      => 'Friendly Name',
    *                 'form_tip'   => 'This is the one people actually see',
    *                 'formatter'  => 'textedit',
    *                 'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),
    *
    *     'contest_description' => array (
    *                 'label'      => 'Description',
    *                 'form_tip'   => 'Let people know that this contest is about',
    *                 'formatter'  => 'textarea',
    *                 'flags'      => CCFF_POPULATE),
    *        );
    *
    * $this->AddFormFields( $fields );
    * 
    * </code>
    * 
    * The name ('contest_friendly_name' above) will be the name and id of the HTML form
    * element and can be used in $_POST although all that is encapsulated in this class.
    * 
    * The various fields are described below:
    *<ul>
    *    <li><b>label</b>  <i>string</i> The main text label for the field</li>
    *    <li><b>form_tip</b> <i>string</i> Helpful text used to further describe, preferrably by example, the field</li>
    *    <li><b>formatter</b> <i>string</i> 
    *                 This name will map into two functions in the CCForm (or derived class). One has a 'generator_' prefix,
    *                    the other has a 'validator_' prefix. For example, if the formatter value here is 'textedit', that means
    *                    that there are two methods on this class 'generator_textedit' and 'validator_textedit'. The generator
    *                    is used when HTML need to be generated, the validator is used on POST to validate if the user has
    *                    entered valid data. <br /><br />CCForm has many stock formatters for standard INPUT fields but the formatter
    *                    can be a completely unique value as long there are matching methods in the level of derivation (or above).
    *                    For example if the formatter is 'unique_name' you can write a generator_unique_name() method that simply
    *                    calls the generator_textedit() and a validator_unique_name() method that checks against a database to
    *                    ensure the value is unique to the database before accepting the form.<br /><br />This field is required
    *                    unless it is hidden.
    *                 </li>
    *     <li><b>value</b> <i>mixed</i> A default value used by the generator.</li>
    *     <li><b>options</b> <i>array</i> Applies to multiple choice formatters (e.g. radio, select).
    *<code>
    *    'formatter'  => 'radio',
    *    'options'      => array( 
    *                        '0' => 'Winner is determined offline',
    *                        '1' => 'Display a poll after deadline for entries has passed' )
    *</code>
    *</li>
    *    <li><b>flags</b> <i>integer</i>
    *                 Control flags for how to treat this field during various stags of processing. here are possible values:
    *  <ul>
    *      <li>{@link CCFF_NONE} Just do all default behavior</li>
    *      <li> {@link CCFF_SKIPIFNULL} (used by {@link GetFormValues}) Do not return this field if blank(good for passwords left blank)</li>
    *      <li> {@link CCFF_NOUPDATE} (used by {@link GetFormValues}) Never return this field (good for static and hidden)</li>
    *      <li> {@link CCFF_POPULATE} (used by {@link PopulateValues}) If this flag is set, it will 
    *          use the matching value
    *         passed in to PopulateValues, otherwise this field will be left alone during that process.</li>
    *      <li> {@link CCFF_HIDDEN} (used by {@link GenerateForm}) Creates a type='hidden' INPUT field, you can also use the
    *               SetHiddenField() method.</li>
    *      <li> {@link CCFF_REQUIRED} validator_must_exist validator_* methods call validator_must_exist()
    *         and if this flag is present it will return 'false'</li>
    *      <li> {@link CCFF_STATIC} (used by {@link ValidateFields}) No validation will be done this field.</li>
    *      <li> {@link CCFF_HTML} (used by Generate/Validate) Alters the generator and validator not to do any special 
    *             processing (e.g. nl2br or other character encoding). </li>
    *      <li> {@link CCFF_HIDDEN_DEFAULT} Combination of CCFF_HIDDEN | CCFF_POPULATE</li>
    *   </ul>
    *             </li>
    *     <li><b>class</b> <i>string</i> Name of css class to use. This is rare since most skins will style the INPUT fields
    *     using generic selectors in the style sheet, however 'form_input_short' is used when a smaller text input field is desired.</li>
    *     <li><b>maxwidth</b> <i>integer</i> Used by the 'avatar' formatter for sizing the image.</li>
    *     <li><b>maxheight</b> <i>integer</i> Used by the 'avatar' formatter for sizing the image.</li>
    *     <li><b>macro</b> <i>string</i> Used by the 'metalmacro' formatter and refers to a template macro
    *       to use form formatting the output for this field.  
    *<code>
    *        'upload_license' =>
    *                    array( 'label'      => 'License',
    *                           'formatter'  => 'metalmacro',
    *                           'macro'      => 'my_license_choice',
    *                           'flags'      => CCFF_POPULATE,
    *                           'license_choices' => $lics
    *                    )
    *</code>
    *        In this case 'license_choices' is a value expected by the macro 'license_choice' in the file 'license.xml'.
    *       </li>
    *    <li><b>nomd5</b> <i>boolean</i> Used by the 'password' formatter which normally would hash the input
    *    value using md5(). If this flag is present and set to 'true' the value is not hashed. </li>
    *                     
    *</ul>
    * 
    * As you can see there are a core set of elements ('label', 'form_tip', 'value', etc.) while others are specific for
    * various formatters. Obviously, any formatter pair of generator/validator functions can speficy any name/value pair
    * in the element structure.
    *
    * @param array $fields Array of meta-data structures.
    */
    function AddFormFields( &$fields, $trigger_event = true )
    {
        // the += operator will NOT overwrite existing keys with new
        // information:
        // http://us4.php.net/manual/en/language.operators.array.php
        $this->_form_fields = array_merge($this->_form_fields,$fields);

        if( $trigger_event )
        CCEvents::Invoke( CC_EVENT_FORM_FIELDS, array( &$this, &$this->_form_fields ) );
    }

    /**
    * Put fields into a specific place in the form
    *
    * @param array &$fields Array of new fields to insert
    * @param string $before_or_after One of: 'before', 'after', 'top', 'bottom'
    * @param string $target_field Name of field to place new feilds before or after
    * @see AddFields
    */
    function InsertFormFields( &$fields, $before_or_after, $target_field = '')
    {
        $this->InnerInsertFormFields($this->_form_fields, $fields, $before_or_after, $target_field );
        CCEvents::Invoke( CC_EVENT_EXTRA_FORM_FIELDS, array( &$this, &$fields ) );
    }

    /**
    * Put fields into a specific place into an array of fields
    *
    * @param array &$target_fields Array of fields to receive the insert
    * @param array &$fields Array of new fields to insert
    * @param string $before_or_after One of: 'before' or 'after'
    * @param string $target_field Name of field to place new feilds before or after
    * @see AddFields
    */
    function InnerInsertFormFields( &$target_fields, &$fields, $before_or_after, $target_field )
    {
        if( $before_or_after == 'top' )
        {
            $pos = 0;
        }
        elseif( $before_or_after == 'bottom' )
        {
            $pos = count($target_fields);
        }
        else
        {
            $pos = array_search($target_field, array_keys($target_fields));
            if( $before_or_after == 'after' )
                $pos++;
        }
        if( $pos == count($target_fields) )
        {
            $this->AddFormFields($fields,false);
        }
        else
        {
            $target_fields = array_merge(
                                    array_slice($target_fields, 0, $pos), 
                                    $fields, 
                                    array_slice($target_fields, $pos));
        }
    }

    /**
    * Set the 'action' field of the form element. (The default is the current url.)
    *
    * @param string $handler URL of the post url.
    */
    function SetHandler( $handler )
    {
        $this->_template_vars['form_action'] = $handler;
    }

    /**
    * Set the text for the submit button. (Set to '' to remove the button from the form.)
    *
    * @param string $text Value for submit button.
    */
    function SetSubmitText($text)
    {
        if( empty($text) )
        {
            if( array_key_exists('submit_text',$this->_template_vars ) )
            {
                unset($this->_template_vars['submit_text']);
            }
        }
        else
        {
            $this->_template_vars['submit_text'] = $text;
        }
    }

    /**
    * Puts up a helper caption text above the form.
    *
    * @param string $text Value for helper text.
    */
    function SetHelpText($text)
    {
        $this->_form_help_messages[] = $text;

        $this->CallFormMacro('form_about','html_form.php/show_form_about',$this->_form_help_messages);
    }

    /**
    * Alias for SetHelpText
    * 
    * @see SetHelpText
    * @param string $text Value for helper text.
    */
    function SetFormHelp($text)
    {
        $this->SetHelpText($text);
    }

    /**
    * Sets error text when a validator has failed on a given field.
    *
    * This method is called from validators. If the form is generated and shown this text is output directly
    * above the offending field.
    *
    * @param string $fieldname Name passed into AddFormFields for the field
    * @param string $errmsg Text to display to user
    */
    function SetFieldError($fieldname,$errmsg)
    {
        $field_info =& $this->_get_form_field($fieldname);
        $field_info['form_error'] = $errmsg;
        if( empty($field_info['class']) )
            $field_info['class'] = 'form_error_input';
        elseif( strstr($field_info['class'],'form_error_input') === false )
            $field_info['class'] .= ' form_error_input';
    }

    /**
    * Returns the error the validator set on this field
    *
    * @param string $fieldname Name passed into AddFormFields for the field
    * @param returns $errmsg Text that will be displayed to user
    */
    function GetFieldError($fieldname)
    {
        return $this->GetFormFieldItem($fieldname,'form_error');
    }

    /**
    * Checks the existance of a field in the form
    *
    * @param string $name Name passed into AddFormFields for the field
    */
    function FormFieldExists( $name )
    {
        return array_key_exists($name,$this->_form_fields);
    }

    /**
    * Retrieves a specific element from the field's meta-data structure.
    *
    * @see CCForm::AddFormFields()
    * @param string $fieldname Name passed into AddFormFields for the field
    * @param string $itemname Name of the element to retrieve.
    */
    function GetFormFieldItem( $fieldname, $itemname )
    {
        $field_info =& $this->_get_form_field($fieldname);
        if( empty($field_info[$itemname]) )
            return null;
        return $field_info[$itemname];
    }

    /**
    * Set the value for a specific element in a field's meta-data structure.
    *
    * @see CCForm::AddFormFields()
    * @param string $fieldname Name passed into AddFormFields for the field
    * @param string $itemname Name of the element to retrieve.
    * @param mixed $value Value to put into structure
    */
    function SetFormFieldItem( $fieldname, $itemname, $value )
    {
        $field_info =& $this->_get_form_field($fieldname);
        $field_info[$itemname] = $value;
    }

    /**
    * Set (or replace) a field's meta-data structure.
    *
    * @see CCForm::AddFormFields()
    * @param string $name  Name of HTML field
    * @param mixed $value Meta-data structure for field
    */
    function AddFormField( $name, $value )
    {
        $this->_form_fields[$name] = $value;
    }

    function RemoveFormField( $name )
    {
        unset($this->_form_fields[$name]);
    }

    /**
    * Set (or replace) a template field to be passed to the template generator
    *
    * This method is used when specific adornments to the form are needed (e.g.
    * remix search box).
    *
    * @param string $name Name of template field
    * @param mixed $value Data for template processor
    */
    function SetTemplateVar($name,$value)
    {
        $this->_template_vars[$name] = $value;
    }

    /**
    * Get template data to be passed to the template generator
    *
    * This method is used when specific adornments to the form are needed (e.g.
    * remix search box).
    *
    * @param string $name Name of template field
    * @returns mixed $value Data posted back to form for this element
    */
    function GetTemplateVar($name)
    {
        return $this->_template_vars[$name];
    }

    /**
    * Get all template vars for this form (used by page generator)
    *
    * @returns array $vars Template vars to be passed to template generator
    */
    function GetTemplateVars()
    {
        return $this->_template_vars;
    }

    /**
    * Test if a specific form adornment template exists.
    *
    * This method is used when specific adornments to the form are needed (e.g.
    * remix search box).
    *
    * @param string $name Name of template field
    * @returns boolean $bool true if element exists
    */
    function TemplateVarExists($name)
    {
        return array_key_exists($name,$this->_template_vars);
    }

    /**
    * Set (or replace) a a group of template fields to be passed to the template generator
    *
    * This method is used when specific adornments to the form are needed (e.g.
    * licensing methods).
    *
    * @param array $value Data for template processor
    */
    function AddTemplateVars($value)
    {
        $this->_template_vars = array_merge($this->_template_vars, $value );
    }

    /**
    * Sets up for custom macros to be called when generating the form
    *
    * This method is used when specific adornments to the form are needed (e.g.
    * remix search box).
    *
    * @param string $data_label The name for the record expected by the macro
    * @param string $macro_name The name of the template macro
    * @param mixed  $value      Value to be assigned to $data_label
    */
    function CallFormMacro($data_label,$macro_name,$value=true)
    {
        $this->_template_vars[$data_label] = $value;
        if( empty($this->_template_vars['form_macros']) 
            || !in_array( $macro_name, $this->_template_vars['form_macros'] ) )
        {
            $this->_template_vars['form_macros'][] = $macro_name;
        }
    }

    /**
    * Determines the main template macro to be used for this form (default is 'html_form')
    *
    * @param string $macro_name The name of the template macro
    */
    function SetTemplateMacro($macro_name)
    {
        $this->_template_macro = $macro_name;
    }

    /**
    * Retrieves the main template macro to be used for this form (default is 'html_form')
    *
    * @returns string $macro_name The name of the template macro
    */
    function GetTemplateMacro()
    {
        return( $this->_template_macro );
    }

    /**
    * Retrieves the value that will be put into the HTML forms 'id' attribute
    *
    * @returns string $form_id
    */
    function GetFormID()
    {
        return strtolower(substr(get_class($this),2));
    }

    /**
     * Prepares form variables for display.
     * 
     * Generates template arrays. Returns $this to make it easy to add to pages.
     * 
     * <code>
     *    $page->AddForm( $form->GenerateForm() );
     *</code>
     *  
     * @see CCForm::AddFormFields()
     * @returns array $varsname Array containing two elements: array of variables and template marco name
    */
    function GenerateForm($hiddenonly = false)
    {
        if( $this->_enable_submit_message )
            $this->DoSubmitMessage();

        $this->_template_vars['html_form_fields']   = array();
        $this->_template_vars['html_hidden_fields'] = array();

        $fieldnames = array_keys($this->_form_fields);
        foreach(  $fieldnames as $fieldname )
        {
            $form_fields =& $this->_get_form_field($fieldname);

            if( $form_fields['flags'] & CCFF_HIDDEN )
            {
                $this->_template_vars['html_hidden_fields'][] = 
                        array( 'hidden_name' => $fieldname,
                               'hidden_value' => $form_fields['value']);
            }
            else
            {
                if( $hiddenonly )
                    continue;

                $generator  = 'generator_' . $form_fields['formatter'];
                if( ($form_fields['formatter'] != 'password') && isset($form_fields['value']) )
                {
                    $value = $form_fields['value'];
                    if( $form_fields['flags'] & CCFF_HTML )
                    {
                        $value = htmlspecialchars($value);
                    }
                }
                else
                {
                    $value = '';
                }
                $class = empty($form_fields['class']) ? '' : $form_fields['class'];
                if( !empty($form_fields['form_error']) ) 
                    $class .= ' form_error_input';
                    
                if( method_exists($this,$generator) )
                    $form_fields['form_element'] = $this->$generator( $fieldname, $value, $class );
                else
                    $form_fields['form_element'] = $generator( $this, $fieldname, $value, $class );
                $this->_template_vars['html_form_fields'][$fieldname] = $form_fields;
            }
        }

        $id = $this->GetFormID();
        $this->_template_vars['form_id'] = $id;

        return( $this );
    }

    /**
    * Generate HTML from this form.
    *
    * This is actually rarely used, usually for secondary forms on the page.
    *
    * @see GenerateForm
    */
    function GenerateHTML()
    {
        global $CC_GLOBALS;
        $template = new CCSkinMacro( $this->_template_macro );
        $this->GenerateForm();
        return( $template->SetAllAndParse($this->_template_vars) );
    }

    /**
     * Validates the fields in this form, called during POST processing.
     * 
     * @see CCForm::AddFormFields()
     * @returns bool $success true if all fields validated, false on errors
    */
    function ValidateFields()
    {
        $retval   = true;
        $nostrip = CCUser::IsAdmin() ? CCFF_NOADMINSTRIP : 0;
        $nostrip |= CCFF_NOSTRIP;

        $fieldnames = array_keys($this->_form_fields);
        foreach(  $fieldnames as $fieldname )
        {
            $form_fields =& $this->_get_form_field($fieldname);
            $flags = $form_fields['flags'];
            if( $flags & CCFF_STATIC )
                continue;

            $value = $this->_fetch_post_value($fieldname); 

            if( !empty($value) && !is_array($value) )
            {
                if( CCUser::IsAdmin() ) // $flags & $nostrip )
                    CCUtil::StripSlash($value);
                else
                    CCUtil::StripText($value);
            }

            $this->_form_fields[$fieldname]['value'] = $value;
            if( !($flags & CCFF_HIDDEN) )
            {
                $validator = 'validator_' . $form_fields['formatter'];
                if( method_exists($this,$validator) )
                {
                    $ret = $this->$validator($fieldname);
                }
                else
                {
                    if( !empty($form_fields['formatter_module']) )
                        require_once($form_fields['formatter_module']);
                    $ret = $validator($this,$fieldname);
                }
                $retval = $retval && $ret;
            }
        }

        CCEvents::Invoke( CC_EVENT_FORM_VERIFY, array( &$this, &$retval ) );
        return( $retval );
    }

    /**
     * Populate the values of the field with specific data (e.g. a database record)
     * 
     * @see CCForm::AddFormFields()
     * @param array $values Name/value pairs to be used to populate fields
    */
    function PopulateValues($values)
    {
        CCEvents::Invoke( CC_EVENT_FORM_POPULATE, array( &$this, &$values ) );

        $keys = array_keys($this->_form_fields);

        foreach( $keys as $fieldname )
        {
            $F =& $this->_form_fields[$fieldname];
            if( $F['flags'] & CCFF_POPULATE )
            {
                if( empty($values[$fieldname]) )
                {
                    if( !($F['flags'] & CCFF_POPULATE_WITH_DEFAULT) )
                        $F['value'] = '';
                }
                else
                {
                    $F['value'] = $values[$fieldname];
                }
            }
        }
    }


    // ----------------------------------------------------------------
    //
    //   Internal helpers 
    //
    // ----------------------------------------------------------------

    /**
    * Internal wrapper
    * @access private
    */
    function & _get_form_field( $name )
    {
        if( !empty($this->_form_fields[$name]) )
        {
            $m =& $this->_form_fields[$name];
            return($m);
        }
        $m = null;
        return $m;
    }

    /**
    * Internal checks for all the ways a field might deny updating
    * @access private
    */
    function _should_update(&$form_fields)
    {
        $flags = $form_fields['flags'];

        if( ($flags & CCFF_NOUPDATE) ||
               (empty($form_fields['value']) && ($flags & CCFF_SKIPIFNULL)) 
           )
        {
            return(false);
        }

        return(true);
    }

    /**
    * Internal: return a $_POST value to be used as a value 
    * 
    * This method is overridden in base classes for array cases
    */
    function _fetch_post_value($name)
    {
        if( !array_key_exists($name,$_POST) )
            return( null );

        $value = $_POST[$name];
        return( $value );
    }

    // ----------------------------------------------------------------
    //
    //   Helpers for html generators and validators 
    //
    // ----------------------------------------------------------------

    /**
    * Generic 'check-for-null-value' validator for HTML field, called during ValidateFields()
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok false if field value must exist and doesn't, otherwise true
    */
    function validator_must_exist($fieldname)
    {
        $flags = $this->GetFormFieldItem($fieldname,'flags');
        $value = $this->GetFormValue($fieldname);
        if( ($flags & CCFF_REQUIRED) && empty($value) )
        {
            $this->SetFieldError( $fieldname, _("This cannot be left blank.") );
            return(false);
        }
        return( true );
    }

    // ----------------------------------------------------------------
    //
    //   Standard html generators and validators below
    //
    // ----------------------------------------------------------------

    /**
     * Handles generation of HTML field (passes 'value' as straight html)
     *
     * @param string $varname (ignored)
     * @param string $value   (ignored)
     * @param string $class   (ignored)
     * @returns string $html (empty)
     */
    function generator_passthru($varname,$value,$class='')
    {
        return($value);
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * This version always returns true since the field is actually
    * just a passthru
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true
    */
    function validator_passthru($fieldname,$value='',$class='')
    {
        return( true );
    }

    /**
    * deprecated use 'template' instead
    */
    function generator_metalmacro($varname,$value,$class='')
    {
        return $this->generator_template($varname,$value,$class);
    }
    
    /**
    * deprecated use 'template' instead
    */
    function validator_metalmacro($fieldname)
    {
        return $this->validator_template($fieldname);
    }

    /**
    * Handles setting up for template generated HTML field
    * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns null 
    */
    function generator_template($varname,$value,$class='')
    {
        $this->_form_fields[$varname]['name'] = $varname;
        if( !empty($value) )
            $this->_form_fields[$varname]['value'] = $value;
        return; // ($value);
    }

    /**
    * Handles validator for template generated HTML field, called during ValidateFields()
    * 
    * This version always returns true since the field is actually
    * generated by the template engine.
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true
    */
    function validator_template($fieldname)
    {
        return true ;
    }

    /**
     * Handles generation of HMTL field INPUT type='text'
     *
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_textedit($varname,$value='',$class='')
    {
        if( empty($class) )
            $class='form_input';
        elseif( strstr($class,'form_input') === false )
            $class .= ' form_input';
            
        $prefix = $this->GetFormFieldItem( $varname, 'prefix' );
        $value = str_replace('"', '&quot;',$value);
        $html = "{$prefix} <input type='text' id=\"$varname\" name=\"$varname\" value=\"$value\" class=\"$class\" />";
        return $html;
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Use the 'maxlenghth' field to limit user's input
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_textedit($fieldname)
    {
        // N.B. validator_textarea calls here, so textedit specific
        //      changes should be handled with that in mind

        $maxlen = $this->GetFormFieldItem( $fieldname, 'maxlength' );
        if( !empty($maxlen)  )
        {
            $value = $this->GetFormValue($fieldname);
            if( strlen($value) > $maxlen )
            {
                $this->SetFieldError( $fieldname, sprintf(_("This can not be longer then %s characters"), $maxlen) );
                return(false);
            }
        }
        return( $this->validator_must_exist($fieldname) );
    }

    /**
     * Handles generation of HTML field &lt;span
     *
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_statictext($varname,$value='',$class='')
    {
        $page =& CCPage::GetPage();
        $value = $page->String($value);
        return( "<span class=\"$class\">$value</span>" );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true
    */
    function validator_statictext($fieldname)
    {
        return( true );
    }

    /**
     * Handles generation of HTML field &lt;textarea
     *
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_textarea($varname,$value='',$class='')
    {
        if( $this->GetFormFieldItem($varname,'expanded') )
        {
            $h = '300';
            $w = '450';
            $p = '-';
        }
        else
        {
            $h = '100';
            $w = '300';
            $p = '+';
        }
        $html =<<<END
            <textarea style="width:{$w}px;height:{$h}px;" id="$varname" name="$varname" class="$class">$value</textarea><br />
            <a id="grow_$varname" name="grow_$varname" style="font-size:10px;" href="javascript: void(0);" onclick="cc_grow_textarea('$varname');">[ {$p} ]</a>
END;
        return($html);
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_textarea($fieldname)
    {
        // calling textedit gives us 'maxlength' value
        return $this->validator_textedit($fieldname);
    }

    /**
     * Handles generation of HTML button/link field
     *
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_button($varname,$value='',$class='')
    {
        if( empty($value) )
            $value = 'str_click_here';

        $page =& CCPage::GetPage();
        $str = $page->String($value);
        $wid = strlen($str);
        if( empty($class) )
            $class='cc_gen_button';
            
        $url =  $this->GetFormFieldItem($varname,'url');
        return( "<div style=\"width:{$wid}em\"><a class=\"{$class}\" href=\"{$url}\"><span>$str</span></a></div>" );
    }

    function validator_button()
    {
        return true;
    }

    /**
     * Handles generation of HMTL field <input type='checkbox'
     *
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_checkbox($varname,$value='',$class='')
    {
        if( !empty( $value ) )
            $value = 'checked = "checked" ';
        else
            $value ='';

        return( "<input type=\"checkbox\" id=\"$varname\" name=\"$varname\" $value class=\"$class\" />" );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Converts the HTML value to 1 or 0 in the value field.
    *
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true
    */
    function validator_checkbox($fieldname)
    {
        $value = $this->GetFormValue($fieldname);
        $this->SetFormValue( $fieldname, isset($value) ? 1 : 0 );
        return( true );
    }

    /**
     * Handles generation of several &lt;input type='radio' HTML field 
     * 
     * The 'options' field for the field descriptor must be an array
     * of options to be generated here
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_radio($varname,$value=null,$class='')
    {
        $options = $this->GetFormFieldItem($varname,'options');
        $nobr = $this->GetFormFieldItem($varname,'nobr');
        if( $nobr )
            $html = '<span style="white-space:nowrap">';
        else
            $html = '';
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        foreach( $options as $ovalue => $otext )
        {
            if( !isset($value) )
                $value = $ovalue;

            if( $value == $ovalue )
                $selected = 'checked="checked" ';
            else
                $selected = '';

            $html .= "<input type=\"radio\" id=\"$varname\" name=\"$varname\" value=\"$ovalue\" ".
                    "$selected class=\"$class\" />";
            
            if( !empty($otext) )
            {
                $otext = $page->String($otext);
                $html .= "<label>$otext</label>";
            }

            if( empty($nobr) )
            {
                $html .= '<br />';
            }
        }

        if( $nobr )
            $html .= '</span>';
        return($html);
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true 
    */
    function validator_radio($fieldname)
    {
        return( true );
    }

    /**
     * Handles generation &lt;select HTML field with gen-time string xlate
     * 
     * The 'options' field for the field descriptor must be an array
     * of options to be generated here
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_raw_select($varname,$value='',$class='')
    {
        $options = $this->GetFormFieldItem($varname,'options');
        $fvalue   = $this->GetFormValue($varname);
        $html = "<select id=\"$varname\" name=\"$varname\" class=\"$class\">";
        $page =& CCPage::GetPage();

        foreach( $options as $value => $text )
        {
            if( $value == $fvalue )
                $selected = ' selected="selected" ';
            else
                $selected = '';
            $text = $page->String($text);
            $html .= "<option value=\"$value\" $selected >{$text}</option>";
        }
        $html .= "</select>";
        return( $html );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true
    */
    function validator_raw_select($fieldname)
    {
        return(true);
    }

    /**
     * Handles generation &lt;select and several &lt;option HTML field 
     * 
     * The 'options' field for the field descriptor must be an array
     * of options to be generated here
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_select($varname,$value='',$class='')
    {
        $F =& $this->_form_fields[$varname];
        $F['class'] = $class;
        $F['name'] = $varname;
        $F['macro'] = 'form_fields.tpl/select';
        return '';
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Use the 'maxlenghth' field to limit user's input
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok always true
    */
    function validator_select($fieldname)
    {
        return(true);
    }

    /**
     * Handles generation of &lt;input type='password' HTML field 
     * 
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_password($varname,$value='',$class='')
    {
        return( "<input type=\"password\" id=\"$varname\" name=\"$varname\" value=\"$value\" class=\"$class\" />" );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Checks for password length and letters/number values. Sets md5 
    * version of password into field structure.
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_password($fieldname)
    {
        if( $this->validator_must_exist($fieldname) )
        {
            $value = $this->GetFormValue($fieldname);

            if( empty( $value ) )
                return( true );

            if( strlen($value) < 5 )
            {
                $this->SetFieldError($fieldname," " . _("This must be at least 5 characters."));
                return(false);
            }
            if( preg_match('/[^A-Za-z0-9!@#$%^&_\.-]/', $value) )
            {
                $this->SetFieldError($fieldname, " " . _('Illegal character in password.'));
                return(false);
            }

            $nomd5 = $this->GetFormFieldItem( $fieldname, 'nomd5' );
            if( !$nomd5 )
                $value = md5($value);
            $this->SetFormValue( $fieldname, $value );

            return( true );
        }

        return( false );
    }

    /**
     * Handles generation of &lt;input type='text' HTML field 
     * 
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_email($varname,$value='',$class='')
    {
        return( $this->generator_textedit($varname,$value,$class) );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Does a (very) basic check for a valid email address
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_email($fieldname)
    {
        if( $this->validator_must_exist($fieldname) )
        {
            $value = $this->GetFormValue($fieldname);

            $regex = "/^[A-Z0-9]+([\._\-A-Z0-9+]+)?@[A-Z0-9\.\-_]+\.{1}[A-Z0-9\-_]{2,7}$/i";

            // also see cc-facebook.php
            //
            $bad_email = "/(@cz\.|@lv\.|@sk\.|\.info$|\.pl$|\.eu|\.top|\.win$)/";

            if( !preg_match( $regex, $value ) || preg_match($bad_email, $value) )
            {
                $this->SetFieldError($fieldname, _("This is not a valid email address."));
                return(false);
            }

            return( true );
        }
        return( false );
    }


    /**
     * Handles generation of &lt;input type='text' HTML field for user names
     *
     * Converts a user_id to user_name during validation
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_username($varname,$value='',$class='')
    {
        if( !empty($value) )
        {
            $value = CCUser::GetUserName($value);
        }
        return( $this->generator_textedit($varname,$value,$class) );
    }

    /**
    * Handles validator for a user name field, called during ValidateFields()
    *
    * Converts a user_name entered into a valid user_id 
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_username($fieldname)
    {
        if( $this->validator_must_exist($fieldname) )
        {
            $value = $this->GetFormValue($fieldname);
            if( $value )
            {
                $value = CCUser::IDFromName($value);
                if( $value )
                {
                    $this->SetFormValue($fieldname,$value);
                }
                else
                {                    
                    $this->SetFieldError($fieldname, _("This is not a valid user name."));
                    return(false);
                }
            }
            return( true );
        }
        return( false );
    }



    /**
     * Handles generation of &lt;input type='text' HTML field 
     * 
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_tagsedit($varname,$value='',$class='')
    {
        if( is_array($value) )
            $value = implode(', ',$value);

        return( $this->generator_textedit($varname,$value,$class) );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Accepts a comma separated list and 'cleans it up' to standardize
    * format
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_tagsedit($fieldname)
    {
        require_once('cchost_lib/cc-tags.inc');
        require_once('cchost_lib/cc-tags.php');
        $value = $this->GetFormValue($fieldname);
        $tags =& CCTags::GetTable();
        $value = $tags->Normalize($value);
        if( !is_string($value) && $this->GetFormFieldItem( $fieldname, 'is_array' ) )
            $value = CCTag::TagSplit($value);

        $this->SetFormValue($fieldname,$value);

        return( $this->validator_must_exist($fieldname) );
    }

    /**
     * Handles generation a bunch of &lt;select HTML fields that represents a date/time
     * 
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_date($varname,$value='',$class='')
    {
        if( empty($class) )
            $class = 'cc_date_field';

        if( !empty($value) )
        {
            // Windows and older PHP rolf on dates before 1970
            $time = @strtotime($value);
            if( $time == -1 )
            {
                $newvalue = preg_replace('#^([0-9]{4})#',date('Y'),$value);
                if( $newvalue != $value )
                    $value = date(substr($value,0,4) . '-m-d-g-i-a',strtotime($newvalue));
                else
                    $value = '';
            }
            else
            {
                $value = date('Y-m-d-g-i-a',$time);
            }
        }

        if( empty($value) )
        {
            $value = date('Y-m-d-g-i-a' );
        }

        $day_only = $this->GetFormFieldItem( $varname, 'day_only' );

        if( $day_only )
        {
            list( $year, $month, $today ) = cc_split('-',$value);
        }
        else
        {
            list( $year, $month, $today, $hour, $minute, $ampm ) = cc_split('-',$value);
        }

        $month = date('F', mktime(0,0,0,$month));

        $html = "<div class=\"$class\">";
        
        //
        // MONTH
        //
        $html .= "<select name=\"$varname" . "[m]\"  id=\"$varname" . "[m]\" >";
        for( $i = 1; $i < 13; $i++ )
        {
            $m = date('F', mktime(0,0,0,$i));
            $selected = $month == $m ? 'selected="selected"' : '';
            $html .= "<option $selected value=\"$m\">$m</option>";
        }
        $html .= "</select>\n";

        //
        // DAY
        //
        $html .= "<select name=\"$varname" . "[d]\"  id=\"$varname" . "[d]\" >";
        for( $i = 1; $i < 32; $i++ )
        {
            if( $i == $today )
                $selected = ' selected = "selected" ';
            else
                $selected = '';

            $html .= "<option value=\"$i\" $selected>$i</option>";
        }
        $html .= "</select>\n";

        //
        // YEAR
        //

        $year_begin = $this->GetFormFieldItem($varname,'year_begin');
        $year_end   = $this->GetFormFieldItem($varname,'year_end');
        if( empty($year_begin) )
            $year_begin = $year;

        if( empty($year_end) )
            $year_end = $year_begin + 5;

        $y_i   = min($year_begin,$year_end);
        $y_max = max($year_begin,$year_end);

        $html .= "<select name=\"$varname" . "[y]\"  id=\"$varname" . "[y]\" >";
        $year = intval($year);
        for( ; $y_i <= $y_max; ++$y_i )
        {
            $selected = ( $y_i == $year ) ? 'selected="selected"' : '';
            $html .= "<option value=\"$y_i\" $selected>$y_i</option>";
        }
        $html .= "</select>\n";

        if( empty($day_only) )
        {
            $html .= ' - ';

            // 
            // HOUR
            //
            $html .= "<select name=\"$varname" . "[h]\"  id=\"$varname" . "[h]\" >";
            for( $i = 1; $i < 13; $i++ )
            {
                if( $i == $hour )
                    $selected = ' selected = "selected" ';
                else
                    $selected = '';

                $html .= "<option value=\"$i\" $selected>$i</option>";
            }
            $html .= "</select>";

            $html .= ':';

            //
            // MINUTE
            //
            $html .= "<select name=\"$varname" . "[i]\"  id=\"$varname" . "[i]\" >";
            for( $i = 0; $i < 60; $i++ )
            {
                if( $i == $minute )
                    $selected = ' selected = "selected" ';
                else
                    $selected = '';

                if( $i < 10 )
                    $it = "0$i";
                else
                    $it = $i;

                $html .= "<option value=\"$it\" $selected>$it</option>";
            }
            $html .= "</select>";

            // 
            // AM/PM
            //
            $html .= "<select name=\"$varname" . "[a]\"  id=\"$varname" . "[a]\" >";
            $apa = array( "am", "pm" );
            foreach( $apa as $apc )
            {
                if( $apc == $ampm)
                    $selected = ' selected = "selected" ';
                else
                    $selected = '';

                $html .= "<option value=\"$apc\" $selected>$apc</option>";
            }
            $html .= '</select>';
        }

        $html .= '</div>';

        return($html);
    }


    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Checks to make sure date belongs to this planet (May be limiting
    * for certain electronic music sites.)
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_date($fieldname)
    {
        $v = $this->GetFormValue($fieldname);
        $day_only = $this->GetFormFieldItem( $fieldname, 'day_only' );
        $v['m'] = date('m',strtotime($v['m'] . ' 1,2000')) ;
        $v['d'] = sprintf('%02d',$v['d']);
        $ok = true;
        if( empty($day_only) )
        {
            $fmt = $v['h'] . ':' . $v['i'] . ' ' . $v['a'];
            $t = ' ' . date( 'H:i', strtotime($fmt));
        }
        else
        {
            $t = '';
        }

        $str = "{$v['y']}-{$v['m']}-{$v['d']}$t";
        if( checkdate($v['m'],$v['d'],$v['y']) )
        {
            $time = strtotime($str);

            if( $time > 0 )
            {
                $range_end = $this->GetFormFieldItem($fieldname,'range_end');
                $out_of_range = !empty($range_end) && ($time > $range_end);
                if( !$out_of_range )
                {
                    $range_start = $this->GetFormFieldItem($fieldname,'range_begin');
                    $out_of_range = !empty($range_start) && ($time < $range_start);
                }

                if( $out_of_range )
                {
                    $this->SetFieldError($fieldname, _("The date is out of range.") );
                    $ok = false;
                }
            }

        }
        else
        {
            $this->SetFieldError($fieldname, _("This is not a valid date.") );
            $ok = false;
        }

        $this->SetFormValue($fieldname,$str);
        return $ok;
    }

    /**
     * Handles generation of HTML field 
     *
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string of HTML that represents the field
     */
    function generator_localdir($varname,$value='',$class='')
    {
        return( $this->generator_textedit( $varname,$value, $class ) );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Ensures that directory actually exists
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_localdir($fieldname)
    {
        if( $this->validator_textedit($fieldname) )
        {
            $dir = $this->GetFormValue($fieldname);
            if( !file_exists($dir) )
            {
                $this->SetFieldError($fieldname, _('This file or directory does not exist.'));
                return false ;
            }
        }

        return true;
    }


    /**
    * Validates the user against a regex pattern
    *
    * The regex pattern to check against is sent via the
    * the 'pattern' property
    * 
    * @param string $fieldsname Name of the HTML field
    * @param string $value   value to be published into the field
    * @param string $class   CSS class (rarely used)
    * @returns string of HTML that represents the field
    */
    function generator_pattern($varname,$value='',$class='')
    {
        return $this->generator_textedit($varname,$value,$class) ;
    }

    /**
    * Validates the user against a regex pattern
    *
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_pattern($fieldname)
    {
        $pattern = $this->GetFormFieldItem( $fieldname, 'pattern' );

        $ok = $this->validator_textedit($fieldname);

        if( $ok )
        {
            if( empty($pattern) )
                return true;  // hmmmmm

            $value = $this->GetFormValue($fieldname);

            $ok = preg_match( $pattern, $value );

            if( !$ok )
            {
                $errmsg = $this->GetFormFieldItem( $fieldname, 'pattern_error' );
                if( empty($errmsg) )
                {
                    $errmsg = _('This does match a proper pattern.');
                }
                $this->SetFieldError( $fieldname, $errmsg );
            }
        }

        return $ok;
    }

    /**
    * Accepts a regular expression as input and compiles to verify it
    * 
    * @param string $fieldsname Name of the HTML field
    * @param string $value   value to be published into the field
    * @param string $class   CSS class (rarely used)
    * @returns string of HTML that represents the field
    */
    function generator_regex($fieldname,$value='',$class='')
    {
        return( $this->generator_textedit($fieldname,$value,$class) );
    }

    /**
    * Validates that a regular expression will compile
    *
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_regex($fieldname)
    {
        $mask = $this->GetFormValue($fieldname);
        $prev_handler = CCDebug::InstallErrorHandler(false);
        ob_start();
        preg_match("/$mask/",'dummystring');
        $t = ob_get_contents();
        ob_end_clean();
        CCDebug::InstallErrorHandler($prev_handler);
        if( $t )
        {
            // I have my doubts about how internation 
            // this is... depends on the format of the 
            // warning messages back from PHP
            $t = str_replace('\\','/',$t);
            $f = str_replace('\\','/',__FILE__);
            $place = "|(in )?<b>$f.*|";
            $error_msg = preg_replace( $place, '', $t);
            $this->SetFieldError($fieldname,$error_msg);
            return(false);
        }
        return(true);
    }

    /**
    * Adds the necessary javascript macros to disable the submit button
    */
    function DisableSubmitOnInit()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddScriptBlock( 'util.php/disable_submit_button', true ); 
    }

    /**
     * Handles generation of HTML field for avatars.
     *
     * Generates HTML for displaying browse field and (if the image exists)
     * a thumbnail of the image and a 'delete this' check box. It requires
     * that the field information array contains an entry called 'upload_dir'
     * is the local directory (relative, <b>not</b> full path) that is to
     * receive the image upload. This method is called automatically from CCForm::GenerateForm()
     *
     * @param string $varname Name of the HTML field
     * @param string $value   Image file name (optional)
     * @param string $class   CSS class (ignored)
     * @returns string of HTML that represents the field
     */
    function generator_avatar($varname,$value='',$class='')
    {
        $html = $this->generator_upload($varname,$value,$class);

        // if we came back here on error, display the original file
        // (if there is one)

        if( $this->GetFieldError($varname) )
        {
            $value = empty( $_POST[$varname . '_file'] ) ? null : $_POST[$varname . '_file'];
        }

        if( !empty($value) )
        {
            $imagedir = $this->GetFormFieldItem($varname,'upload_dir');
            if( is_array($value) ) // err, sorry about this
                $value = $value['name']; 
            $real = cca(preg_replace('#/$#','',$imagedir),$value);
            if( file_exists($real) )
            {
                $path     = ccd($imagedir,$value);
                $html .= '<br /><img style="background-color:#999" src="' . $path . '" /><br /> '.
                  '<input type="checkbox" id="' . $varname . '_delete" name="' . $varname . '_delete" />'.
                  '<input type="hidden"   id="' . $varname . '_file"   name="' . $varname . '_file" ' .
                      'value="' . $value . '" />'.
                         ' ' . _('Delete this image');
            }
        }

        return($html);
    }

    /**
     * Validates HTML field for avatars at POST time.
     * 
     * Checks for such things as 'required' flags. Also checks against 
     * an ield information array about maximum height/width requirements. Generates 
     * HTML for displaying browse field and delete checkbox (if the image exists).
     * This method is called automatically from CCForm::ValidateFields()
     * 
     * @param string $fieldname Name of the HTML field
     * @returns boolean $bool true if field data passes validation, false on errors
     */
    function validator_avatar($fieldname)
    {
        $retval = CCUploadForm::validator_upload($fieldname);

        if( $retval )
        {
            $filesobj = $this->GetFormValue($fieldname);
            if( !$filesobj || ($filesobj['error'] == UPLOAD_ERR_NO_FILE) )
                return(true);

            $tmp_name   = $filesobj['tmp_name'];
            $image_size = @getimagesize($tmp_name);

            if( $image_size === false )
            {
                $this->SetFieldError($fieldname,_("This file does not appear to be an image."));
                $retval = false;
            }
            else
            {
                global $CC_GLOBALS;
                
                if( empty($CC_GLOBALS['imagemagick-path']) || !file_exists($CC_GLOBALS['imagemagick-path']) )
                {
                    $maxheight = intval($this->GetFormFieldItem($fieldname,'maxheight'));
                    $maxwidth  = intval($this->GetFormFieldItem($fieldname,'maxwidth'));
                    if( $maxheight && $maxwidth )
                    {
                        // getimagesize will try to read this even if the
                        // user typed in garbage into the file input field
                        // is_file() returns true so we have to squash the error

                        list( $width, $height ) = $image_size;
                        if( !$width || !$height )
                        {
                            $this->SetFieldError($fieldname,_("The image size could not be determined."));
                            $retval = false;
                        }
                        else if( ($width > $maxwidth) || ($height > $maxheight ) )
                        {
                            $this->SetFieldError($fieldname,_("The image must be no larger than 93px x 93px."));
                            $retval = false;
                        }
                    }
                }
                else
                {
                    define('MAX_IMAGE_FILESIZE_MB', 5);
                    define('MAX_IMAGE_FILESIZE', MAX_IMAGE_FILESIZE_MB * 1024 * 1024);
                    
                    $filesize = filesize($tmp_name);
                    
                    if( $filesize > MAX_IMAGE_FILESIZE )
                    {
                        $this->SetFieldError($fieldname, _("The image file is over the " . MAX_IMAGE_FILESIZE_MB . "MB limit."));
                    }
                    
                }
            }
        }

        return( $retval );
    }

    /**
     * Handles generation of HTML field for simple file uploads.
     *
     * Generates HTML for displaying browse field. This method is called automatically from CCForm::GenerateForm()
     *
     * @param string $varname Name of the HTML field
     * @param string $value   (ignored)
     * @param string $class   CSS class (optional)
     * @returns string of HTML that represents the field
     */
    function generator_upload($varname,$value='',$class='')
    {
        return( "<input type=\"file\" id=\"$varname\" name=\"$varname\" class=\"$class\" />" );
    }

    /**
     * Validates HTML field for file uploads at POST time.
     *
     * Checks for such things as 'required' flags. Also checks against 
     * PHP system errors on upload. If successful will populate the 
     * the 'value' field of the fields info array with the name of the
     * target file and creates an entry called 'fileobj' that contains 
     * a copy of the PHP $_FILES object for this field.
     * This method is called automatically from CCForm::ValidateFields()
     *
     * @param string $fieldname Name of the HTML field
     * @returns boolean $bool true if field data passes validation, false on errors
     */
    function validator_upload($fieldname)
    {
        $flags = $this->GetFormFieldItem($fieldname,'flags');
        if( !isset($_FILES[$fieldname]) )
        {
            if( ($flags & CCFF_REQUIRED)!=0 )
            {
                $this->SetFieldError( $fieldname, _("You must specify a file.") );
                return false;
            }
            return true;
        }
        $filesobj = $_FILES[$fieldname];

        if( !($flags & CCFF_REQUIRED) && ($filesobj['error'] == UPLOAD_ERR_NO_FILE) )
            return(true);

        if( $filesobj['error'] != 0 )
        {
            $problems = array( UPLOAD_ERR_INI_SIZE  => 
                                    _('The file is too big (ini).'),
                               UPLOAD_ERR_FORM_SIZE => 
                                    _('The file is too big (form).'),
                               UPLOAD_ERR_PARTIAL   => 
                                    _('The file was not fully uploaded.'),
                               UPLOAD_ERR_NO_FILE   => 
                                    _('Missing file name'));

            $this->SetFieldError($fieldname, $problems[$filesobj['error']]);
            return(false);
        }

        $filesobj['name'] = CCUtil::StripSlash($filesobj['name']);
        $this->SetFormValue($fieldname,$filesobj);
        return(true);
    }


    /**
     * Avatar upload is not completed until this helper is called.
     *
     * This method should be called (after field verification) to
     * move the uploaded to the right location. It requires
     * that the field information array contains an entry called 'upload_dir'
     * is the local directory (relative, <b>not</b> full path) that is to
     * receive the image upload. 
     *
     * @param string $fieldname Name of the HTML field
     * @param string $imagedir Directory to put the uploaded image into
     */
    function FinalizeAvatarUpload($fieldname,$imagedir)
    {
        $ok = true;
        $delfield = $fieldname . '_delete';
        if( array_key_exists($delfield,$_POST) ) // && ($_POST[$delfield] == 'on') )
        {
            $oldname  = CCUtil::StripText($_POST[$fieldname . '_file']);
            if( $oldname )
            {
                CCUtil::MakeSubdirs( $imagedir ); 
                    $path = realpath($imagedir) . '/' . $oldname; 
                unlink( $path );

                // we have to strip the SKIP flag to maek sure the blank
                // record gets written to the db
                $flags = $this->GetFormFieldItem($fieldname,'flags');
                $this->SetFormFieldItem($fieldname,'flags', $flags &= ~CCFF_SKIPIFNULL);
            }
            $this->SetFormValue($fieldname,'');
        }
        else
        {
            $filesobj = $this->GetFormValue($fieldname);

            if( $filesobj )
            {
                CCUtil::MakeSubdirs($imagedir);

                $clean_name = preg_replace('/[^a-z0-9\._-]/i','_',$filesobj['name']);
                if( !preg_match('/\.[a-z]+$/i',$clean_name) )
                    $clean_name .= '.gif'; // er, just a hunch
                
                if( $clean_name != $filesobj['name'] )
                {
                    $filesobj['name'] = $clean_name;
                    // I don't think this next line is right...
                    $this->SetFormValue($fieldname,$filesobj);
                }

                $realpath = realpath( $imagedir) . '/' . $clean_name ;
                
                if( file_exists($realpath) )
                    unlink($realpath);

                $ok = move_uploaded_file($filesobj['tmp_name'],$realpath );

                if( $ok )
                {
                    chmod($realpath,cc_default_file_perms());

                    $maxheight = intval($this->GetFormFieldItem($fieldname,'maxheight'));
                    $maxwidth  = intval($this->GetFormFieldItem($fieldname,'maxwidth'));
                
                    $clean_name = CCForm::ResizeAvatar($maxwidth, $maxheight, $clean_name, $imagedir);
                    if( $clean_name )
                        $filesobj['name'] = $clean_name;
                }
                else
                {
                    $filesobj['name'] = null;
                }

                $this->SetFormValue($fieldname,$filesobj['name']);
            }
        }
        
        return( $ok );
    }
    
    public static function ResizeAvatar($maxwidth, $maxheight, $clean_name, $imagedir)
    {
        global $CC_GLOBALS;
        
        if( empty($CC_GLOBALS['imagemagick-path']) || 
            !file_exists($CC_GLOBALS['imagemagick-path']) || 
            empty($maxwidth) ||
            empty($maxheight) )
        {
            return null;
        }
        
        $sizestr = $maxwidth . 'x' . $maxheight;
        $oldrealpath = realpath( $imagedir) . '/' . $clean_name ;
        $clean_name = preg_replace('/(\.[a-z]+)$/i', $sizestr . '\1', $clean_name );
        $realpath = realpath( $imagedir) . '/' . $clean_name ;
        if( file_exists($realpath) )
            unlink($realpath);
        $cmd = $CC_GLOBALS['imagemagick-path'] . 
                    " \"" . $oldrealpath . "\" " . 
                    "-resize " . $sizestr . 
                    " \"" . $realpath . "\"";
        $result = exec($cmd);
        $ok = $result != 0;
        $arr = array( 'cmd' => $cmd,
                      'result' => $result );
        unlink($oldrealpath);                                      
        chmod($realpath,cc_default_file_perms());
        return $clean_name;
    }

    /**
    * Shows or hides the message during file submits
    *
    * @param boolean $bool
    */
    function EnableSubmitMessage($bool)
    {
        $this->_enable_submit_message = $bool;
    }

    /**
    * Adds the necessary javascript macros to show submit message
    */
    function DoSubmitMessage()
    {
        require_once('cchost_lib/cc-page.php');
        $this->AddTemplateVars(array('hide_on_submit' => true));
        $page =& CCPage::GetPage();
        $page->AddScriptBlock( 'hide_upload_form', true ); 
    }


}

/**
 * Alternative view of HTML form controls
 *
 * This class sets up a form with a grid of form elements
 * useful when editing several records at once.
 *
 */
class CCGridForm extends CCForm
{
    /**#@+
    * @access private
    * @var string 
    */
    var $_grid_rows;
    var $_column_heads;
    var $_is_normalized;
    var $_meta_row;
    var $_add_row_caption;
    /**#@-*/

    /**
     * Constructor
     *
     * Setups the template to handle grid of form elements.
     *
     */
    function CCGridForm()
    {
        $this->CCForm();
        $this->_is_normalized = false;
        $this->_grid_rows = array();
        $this->_column_heads = array();
        $this->SetTemplateVar('show_form_grid',   true );
    }

    /**
     * Add a row of controls to the form.
     *
     * @param integer $key Typically the unique key in the a db representing this record
     * @param array   $row An array of field objects 
     * @see CCForm::AddFormFields()
     */
    function AddGridRow($key,&$row)
    {
        $this->_grid_rows[ '#' . $key] = $row;
    }

    /**
     * Specify the meta information for rows added dynamically by the user.
     *
     * @param array  meta_row Meta information of insertable row
     * @param string caption Caption for 'add row' button
     * @param string Javascript to execute after new meta row has been added by user
     */
    function AddMetaRow($meta_row,$caption,$post_stuff_script='')
    {
        $this->_meta_row = $meta_row;
        $this->_add_row_caption = $caption;
        $this->SetTemplateVar('stuffer_script',$post_stuff_script);
    }

    /**
     * Add a row of column headers. 
     *
     * This should be called once to setup the column headers
     *
     * @access public
     * @param array $heads An array of strings
     */
    function SetColumnHeader(&$heads)
    {
        $this->_column_heads = $heads;
        $args = func_get_args();
        if( count($args) > 1 )
            $this->_use_for_name = $args[1];
    }

    function & _get_form_field( $name )
    {
        $field =& parent::_get_form_field($name);

        if( empty($field) && 
            isset($this->_meta_row) 
            )
        {
            $keys = array_keys($this->_meta_row);
            $c = count($keys);
            for( $i = 0; $i < $c; $i++ )
            {
                if( $this->_meta_row[ $keys[$i] ]['element_name'] == $name )
                {
                    $field =& $this->_meta_row[ $keys[$i] ];
                    break;
                }
            }
        }

        return $field;

    }

    /**
     * Overrides base class to handle grid rows
     *
     * @see CCForm::GetFormValues()
     * @access public
     * @param array $values Out parameter to receive values. This is suitable for called CCTable::Update() or CCTable::Insert().
     * 
     */
    function GetFormValues( &$values )
    {
        $this->_normalize_fields();

        return( CCForm::GetFormValues($values) );
    }

    /**
     * This method puts a reference of each field (cell) in the grid and into a format the base class can use for methods like Get/SetValues().
     *
     * @access private
     */
    function _normalize_fields()
    {
        if( $this->_is_normalized )
            return;

        $count = count($this->_grid_rows);
        $keys  = array_keys($this->_grid_rows);
        for( $i = 0; $i < $count; $i++ )
        {
            $grid_row =& $this->_grid_rows[$keys[$i]];
            $count2 = count($grid_row);
            for( $n = 0; $n < $count2; $n++ )
            {
                $grid_cell =& $grid_row[$n];
                $this->_form_fields[$grid_cell['element_name']] = &$grid_cell;
            }
        }

        $this->_is_normalized  = true;
    }

    /**
     * Overrides base class to handle grid rows.
     *
     * Generates template arrays and prepares the form for display.
     * 
     * <code>
     *    $page->AddForm( $form->GenerateForm() );
     *</code>
     * 
     * @returns object $varsname Return $this to make it convienent to add to pages
     */
    function GenerateForm($hiddenonly = false)
    {
        $this->_normalize_fields();

        $headers = array();
        foreach( $this->_column_heads as $ch )
            $headers[] = array( 'column_name' => $ch );

        $this->_template_vars['html_form_grid_columns'] = $headers;
        $this->_template_vars['html_form_grid_rows']    = array();
        $this->_template_vars['html_hidden_fields']     = array();

        foreach( $this->_form_fields as $fieldname => $form_fields )
        {
            if( $form_fields['flags'] & CCFF_HIDDEN )
            {
                $this->_template_vars['html_hidden_fields'][] = 
                        array( 'hidden_name' => $fieldname,
                               'hidden_value' => $form_fields['value']);
            }
            else
            {
                // this is not really defined here...
            }
        }

        $keys  = array_keys($this->_grid_rows);

        if( isset($this->_use_for_name) )
        {
            $name_i = $this->_template_vars['html_form_grid_name_col'] =  $this->_use_for_name;
        }
        else
        {
            $name_i = -1;
        }

        $i = 0;
        $rows = array();
        foreach( $keys as $i => $key )
        {
            $grid_row =& $this->_grid_rows[$key];
            $form_error = '';
            $template_row = $this->_build_row($grid_row,$form_error,$name_i);

            $rows[] = array(  'html_form_grid_fields' => $template_row, 
                               'name' => $name_i == -1 ? substr($key,1) : $grid_row[$name_i]['value'],
                               'has_error' => !empty($grid_row['has_error']),
                                     'grid_row' => ++$i,
                                     'form_error' => $form_error,
                                     'num_columns' => count($grid_row)
                                  );
        }



        $this->_template_vars['html_form_grid_rows'] =& $rows;

        $id = $this->GetFormID();
        $this->_template_vars['form_id'] = $id;

        if( !empty($this->_meta_row) )
        {
            $d = '';
            $mrow = $this->_build_row($this->_meta_row,$d,$name_i);

            /*
                $mrow_text = '';
                foreach( $mrow as $MR )
                    $mrow_text .= '<td>' . $MR['form_grid_element'] . '</td>';
                $this->_template_vars['html_meta_row'] = str_replace("'", "\'", $mrow_text );
            */
            $mrows = array();
            foreach( $mrow as $MR )
                $mrows[] = str_replace("'", "\'", $MR['form_grid_element']);
            $this->_template_vars['html_meta_row'] = $mrows;
            $this->_template_vars['html_form_grid_num_rows'] = count($rows);
            $this->_template_vars['html_form_grid_num_cols'] = count($mrow);
            $this->_template_vars['html_add_row_caption'] = $this->_add_row_caption;
        }

        return( $this );
    }

    function _build_row(&$grid_row,&$form_error,$name_i)
    {
        $count2 = count($grid_row);
        $template_row = array();
        for( $n = 0; $n < $count2; $n++ )
        {
            $grid_cell =& $grid_row[$n];

            $generator  = 'generator_' . $grid_cell['formatter'];
            $value = empty($grid_cell['value']) ? '' : $grid_cell['value'];
            
            $class = empty($grid_cell['class']) ? '' : $grid_cell['class'];

            if( !empty($grid_cell['form_error']) )
            {
                $form_error .= '   ' . $grid_cell['form_error'];
                $class .= "\" style='background:pink' ";
                $grid_row['has_error'] = true;
            }

            $gen = array();

            if( method_exists($this,$generator) )
            { 
                $gen['form_grid_element'] = $this->$generator( $grid_cell['element_name'], $value, $class );
            }
            else
                $gen['form_grid_element'] = $generator( $this, $grid_cell['element_name'], $value, $class );

            if( !empty($grid_cell['macro']) )
            {
                $gen += $grid_cell;
                if( empty($gen['form_grid_element']) )
                    $gen['form_grid_element'] = '<!-- -->';
            }

            $template_row[] = $gen;
        }

        return $template_row;
    }

    /**
     * Overrides base class to handle grid rows.
     *
     * Call this method on POST to verify each field in the form. see CCForm::ValidateFields()
     * @returns boolean true if fields validate, false if there is a problem.
     * @access public
     * 
     */
    function ValidateFields()
    {
        $this->_normalize_fields();

        return( CCForm::ValidateFields() );
    }

    /**
     * Does special array-aware parsing of field names.
     * @param string $name name of field, typically in the form 'something[integer][subfieldname]'
     * @returns value of field
     * @access private
     * 
     */
    function _fetch_post_value($name)
    {
        if( strpos($name,'[') === false )
            return( CCForm::_fetch_post_value($name) );

        // typical format of '$name' is: mi[9][fname]
        //
        // We turn that into:
        //
        //  if( array_key_exists( 'fname', $_POST['mi']['9'] ) )
        //     $value = $_POST['mi']['9']['fname']
        //
        $m = preg_replace('/^([^\]]+)\[/',"[$1][",$name);
        preg_match_all('/\[([^\]]+)]/',$m,$a);
        $c = count($a[1]) - 1;
        $key = $a[1][$c];
        $s = str_replace("[$key]",'',$m);
        $m = "if( array_key_exists( '$key', \$_POST$s) ) \$value = \$_POST$m;";
        $m = preg_replace('/\[([^\]]+)\]/',"['$1']",$m);

        $value = null;
        if( strstr($m,'word') )
            CCDebug::PrintVar($this);
        eval($m);
        return($value);
    }

}


/**
 * Sets up an HTML form that is capable of receiving file uploads.
 *
 * Derive from this class when your form has file upload fields. It has 
 * built in support avatar images.
 *
 */
class CCUploadForm extends CCForm
{

    /**
     * Constructor
     *
     * Setups the template to handle file uploads.
     *
     */
    function CCUploadForm()
    {
        $this->CCForm();
        $this->EnableUploads();
    }

}

class CCGenericForm extends CCForm
{
}

?>
