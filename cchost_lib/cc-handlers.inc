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
* $Id: cc-handlers.inc 12719 2009-06-04 08:09:04Z fourstones $
*
*/

// this one needs to happen before the others
CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE, array( 'CCUserPage' , 'OnFilterUserProfile'), 'cchost_lib/cc-user-page.php'  );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,    array( 'CCAccess' , 'OnMapUrls'),  'cchost_lib/cc-access.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,  array( 'CCAccess' , 'OnAdminMenu'), 'cchost_lib/cc-access.php' );

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,         array( 'CCAdmin' , 'OnAdminMenu'), 'cchost_lib/cc-admin.php' );
CCEvents::AddHandler(CC_EVENT_MAIN_MENU,          array( 'CCAdmin' , 'OnBuildMenu'), 'cchost_lib/cc-admin.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCAdmin' , 'OnMapUrls'), 'cchost_lib/cc-admin.php' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCAdmin' , 'OnGetConfigFields'), 'cchost_lib/cc-admin.php' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS, array('CCRestAPI', 'OnMapUrls'), 'cchost_lib/cc-api.php' );

CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,        array( 'CCBanHV',  'OnUploadMenu'), 'cchost_lib/cc-ban.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCBan',  'OnMapUrls'), 'cchost_lib/cc-ban.inc' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,         array( 'CCBan',  'OnAdminMenu'), 'cchost_lib/cc-ban.inc'  );

CCEvents::AddHandler(CC_EVENT_GET_MACROS,    array( 'CCContest' , 'OnGetMacros'), 'cchost_lib/cc-contest.inc' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,            array( 'CCDatabaseAdmin', 'OnMapUrls'), 'cchost_lib/cc-db-admin.php' );

CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,        array( 'CCEditorials',  'OnUploadMenu'), 'cchost_lib/cc-editorials.php');
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCEditorials',  'OnMapUrls'), 'cchost_lib/cc-editorials.php');
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCEditorials' , 'OnGetConfigFields'), 'cchost_lib/cc-editorials.php' );
CCEvents::AddHandler(CC_EVENT_GET_SYSTAGS,           array( 'CCEditorials',  'OnGetSysTags'), 'cchost_lib/cc-editorials.php');
CCEvents::AddHandler(CC_EVENT_FILTER_ED_PICK,        array( 'CCEditorials',  'OnFilterEdPick'), 'cchost_lib/cc-editorials.php');
CCEvents::AddHandler(CC_EVENT_FILTER_ED_PICK_DETAIL, array( 'CCEditorials',  'OnFilterEdPickDetail'), 'cchost_lib/cc-editorials.php');

CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCFeed', 'OnGetConfigFields'), 'cchost_lib/cc-feed.php' );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,      array( 'CCFeed',  'OnUploadDelete'), 'cchost_lib/cc-feed.php');
CCEvents::AddHandler(CC_EVENT_DELETE_FILE,        array( 'CCFeed',  'OnDeleteFile'), 'cchost_lib/cc-feed.php');
CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,        array( 'CCFeed',  'OnUploadDone'), 'cchost_lib/cc-feed.php');

CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP,   array( 'CCFeedsAtom', 'OnApiQuerySetup'),'cchost_lib/cc-feeds-atom.php'); 
CCEvents::AddHandler(CC_EVENT_API_QUERY_FORMAT,   array( 'CCFeedsAtom', 'OnApiQueryFormat'),'cchost_lib/cc-feeds-atom.php'); 

CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP,   array( 'CCFeedsRSS',  'OnApiQuerySetup'), 'cchost_lib/cc-feeds-rss.php'); 
CCEvents::AddHandler(CC_EVENT_API_QUERY_FORMAT,   array( 'CCFeedsRSS',  'OnApiQueryFormat'), 'cchost_lib/cc-feeds-rss.php'); 

