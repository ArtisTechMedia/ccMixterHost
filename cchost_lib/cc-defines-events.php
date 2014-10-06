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
* $Id: cc-defines-events.php 12559 2009-05-06 19:54:43Z fourstones $
*
*/

/**
* Core defines for the system
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Request for Data Event: App session init
*
* Event triggered after app session has been initialized, all
* modules are loaded, user is logged in.
* 
* Call back (handler) prototype:
*<code>
* function OnAppInit()
* </code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_APP_INIT',            'init');

/**
* Request for Data Event: App session done
*
* Event triggered after app session has executed the
* incoming URL and page has been displayed 
* 
* Call back (handler) prototype:
*<code>
* function OnAppDone()
* </code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_APP_DONE',            'done');


/**
* Request for Data Event: Build main menu
*
* Event triggered when the system needs to build and cache the main menu.
* 
* Call back (handler) prototype:
*<code>
* function OnBuildMenu()
*</code>
* The callback needs to call {@link CCMenu::AddItems()} in order place items into the menu.
* @see CCEvents::AddHandler()
* @see CCMenu::GetMenu()
*/
define('CC_EVENT_MAIN_MENU',           'mainmenu');


/**
* Request for Data Event: Display and patch menu
*
* Event triggered when the system is about to display the main menu giving modules
* an opportunity to dynamically alter the menu based on context (i.e. who
* is logged in.)
* 
* Call back (handler) prototype:
*<code>
* // The callback edits the $menu structure directly
* function OnPatchMenu(&$menu )
*</code>
* @see CCEvents::AddHandler()
* @see CCMenu::GetMenu()
*/
define('CC_EVENT_PATCH_MENU',          'patchmenu');


/**
* Request for Data Event: Display and patch upload local menu
*
* Event triggered when the system is about to display the local upload
* menu for a given upload record. This gives modules 
* an opportunity to dynamically alter the menu based on context (i.e. who
* is logged in.)
* 
* Call back (handler) prototype:
*<code>
* // The callback edits the $menu structure directly
* // $record is the upload record this menu is for
* function OnUploadMenu(&$menu, &$record )
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_UPLOAD_MENU',         'uploadmenu');

/**
* Request for Data Event: Build and display the admin's menu
*
* Triggered when the system is requesting to build the
* one of either the global admin functions or the admin functions
* for the current virtual root.
* 
* Call back (handler) prototype:
*<code>
* // The callback edits the $menu structure directly
* // $type is one of either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
* function OnAdminMenu(&$menu, $type )
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_ADMIN_MENU',          'buildadminmenu');

/**
* @deprecated
*/
define('CC_EVENT_UPLOAD_ROW',          'uploadrow' );

// no longer supported:
//define('CC_EVENT_UPLOAD_LISTING',      'uploadlisting' );

/*
* @deprecated
*/
define('CC_EVENT_LISTING_RECORDS',     'listingrecs' );

/**
* @deprecated
*/
define('CC_EVENT_UPLOAD_FILES',        'uploadfiles' );


/**
* Notification Event: New or changed upload
*
* Triggered at the end of new upload processing, properties
* edit, a file has been added or replaced in an upload
* record, etc.
*
* The $op value can be one of the following:
* <ul>
* <li><b>CC_UF_NEW_UPLOAD</b> - This is a new upload record</li>
* <li><b>CC_UF_PROPERTIES_EDIT</b> - User has change properties that might affect
* things like the physical filename or the remix sources, etc.</li>
* <li><b>CC_UF_FILE_ADD</b> - User has added a new file (through 'Manage Files')
* to the upload record.</li>
* <li><b>CC_UF_FILE_REPLACE</b> - User has replaced one of the physical files</li>
* </ul>
*
* If the $parents parameter is present it is an array of remix sources for the upload.
*
* Call back (handler) prototype:
*<code>
* function OnUploadDone( $upload_id, $op, &$parents = array() )
*</code>
* @see CCEvents::AddHandler()
* @see CC_EVENT_FILE_DONE
*/
define('CC_EVENT_UPLOAD_DONE',         'uploaddone' );


