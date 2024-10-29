<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Klockjatten_Featured_Image_By_URL_Admin {

	public $image_meta_url = '_image_path';
	public $image_meta_alt = '_klock_alt';

	public function __construct() {
	
	}

	function klock_get_image_meta( $post_id, $is_single_page = false ){
		
		$image_meta  = array();

		$img_url = get_post_meta( $post_id, $this->image_meta_url, true );
		$img_alt = get_post_meta( $post_id, $this->image_meta_alt, true );
		
		if( is_array( $img_url ) && isset( $img_url['img_url'] ) ){
			$image_meta['img_url'] 	 = $img_url['img_url'];	
		}else{
			$image_meta['img_url'] 	 = $img_url;
		}
		$image_meta['img_alt'] 	 = $img_alt;
		if( ( 'product_variation' == get_post_type( $post_id ) || 'product' == get_post_type( $post_id ) ) && $is_single_page ){
			if( isset( $img_url['width'] ) ){
				$image_meta['width'] 	 = $img_url['width'];
				$image_meta['height'] 	 = $img_url['height'];
			}else{

				if( isset( $image_meta['img_url'] ) && $image_meta['img_url'] != '' ){
					$imagesize = @getimagesize( $image_meta['img_url'] );
					$image_url = array(
						'img_url' => $image_meta['img_url'],
						'width'	  => isset( $imagesize[0] ) ? $imagesize[0] : '',
						'height'  => isset( $imagesize[1] ) ? $imagesize[1] : ''
					);
					update_post_meta( $post_id, $this->image_meta_url, $image_url );
					$image_meta = $image_url;	
				}				
			}
		}
		return $image_meta;
	}
	
	function klockjatten_get_posttypes( $raw = false ) {

		$post_types = array_diff( get_post_types( array( 'public'   => true ), 'names' ), array( 'nav_menu_item', 'attachment', 'revision' ) );
		if( !empty( $post_types ) ){
			foreach ( $post_types as $key => $post_type ) {
				if( !post_type_supports( $post_type, 'thumbnail' ) ){
					unset( $post_types[$key] );
				}
			}
		}
		if( $raw ){
			return $post_types;	
		}else{
			$options = get_option( KLOCK_OPTIONS );
			$disabled_posttypes = isset( $options['disabled_posttypes'] ) ? $options['disabled_posttypes']  : array();
			$post_types = array_diff( $post_types, $disabled_posttypes );
		}

		return $post_types;
	}

}