// this turns out to be order dependent and we want rss as the default
CCEvents::AddHandler(CC_EVENT_ADD_PAGE_FEED,        array( 'CCFeedsRSS',  'OnAddPageFeed'),     'cchost_lib/cc-feeds-rss.php'); 
CCEvents::AddHandler(CC_EVENT_ADD_PAGE_FEED,        array( 'CCFeedsAtom', 'OnAddPageFeed'),    'cchost_lib/cc-feeds-atom.php'); 

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,       array( 'CCFileRename', 'OnAdminMenu'),     'cchost_lib/cc-filerename.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,         array( 'CCFileRename', 'OnMapUrls'),       'cchost_lib/cc-filerename.php' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCPhysicalFile', 'OnMapUrls'), 'cchost_lib/cc-files.php');

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,   array( 'CCID3Tagger', 'OnAdminMenu'), 'cchost_lib/cc-filetagger.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCID3Tagger', 'OnMapUrls'), 'cchost_lib/cc-filetagger.php'  );

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,          array( 'CCFileVerify', 'OnAdminMenu'),  'cchost_lib/cc-fileverify.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,            array( 'CCFileVerify', 'OnMapUrls'),    'cchost_lib/cc-fileverify.php'  );
CCEvents::AddHandler(CC_EVENT_GET_SYSTAGS,         array( 'CCFileVerify', 'OnGetSysTags'), 'cchost_lib/cc-fileverify.php' );
CCEvents::AddHandler(CC_EVENT_GET_MACROS,          array( 'CCFileVerify', 'OnGetMacros'),  'cchost_lib/cc-fileverify.php' );

CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCGetID3' , 'OnGetConfigFields' ), 'cchost_lib/cc-getid3.php' );
CCEvents::AddHandler(CC_EVENT_SYSPATHS, array( 'CCGetID3' , 'OnSysPaths' ), 'cchost_lib/cc-getid3.php' );

CCEvents::AddHandler(CC_EVENT_APP_INIT, array( 'CCLanguage', 'OnInitApp'), 'cchost_lib/cc-language.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS, array( 'CCLanguageAdmin',  'OnMapUrls'), 'cchost_lib/cc-language.inc' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU, array( 'CCLanguageAdmin' , 'OnAdminMenu'), 'cchost_lib/cc-language.inc'  );

CCEvents::AddHandler(CC_EVENT_FILE_DONE,    array( 'CCLicense',  'OnFileDone'), 'cchost_lib/cc-license.inc' );
CCEvents::AddHandler(CC_EVENT_GET_MACROS,   array( 'CCLicense' , 'OnGetMacros'), 'cchost_lib/cc-license.inc' );
CCEvents::AddHandler(CC_EVENT_GET_SYSTAGS,  array( 'CCLicense',  'OnGetSysTags'), 'cchost_lib/cc-license.inc' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,   array( 'CCLicense',  'OnAdminMenu'), 'cchost_lib/cc-license.inc' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCLicense',  'OnMapUrls'), 'cchost_lib/cc-license.inc' );
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_PAGE,         array( 'CCLicenseHV', 'OnFilterUploadPage'),'cchost_lib/cc-license.php' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCLicWaiver', 'OnMapUrls'), 'cchost_lib/cc-lic-waiver-opts.php'  );

CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCLogin' , 'OnGetConfigFields'), 'cchost_lib/cc-login.php' );
CCEvents::AddHandler(CC_EVENT_MAIN_MENU,  array( 'CCLogin',  'OnBuildMenu'), 'cchost_lib/cc-login.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,   array( 'CCLogin',  'OnMapUrls'), 'cchost_lib/cc-login.php' );

CCEvents::AddHandler(CC_EVENT_GET_MACROS,   array( 'CCMacro' , 'OnGetMacros'), 'cchost_lib/cc-macro.php' );

CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,        array( 'CCMediaHost',  'OnUploadMenu'), 'cchost_lib/cc-mediahost.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCMediaHost',  'OnMapUrls'), 'cchost_lib/cc-mediahost.php' );
CCEvents::AddHandler(CC_EVENT_GET_MACROS,         array( 'CCMediaHost',  'OnGetMacros'), 'cchost_lib/cc-mediahost.php' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCMediaHost' , 'OnGetConfigFields'), 'cchost_lib/cc-mediahost.php'  );

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,   array( 'CCMenu', 'OnAdminMenu'), 'cchost_lib/cc-menu.php' );
CCEvents::AddHandler(CC_EVENT_MAIN_MENU,    array( 'CCMenu', 'OnBuildMenu'), 'cchost_lib/cc-menu.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCMenu', 'OnMapUrls'), 'cchost_lib/cc-menu.php' );

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,  array( 'CCNavigatorAdmin',  'OnAdminMenu'), 'cchost_lib/cc-navigator-admin.inc' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,    array( 'CCNavigatorAdmin',  'OnMapUrls'), 'cchost_lib/cc-navigator-admin.inc' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCPageAdmin', 'OnMapUrl'), 'cchost_lib/cc-page.php' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCPageAdmin', 'OnGetConfigFields'), 'cchost_lib/cc-page.php'  );
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP,   array( 'CCPageAdmin',  'OnApiQuerySetup'), 'cchost_lib/cc-page.php' ); 
CCEvents::AddHandler(CC_EVENT_API_QUERY_FORMAT,   array( 'CCPageAdmin',  'OnApiQueryFormat'), 'cchost_lib/cc-page.php' ); 
CCEvents::AddHandler(CC_EVENT_LOGOUT,    array( 'CCPageAdmin' , 'OnLogout'), 'cchost_lib/cc-page.php' );

