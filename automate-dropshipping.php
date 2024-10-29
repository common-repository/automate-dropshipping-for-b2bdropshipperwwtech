<?php /*Plugin Name: Automate Dropshipping for B2BDropshipper(WWTech)Description: Automate Dropshipping for B2BDropshipper(WWTech) plugin provides fully integration with woocommerce to automate import products and manage orders.Version: 3.0.7Author: Team MidriffAuthor URI: http://www.midriffinfosolution.org/Text Domain: klockjattenStable tag: 3.0.7*/define ( 'KLOCK_VERSION', '3.0.7' );if ( ! defined( 'ABSPATH' ) ) {	exit;}/** * Check if WooCommerce is active **/if( !function_exists('klockjatten_woocommerce_missing_wc_notice') ) {function klockjatten_woocommerce_missing_wc_notice() {	/* translators: 1. URL link. */	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Automate Dropshipping plugin requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';}}/* add plugin upgrade notification */add_action('in_plugin_update_message-automate-dropshipping-for-b2bdropshipperwwtech/automate-dropshipping.php', 'klockjattenUpgradeNotification', 10, 2);function klockjattenUpgradeNotification($data, $response){    /* check "upgrade_notice */	if( isset( $response->upgrade_notice ) ) {			printf(				'<span><strong style="color:#ff0000"> %s </strong></span>',				__( wp_strip_all_tags($response->upgrade_notice), 'klockjatten' )			);		}	}add_action( 'plugins_loaded', 'klockjatten_alert_init' );if( !function_exists('klockjatten_alert_init') ) {function klockjatten_alert_init() {	if ( ! class_exists( 'WooCommerce' ) ) {		add_action( 'admin_notices', 'klockjatten_woocommerce_missing_wc_notice' );		return;	}}}class Klockjatten { 	function __construct() {		register_activation_hook(__FILE__, array($this, 'klock_activate') );	    register_deactivation_hook( __FILE__, array($this, 'klock_deactivate') );	    register_uninstall_hook( __FILE__, 'klock_uninstall');		add_action('admin_enqueue_scripts', array($this, 'klock_add_css_files_for_backend'));		add_action('admin_menu', array($this, 'klock_my_menu_pages') );				add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'klock_add_plugin_page_settings_link') );		add_action('wp_ajax_klock_add_new_products_cron', array($this,'klock_add_new_products_cron'));		add_action('wp_ajax_nopriv_klock_add_new_products_cron', array($this, 'klock_add_new_products_cron') );				add_action('wp_ajax_klock_manage_removed_product', array($this,'klock_update_dropshipping_products'));		add_action('wp_ajax_nopriv_klock_manage_removed_product', array($this, 'klock_update_dropshipping_products') );			}	function klock_activate(){		/* Activation Hook */	}		function klock_deactivate(){		/* Deactivation Hook */	}	function klock_uninstall(){		/* Deletion Hook */	}	function klock_my_menu_pages(){	    add_menu_page('Automate B2B', 'Automate B2B', 'manage_options', 'klockjatten-menu', array($this, 'klock_setting_page'),'dashicons-cart', 25);	    add_submenu_page('klockjatten-menu', 'API Settings', 'API Settings', 'manage_options', 'klockjatten-menu', array($this, 'klock_setting_page') );		add_submenu_page('klockjatten-menu', 'Brand Names', 'Brand Names', 'manage_options','brand-name-menu',array($this, 'klock_brand_name_page') );	    add_submenu_page('klockjatten-menu', 'Payment', 'Payment', 'manage_options','payment-setting',array($this, 'klock_payment_option_callback') );	   	    add_submenu_page('klockjatten-menu', 'General', 'General Settings', 'manage_options','klock-general',array($this, 'klock_general_create_admin_page') );	   	    add_submenu_page('klockjatten-menu', 'Help', 'Help', 'manage_options','klock-help',array($this, 'klock_help_guide_callback') );	   	}    function klock_help_guide_callback(){		require_once ("automate-help.php");	}	/*** Include css files for wp admin ***/	function klock_add_css_files_for_backend() {		global $pagenow;		 wp_enqueue_script("jquery");		 wp_enqueue_style( 'backend-css', plugins_url( 'assets/css/backend.css', __FILE__ ) ); 		 wp_localize_script( 'klockjatten-script', 'ajax_object', array(          'ajaxurl' => admin_url( 'admin-ajax.php' ),        ));		wp_enqueue_style( 'font-awesome-css', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ) );		if($pagenow =='admin.php' && $_GET['page']=='brand-name-menu'){		  wp_enqueue_style( 'select2-css', plugins_url( 'assets/css/select2.min.css', __FILE__ ) );		  wp_enqueue_script( 'select2-js', plugins_url( 'assets/js/select2.min.js', __FILE__ ) );		}    }	function klock_setting_page() {         $classSettings = new KlockjattenSettings();        $classSettings->klock_add_setting_options();       		}	function klock_general_create_admin_page() { 		$general = new Klock_general();		$general->klock_create_general_page();	      		}	function klock_brand_name_page() {     	$classBrand = new KlockjattenBrand();	   	$classBrand->klock_add_brand(); 	   		}	function klock_payment_option_callback() { 		$klock_payment = new Klock_payment();		$klock_payment->klock_create_payment_page();	 	}	function klock_add_new_products_cron(){		if(isset($_REQUEST['action'])){			if($_REQUEST['action']=='klock_add_new_products_cron'){				$classProduct = new KlockjattenProduct();	         	$classProduct->klock_add_product(); 			}		}	}	function klock_add_plugin_page_settings_link( $links ) {		$links[] = '<a href="' .			admin_url( 'admin.php?page=klockjatten-menu' ) .			'">' . __('Settings') . '</a>';		return $links;	}		function klock_update_dropshipping_products(){?>	 <div>	 <span class="klock_load_process"></span><i> ( Note : Please do not refresh untill process is done.)</i>	 </div>	 <script src="<?= plugins_url('/assets/js/jquery-3.5.1.min.js',__FILE__);  ?>"></script>		<script>		 $(function(){			 		    run_klock_upload_ajax_on_demand_2(); 			function run_klock_upload_ajax_on_demand_2(){								var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";				jQuery.ajax({					  url:      ajaxurl,					  data:    ({action  : 'klock_upload_brand_product_on_demand_2'}),					  success: function(data){							var data = JSON.parse(data);							  console.log(data);							jQuery(".klock_load_process").text('Product Updating ... ');							if(data.status == 'done'){								jQuery(".klock_load_process").text('Process Completed.');								jQuery(".klock_load_process").css("color", "#11a42f");								jQuery(".klock_loader").hide();							}else{								run_klock_upload_ajax_on_demand_2();							}					  }				});							}					 });							</script>	<?php }}require_once ("automate-functions.php");$klock_obj = new Klockjatten();require_once ("settings.php");require_once ("general-settings.php");require_once ("payment-page.php");require_once ("brand-name.php");require_once ("products.php");/***************************************************Feature Image By Dropship Url******************************************************/// Plugin folder Path.	if( ! defined( 'KLOCK_PLUGIN_DIR' ) ){	define( 'KLOCK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );}// Plugin folder URL.if( ! defined( 'KLOCK_PLUGIN_URL' ) ){	define( 'KLOCK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );}// Plugin root file.if( ! defined( 'KLOCK_PLUGIN_FILE' ) ){	define( 'KLOCK_PLUGIN_FILE', __FILE__ );}// Optionsif( ! defined( 'KLOCK_OPTIONS' ) ){	define( 'KLOCK_OPTIONS', 'klock_options' );}// gallary meta keyif( ! defined( 'KLOCK_WCGALLARY' ) ){	define( 'KLOCK_WCGALLARY', '_klock_wcgallary' );}require_once KLOCK_PLUGIN_DIR . 'class-featured-image-init.php';function klock_run_klockjatten() {	return Featured_Image_By_URL::instance();}// Get Featured_Image_By_URL Running.$GLOBALS['klock'] = klock_run_klockjatten();?>