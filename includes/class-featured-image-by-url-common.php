<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Klockjatten_Featured_Image_By_URL_Common {

	public function __construct() {
		add_action( 'init', array( $this, 'klockjatten_set_thumbnail_id_true' ) );
		if( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
			add_filter( 'wp_get_attachment_image_src', array( $this, 'klock_replace_attachment_image_src' ), 10, 4 );
		}
		/// Add WooCommerce Product listable Thumbnail Support for Woo 3.5 or greater
		add_action( 'admin_init', array( $this, 'klock_woo_thumb_support' ) );

		$options = get_option( KLOCK_OPTIONS );
		$resize_images = isset( $options['resize_images'] ) ? $options['resize_images']  : false;
		add_filter('woocommerce_product_get_image_id', array( $this, 'klock_woocommerce_36_support'), 99, 2);
	}

	/**
	 * Fix getting the correct url for product image.
	 *
	 * @return value
	 */
	 

	function klock_woocommerce_36_support( $value, $product){
		global $klock;
		$product_id = $product->get_id();
		if(!empty($product_id) && !empty($klock)){
			$post_type = get_post_type( $product_id );
			$image_data = $klock->admin->klock_get_image_meta( $product_id );
			if ( isset( $image_data['img_url'] ) && $image_data['img_url'] != '' ){
				return '_klock_fimage_url__' . $product_id;
			}
		}
		return $value;
	}

	function klockjatten_set_thumbnail_id_true(){
		global $klock;
		foreach ( $klock->admin->klockjatten_get_posttypes() as $post_type ) {
			add_filter( "get_{$post_type}_metadata", array( $this, 'klock_set_thumbnail_true' ), 10, 4 );
		}
	}

	function klock_set_thumbnail_true( $value, $object_id, $meta_key, $single ){

		global $klock;
		$post_type = get_post_type( $object_id );
		if( $this->klock_is_disallow_posttype( $post_type ) ){
			return $value;
		}
        
		if ( $meta_key == '_image_path' ){
			//echo get_post_meta($object_id,'_image_path',true);
		}
		if ( $meta_key == '_thumbnail_id' ){
			$image_data = $klock->admin->klock_get_image_meta( $object_id );
			
			if ( isset( $image_data['img_url'] ) && $image_data['img_url'] != '' ){
				if( $post_type == 'product_variation' ){
					if( !is_admin() ){
						return $object_id;
					}else{
						return $value;
					}
				}
				return true;
			}
		}
		return $value;
	}


	public function klock_resize_image_on_the_fly( $image_url, $size = 'full' ){
		if( $size == 'full' || empty( $image_url )){
			return $image_url;
		}

		if( !class_exists( 'Jetpack_PostImages' ) || !defined( 'JETPACK__VERSION' ) ){
			return $image_url;
		}

		/**
		 * Photon doesn't support query strings so we ignore image url with query string.
		 */
		$parsed = parse_url( $image_url );
		if( isset( $parsed['query'] ) && $parsed['query'] != '' ){
			return $image_url;
		}

		$image_size = $this->klock_get_image_size( $size );
		
		if( !empty( $image_size ) && !empty( $image_size['width'] ) ){
			$width = (int) $image_size['width'];
			$height = (int) $image_size['height'];

			if ( $width < 1 || $height < 1 ) {
				return $image_url;
			}

			// If WPCOM hosted image use native transformations
			$img_host = parse_url( $image_url, PHP_URL_HOST );
			if ( '.files.wordpress.com' == substr( $img_host, -20 ) ) {
				return add_query_arg( array( 'w' => $width, 'h' => $height, 'crop' => 1 ), set_url_scheme( $image_url ) );
			}

			// Use Photon magic
			if( function_exists( 'jetpack_photon_url' ) ) {
				if( isset( $image_size['crop'] ) && $image_size['crop'] == 1 ){
					return jetpack_photon_url( $image_url, array( 'resize' => "$width,$height" ) );
				}else{
					return jetpack_photon_url( $image_url, array( 'fit' => "$width,$height" ) );
				}
				
			}
			//$image_url = Jetpack_PostImages::fit_image_url ( $image_url, $image_size['width'], $image_size['height'] );
		}
		
		//return it.
		return $image_url;
	}


	function klock_get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	
	function klock_get_wcgallary_meta( $post_id ){
		
		$image_meta  = array();

		$gallary_images = get_post_meta( $post_id, KLOCK_WCGALLARY, true );
		
		if( !is_array( $gallary_images ) && $gallary_images != '' ){
			$gallary_images = explode( ',', $gallary_images );
			if( !empty( $gallary_images ) ){
				$gallarys = array();
				foreach ($gallary_images as $gallary_image ) {
					$gallary = array();
					$gallary['url'] = $gallary_image;
					$imagesizes = @getimagesize( $gallary_image );
					$gallary['width'] = isset( $imagesizes[0] ) ? $imagesizes[0] : '';
					$gallary['height'] = isset( $imagesizes[1] ) ? $imagesizes[1] : '';
					$gallarys[] = $gallary;
				}
				$gallary_images = $gallarys;
				update_post_meta( $post_id, KLOCK_WCGALLARY, $gallary_images );
				return $gallary_images;
			}
		}else{
			if( !empty( $gallary_images ) ){
				$need_update = false;
				foreach ($gallary_images as $key => $gallary_image ) {
					if( !isset( $gallary_image['width'] ) && isset( $gallary_image['url'] ) ){
						$imagesizes1 = @getimagesize( $gallary_image['url'] );
						$gallary_images[$key]['width'] = isset( $imagesizes1[0] ) ? $imagesizes1[0] : '';
						$gallary_images[$key]['height'] = isset( $imagesizes1[1] ) ? $imagesizes1[1] : '';
						$need_update = true;
					}
				}
				if( $need_update ){
					update_post_meta( $post_id, KLOCK_WCGALLARY, $gallary_images );
				}
				return $gallary_images;
			}	
		}
		
		
		return $gallary_images;
	}

	function klock_replace_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		global $klock;
		if($size == 'shop_catalog' || $size == 'shop_single' || $size == 'woocommerce_thumbnail'){
		$image_data = $klock->admin->klock_get_image_meta( get_the_ID(), true );
			if( !empty( $image_data['img_url'] ) ){

				$image_url = $image_data['img_url'];
				$width = isset( $image_data['width'] ) ? $image_data['width'] : '';
				$height = isset( $image_data['height'] ) ? $image_data['height'] : '';

				// Run Photon Resize Magic.
				if( apply_filters( 'klock_user_resized_images', true ) ){
					$image_url = $klock->common->klock_resize_image_on_the_fly( $image_url, $size );
				}

				$image_size = $klock->common->klock_get_image_size( $size );
				if ($image_url) {
		    	if( $image_size ){
		      		if( !isset( $image_size['crop'] ) ){
								$image_size['crop'] = '';
							}
							return array(
			                $image_url,
			                $image_size['width'],
			                $image_size['height'],
			                $image_size['crop'],
			            );
					}else{

						if( $width != '' && $height != '' ){
							return array( $image_url, $width, $height, false );
						}
						return array( $image_url, 800, 600, false );
					}
				}
			}		
		}
		if( false !== strpos( $attachment_id, '_klock_wcgallary' ) ){
			$attachment = explode( '__', $attachment_id );
			$image_num  = $attachment[1];
			$product_id = $attachment[2];
			if( $product_id > 0 ){
				
				$gallery_images = $klock->common->klock_get_wcgallary_meta( $product_id );
				if( !empty( $gallery_images ) ){
					if( !isset( $gallery_images[$image_num]['url'] ) ){
						return false;
					}
					$url = $gallery_images[$image_num]['url'];
					
					if( apply_filters( 'klock_user_resized_images', true ) ){
						$url = $klock->common->klock_resize_image_on_the_fly( $url, $size );	
					}
					$image_size = $klock->common->klock_get_image_size( $size );
					if ($url) {
						if( $image_size ){
							if( !isset( $image_size['crop'] ) ){
								$image_size['crop'] = '';
							}
							return array(
										$url,
										$image_size['width'],
										$image_size['height'],
										$image_size['crop'],
								);
						}else{
							if( $gallery_images[$image_num]['width'] != '' && $gallery_images[$image_num]['width'] > 0 ){
								return array( $url, $gallery_images[$image_num]['width'], $gallery_images[$image_num]['height'], false );
							}else{
								return array( $url, 800, 600, false );
							}
						}
					}
				}
			}
		}

		$is_product_image = ( false !== strpos( $attachment_id, '_klock_fimage_url' ) );
		$is_productvariation_image = ( is_numeric($attachment_id ) && $attachment_id > 0 && 'product_variation' == get_post_type( $attachment_id ) );
		if( $is_product_image || $is_productvariation_image ){

			$product_id = $attachment_id;
			if( $is_product_image ){
				$attachment = explode( '__', $attachment_id );
				$product_id  = $attachment[1];
			}

			$image_data = $klock->admin->klock_get_image_meta( $product_id, true );

			if( !empty( $image_data['img_url'] ) ){

				$image_url = $image_data['img_url'];
				$width = isset( $image_data['width'] ) ? $image_data['width'] : '';
				$height = isset( $image_data['height'] ) ? $image_data['height'] : '';

				// Run Photon Resize Magic.
				if( apply_filters( 'klock_user_resized_images', true ) ){
					$image_url = $klock->common->klock_resize_image_on_the_fly( $image_url, $size );
				}

				$image_size = $klock->common->klock_get_image_size( $size );
				if ($image_url) {
		    	if( $image_size ){
		      		if( !isset( $image_size['crop'] ) ){
								$image_size['crop'] = '';
							}
							return array(
			                $image_url,
			                $image_size['width'],
			                $image_size['height'],
			                $image_size['crop'],
			            );
					}else{
						if( $width != '' && $height != '' ){
							return array( $image_url, $width, $height, false );
						}
						return array( $image_url, 800, 600, false );
					}
				}
			}
		}
		return $image;
	}

	/**
	 * Get size information for a specific image size.
	 *
	 * @uses   get_image_sizes()
	 * @param  string $size The image size for which to retrieve data.
	 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
	 */
	function klock_get_image_size( $size ) {
		$sizes = $this->klock_get_image_sizes();

		if( is_array( $size ) ){
			$woo_size = array();
			$woo_size['width'] = $size[0];
			$woo_size['height'] = $size[1];
			return $woo_size;
		}
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
	}

	/**
	 * Get if Is current posttype is active to show featured image by url or not.
	 *
	 * @param  string $posttype Post type
	 * @return bool
	 */
	function klock_is_disallow_posttype( $posttype ) {

		$options = get_option( KLOCK_OPTIONS );
		$disabled_posttypes = isset( $options['disabled_posttypes'] ) ? $options['disabled_posttypes']  : array();

		return in_array( $posttype, $disabled_posttypes );
	}

	public function klock_woo_thumb_support() {
		global $pagenow;
		if( 'edit.php' === $pagenow ){
			global $typenow;
			if( 'product' === $typenow && isset( $_GET['post_type'] ) && 'product' === sanitize_text_field( $_GET['post_type'] ) ){
				add_filter( 'wp_get_attachment_image_src', array( $this, 'klock_replace_attachment_image_src' ), 10, 4 );
			}
		}
	}



}
