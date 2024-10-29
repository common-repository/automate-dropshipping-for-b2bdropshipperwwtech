<?php
class Featured_Image_By_URL{

	private static $instance;

	public static function instance() {
		if( ! isset( self::$instance ) && ! (self::$instance instanceof Featured_Image_By_URL ) ) {
			self::$instance = new Featured_Image_By_URL;
			self::$instance->setup_constants();

			self::$instance->includes();
			self::$instance->admin  = new Klockjatten_Featured_Image_By_URL_Admin();
			self::$instance->common = new Klockjatten_Featured_Image_By_URL_Common();

		}
		return self::$instance;	
	}
	
	private function setup_constants() {

	}

	private function includes() {
		require_once KLOCK_PLUGIN_DIR . 'includes/class-featured-image-by-url-admin.php';
		require_once KLOCK_PLUGIN_DIR . 'includes/class-featured-image-by-url-common.php';
	}	
}
?>