/**
* Notification Event: 'I Sampled This' list changed (or created) 
*
* Call back (handler) prototype:
*<code>
*function OnFileDone($upload_id, &$sources)
*</code>
* @see CCEvents::AddHandler()
* @see CC_EVENT_UPLOAD_DONE
*/
define('CC_EVENT_SOURCES_CHANGED',      'srcchange' );

/**
* Notification Event: A new physical file has been uploaded or changed.
*
* Triggered when a physical file has been added, replaced or edited. 
*
* Call back (handler) prototype:
*<code>
*function OnFileDone(&$file)
*</code>
* @see CCEvents::AddHandler()
* @see CC_EVENT_UPLOAD_DONE
*/
define('CC_EVENT_FILE_DONE',           'filedone' );

/**
* @access private
*/
define('CC_EVENT_ED_PICK',             'edpick' );

/**
* Notification Event: Upload has been rated
*
* Call back (handler) prototype:
*<code>
*function OnRated( $ratings_record, $score, &$upload_record )
*</code>
* @see CCEvents::AddHandler()
*/ 
define('CC_EVENT_RATED',               'rated' );

/**
* Request for Data Event: Data request that an upload has been rated
*
* Triggered when calculating system tags for an upload. Different
* modules will produce different tags depending on the record in
* question. This event is called for <i>both</i> upload and file
* records since they both have their own tags that are then combined.
*
* Either the upload <i>or</i> file record paramater will be set,
* but never both.
*
* The event callback is to put the tags into the $tags argument.
*
* Call back (handler) prototype:
*<code>
*function OnGetSysTags( &$upload_record, &$file_record, &$tags)
*</code>
* @see CCEvents::AddHandler()
*/ 
define('CC_EVENT_GET_SYSTAGS',         'getsystags' );

/**
* @deprecated
*/
define('CC_EVENT_USER_ROW',            'userrow' );

/**
* Request for Data Event: Map URLs to methods and functions
*
* Triggered when the system to build up the map of URL-to-functions.
* Call back is expected to call {@link CCEvents::MapUrl()} in order
* to populate the map.
*
* Call back (handler) prototype:
*<code>
* function OnMapUrls()
*</code>
* @see CCEvents::AddHandler()
* @see CCEvents::MapUrl()
*/
define('CC_EVENT_MAP_URLS',            'mapurls');

/**
* Request for Data Event: Can the user upload this type?
*
* Triggered when the system needs to know if the requested
* submit type is allowed for the current user.
*
* Call back (handler) prototype:
*<code>
* function OnUploadAllowed( &$submit_types )
*</code>
* @see CCSubmit::Submit()
* @see CCThrottle::OnUploadAllowed()
*/
define('CC_EVENT_UPLOAD_ALLOWED',      'throttle');

/**
* @deprecated
*/
define('CC_EVENT_CONTEST_ROW',         'contestrow' );

/**
* Request for Data Event: Get macro translations
* 
* A 'macro' in this context is a token that can be
* used for renaming or ID3 tagging a file. Macros
* are defined by modules that respond to this event
* and fill in the macro array with tokens (and 
* values if requested). 
*
* For example, if your module has a set of values 
* that might be useful in file renaming (like the
* frame rate of a video) then you could register
* for this event and expose a '%fps%' macro that
* allows admins to use that value whenever someone
* uploads a file.
*
* See the implementation of {@link CCFileRename::Rename()} for an example of 
* how to invoke this event.
*
* See the implemention of {@link CCContest::OnGetMacros()} for an example
* of how to handle the event and return the right data.
*
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_GET_MACROS',          'getmacros' );

/**
* Request for Data Event: Page is about to be rendered
*
* Triggered just before the page object does a merge
* with the environment variables and the current
* skin's page template. This allows modules to do
* any last moment tweaks to the page before display.
*
* Event handler prototype:
*<code>
* // $page is an instance of {@link CCPage}
*function OnRenderPage( &$page );
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_RENDER_PAGE',         'renderpage');

define('CC_EVENT_ADD_PAGE_FEED', 'addpagefeed');

/**#@+
* Request for Data Event: Phase of form processing.
*
* $form paramater is an insance of {@link CCForm}
* @see CCEvents::AddHandler()
*/
/** 
* Prototype:
*<code>
*function OnFormInit( &$form );
*</code>
*/
define('CC_EVENT_FORM_INIT',           'forminit' );

