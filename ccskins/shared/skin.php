<?

$A['admin_menu_page']          = 'admin.php/print_admin_menu';
$A['comment_thread']           = 'file_macros.php/request_reviews';
$A['comment_thread_list']      = 'file_macros.php/print_recent_reviews';
$A['flash_player']             = 'util.php/empty';
$A['flat_grid_form_fields']    = 'form_fields.tpl/flat_grid_form_fields';
$A['format_sig']               = 'util.php/format_signature';
$A['hide_upload_form']         = 'util.php/hide_upload_form';
$A['horizontal_form_fields']   = 'form_fields.tpl/horizontal_form_fields';
$A['horizontal_form_fields']   = 'form_fields.tpl/horizontal_form_fields';
$A['html_form']                = 'html_form.php/html_form';
$A['license_choice']           = 'license_editing.tpl/license_choice';
$A['license_rdf']              = 'file_macros.php/license_rdf';
$A['multi_checkbox']           = 'form_fields.tpl/multi_checkbox';
$A['picks_links']              = 'picks.xml/picks_links';
$A['playlist_list_lines']      = 'playlist_2_audio.tpl';
$A['popular_tags']             = 'tags.xml/popular_tags';
$A['popup_background']         = 'popup_background.php';
$A['print_bread_crumbs']       = 'util.php/print_bread_crumbs';
$A['print_client_menu']        = 'util.php/print_client_menu';
$A['print_forms']              = 'util.php/print_forms';
$A['print_howididit_link']     = 'file_macros.php/print_howididit_link';
$A['print_html_content']       = 'util.php/print_html_content';
$A['print_prompts']            = 'util.php/print_prompts';
$A['ratings_stars_small_user'] = 'util.php/ratings_stars_small_user';
$A['ratings_stars_small']      = 'util.php/ratings_stars_small';
$A['ratings_stars_user']       = 'util.php/ratings_stars_user';
$A['ratings_stars']            = 'util.php/ratings_stars';
$A['recommends']               = 'util.php/recommends';
$A['render_link']              = 'render_link.tpl/render_play_link';
$A['search_results_all']       = 'search_results.tpl/search_results_all';
$A['search_results_head']      = 'search_results.tpl/search_results_head';
$A['show_nsfw']                = 'file_macros.php/show_nsfw';
$A['show_zip_dir']             = 'file_macros.php/show_zip_dir';
$A['tabs_id']                  = 'tabs';
$A['tags']                     = 'tags.php/tags';
$A['upload_banned']            = 'file_macros.php/upload_banned';
$A['upload_not_published']     = 'file_macros.php/upload_not_published';
$A['upload_page_div_layout']   = 'upload_page_pieces.tpl/upload_page_div_layout';
$A['upload_page_foot']         = 'upload_page_pieces.tpl/upload_page_foot';
$A['upload_page_head']         = 'upload_page_pieces.tpl/upload_page_head';
$A['upload_page_middle']       = 'upload_page_pieces.tpl/upload_page_middle';
$A['upload_page_sidebar']      = 'upload_page_pieces.tpl/upload_page_sidebar';
$A['upload_page_tbl_layout']   = 'upload_page_pieces.tpl/upload_page_tbl_layout';
$A['user_listings']            = 'user_list.tpl';
$A['user_listing']             = 'user_profile.tpl';

$A['script_links'][] = 'js/selector-addon-v1.js';

//$A['script_links'][] = 'js/scriptaculous/scriptaculous.js';
$A['script_links'][] = 'js/scriptaculous/builder.js';
$A['script_links'][] = 'js/scriptaculous/effects.js';
$A['script_links'][] = 'js/scriptaculous/controls.js';
$A['script_links'][] = 'js/scriptaculous/dragdrop.js';

$A['script_links'][] = url_args(ccl('docs/strings_js.php'),'ajax=1');
$A['script_links'][] = 'js/modalbox/modalbox.js';
$A['script_links'][] = 'js/cchost.js';

array_unshift($A['script_links'],'js/prototype.js');

$A['style_sheets'][] = 'js/modalbox/modalbox.css';
$A['style_sheets'][] = 'css/shared.css';
$A['style_sheets'][] = 'css/cc-format.css';
$A['style_sheets'][] = 'css/form.css';

?>
