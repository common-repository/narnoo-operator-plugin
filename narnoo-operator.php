<?php
/*
Plugin Name: Narnoo Operator
Plugin URI: http://narnoo.com/
Description: Allows Wordpress users to manage and include their Narnoo media into their Wordpress site. You will need a Narnoo API key pair to include your Narnoo media. You can find this by logging into your account at Narnoo.com and going to Account -> View APPS.
Version: 2.2.2
Author: Narnoo Wordpress developer
Author URI: http://www.narnoo.com/
License: GPL2 or later
*/

/*  Copyright 2019  Narnoo.com  (email : info@narnoo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// plugin definitions
define( 'NARNOO_OPERATOR_PLUGIN_NAME', 'Narnoo Operator' );
define( 'NARNOO_OPERATOR_CURRENT_VERSION', '2.2.2' );
define( 'NARNOO_OPERATOR_I18N_DOMAIN', 'narnoo-operator' );

define( 'NARNOO_OPERATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NARNOO_OPERATOR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'NARNOO_OPERATOR_SETTINGS_PAGE', 'options-general.php?page=narnoo-operator-api-settings' );

// include files
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-helper.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-followers-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-images-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-brochures-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-videos-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-albums-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-products-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-operator-library-images-table.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'class-narnoo-distributor-attraction-template.php' );

//Load CMB2 for metabox creation and tab order
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/inti-cmb2.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/cmb2-tabs/inti.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'product-metabox-layout.php' );


//PHP VERSION 2.0
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/narnoo/authen.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/narnoo/http/WebClient.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/narnoo/operator.php' );

//Cache Php Fastcache
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/cache/phpfastcache.php' );

// NARNOO PHP SDK API //
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/narnooauthn.php' );
require_once( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/narnooapi.php' );

// begin!
new Narnoo_Operator();

class Narnoo_Operator {

	/**
	 * Plugin's main entry point.
	 **/
	function __construct() {
		register_uninstall_hook( __FILE__, array( 'NarnooOperator', 'uninstall' ) );
		add_action( 'init', array( &$this, 'create_custom_post_type' ) );


		if ( is_admin() ) {
			add_action( 'plugins_loaded', 						array( &$this, 'load_language_file' ) );
			add_filter( 'plugin_action_links', 					array( &$this, 'plugin_action_links' ), 10, 2 );

			add_action( 'admin_notices', 						array( &$this, 'display_reminders' ) );
			add_action( 'admin_menu', 							array( &$this, 'create_menus' ) );
			add_action( 'admin_init', 							array( &$this, 'admin_init' ) );

			add_filter( 'media_upload_tabs', 					array( &$this, 'add_narnoo_library_menu_tab' ) );
			add_action( 'media_upload_narnoo_library', 			array( &$this, 'media_narnoo_library_menu_handle') );

			add_action( 'wp_ajax_narnoo_operator_api_request', 	array( 'Narnoo_Operator_Helper', 'ajax_api_request' ) );


			//Meta Boxes
			add_action('add_meta_boxes', 		array( &$this, 'add_noo_album_meta_box'));
			add_action( 'save_post', 			array( &$this, 'save_noo_album_meta_box'));
			add_action('add_meta_boxes',		array( &$this, 'add_noo_video_meta_box'));
			add_action( 'save_post', 			array( &$this, 'save_noo_video_meta_box'));
			/*add_action('add_meta_boxes', 		array( &$this, 'add_noo_print_meta_box'));
			add_action( 'save_post', 			array( &$this, 'save_noo_print_meta_box'));*/
			

		} else {
			
			add_filter( 'widget_text', 					'do_shortcode' );
		}

	}

	/**
	 * Register custom post types for Narnoo Products.
	 **/
	function create_custom_post_type() {

		register_post_type(
				'narnoo_product',
				array(
					'label' => 'Products',
					'labels' => array(
						'singular_name' => 'Product',
					),
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'product' ),
					'description' => "Custom post type for imported products from Narnoo",
					'public' => true,
					'exclude_from_search' => true,
					'has_archive' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'show_in_menu' => true, /* 'product_import_page', */
					'show_in_nav_menus'	=> TRUE,
                    'menu_position' => 13,
					'menu_icon' => 'dashicons-tickets-alt',
					'show_in_admin_bar' => true,
					'supports' => array( 'title', 'excerpt', 'thumbnail', 'editor', 'author', 'revisions', 'page-attributes' ),
				)
			);

		flush_rewrite_rules();

	}

	/**
	 * Add Narnoo Library tab to Wordpress media upload menu.
	 **/
	function add_narnoo_library_menu_tab( $tabs ) {
		$newTab = array( 'narnoo_library' => __( 'Narnoo Library', NARNOO_OPERATOR_I18N_DOMAIN ) );
		return array_merge($tabs, $newTab);
	}

	/**
	 * Handle display of Narnoo library in Wordpress media upload menu.
	 **/
	function media_narnoo_library_menu_handle() {
		return wp_iframe( array( &$this, 'media_narnoo_library_menu_display' ) );
	}

	function media_narnoo_library_menu_display() {
		media_upload_header();
		$narnoo_operator_library_images_table = new Narnoo_Operator_Library_Images_Table();
		?>
			<form id="narnoo-images-form" class="media-upload-form" method="post" action="">
				<?php
				$narnoo_operator_library_images_table->prepare_items();
				$narnoo_operator_library_images_table->display();
				?>
			</form>
		<?php
	}

	/**
	 * Clean up upon plugin uninstall.
	 **/
	static function uninstall() {
		unregister_setting( 'narnoo_operator_settings', 'narnoo_operator_settings', array( &$this, 'settings_sanitize' ) );
	}

	/**
	 * Add settings link for this plugin to Wordpress 'Installed plugins' page.
	 **/
	function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname(__FILE__) . '/narnoo-operator.php' ) ) {
			$links[] = '<a href="' . NARNOO_OPERATOR_SETTINGS_PAGE . '">' . __('Settings') . '</a>';
		}

		return $links;
	}

	/**
	 * Load language file upon plugin init (for future extension, if any)
	 **/
	function load_language_file() {
		load_plugin_textdomain( NARNOO_OPERATOR_I18N_DOMAIN, false, NARNOO_OPERATOR_PLUGIN_PATH . 'languages/' );
	}

	/**
	 * Display reminder to key in API keys in admin backend.
	 **/
	function display_reminders() {
		$options = get_option( 'narnoo_operator_settings' );

		if ( empty( $options['access_key'] ) || empty( $options['secret_key'] ) ) {
			Narnoo_Operator_Helper::show_notification(
				sprintf(
					__( '<strong>Reminder:</strong> Please key in your Narnoo API settings in the <strong><a href="%s">Settings->Narnoo API</a></strong> page.', NARNOO_OPERATOR_I18N_DOMAIN ),
					NARNOO_OPERATOR_SETTINGS_PAGE
				)
			);
		}
	}

	/**
	 * Add admin menus and submenus to backend.
	 **/
	function create_menus() {
		// add Narnoo API to settings menu
		add_options_page(
			__( 'Narnoo API Settings', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Narnoo API', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-api-settings',
			array( &$this, 'api_settings_page' )
		);

		// add main Narnoo Media menu
		add_menu_page(
			__( 'Narnoo Media', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Narnoo', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-followers',
			array( &$this, 'narnoo_page' ),
			NARNOO_OPERATOR_PLUGIN_URL . 'images/icon-16.png',
			11
		);

		// add main Narnoo Imports menu
		/* add_menu_page(
			__( 'Product', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Product', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options', 
			'product_import_page', 
			array( &$this, 'product_import_page' ),   
			NARNOO_OPERATOR_PLUGIN_URL . 'images/icon-16.png', 
			12
		); */


		$page = add_submenu_page(
			'narnoo-operator-followers',
			__( 'Narnoo Media - Albums', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Albums', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-albums',
			array( &$this, 'albums_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Operator_Albums_Table', 'add_screen_options' ) );

		$page = add_submenu_page(
			'narnoo-operator-followers',
			__( 'Narnoo Media - Images', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Images', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-images',
			array( &$this, 'images_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Operator_Images_Table', 'add_screen_options' ) );

		$page = add_submenu_page(
			'narnoo-operator-followers',
			__( 'Narnoo Media - Print', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Print', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-brochures',
			array( &$this, 'brochures_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Operator_Brochures_Table', 'add_screen_options' ) );

		$page = add_submenu_page(
			'narnoo-operator-followers',
			__( 'Narnoo Media - Videos', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Videos', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-videos',
			array( &$this, 'videos_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Operator_Videos_Table', 'add_screen_options' ) );

		$page = add_submenu_page(
			'narnoo-operator-followers',
			__( 'Narnoo Media - Products', NARNOO_OPERATOR_I18N_DOMAIN ),
			__( 'Products', NARNOO_OPERATOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-operator-products',
			array( &$this, 'products_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Operator_Products_Table', 'add_screen_options' ) );
		
	}

	/**
	 * Upon admin init, register plugin settings and Narnoo shortcodes button, and define input fields for API settings.
	 **/
	function admin_init() {
		register_setting( 'narnoo_operator_settings', 'narnoo_operator_settings', array( &$this, 'settings_sanitize' ) );
		if( isset( $_REQUEST['narnoo_section'] ) && $_REQUEST['narnoo_section'] == 'webhook' ) {

			add_settings_section(
				'api_settings_section_webhook',
				__( 'Webhook', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_section' ),
				'narnoo_operator_api_settings'
			);

			add_settings_field(
				'webhook_is_enable',
				__( 'Enable Webhook', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_is_enable' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_url',
				__( 'Webhook URL', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_url' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_secret',
				__( 'Webhook Secret', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_secret' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_follow_operator',
				__( 'Follow Operator', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_follow_operator' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_unfollow_operator',
				__( 'Unfollow Operator', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_unfollow_operator' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_create_product',
				__( 'Create Product', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_create_product' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_update_product',
				__( 'Update Product', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_update_product' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

			add_settings_field(
				'webhook_delete_product',
				__( 'Delete Product', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_webhook_delete_product' ),
				'narnoo_operator_api_settings',
				'api_settings_section_webhook'
			);

		} else {
			
			add_settings_section(
				'api_settings_section',
				__( 'API Settings', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_api_section' ),
				'narnoo_operator_api_settings'
			);

			add_settings_field(
				'access_key',
				__( 'Acesss key', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_access_key' ),
				'narnoo_operator_api_settings',
				'api_settings_section'
			);

			add_settings_field(
				'secret_key',
				__( 'Secret key', NARNOO_OPERATOR_I18N_DOMAIN ),
				array( &$this, 'settings_secret_key' ),
				'narnoo_operator_api_settings',
				'api_settings_section'
			);

		}
		// register Narnoo shortcode button and MCE plugin
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option('rich_editing') == 'true' ) {
			//add_filter( 'mce_external_plugins', array( &$this, 'add_shortcode_plugin' ) );
			//add_filter( 'mce_buttons', array( &$this, 'register_shortcode_button' ) );
		}
	}

	function settings_webhook_section() {
		echo '<p>' . __( 'Webhooks can only be registered via domains with SSL certificates.', NARNOO_OPERATOR_I18N_DOMAIN ) . '</p>';
	}

	function settings_webhook_is_enable() {
		$options = get_option('narnoo_operator_settings');

        $html = '<input type="checkbox" id="checkbox_operator" name="narnoo_operator_settings[webhook_is_enable]" value="1"' . checked( 1, $options['webhook_is_enable'], false ) . '/>';
	    $html .= '<label for="checkbox_operator">'.__('Enable Webhook', NARNOO_OPERATOR_I18N_DOMAIN).'</label>';
	    $html .= '<script>';
	    if( !$options['webhook_is_enable'] ) {
	    	$html .= 'jQuery("document").ready(function() {';
	    	$html .= '  jQuery("#webhook_url").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_secret").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_follow_operator").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_unfollow_operator").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_create_product").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_update_product").parents("tr").hide();';
	    	$html .= '  jQuery("#webhook_delete_product").parents("tr").hide();';
	    	$html .= '});';
	    }
	    $html .= '	jQuery(document).on("click", "#checkbox_operator", function() {';
	    $html .= '		if( jQuery(this).prop("checked") == true ){';
	    $html .= '			jQuery("#webhook_url").parents("tr").show();';
	    $html .= '			jQuery("#webhook_secret").parents("tr").show();';
		$html .= '  		jQuery("#webhook_follow_operator").parents("tr").show();';
		$html .= '  		jQuery("#webhook_unfollow_operator").parents("tr").show();';
		$html .= '  		jQuery("#webhook_create_product").parents("tr").show();';
		$html .= '  		jQuery("#webhook_update_product").parents("tr").show();';
		$html .= '  		jQuery("#webhook_delete_product").parents("tr").show();';
	    $html .= '		} else {';
	    $html .= '			jQuery("#webhook_url").parents("tr").hide();';
	    $html .= '			jQuery("#webhook_secret").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_follow_operator").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_unfollow_operator").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_create_product").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_update_product").parents("tr").hide();';
		$html .= '  		jQuery("#webhook_delete_product").parents("tr").hide();';
	    $html .= '		}';
	    $html .= '	})';
	    $html .= '</script>';

	    echo $html;
	}

	function settings_webhook_url() {
		$options = get_option( 'narnoo_operator_settings' );
		$siteurl = get_site_url( get_current_blog_id() );
		$url = !empty(esc_attr($options['webhook_url'])) ? esc_attr($options['webhook_url']) : $siteurl . '/wp?narnoo_hook=' . md5($siteurl) . rand(9,999);
		echo "<input id='webhook_url' name='narnoo_operator_settings[webhook_url]' size='40' type='text' value='" . $url . "' />";
	}

	function settings_webhook_secret() {
		$options = get_option( 'narnoo_operator_settings' );
		echo "<input id='webhook_secret' name='narnoo_operator_settings[webhook_secret]' size='40' type='text' value='" . esc_attr($options['webhook_secret']). "' />";
	}

	function settings_webhook_follow_operator() {
		$options = get_option( 'narnoo_operator_settings' );
		$html = '<input type="checkbox" id="webhook_follow_operator" name="narnoo_operator_settings[webhook_follow_operator]" value="1"' . checked( 1, $options['webhook_follow_operator'], false ) . '/>';
	    $html .= '<label for="webhook_follow_operator">'.__('Enable Follow Operator', NARNOO_OPERATOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_unfollow_operator() {
		$options = get_option( 'narnoo_operator_settings' );
		$html = '<input type="checkbox" id="webhook_unfollow_operator" name="narnoo_operator_settings[webhook_unfollow_operator]" value="1"' . checked( 1, $options['webhook_unfollow_operator'], false ) . '/>';
	    $html .= '<label for="webhook_unfollow_operator">'.__('Enable Unfollow Operator', NARNOO_OPERATOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_create_product() {
		$options = get_option( 'narnoo_operator_settings' );
		$html = '<input type="checkbox" id="webhook_create_product" name="narnoo_operator_settings[webhook_create_product]" value="1"' . checked( 1, $options['webhook_create_product'], false ) . '/>';
	    $html .= '<label for="webhook_create_product">'.__('Enable Create Product', NARNOO_OPERATOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_update_product() {
		$options = get_option( 'narnoo_operator_settings' );
		$html = '<input type="checkbox" id="webhook_update_product" name="narnoo_operator_settings[webhook_update_product]" value="1"' . checked( 1, $options['webhook_update_product'], false ) . '/>';
	    $html .= '<label for="webhook_update_product">'.__('Enable Update Product', NARNOO_OPERATOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_webhook_delete_product() {
		$options = get_option( 'narnoo_operator_settings' );
		$html = '<input type="checkbox" id="webhook_delete_product" name="narnoo_operator_settings[webhook_delete_product]" value="1"' . checked( 1, $options['webhook_delete_product'], false ) . '/>';
	    $html .= '<label for="webhook_delete_product">'.__('Enable Delete Product', NARNOO_OPERATOR_I18N_DOMAIN).'</label>';
	    echo $html;
	}

	function settings_api_section() {
		echo '<p>' . __( 'You can edit your Narnoo API settings below.', NARNOO_OPERATOR_I18N_DOMAIN ) . '</p>';
	}

	function settings_access_key() {
		$options = get_option( 'narnoo_operator_settings' );
		echo "<input id='access_key' name='narnoo_operator_settings[access_key]' size='40' type='text' value='" . esc_attr($options['access_key']). "' />";
	}

	function settings_secret_key() {
		$options = get_option( 'narnoo_operator_settings' );
		echo "<input id='secret_key' name='narnoo_operator_settings[secret_key]' size='40' type='text' value='" . esc_attr($options['secret_key']). "' />";
	}


	/**
	 * Sanitize input settings.
	 **/
	function settings_sanitize( $input ) {
		$option = get_option( 'narnoo_operator_settings' );

		if( !empty($input['access_key']) || !empty($input['secret_key']) ) {

			$new_input['access_key'] 				= trim( $input['access_key'] );
			$new_input['secret_key'] 				= trim( $input['secret_key'] );
			$new_input['webhook_is_enable'] 		= isset( $option['webhook_is_enable'] ) ? $option['webhook_is_enable'] : '';
	        $new_input['webhook_url']				= isset( $option['webhook_url'] ) ? $option['webhook_url'] : '';
	       	$new_input['webhook_secret']			= isset( $option['webhook_secret'] ) ? $option['webhook_secret'] : '';
	       	$new_input['webhook_follow_operator'] 	= isset( $input['webhook_follow_operator'] ) ? $option['webhook_follow_operator'] : '';
	       	$new_input['webhook_unfollow_operator'] = isset( $input['webhook_unfollow_operator'] ) ? $option['webhook_unfollow_operator'] : '';
	       	$new_input['webhook_create_product'] 	= isset( $input['webhook_create_product'] ) ? $option['webhook_create_product'] : '';
	       	$new_input['webhook_update_product'] 	= isset( $input['webhook_update_product'] ) ? $option['webhook_update_product'] : '';
	       	$new_input['webhook_delete_product'] 	= isset( $input['webhook_delete_product'] ) ? $option['webhook_delete_product'] : '';
	       	$new_input['webhook_response']			= isset( $input['webhook_response'] ) ? $option['webhook_response'] : '';

		} else if( $input['webhook_is_enable'] ) {

			$request = Narnoo_Operator_Helper::init_api( "new" );
			$api_token = get_option( 'narnoo_api_token' );

			$data = array();
			$data['action'] = array();
			if( $input['webhook_follow_operator'] ) 	{ $data['action'][] = "follow.operator"; }
	       	if( $input['webhook_unfollow_operator'] ) 	{ $data['action'][] = "unfollow.operator"; }
	       	if( $input['webhook_create_product'] ) 		{ $data['action'][] = "create.product"; }
	       	if( $input['webhook_update_product'] ) 		{ $data['action'][] = "update.product"; }
	       	if( $input['webhook_delete_product'] ) 		{ $data['action'][] = "delete.product"; }

	       	$body = array();
	       	$webhook = empty($option['webhook_response']) ? '' : json_decode($option['webhook_response'], true);
	       	$webhook_secret = ( isset($webhook['data']['key']) && !empty($webhook['data']['key']) ) ? $webhook['data']['key'] : '';
	       	$webhook_mode = '';
			if( isset($webhook['data']['id']) && !empty($webhook['data']['id']) ) {

	       		// for update webhook
	       		$webhook_mode = 'update';
	       		$data['webhookId'] = $webhook['data']['id'];
			    $response = wp_remote_post( 'https://apis.narnoo.com/api/v1/webhook/update', array(
						'method' => 'POST',
						'headers' => array( "Authorization" => "bearer " . $api_token, "Content-Type" => "application/json" ),
						'body' => json_encode($data)
					    )
					);
			    $body = json_decode( $response['body'], true );

			} else {

		       	// for create webhook.
		       	$webhook_mode = 'add';
		       	$data['url'] = $input['webhook_url'];
			    $response = wp_remote_post( 'https://apis.narnoo.com/api/v1/webhook/create', array(
						'method' => 'POST',
						'headers' => array( "Authorization" => "bearer " . $api_token, "Content-Type" => "application/json" ),
						'body' => json_encode($data)
					    )
					);
			    $body = json_decode( $response['body'], true );
			    $webhook_secret = ( isset($body['data']['key']) && !empty($body['data']['key']) ) ? $body['data']['key'] : '';
		    
			}
		
			if ( is_wp_error( $response ) ) {
				
				$new_input = $option;
			    $error_message = $response->get_error_message();

			} else if( isset($body['success']) && $body['success'] == true ) {
			  
				$new_input['access_key'] 		= isset( $option['access_key'] ) ? $option['access_key'] : '';
				$new_input['secret_key'] 		= isset( $option['secret_key'] ) ? $option['secret_key'] : '';
		        //$new_input['token_key'] 		= isset( $option['token_key'] ) ? $option['token_key'] : '';
		        $new_input['operator_import']   = isset( $option['operator_import'] ) ? $option['operator_import'] : '';

		        $new_input['webhook_is_enable'] 		= trim( $input['webhook_is_enable'] );
		        $new_input['webhook_url']				= trim( $input['webhook_url'] );
		       	$new_input['webhook_secret']			= trim( $webhook_secret );
		       	$new_input['webhook_follow_operator'] 	= trim( $input['webhook_follow_operator'] );
		       	$new_input['webhook_unfollow_operator'] = trim( $input['webhook_unfollow_operator'] );
		       	$new_input['webhook_create_product'] 	= trim( $input['webhook_create_product'] );
		       	$new_input['webhook_update_product'] 	= trim( $input['webhook_update_product'] );
		       	$new_input['webhook_delete_product'] 	= trim( $input['webhook_delete_product'] );
		       	$new_input['webhook_response']			= ( $webhook_mode == 'add' ) ? $response['body'] : $option['webhook_response'];
		    
			} else {
			    
				$new_input = $option;
				
				if( isset( $body['message'] ) && !empty( $body['message'] ) ) {
    				add_settings_error(
                        'webhook',
                        esc_attr( 'settings_updated' ),
                        $body['message'],
                        'error'
                    );
				}
				
			}

		} else {

			$webhook = empty($option['webhook_response']) ? '' : json_decode($option['webhook_response'], true);
			if( isset($webhook['data']['id']) && !empty($webhook['data']['id']) ) {
				$api_token = get_option( 'narnoo_api_token' );
				$webhook_url = 'https://apis.narnoo.com/api/v1/webhook/delete';

				$response = wp_remote_post( $webhook_url, array(
					'method' => 'POST',
					'headers' => array( "Authorization" => "bearer " . $api_token, "Content-Type" => "application/json" ),
					'body' => json_encode( array( "webhookId" => $webhook['data']['id'] ) )
				    )
				);
			}

			$option['webhook_is_enable'] 		= trim( $input['webhook_is_enable'] );
	        $option['webhook_url']				= '';
	       	$option['webhook_secret']			= '';
	       	$option['webhook_follow_operator'] 	= '0';
	       	$option['webhook_unfollow_operator'] = '0';
	       	$option['webhook_create_product'] 	= '0';
	       	$option['webhook_update_product'] 	= '0';
	       	$option['webhook_delete_product'] 	= '0';
	       	$option['webhook_response']			= '';

			$new_input = $option;

		}
		
		return $new_input;
	}

	/**
	 * Display API settings page.
	 **/
	function api_settings_page() {
		$current_tab = (isset($_REQUEST['narnoo_section']) && $_REQUEST['narnoo_section']!='') ? $_REQUEST['narnoo_section'] : 'general';
		$section = array( 'general'=>'General', 'webhook'=>'Webhook' );
		$function_name = '';
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo API Settings', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h2>
			<nav class="nav-tab-wrapper">
		        <?php 
		        foreach( $section as $tab_key => $tab_value ) {
			        $tab_class = 'nav-tab ';
			        $tab_class.= ($current_tab == $tab_key) ? 'nav-tab-active' : '';
			        $function_name.= ($current_tab == $tab_key) ? "narnoo_".$current_tab."_func" : '';
			        $tab_url = admin_url( NARNOO_OPERATOR_SETTINGS_PAGE.'&amp;narnoo_section='.$tab_key );
			        echo '<a href="'.$tab_url.'" class="'.$tab_class.'">'.$tab_value.'</a>';
			    }
			    ?>
			</nav>
			<form action="options.php" method="post">
				<?php settings_fields( 'narnoo_operator_settings' ); ?>
				<?php do_settings_sections( 'narnoo_operator_api_settings' ); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
			<?php
			if(  method_exists ( $this, $function_name ) ) {
				$this->$function_name();	
			}
			?>
		</div>
		<?php
	}

	function narnoo_general_func(){
		$cache	 		= Narnoo_Operator_Helper::init_noo_cache();
		$request 		= Narnoo_Operator_Helper::init_api( "new" );

		$operator = null;
		if ( ! is_null( $request ) ) {
			//$operator = $cache->get('operator_details');
			if( empty( $operator ) ){
				try {
					$operator = $request->accountDetails();
					if(!empty( $operator->success ) ){
							//$cache->set('operator_details', $operator, 43200);
					}
				} catch ( Exception $ex ) {
					$operator = null;
					Narnoo_Operator_Helper::show_api_error( $ex );
				}
			}
		}

		if ( ! is_null( $operator ) ) {

			$op_category = get_option( 'narnoo_operator_category' );
			if(empty($op_category)){
				update_option( 'narnoo_operator_category', lcfirst( $operator->data->category ), '', 'yes' );
			}
			?>
			<h3><?php _e( 'Operator Details', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h3>
			<table class="form-table">
				<tr><th><?php _e( 'ID', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->id; ?></td></tr>
				<tr><th><?php _e( 'UserName', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->name; ?></td></tr>
				<tr><th><?php _e( 'Email', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->email; ?></td></tr>
				<tr><th><?php _e( 'Business Name', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->name; ?></td></tr>
				<tr><th><?php _e( 'Contact Name', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->contact; ?></td></tr>
				<tr><th><?php _e( 'Location', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->location; ?></td></tr>
				<tr><th><?php _e( 'Phone', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->phone; ?></td></tr>
				<tr><th><?php _e( 'URL', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->url; ?></td></tr>
				<tr><th><?php _e( 'Category', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->category; ?></td></tr>
				<tr><th><?php _e( 'Sub Category', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->subCategory; ?></td></tr>
				<tr><th><?php _e( 'Keywords', NARNOO_OPERATOR_I18N_DOMAIN ); ?></th><td><?php echo $operator->data->keywords; ?></td></tr>
			</table>
			<?php
		}
			
	}

	/**
	 * Display Narnoo Followers page.
	 **/
	function followers_page() {
		global $narnoo_operator_followers_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Media - Followers', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-followers-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_followers_table->get_pagenum() ) ) ); ?>">
				<?php
				$narnoo_operator_followers_table->prepare_items();
				$narnoo_operator_followers_table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Albums page.
	 **/
	function albums_page() {
		global $narnoo_operator_albums_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Media - Albums', NARNOO_OPERATOR_I18N_DOMAIN ) ?>
				<a href="?<?php echo build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_albums_table->get_pagenum(), 'action' => 'create' ) ); ?>" class="add-new-h2"><?php echo esc_html_x( 'Create New', NARNOO_OPERATOR_I18N_DOMAIN ); ?></a></h2>
			<form id="narnoo-albums-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_albums_table->get_pagenum(), 'album_page' => $narnoo_operator_albums_table->current_album_page, 'album' => $narnoo_operator_albums_table->current_album_id, 'album_name' => urlencode( $narnoo_operator_albums_table->current_album_name ) ) ) ); ?>">
			<?php
			if ( $narnoo_operator_albums_table->prepare_items() ) {
				?><h3>Currently viewing album: <?php echo $narnoo_operator_albums_table->current_album_name; ?></h3><?php
				_e( 'Select album:', NARNOO_OPERATOR_I18N_DOMAIN );
				echo $narnoo_operator_albums_table->select_album_html_script;
				submit_button( __( 'Go', NARNOO_OPERATOR_I18N_DOMAIN ), 'button-secondary action', false, false, array( 'id' => "album_select_button" ) );

				$narnoo_operator_albums_table->views();
				$narnoo_operator_albums_table->display();
			}
			?>
			</form>
		</div>
		<?php
	}


	/**
	 * Display Narnoo Images page.
	 **/
	function images_page() {
		global $narnoo_operator_images_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Media - Images', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-images-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_images_table->get_pagenum() ) ) ); ?>">
				<?php
				if ( $narnoo_operator_images_table->prepare_items() ) {
					$narnoo_operator_images_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Brochures page.
	 **/
	function brochures_page() {
		global $narnoo_operator_brochures_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Media - Print Material', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-brochures-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_brochures_table->get_pagenum() ) ) ); ?>">
				<?php
				if ( $narnoo_operator_brochures_table->prepare_items() ) {
					$narnoo_operator_brochures_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Videos page.
	 **/
	function videos_page() {
		global $narnoo_operator_videos_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Media - Videos', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-videos-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_videos_table->get_pagenum() ) ) ); ?>">
				<?php
				if ( $narnoo_operator_videos_table->prepare_items() ) {
					$narnoo_operator_videos_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 *
	 * Date Created: 14-09-16
	 * Display Narnoo products page.
	 **/
	function products_page() {
		global $narnoo_operator_products_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_OPERATOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Media - Products', NARNOO_OPERATOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-products-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_operator_products_table->get_pagenum() ) ) ); ?>">
				<?php
				if ( $narnoo_operator_products_table->prepare_items() ) {
					$narnoo_operator_products_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/*
	*
	*	title: Narnoo page to display help information
	*	date created: 15-09-16
	*/
	function narnoo_page(){
		ob_start();
		require( NARNOO_OPERATOR_PLUGIN_PATH . 'libs/html/help_info_tpl.php' );
		echo ob_get_clean();
	}

	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_album_meta_box()
	{
	   
	            add_meta_box(
	                'noo-album-box-class',      		// Unique ID
				    'Select Narnoo Album', 		 		    // Title
				    array( &$this,'box_display_album_information'),    // Callback function
				    array( 'page','reefs','narnoo_product' ),         					// Admin page (or post type)
				    'side',         					// Context
				    'low'         					// Priority
	             );
	        
	}

	/*
	*
	*	title: Display the album select box
	*	date created: 15-09-16
	*/
	function box_display_album_information( $post )
	{
	
	global $post;
    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_album_select_id',true);
    //$selected = isset( $values['noo_album_select_id'] ) ? esc_attr( $values['noo_album_select_id'] ) : '';

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'album_meta_box_nonce', 'box_display_album_information_nonce' );
	   
		$current_page 		      = 1;
		$cache	 				  = Narnoo_Operator_Helper::init_noo_cache();
		$request 				  = Narnoo_Operator_Helper::init_api( "new" );

		//Get Narnoo Ablums.....
		if ( ! is_null( $request ) ) {
			
			$list = $cache->get('albums_'.$current_page);

			if( empty($list) ){

					try {

						$list = $request->getAlbums( $current_page );
						if ( ! is_array( $list->data->albums ) ) {
							throw new Exception( sprintf( __( "Error retrieving albums. Unexpected format in response page #%d.", NARNOO_OPERATOR_I18N_DOMAIN ), $current_page ) );
						}

						if(!empty( $list->success ) ){
								$cache->set('albums_'.$current_page, $list, 43200);
						}

					} catch ( Exception $ex ) {
						Narnoo_Operator_Helper::show_api_error( $ex );
					} 		

			}

			//Check the total pages and run through each so we can build a bigger list of albums	
		
		}


    ?> <p>
        <label for="my_meta_box_select">Narnoo Album:</label>
        <select name="noo_album_select" id="noo_album_select">
        	<option value="">None</option>
            <?php foreach ($list->data->albums as $album) { ?>
            		<option value="<?php echo $album->id; ?>" <?php selected( $selected, $album->id ); ?>><?php echo ucwords( $album->title ); ?></option>
            <?php } ?>
        </select>
        <p><small><em>Select an album and this will be displayed the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_album_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	     
	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_album_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_album_information_nonce'], 'album_meta_box_nonce' ) ) return;
	     
	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_album_select'] ) ){
        	update_post_meta( $post_id, 'noo_album_select_id', esc_attr( $_POST['noo_album_select'] ) );
    	}

	}

	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_video_meta_box()
	{
	   
	            add_meta_box(
	                'noo-video-box-class',      		// Unique ID
				    'Enter Video Embed Code', 		 		    // Title
				    array( &$this,'box_display_video_information'),    // Callback function
				    array( 'page','reefs' ),         					// Admin page (or post type)
				    'side',         					// Context
				    'low'         					// Priority
	             );
	        
	}

	/*
	*
	*	title: Display the album select box
	*	date created: 15-09-16
	*/
	function box_display_video_information( $post )
	{
	
	global $post;
    $selected = get_post_meta($post->ID,'noo_video_id',true);

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'video_meta_box_nonce', 'box_display_video_information_nonce' );
	   


    ?> <p>
        <label for="video_box_text">Video Code:</label>
        <input type="text" name="noo_video_box_text" id="noo_video_box_text" value="<?php echo $selected; ?>" />
    </p>
        <p><small><em>Enter a video embed code to display a video on the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_video_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	     
	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_video_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_video_information_nonce'], 'video_meta_box_nonce' ) ) return;
	     
	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;
	    $allowedTags = array(
		    //formatting
		    'strong' => array(),
		    'em'     => array(),
		    'b'      => array(),
		    'i'      => array(),

		    //links
		    'a'     => array(
		        'href' => array()
		    )
		);
	    if( isset( $_POST['noo_video_box_text'] ) ){
        	update_post_meta( $post_id, 'noo_video_id', wp_kses( $_POST['noo_video_box_text'],$allowedTags ) );
    	}

	}



	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_print_meta_box()
	{
	   
	            add_meta_box(
	                'noo-print-box-class',      		// Unique ID
				    'Enter Narnoo Print ID', 		 		    // Title
				    array( &$this,'box_display_print_information'),    // Callback function
				    array( 'page','reefs' ),         					// Admin page (or post type)
				    'side',         					// Context
				    'low'         					// Priority
	             );
	        
	}

	/*
	*
	*	title: Display the print select box
	*	date created: 15-09-16
	*/
	function box_display_print_information( $post )
	{
	
	global $post;
    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_print_id',true);
    //$selected = isset( $values['noo_album_select_id'] ) ? esc_attr( $values['noo_album_select_id'] ) : '';

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'print_meta_box_nonce', 'box_display_print_information_nonce' );
	   


    ?> <p>
        <label for="print_box_text">Narnoo Print Item:</label>
        <input type="text" name="noo_print_box_text" id="noo_print_box_text" value="<?php echo $selected; ?>" />
    </p>
        <p><small><em>Enter a print ID to display a PDF on the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_print_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	     
	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_print_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_print_information_nonce'], 'print_meta_box_nonce' ) ) return;
	     
	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_print_box_text'] ) ){
        	update_post_meta( $post_id, 'noo_print_id', wp_kses( $_POST['noo_print_box_text'] ) );
    	}

	}

}



/*
 * Add webhook
 */
add_action('init', 'narnoo_webhook');

function narnoo_webhook() {
	global $wpdb;
	$narnoo_hook = md5( get_site_url( get_current_blog_id() ) );
	if( isset($_REQUEST['narnoo_hook']) and !empty($_REQUEST['narnoo_hook']) /*&& $_REQUEST['narnoo_hook'] == $narnoo_hook */ ) {

		$narnoo_headers = getallheaders();
		$narnoo_key = isset($narnoo_headers['Narnoo-Signature']) ? $narnoo_headers['Narnoo-Signature'] : '';
		$option = get_option( 'narnoo_operator_settings' );
       	$webhook = empty($option['webhook_response']) ? '' : json_decode($option['webhook_response'], true);

		if( isset($webhook['data']['key']) && !empty($webhook['data']['key']) && $webhook['data']['key'] == $narnoo_key) {
		
			$data     = json_decode( file_get_contents('php://input'), true );

			$file    = __DIR__.'/kp_log_error_log.txt';    
	        $content = json_encode($data);
	        $file_content = "=============== Write At => " . date( "y-m-d H:i:s" ) . " =============== \r\n";
	        $file_content .= $content . "\r\n\r\n";
	        file_put_contents( $file, $file_content, FILE_APPEND | LOCK_EX );

			$action   = $data['action'];
			$op_id    = $data['businessId'];
			$data_ids = $data['data'];

			$post_ids = '';
			if( in_array( $action, array( 'update.product', 'delete.product' ) ) ) {
				foreach ( $data_ids as $narnoo_product_id ) {
					$narnoo_product_args = array(
					    'post_type' => 'narnoo_product',
					    'meta_query' => array(
					   		array(
					   			'key' => 'narnoo_product_id',
					   			'value' => $narnoo_product_id,
					   			'compare' => '='
					   		)
					    )
					);
					$narnoo_product_query = new WP_Query( $narnoo_product_args ); 
					while( $narnoo_product_query->have_posts() ) { 
					    $narnoo_product_query->the_post(); 
					    global $post;
					    $post_ids[$narnoo_product_id] = $post->ID;
					}
				}
			}

			switch ($action) {
				case 'unfollow.operator':
				case 'follow.operator':
					break;

				case 'create.product':
					if( !empty($data_ids) ) {
						foreach ($data_ids as $productId) {
							narnoo_update_product( $productId, $op_id, $action );
						}
					}
					break;

				case 'update.product':
					if( !empty($data_ids) ) {
						foreach ($data_ids as $productId) {
						    $auto_upate = get_post_meta( $post_id[$productId], 'narnoo_product_remove_auto_update', true );
						    if( !$auto_upate ) {
								narnoo_update_product( $productId, $op_id, $action );
							}
						}
					}
					break;

				case 'delete.product':
					if( !empty($post_ids) ) {
						foreach ($post_ids as $post_id) {
							wp_delete_post( $post_id );
						}
					}
					break;
				
				default:
					# code...
					break;
			}
		}
	    echo 'success';
	    die;
	}
}

// Update product for narnoo operator plugin.
function narnoo_update_product( $productId, $op_id, $action ) {
    $user_ID        = get_current_user_id();
    $productDetails = '';

    if( class_exists ( 'Narnoo_Operator_Connect_Helper' ) ) {
	    $request        = Narnoo_Operator_Connect_Helper::init_api( "new" );

	    // Fetch operator data
	    $operator_data  = $request->getBusinessListing( $op_id ); //UPDATED THIS LINE OF CODE
	    if(empty($operator_data)){ die("error notice"); }
	    $operator       = $operator_data->data;
	    $operatorPostId = Narnoo_Operator_Connect_Helper::get_post_id_for_imported_operator_id($op_id);
	    
	    // Fetch operator product data
	    $productDetails = $request->getProductDetails( $productId, $op_id );
	}

    if(!empty($productDetails) || !empty($productDetails->success)){
        $postData = Narnoo_Operator_Connect_Helper::get_post_id_for_imported_product_id( $productDetails->data->productId );

        if ( !empty( $postData['id'] ) && $postData['status'] !== 'trash' && $action == 'update.product' ) {
        
            $post_id = $postData['id'];
            // update existing post, ensuring parent is correctly set
            $update_post_fields = array(
                'ID'            => $post_id,
                'post_title'    => $productDetails->data->title,
                'post_type'     => 'narnoo_product',
                'post_status'   => 'publish',
                'post_author'   => $user_ID,
                'post_modified' => date('Y-m-d H:i:s')
            );

            if(!empty($productDetails->data->description->description)){
                foreach ($productDetails->data->description->description as $text) {
                    if( !empty( $text->english->text ) ){
                        $update_post_fields['post_content'] = $text->english->text;
                    }
                }
            }

            if(!empty($productDetails->data->description->summary)){
                foreach ($productDetails->data->description->summary as $text) {
                    if( !empty( $text->english->text ) ){
                        $update_post_fields['post_excerpt'] = strip_tags( $text->english->text );
                    }
                }
            }

            wp_update_post($update_post_fields);
            
            if(!empty($productDetails->data->description->description)){
                foreach ($productDetails->data->description->description as $text) {
                    if( !empty( $text->english->text ) ){
                        update_post_meta( $post_id, 'product_description', $text->english->text);
                    }
                }

            }

            if(!empty($productDetails->data->description->summary)){
                foreach ($productDetails->data->description->summary as $text) {
                    if( !empty( $text->english->text ) ){
                         update_post_meta( $post_id, 'product_excerpt',  strip_tags( $text->english->text ));
                    }
                }
            }
                            
            // set a feature image for this post but first check to see if a feature is present
            $feature = get_the_post_thumbnail($post_id);
            if(empty($feature)){
                if( !empty( $productDetails->data->featureImage->xxlargeImage ) ){
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $url = "https:" . $productDetails->data->featureImage->xxlargeImage;
                    $desc = $productDetails->data->title . " product image";
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $feature_image  = media_sideload_image($url, $post_id, $desc, 'src');
                    if(!empty($feature_image)){
                        global $wpdb;
                        $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                        set_post_thumbnail( $post_id, $attachment[0] );
                    }
                }
            }
            //$response['msg'] = "Successfully re-imported product details";

        } else if( $action == 'create.product' ) {
                    
            //create new post with operator details
            $new_post_fields = array(
                'post_title'        => $productDetails->data->title,
                'post_status'       => 'publish',
                'post_date'         => date('Y-m-d H:i:s'),
                'post_author'       => $user_ID,
                'post_type'         => 'narnoo_product',
                'comment_status'    => 'closed',
                'ping_status'       => 'closed'
            );

            if(!empty($productDetails->data->description->description)){
                foreach ($productDetails->data->description->description as $text) {
                    if( !empty( $text->english->text ) ){
                       $new_post_fields['post_content'] = $text->english->text;
                    }
                }
            }


            if(!empty($productDetails->data->description->summary)){
                foreach ($productDetails->data->description->summary as $text) {
                    if( !empty( $text->english->text ) ){
                         $new_post_fields['post_excerpt'] = strip_tags( $text->english->text );
                    }
                }
            }
           
            $post_id = wp_insert_post($new_post_fields);
            
            // set a feature image for this post
            if( !empty( $productDetails->data->featureImage->xxlargeImage ) ){
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $url = "https:" . $productDetails->data->featureImage->xxlargeImage;
                $desc = $productDetails->data->title . " product image";
                $feature_image  = media_sideload_image($url, $post_id, $desc, 'id');
                if(!empty($feature_image)){
                    set_post_thumbnail( $post_id, $feature_image );
                }
            }
            
            //$response['msg'] = "Successfully imported product details";
        }

        // insert/update custom fields with operator details into post
        if(!empty($productDetails->data->primary)){
            update_post_meta($post_id, 'primary_product',               "Primary Product");
        }else{
            update_post_meta($post_id, 'primary_product',               "Product");
        }
         
        update_post_meta($post_id, 'narnoo_operator_imported',      true);

        update_post_meta($post_id, 'narnoo_operator_id',            $op_id); 
        update_post_meta($post_id, 'narnoo_operator_name',          $operator->profile->name);
        update_post_meta($post_id, 'parent_post_id',                $operatorPostId);
        update_post_meta($post_id, 'narnoo_booking_id',             $productDetails->data->bookingId);  
        update_post_meta($post_id, 'narnoo_product_id',             $productDetails->data->productId);
        update_post_meta($post_id, 'product_min_price',             $productDetails->data->minPrice);
        update_post_meta($post_id, 'product_avg_price',             $productDetails->data->avgPrice);
        update_post_meta($post_id, 'product_max_price',             $productDetails->data->maxPrice);
        update_post_meta($post_id, 'narnoo_product_primary',        $productDetails->data->primary);
        update_post_meta($post_id, 'product_booking_link',          $productDetails->data->directBooking);
        
        update_post_meta($post_id, 'narnoo_listing_category',       $operator->profile->category);
        update_post_meta($post_id, 'narnoo_listing_subcategory',    $operator->profile->subCategory);

        if( lcfirst( $operator->profile->category ) == 'attraction' ){

            update_post_meta($post_id, 'narnoo_product_duration',   $productDetails->data->additionalInformation->operatingHours);
            update_post_meta($post_id, 'narnoo_product_start_time', $productDetails->data->additionalInformation->startTime);
            update_post_meta($post_id, 'narnoo_product_end_time',   $productDetails->data->additionalInformation->endTime);
            update_post_meta($post_id, 'narnoo_product_transport',  $productDetails->data->additionalInformation->transfer);
            update_post_meta($post_id, 'narnoo_product_purchase',   $productDetails->data->additionalInformation->purchases);
            update_post_meta($post_id, 'narnoo_product_health',     $productDetails->data->additionalInformation->fitness);
            update_post_meta($post_id, 'narnoo_product_packing',    $productDetails->data->additionalInformation->packing);
            update_post_meta($post_id, 'narnoo_product_children',   $productDetails->data->additionalInformation->child);
            update_post_meta($post_id, 'narnoo_product_additional', $productDetails->data->additionalInformation->additional);
            update_post_meta($post_id, 'narnoo_product_terms',      $productDetails->data->additionalInformation->terms);
            
        }
        /**
        *
        *   Import the gallery images as JSON encoded object
        *
        */
        if(!empty($productDetails->data->gallery)){
            update_post_meta($post_id, 'narnoo_product_gallery', json_encode($productDetails->data->gallery) );
        }else{
            delete_post_meta($post_id, 'narnoo_product_gallery');
        }
        /**
        *
        *   Import the video player object
        *
        */
        if(!empty($productDetail->datas->featureVideo)){
            update_post_meta($post_id, 'narnoo_product_video', json_encode($productDetails->data->featureVideo) );
        }else{
            delete_post_meta($post_id, 'narnoo_product_video');
        }
        /**
        *
        *   Import the brochure object
        *
        */
        if(!empty($productDetails->data->featurePrint)){   

            update_post_meta($post_id, 'narnoo_product_print', json_encode($productDetails->data->featurePrint) );
        }else{

            delete_post_meta($post_id, 'narnoo_product_print');
        }
        
    } //if success*/
}


/*
 * Add the extra options to the 'Publish' box
 */
add_action('post_submitbox_misc_actions', 'add_narnoo_product_publish_meta_options');

function add_narnoo_product_publish_meta_options($post_obj) {
  	global $post;
  	$post_type = 'narnoo_product'; // If you want a specific post type
 	$value = get_post_meta($post_obj->ID, 'narnoo_product_remove_auto_update', true); // If saving value to post_meta
 
  	if($post_type==$post->post_type) {
    	echo  '<div class="misc-pub-section misc-pub-section-last">'
         .'<label><input type="checkbox"' . (!empty($value) ? ' checked="checked" ' : null) . ' value="1" name="check_meta" /> Remove Auto Update From Narnoo </label>'
         .'</div>';
  	}
}
 

/*
 * Init extra_publish_options_save() on save_post action
 */
add_action( 'save_post', 'narnoo_product_extra_publish_options_save', 10 , 3);

function narnoo_product_extra_publish_options_save($post_id, $post, $update) {
 
  	$post_type = 'narnoo_product'; // If using specific post type
  	if ( $post_type != $post->post_type ) { return; }
 
  	if ( wp_is_post_revision( $post_id ) ) { return; }
 
  	if(isset($_POST['check_meta']) && $_POST['check_meta'] == 1) { // Checkbox value is 1 if set
    	update_post_meta($post_id, 'narnoo_product_remove_auto_update', $_POST['check_meta']);
  	} else {
  		update_post_meta($post_id, 'narnoo_product_remove_auto_update', $_POST['check_meta']); 
  	}
 
 
}
 