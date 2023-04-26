<?php
/*
	Plugin Name: JCCIB Comment Manager
	Plugin URI: http://www.nichi.com/
	Description: Comment Manager.
	Author: Chandan Chowdhury
	Version: 1.0.0
	Author URI: 
*/

define( 'COMMENT_MANAGER_VERSION', '1.0' );

define( 'COMMENT_MANAGER_WP_VERSION', '4.0' );

define( 'COMMENT_MANAGER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( 'COMMENT_MANAGER_PLUGIN_NAME', trim( dirname( COMMENT_MANAGER_PLUGIN_BASENAME ), '/' ) );

define( 'COMMENT_MANAGER_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

define( 'COMMENT_MANAGER_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'COMMENT_MANAGER_SHORTCODE', 'SHOW_COMMENT_FORM');

define( 'COMMENT_MANAGER_AGREEMENT_SHORTCODE', 'SHOW_COMMENT_AGREEMENT');

define( 'COMMENT_MANAGER_LISTING_SHORTCODE','SHOW_COMMENT_LIST');

define( 'COMMENT_MANAGER_DETAIL_SHORTCODE','SHOW_COMMENT_DETAIL');


function set_html_content_type() {
	return 'text/html';
}

 function force_ie8_meta(){
    echo '<meta http-equiv="X-UA-Compatible" content="IE=8" >';
}

function supress_admin_jccib(){
    global $current_user;
    get_currentuserinfo();
    if ( $current_user->user_login == 'jccibmember' ) {
        wp_safe_redirect('/');
    }
    

    if($current_user->user_login == 'jimukyoku'){
        add_filter( 'pre_site_transient_update_core', '__return_zero' );
        remove_action( 'wp_version_check', 'wp_version_check' );
        remove_action( 'admin_init', '_maybe_update_core' );        
    }
 }
// register admin_menu
function tt_add_menu_items(){
    add_menu_page('会員投稿確認画面', '会員投稿確認画面', 'edit_posts', 'tt_enquiry_test', 'tt_render_list_page');
    add_submenu_page('tt_enquiry_test', '理由テンプレート', '理由テンプレート', 'edit_posts', 'reject_reason_template','tt_render_template_page');
    add_submenu_page('tt_enquiry_test', '会員投稿設定', '会員投稿設定', 'edit_posts', 'admin-comment-settins','tt_render_settings_page');    
} 

if(is_admin())
{

    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/admin-comment-details.php";    

    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/admin-comment-settings.php";    
    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/admin-reject-template-settings.php";
    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/admin-comment-reject.php";    
    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/admin-comment-list.php";


    add_action('admin_head','force_ie8_meta');
    add_action('admin_init','supress_admin_jccib');
    add_action('admin_menu', 'tt_add_menu_items');

    
function comment_manager_install() {
   global $wpdb;

   $table_name = $wpdb->prefix . "contact_form";
      
   $sql = "CREATE TABLE `wp_jccib_contact_form` (
     `id`      mediumint(11) NOT NULL AUTO_INCREMENT,
     `name`    varchar(200) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
     `email`   varchar(200) NOT NULL,
     `mobile`  varchar(20)   NOT NULL,
     `subject` text CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
     `message` longtext CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
     `comnt_status` varchar(5) DEFAULT 'N',
     `file_upload` longtext,
     `reject_reason` longtext,
     `date_expire` date NOT NULL DEFAULT '0000-00-00',
     `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
     UNIQUE KEY `id` (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   add_option('enocp_user_settings',array());
   add_option('enocp_email_template',array());
}
    register_activation_hook( __FILE__, 'comment_manager_install' );
    
}else{

     //  after fixing tempaltes,
    // wp_enqueue_style("jccib-fine-uploader-style", plugins_url('/css/fineuploader.css', __FILE__));
    //wp_enqueue_script("jccib-fine-uploader", plugins_url('/js/fineuploader.jquery.js', __FILE__), array('jquery'));

    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/front-comment-list.php";
    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/front-comment-details.php";    
    require_once COMMENT_MANAGER_PLUGIN_DIR."/inc/front-comment-manage.php";    
}