CCEvents::AddHandler(CC_EVENT_SEARCH_META,          array( 'CCPool',  'OnSearchMeta'), 'cchost_lib/cc-pools.php'  ) ;
CCEvents::AddHandler(CC_EVENT_MAP_URLS,       array( 'CCPoolUI',  'OnMapUrls'), 'cchost_lib/cc-pools-ui.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,     array( 'CCPoolUI',  'OnAdminMenu'), 'cchost_lib/cc-pools-ui.php'  );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,  array( 'CCPoolUI',  'OnUploadDelete'), 'cchost_lib/cc-pools-ui.php' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCQuery',  'OnMapUrls'), 'cchost_lib/cc-query.php' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCQuery' , 'OnGetConfigFields'), 'cchost_lib/cc-query.php'  );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,  'cc_tcache_kill', 'cchost_lib/cc-query.php'   );
CCEvents::AddHandler(CC_EVENT_DELETE_FILE,    'cc_tcache_kill', 'cchost_lib/cc-query.php'   );
CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,    'cc_tcache_kill', 'cchost_lib/cc-query.php'   );


CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCRating',  'OnMapUrls'), 'cchost_lib/cc-ratings.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,         array( 'CCRating' , 'OnAdminMenu'), 'cchost_lib/cc-ratings.php'  );
CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,array( 'CCRating' , 'OnFilterUserProfile'), 'cchost_lib/cc-ratings.php'  );
CCEvents::AddHandler(CC_EVENT_USER_DELETED,       array( 'CCRating' , 'OnUserDelete'), 'cchost_lib/cc-ratings.php'  );

CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,  array( 'CCRemix', 'OnUploadDelete'), 'cchost_lib/cc-remix.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,       array( 'CCRemix' ,'OnMapUrls'), 'cchost_lib/cc-remix.php'  );

CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,     array( 'CCRenderAudio', 'OnUploadMenu'), 'cchost_lib/cc-renderaudio.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,        array( 'CCRenderAudio', 'OnMapUrls'), 'cchost_lib/cc-renderaudio.php' );
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP, array( 'CCRenderAudio', 'OnApiQuerySetup'), 'cchost_lib/cc-renderaudio.php' ); 
CCEvents::AddHandler(CC_EVENT_API_QUERY_FORMAT, array( 'CCRenderAudio', 'OnApiQueryFormat'), 'cchost_lib/cc-renderaudio.php' ); 

CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_PAGE,   array( 'CCRenderFlash', 'OnFilterUploads'), 'cchost_lib/cc-renderflash.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_LIST,   array( 'CCRenderFlash', 'OnFilterUploads'), 'cchost_lib/cc-renderflash.php' );

CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_PAGE,   array( 'CCRenderImage', 'OnFilterUploads'), 'cchost_lib/cc-renderimage.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_LIST,   array( 'CCRenderImage', 'OnFilterUploads'), 'cchost_lib/cc-renderimage.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,             array( 'CCRenderImage', 'OnMapUrls'), 'cchost_lib/cc-renderimage.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,           array( 'CCRenderImage' , 'OnAdminMenu' ), 'cchost_lib/cc-renderimage.php' );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,        array( 'CCRenderImage' , 'OnDeleteUpload' ), 'cchost_lib/cc-renderimage.php' );
CCEvents::AddHandler(CC_EVENT_DELETE_FILE,          array( 'CCRenderImage' , 'OnDeleteFile' ), 'cchost_lib/cc-renderimage.php' );

