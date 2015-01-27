-- phpMyAdmin SQL Dump
-- version 2.9.1.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 03, 2008 at 03:23 AM
-- Server version: 5.0.27
-- PHP Version: 5.2.1
-- 
-- Database: 'cchost'
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_activity_log'
-- 

CREATE TABLE cc_tbl_activity_log (
  activity_log_id int(11) unsigned NOT NULL auto_increment,
  activity_log_event varchar(255) NOT NULL default '',
  activity_log_date datetime default NULL,
  activity_log_user_name varchar(255) NOT NULL default '',
  activity_log_ip varchar(255) NOT NULL default '',
  activity_log_param_1 varchar(255) NOT NULL default '',
  activity_log_param_2 varchar(255) NOT NULL default '',
  activity_log_param_3 varchar(255) NOT NULL default '',
  PRIMARY KEY  (activity_log_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_cart'
-- 

CREATE TABLE cc_tbl_cart (
  cart_id int(11) unsigned NOT NULL auto_increment,
  cart_user int(11) unsigned NOT NULL default '0',
  cart_name varchar(255) NOT NULL default '',
  cart_desc text NOT NULL,
  cart_tags text NOT NULL,
  cart_type varchar(20) NOT NULL default '',
  cart_subtype varchar(20) NOT NULL default '',
  cart_rating int(4) NOT NULL default '0',
  cart_date datetime NOT NULL default '0000-00-00 00:00:00',
  cart_dynamic text NOT NULL,
  cart_num_items int(11) unsigned NOT NULL default '0',
  cart_num_plays int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (cart_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_cart_items'
-- 

CREATE TABLE cc_tbl_cart_items (
  cart_item_id int(11) NOT NULL auto_increment,
  cart_item_cart int(11) NOT NULL default '0',
  cart_item_upload int(11) NOT NULL default '0',
  cart_item_order int(4) NOT NULL default '0',
  PRIMARY KEY  (cart_item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_collab_uploads'
-- 

CREATE TABLE cc_tbl_collab_uploads (
  collab_upload_collab int(11) NOT NULL default '0',
  collab_upload_upload int(11) NOT NULL default '0',
  collab_upload_role varchar(20) NOT NULL default '',
  collab_upload_type varchar(100) NOT NULL,
  KEY collab_upload_collab (collab_upload_collab)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_collab_users'
-- 

CREATE TABLE cc_tbl_collab_users (
  collab_user_collab int(11) NOT NULL default '0',
  collab_user_user int(11) NOT NULL default '0',
  collab_user_role varchar(100) NOT NULL default '',
  collab_user_credit varchar(100) NOT NULL default '',
  collab_user_confirmed int(1) NOT NULL default '0',
  KEY collab_user_collab (collab_user_collab)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_collabs'
-- 

CREATE TABLE cc_tbl_collabs (
  collab_id int(11) NOT NULL auto_increment,
  collab_name varchar(255) NOT NULL default '',
  collab_desc text NOT NULL,
  collab_user int(11) NOT NULL default '0',
  collab_date datetime NOT NULL default '0000-00-00 00:00:00',
  collab_confirmed int(1) NOT NULL default '0',
  PRIMARY KEY  (collab_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_config'
-- 

CREATE TABLE cc_tbl_config (
  config_id int(11) unsigned NOT NULL auto_increment,
  config_type varchar(255) default NULL,
  config_scope varchar(40) default NULL,
  config_data mediumtext,
  PRIMARY KEY  (config_id),
  KEY config_type (config_type)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_contests'
-- 

CREATE TABLE cc_tbl_contests (
  contest_id int(11) unsigned NOT NULL auto_increment,
  contest_user int(11) unsigned NOT NULL default '0',
  contest_short_name varchar(255) NOT NULL default '',
  contest_friendly_name varchar(255) NOT NULL default '',
  contest_rules_file varchar(255) NOT NULL default '',
  contest_template varchar(255) NOT NULL default '',
  contest_bitmap varchar(255) NOT NULL default '',
  contest_description text NOT NULL,
  contest_open datetime NOT NULL default '0000-00-00 00:00:00',
  contest_deadline datetime NOT NULL default '0000-00-00 00:00:00',
  contest_created datetime NOT NULL default '0000-00-00 00:00:00',
  contest_auto_publish int(1) NOT NULL default '0',
  contest_publish int(1) NOT NULL default '0',
  contest_vote_online int(1) NOT NULL default '0',
  contest_vote_deadline datetime NOT NULL default '0000-00-00 00:00:00',
  contest_entries_accept datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (contest_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_files'
-- 

CREATE TABLE cc_tbl_files (
  file_id int(11) unsigned NOT NULL auto_increment,
  file_upload int(11) unsigned NOT NULL default '0',
  file_name mediumtext NOT NULL,
  file_nicname varchar(25) NOT NULL default '',
  file_format_info mediumtext NOT NULL,
  file_extra mediumtext NOT NULL,
  file_filesize int(20) unsigned NOT NULL default '0',
  file_order int(11) unsigned NOT NULL default '0',
  file_is_remote tinyint(3) unsigned NOT NULL default '0',
  file_num_download int(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (file_id),
  KEY file_order (file_upload,file_order)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_forum_groups'
-- 

CREATE TABLE cc_tbl_forum_groups (
  forum_group_id int(4) unsigned NOT NULL auto_increment,
  forum_group_name varchar(255) NOT NULL default '',
  forum_group_weight int(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (forum_group_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_forum_threads'
-- 

CREATE TABLE cc_tbl_forum_threads (
  forum_thread_id int(11) unsigned NOT NULL auto_increment,
  forum_thread_forum int(6) unsigned NOT NULL default '0',
  forum_thread_user int(11) unsigned NOT NULL default '0',
  forum_thread_oldest int(11) unsigned NOT NULL default '0',
  forum_thread_newest int(11) unsigned NOT NULL default '0',
  forum_thread_date datetime NOT NULL default '0000-00-00 00:00:00',
  forum_thread_extra mediumtext NOT NULL,
  forum_thread_sticky int(2) unsigned NOT NULL default '0',
  forum_thread_closed int(2) unsigned NOT NULL default '0',
  forum_thread_name mediumtext NOT NULL,
  PRIMARY KEY  (forum_thread_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_forums'
-- 

CREATE TABLE cc_tbl_forums (
  forum_id int(6) unsigned NOT NULL auto_increment,
  forum_post_access int(4) unsigned NOT NULL default '0',
  forum_read_access int(4) unsigned NOT NULL default '0',
  forum_weight int(4) unsigned NOT NULL default '0',
  forum_name varchar(255) NOT NULL default '',
  forum_description varchar(255) NOT NULL default '',
  forum_group int(4) NOT NULL default '0',
  PRIMARY KEY  (forum_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_keys'
-- 

CREATE TABLE cc_tbl_keys (
  keys_id int(11) unsigned NOT NULL auto_increment,
  keys_key varchar(255) default NULL,
  keys_ip varchar(40) default NULL,
  keys_time datetime default NULL,
  PRIMARY KEY  (keys_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_licenses'
-- 

CREATE TABLE cc_tbl_licenses (
  license_id varchar(255) NOT NULL default '',
  license_url mediumtext NOT NULL,
  license_name varchar(255) NOT NULL default '',
  license_jurisdiction varchar(255) NOT NULL default '',
  license_permits mediumtext NOT NULL,
  license_required mediumtext NOT NULL,
  license_prohibits mediumtext NOT NULL,
  license_logo varchar(255) NOT NULL default '',
  license_img_small mediumtext NOT NULL,
  license_img_big mediumtext NOT NULL,
  license_tag varchar(255) NOT NULL default '',
  license_strict int(4) NOT NULL default '0',
  license_text mediumtext NOT NULL,
  PRIMARY KEY  (license_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_notifications'
-- 

CREATE TABLE cc_tbl_notifications (
  notify_id int(11) unsigned NOT NULL auto_increment,
  notify_user int(11) unsigned NOT NULL default '0',
  notify_other_user int(11) unsigned NOT NULL default '0',
  notify_mask int(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (notify_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_pool_item'
-- 

CREATE TABLE cc_tbl_pool_item (
  pool_item_id int(5) unsigned NOT NULL auto_increment,
  pool_item_pool int(5) default NULL,
  pool_item_url mediumtext NOT NULL,
  pool_item_download_url mediumtext NOT NULL,
  pool_item_description mediumtext NOT NULL,
  pool_item_extra mediumtext NOT NULL,
  pool_item_license varchar(255) NOT NULL default '',
  pool_item_name varchar(255) NOT NULL default '',
  pool_item_artist varchar(255) NOT NULL default '',
  pool_item_approved tinyint(4) NOT NULL default '0',
  pool_item_timestamp int(30) default NULL,
  albumsku varchar(255) default NULL,
  pool_item_num_remixes int(6) unsigned default NULL,
  pool_item_num_sources int(6) unsigned default NULL,
  PRIMARY KEY  (pool_item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_pool_tree'
-- 

CREATE TABLE cc_tbl_pool_tree (
  pool_tree_id int(5) unsigned NOT NULL auto_increment,
  pool_tree_parent int(5) unsigned default NULL,
  pool_tree_child int(5) unsigned default NULL,
  pool_tree_pool_parent int(5) unsigned default NULL,
  pool_tree_pool_child int(5) unsigned default NULL,
  PRIMARY KEY  (pool_tree_id),
  KEY pool_tree_child (pool_tree_child,pool_tree_parent),
  KEY pool_tree_pool_parent (pool_tree_pool_parent)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_pools'
-- 

CREATE TABLE cc_tbl_pools (
  pool_id int(5) unsigned NOT NULL auto_increment,
  pool_name varchar(255) default NULL,
  pool_short_name varchar(50) default NULL,
  pool_description mediumtext NOT NULL,
  pool_api_url mediumtext NOT NULL,
  pool_site_url mediumtext NOT NULL,
  pool_ip varchar(10) NOT NULL default '',
  pool_banned tinyint(1) NOT NULL default '0',
  pool_search tinyint(1) NOT NULL default '0',
  pool_default_license varchar(50) NOT NULL default '',
  pool_auto_approve int(1) unsigned default NULL,
  PRIMARY KEY  (pool_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_ratings'
-- 

CREATE TABLE cc_tbl_ratings (
  ratings_id int(11) unsigned NOT NULL auto_increment,
  ratings_score int(11) NOT NULL default '0',
  ratings_upload int(11) NOT NULL default '0',
  ratings_user int(11) NOT NULL default '0',
  ratings_ip varchar(20) default NULL,
  PRIMARY KEY  (ratings_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_role_permissions'
-- 

CREATE TABLE cc_tbl_role_permissions (
  role_permssion_role int(5) NOT NULL,
  role_permission_actions varchar(255) NOT NULL,
  KEY role_permssion_role (role_permssion_role)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_role_users'
-- 

CREATE TABLE cc_tbl_role_users (
  role_users_user int(11) NOT NULL,
  role_users_role int(5) NOT NULL,
  role_users_scope varchar(40) NOT NULL,
  KEY role_users_user (role_users_user)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_roles'
-- 

CREATE TABLE cc_tbl_roles (
  role_id int(5) NOT NULL,
  role_role varchar(100) NOT NULL,
  PRIMARY KEY  (role_id),
  KEY role_role (role_role)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_tag_alias'
-- 

CREATE TABLE cc_tbl_tag_alias (
  tag_alias_tag varchar(50) NOT NULL default '',
  tag_alias_alias varchar(50) default NULL,
  PRIMARY KEY  (tag_alias_tag)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_tags'
-- 

CREATE TABLE cc_tbl_tags (
  tags_tag varchar(50) NOT NULL default '',
  tags_count int(11) unsigned NOT NULL default '0',
  tags_type int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (tags_tag)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_topic_i18n'
-- 

CREATE TABLE cc_tbl_topic_i18n (
  topic_i18n_topic int(11) unsigned default NULL,
  topic_i18n_xlat_topic int(11) unsigned default NULL,
  topic_i18n_language varchar(100) NOT NULL default '',
  KEY topic_i18n_topic (topic_i18n_topic)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_topic_tree'
-- 

CREATE TABLE cc_tbl_topic_tree (
  topic_tree_id int(11) unsigned NOT NULL auto_increment,
  topic_tree_parent int(11) unsigned NOT NULL default '0',
  topic_tree_child int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (topic_tree_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_topics'
-- 

CREATE TABLE cc_tbl_topics (
  topic_id int(11) unsigned NOT NULL auto_increment,
  topic_upload int(11) unsigned NOT NULL default '0',
  topic_user int(11) unsigned NOT NULL default '0',
  topic_views int(11) unsigned NOT NULL default '0',
  topic_type varchar(100) NOT NULL default '',
  topic_date datetime NOT NULL default '0000-00-00 00:00:00',
  topic_edited datetime NOT NULL default '0000-00-00 00:00:00',
  topic_deleted int(2) unsigned NOT NULL default '0',
  topic_name mediumtext NOT NULL,
  topic_text mediumtext NOT NULL,
  topic_tags mediumtext NOT NULL,
  topic_forum int(6) unsigned default NULL,
  topic_thread int(11) unsigned default NULL,
  topic_locked int(2) unsigned NOT NULL default '0',
  topic_can_xlat int(1) unsigned NOT NULL default '0',
  topic_left int(11) NOT NULL,
  topic_right int(11) NOT NULL,
  PRIMARY KEY  (topic_id),
  KEY topic_upload (topic_upload),
  KEY topic_thread (topic_thread),
  KEY topic_user (topic_user)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_tree'
-- 

CREATE TABLE cc_tbl_tree (
  tree_id int(11) unsigned NOT NULL auto_increment,
  tree_parent int(11) unsigned NOT NULL default '0',
  tree_child int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (tree_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_uploads'
-- 

CREATE TABLE cc_tbl_uploads (
  upload_id int(11) unsigned NOT NULL auto_increment,
  upload_user int(11) unsigned NOT NULL default '0',
  upload_contest int(11) unsigned NOT NULL default '0',
  upload_name varchar(255) NOT NULL default '',
  upload_license varchar(255) NOT NULL default '',
  upload_config varchar(255) NOT NULL default '',
  upload_extra mediumtext NOT NULL,
  upload_tags mediumtext NOT NULL,
  upload_date datetime NOT NULL default '0000-00-00 00:00:00',
  upload_description mediumtext NOT NULL,
  upload_published int(1) unsigned NOT NULL default '0',
  upload_banned int(1) unsigned NOT NULL default '0',
  upload_topic_id int(11) unsigned NOT NULL default '0',
  upload_num_remixes int(7) unsigned NOT NULL default '0',
  upload_num_pool_remixes int(7) unsigned NOT NULL default '0',
  upload_num_sources int(7) unsigned NOT NULL default '0',
  upload_num_pool_sources int(7) unsigned NOT NULL default '0',
  upload_score int(11) unsigned NOT NULL default '0',
  upload_num_scores int(11) unsigned NOT NULL default '0',
  upload_last_edit datetime NOT NULL default '0000-00-00 00:00:00',
  upload_num_playlists int(5) unsigned NOT NULL default '0',
  upload_num_plays int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (upload_id),
  KEY upload_tags (upload_tags(300))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table 'cc_tbl_user'
-- 

CREATE TABLE cc_tbl_user (
  user_id int(11) unsigned NOT NULL auto_increment,
  user_name varchar(25) NOT NULL default '',
  user_real_name varchar(255) NOT NULL default '',
  user_password tinyblob NOT NULL,
  user_email tinytext NOT NULL,
  user_image varchar(255) NOT NULL default '',
  user_description mediumtext NOT NULL,
  user_homepage mediumtext NOT NULL,
  user_registered datetime NOT NULL default '0000-00-00 00:00:00',
  user_favorites mediumtext NOT NULL,
  user_whatilike mediumtext NOT NULL,
  user_whatido mediumtext NOT NULL,
  user_lookinfor mediumtext NOT NULL,
  user_extra mediumtext NOT NULL,
  user_last_known_ip varchar(25) NOT NULL default '',
  user_num_remixes int(7) unsigned NOT NULL default '0',
  user_num_remixed int(7) unsigned NOT NULL default '0',
  user_num_uploads int(7) unsigned NOT NULL default '0',
  user_score int(11) unsigned NOT NULL default '0',
  user_num_scores int(11) unsigned NOT NULL default '0',
  user_rank int(11) unsigned NOT NULL default '0',
  user_num_reviews int(7) unsigned NOT NULL default '0',
  user_num_reviewed int(7) unsigned NOT NULL default '0',
  user_num_posts int(11) unsigned NOT NULL default '0',
  user_language varchar(255) NOT NULL default '',
  user_quota int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_id),
  KEY user_name (user_name)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