/** 
* Prototype:
*<code>
*function OnFormFields( &$form, &$form_fields );
*</code>
*/
define('CC_EVENT_FORM_FIELDS',         'formfields' );

/** 
* Prototype:
*<code>
*function OnFormExtraFields( &$form, &$form_extra_fields );
*</code>
*/
define('CC_EVENT_EXTRA_FORM_FIELDS',   'formfieldsex' );

/** 
* Prototype:
*<code>
*function OnFormPopulate( &$form, &$values);
*</code>
*/
define('CC_EVENT_FORM_POPULATE',       'formpopulate' );

/** 
* Prototype:
*<code>
*function OnFormVerify( &$form, &$is_verified );
*</code>
*/
define('CC_EVENT_FORM_VERIFY',         'formverify' );

/**#@-*/

/**
* Request for Data Event: Advanced search hook
*
* Triggered <i>before</i> the default search takes place. This
* gives a chance for modules to hook the search request and
* process it completely on their own.
*
* See the implementation of {@link CCReview::OnDoSearch()} for
* example of a search hook and replace.
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_SEARCH_META',            'srchmeta' );

/**
* Request for Data Event: Fields for Admin Settings Forms
*
* Triggered by {@link CCAdminConfigForm::CCAdminConfigForm()} 
* and {@link CCAdminSettingsForm::CCAdminSettingsForm()}
* when the system is populating the fields for either the
* Global Settings admin form or the Settings admin form for
* the current virtual root.
*
* This allows modules to store variables in $CC_GLOBALS 
* across sessions as well as allow admins to edit those
* values. See the implementation of {@link CCEditorials::OnGetConfigFields()}
* for an example of how this is done.
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_GET_CONFIG_FIELDS',   'getcfgflds' );

define('CC_EVENT_CONFIG_CHAGNED', 'cfgchanged');

/**
* Notification Event: Upload is about to be deleted
*
* N.B. Record could be in an unstable place as modules
* that respond to this event are deleting resources
* associated with the record along the way.
*
* Event call back (handler) prototype:
*<code>
* function OnUploadDelete( &$record );
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_DELETE_UPLOAD',       'delete' );

/**
* Notification Event: Physical file is about to be deleted
*
* Event call back (handler) prototype:
*<code>
* function OnUploadFile( &$file_id );
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_DELETE_FILE',         'deletefile' );

/**
* Notification Event: User record is about to be deleted
*
* N.B. Record could be in an unstable place as modules
* that respond to this event are deleting resources
* associated with the record along the way.
*
* Event call back (handler) prototype:
*<code>
* function OnUserDelete( $user_id );
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_USER_DELETED',         'userdel' );

/**
* Notification Request: Form fields for system paths
*
* Call back (handler) prototype:
*<code>
*function OnSysPaths( &$fields )
*</code>
* Where $fields should appened with an array with 
* the follow structure:
*
*<code>
$fields['my_sys_dir'] =
( 
  'label'       => '',    // string: form label
  'form_tip'   => '',    // string: help tip
  'value'      => '',    // string: default value
  'formatter'  => '',    // string: formatter/verifier (use 'sysdir' !!)
  'writable'   => true,  // boolean: true means check for writable
  'slash'      => true,  // boolean: true means add trailing fwd slash '/'
  'flags'      => CCFF_POPULATE | CCFF_REQUIRED 
                         // integer: drop the CCFF_REQUIRED if you code
                         // can handle it
);
*
</code>
* @see CCEvents::AddHandler()
*/ 
define('CC_EVENT_SYSPATHS',               'syspaths' );