CCEvents::AddHandler(CC_EVENT_FILTER_MACROS,      array( 'CCRenderZip',  'OnFilterMacros'), 'cchost_lib/cc-renderzip.php'  );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,              array( 'CCSearch', 'OnMapUrls'), 'cchost_lib/cc-search.php'  );
CCEvents::AddHandler(CC_EVENT_FILTER_SEARCH_RESULTS, array( 'CCSearch', 'OnFilterSearch'), 'cchost_lib/cc-search.php'  );
CCEvents::AddHandler(CC_EVENT_SEARCH_META,          array( 'CCSearch',  'OnSearchMeta'), 'cchost_lib/cc-search.php'  ) ;
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,   array( 'CCSearch', 'OnGetConfigFields'), 'cchost_lib/cc-search.php' );

CCEvents::AddHandler(CC_EVENT_CONFIG_CHAGNED, 'skin_OnConfigChange', 'cchost_lib/cc-template.php' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,    array( 'CCSkinAdmin' , 'OnMapUrls'),  'cchost_lib/cc-skin-admin.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,  array( 'CCSkinAdmin' , 'OnAdminMenu'), 'cchost_lib/cc-skin-admin.php' );



CCEvents::AddHandler(CC_EVENT_MAIN_MENU,    array( 'CCSubmit',  'OnBuildMenu'), 'cchost_lib/cc-submit.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCSubmit',  'OnMapUrls'), 'cchost_lib/cc-submit.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,   array( 'CCSubmit', 'OnAdminMenu'), 'cchost_lib/cc-submit.php' );

CCEvents::AddHandler(CC_EVENT_RATED,  array( 'CCSync', 'OnRated'), 'cchost_lib/cc-sync.php');


CCEvents::AddHandler(CC_EVENT_MAIN_MENU,        array( 'CCTagEvents', 'OnBuildMenu'), 'cchost_lib/cc-tags.inc' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,         array( 'CCTagEvents', 'OnMapUrls'), 'cchost_lib/cc-tags.inc' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,       array( 'CCTagEvents', 'OnAdminMenu'), 'cchost_lib/cc-tags.inc' );
CCEvents::AddHandler(CC_EVENT_SOURCES_CHANGED,  array( 'CCTag', 'OnSourcesChanged'), 'cchost_lib/cc-tags.php'  );


CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,   array( 'CCTemplateAdmin', 'OnAdminMenu'), 'cchost_lib/cc-template.inc' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCTemplateAdmin', 'OnMapUrls'), 'cchost_lib/cc-template.inc' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCUserAdmin', 'OnMapUrls'), 'cchost_lib/cc-user-admin.php' );
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,   array( 'CCUserAdmin', 'OnAdminMenu'), 'cchost_lib/cc-user-admin.php' );

CCEvents::AddHandler(CC_EVENT_MAIN_MENU,          array( 'CCUserAPI', 'OnBuildMenu'), 'cchost_lib/cc-user.inc' );
CCEvents::AddHandler(CC_EVENT_PATCH_MENU,         array( 'CCUser', 'OnPatchMenu'), 'cchost_lib/cc-user.php' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCUserAPI', 'OnMapUrls'), 'cchost_lib/cc-user.inc' );
CCEvents::AddHandler(CC_EVENT_GET_MACROS,         array( 'CCUserAPI', 'OnGetMacros'), 'cchost_lib/cc-user.inc' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCUserAPI' , 'OnGetConfigFields'), 'cchost_lib/cc-user.inc'  );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCUserSearch', 'OnMapUrls'), 'cchost_lib/cc-user-search.php' );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCUserHook', 'OnMapUrls'), 'cchost_lib/cc-user-hook.php' );

CCEvents::AddHandler(CC_EVENT_FILTER_DOWNLOAD_URL, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_EXTRA, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_FILES, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_NUM_FILES, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_RATINGS_STARS, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_REMIXES_FULL, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_REMIXES_SHORT, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_MENU, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_TAGS, 'cc_filter_std', 'cchost_lib/cc-filters.php' );
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_USER_TAGS, 'cc_filter_std', 'cchost_lib/cc-filters.php' );


CCEvents::AddHandler(CC_EVENT_FILTER_POOL_ITEMS, 'cc_filter_pool_items', 'cchost_lib/cc-pool-filters.php' );



?>