/**
* Request for Data Event: api/query setup
*
* Triggered when caller has requested a query in a 
* format unknown to the default handler (phase 1).
*
* The respondant is reponsible for filling out (at least)
* the 'datasource' and 'dataview' properties and otherwise
* validate the query params
*
* Event handler prototype:
*<code>
*function OnApiQuerySetup( &$query_args, &$queryObj, $requiresValidation );
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_API_QUERY_SETUP',         'apiquerysetup');

/**
* Request for Data Event: api/query render
*
* Triggered when caller has requested a query in a 
* format unknown to the default handler (phase 2).
*
* The respondant can exit the session if the request is fullfilled
* or put the results and the mime type to return (if not 'html')
* into the last two parameters
*
* Event handler prototype:
*<code>
*function OnApiQueryFormat( &$records, $calling_args, &$result, &$result_mime );
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_API_QUERY_FORMAT',         'apiqueryformat');

/**
* Request for Data Event: Add tabs to user profile page
*
* Called when user profile page is being rendered
*
* Event handler prototype/example:
*<code>
*function OnUserProfileTabs(&$tabs)
*{
*    $tabs['reviews'] = array(
*        'text' => 'Reviews',
*        'help' => 'Reviews',
*        'tags' => "reviews", // this is appended to media/people/username/...
*        'access' => CC_DONT_CARE_LOGGED_IN,
*        'function' => 'url',
*        'user_cb' => array( 'CCReviews', 'UserReviewsTab' ), // callback handler
*        'user_cb_mod' => 'cchost_lib/ccextras/cc-reviews.inc',          // handler's module
*        );
*}
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_USER_PROFILE_TABS', 'utabs');


/**
* Notification Event: Upload moderated
*
* Event handler prototype:
*<code>
*function OnUploadedModerated($record)
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_UPLOAD_MODERATED', 'uploadmoderated');

/**
* Notification Event: User IP Banned
*
* Event handler prototype:
*<code>
*function OnUserIPBannded($record)
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_USER_IP_BANNED', 'useripbanned');



/**
* Notification Event: New User Registered 
*
* Event handler prototype:
*<code>
*function OnUserRegistered($fields,&$status)
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_USER_REGISTERED',      'userreg' );

/**
* Notification Event: User profile information changed
*
* Event handler prototype:
*<code>
*function OnUserProfiledChanged($user_id,&$row)
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_USER_PROFILE_CHANGED', 'userprof' );


/**
* Notification Event: User logged in
*
* Event handler prototype:
*<code>
*function OnLogin( $user_id )
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_LOGIN', 'userlogin' );


/**
* Request for Data Event: Login form is being invoked
*
* Event handler prototype:
*<code>
*function OnLoginForm(&$form)
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_LOGIN_FORM',           'loginform' );

/**
* Notification Event: User logged out
*
* Event handler prototype:
*<code>
*function OnLogout()
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_LOGOUT',               'logout' );

/**
* Notification Event: Lost Password
*
* Event handler prototype:
*<code>
*function OnLostPassword()
*</code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_LOST_PASSWORD', 'lostpassword' );

/**
* Notification Event: Trackback(s) approved
*
* Event call back (handler) prototype:
*<code>
* function OnTrackbacksApproved( &$trackback_info );
*</code>
* 
* where 'trackback_info' is an array:
*
*    $trackback_info['pool_tree_parent'] = $upload_id;
*    $trackback_info['pool_tree_pool_child'] = $pool_item_id;
*
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_TRACKBACKS_APPROVED',  'trackbacksapp' );


/**#@+
* @access private
*/
define('CC_EVENT_TRANSLATE',            'translate' );
/**#@-*/

?>
