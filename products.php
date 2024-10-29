<?php 
class KlockjattenProduct {

	function klockjatten_array_flatten($array) { 
	  if (!is_array($array)) { 
		return FALSE; 
	  } 
	  $result = array(); 
	  foreach ($array as $key => $value) { 
		if (is_array($value)) { 
		  $result = array_merge($result, $this->klockjatten_array_flatten($value)); 
		} 
		else { 
		  $result[$key] = $value; 
		} 
	  } 
	  return $result; 
	} 
	
	function klockjatten_get_category_name_by_brand_id($id = null){
	if(empty($id)){
		return false;
	}
	$brands_data_arr = get_option('klock_brand_name_array');		
		$cat_id=array();
	    foreach($brands_data_arr as $key=>$brand){
			if(in_array($id,$brand)){
				$category = get_term_by( 'slug', $key, 'product_cat' );
				$cat_id[] = $category->term_id;
			}		
		}
			return $cat_id;
	}
	
	
	   function klock_add_product(){
	 
	  ?> 
	 
	 <div>
	 <span class="klock_load_process"></span><i> ( Note : Please do not refresh untill process is done.)</i>
	 </div>
	 <script src="<?php echo plugins_url('/assets/js/jquery-3.5.1.min.js',__FILE__);  ?>"></script>
	 <script>
	 
    $(function() {
     run_klock_upload_ajax_on_demand(); 
	var on_demand =0;
	function run_klock_upload_ajax_on_demand(){
		         
				var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
				jQuery.ajax({
					  url:      ajaxurl,
					  data:    ({action  : 'klock_upload_brand_product_on_demand'}),
					  success: function(data){
							var data = JSON.parse(data);
							 console.log(data);
							on_demand += data.count;
							jQuery(".klock_load_process").text(on_demand+' Product Uploaded. ');
							if(data.status == 'done'){
								jQuery(".klock_load_process").append('Process Completed.');
								jQuery(".klock_load_process").css("color", "#11a42f");
							}else{
								run_klock_upload_ajax_on_demand();
							}
					  }
				});
	}
  });
  
 </script>
    <?php
	
	 }
	

	function klockjatten_manage_removed_product(){
		
		     $post_per_page = get_option('add_manage_product_limit_per_page');
			 if(!$post_per_page){
				  $post_per_page = 1;
			 }
		
			 $args = array(
				'post_type'      => 'product',
				'posts_per_page' => 1000,
				'post_status' => array('publish'),
				'paged' => $post_per_page,
				'meta_query' => array(
					array(
						'key' => '_stock_status',
						'value' => 'instock'
					),
					array(
						'key' => 'dropshipping_api_product',
						'value' => 'yes',
						'compare' => '=' // this should work...
					),	
					array(
					'key' => '_sku',
					'value' => '',
					'compare' => '!='
					)			
				),		
			); 

			$loop = new WP_Query( $args );
            $products  =  $loop->posts;
			if(!empty($products)){
			$manage_removed_product_key  =  get_option('add_manage_removed_product_key');
			$limit = 0;
			foreach($products as $key => $product){
				  
							if($limit >= 10){
								 break;
							}
							
							if($manage_removed_product_key){
								 if($key < $manage_removed_product_key){
									  continue;
								 }
							}
							  
							$product_id = $product->ID;
							$sku_value = get_post_meta($product_id,'_sku',true);			
							$stock = get_post_meta($product_id,'_stock',true);
							$uid = get_option('api_userid');
							$pid = get_option('api_pid');
							$lid = get_option('api_lid');
							$key = get_option('api_key');
							$api_version = get_option('api_version');
							$api_url =get_option('api_url');
							
							
							$data = array(
									   "uid"          => $uid,
									   "pid"          =>$pid,
									   "lid"          => $lid,
									   "key"          => $key,
									   "api_version"  => $api_version,	   
									   "request"      => "get_item",
									   "id_product"   => $sku_value,
									   "display_reference"     => true,
									   "display_name"          => true,
									   "display_stock"         => true,
									   "display_weight"        => true,
									   "display_retail_price"  => true,
									   "display_discount"      => true,
									   "display_price"         => true,
									   "display_id_supplier"   => true,
									   "display_speed_shipping"=> true,
									   "display_ean"           => true,
									   "display_currency"      => true,
									   "display_icon_path"     => true,
									   "display_image_path"    => true,
									   "display_image_last_update" => true,
									   "display_attributes"        => true
							);
							$data = array('data' => json_encode($data));
							$args = array(
								'method' => 'POST',
								'body' => $data,
								'timeout' => 120
							);
						$response = wp_remote_get($api_url,$args);
						if ( !is_wp_error( $response ) ) {
							$body = wp_remote_retrieve_body( $response );
							$result = array('data' => json_decode( $body ));
							if(!empty($result['data']->rows)){
								foreach($result['data']->rows as $res){
									$dropship_stock=$res->stock;
									$drop_retial_price=$res->retail_price;
									$drop_price=$res->price;
									if($drop_price < $drop_retial_price){ 
									   $price = $drop_price;
									}else{
										$price = $drop_retial_price;
									}
									$custom_price = get_post_meta($product_id,'_custom_price',true);
									$custom_retail_price = get_post_meta($product_id,'_custom_retail_price',true);					
									if($stock != $dropship_stock){	
										update_post_meta($product_id,'_stock',sanitize_text_field($dropship_stock));					
										if($dropship_stock > 0){
											update_post_meta($product_id, '_stock_status', wc_clean('instock') );
										}else{
											update_post_meta($product_id, '_stock_status', wc_clean('outofstock') );
											echo " Product ID - ".$product_id." moved to out of stock </br>";
										}
										
										echo " Product ID - ".$product_id." stock Updated </br>";
									}
									if($drop_price != $custom_price || $drop_retial_price != $custom_retail_price){
										update_post_meta($product_id,'_custom_price',sanitize_text_field($drop_price));
										update_post_meta($product_id,'_custom_retail_price',sanitize_text_field($drop_retial_price));
										update_post_meta($product_id,'_regular_price',sanitize_text_field($drop_retial_price));
										$product_mode = esc_attr( get_option('klock_crete_product_mode') );							
										if(!$product_mode){
											update_post_meta($product_id,'_sale_price',sanitize_text_field($drop_price));
											update_post_meta($product_id,'_price',sanitize_text_field($drop_price));
										} else {
											update_post_meta($product_id,'_price',sanitize_text_field($drop_retial_price));
										}								
										wp_update_post( array(
											'ID' => $product_id,
											'post_status' => 'draft',
										) );
										
										echo " Product ID - ".$product_id." moved to draft</br>";
									}						
								}
							}else{
								wp_update_post( array(
									'ID' => $product_id,
									'post_status' => 'trash',
								) );
								
								echo " Product ID - ".$product_id." moved to Trash</br>";
													 
							}
							
						}			
					  $limit++;			  
		    }
            } else {
				
				update_option('add_manage_product_limit_per_page', '' );
				echo "Cron Ended";
				exit;
				 
			}
			
			if($limit < 10){
				
				update_option('add_manage_product_limit_per_page', $post_per_page+1);
				
				update_option('add_manage_removed_product_key', '' );
				  
			} else {
				if(is_int($key)){
					    
                       update_option('add_manage_removed_product_key', $key );
					   
                } else {
					
					  update_option('add_manage_removed_product_key', '' );
					  update_option('add_manage_product_limit_per_page', $post_per_page+1);
					 
                }					
				
			}
			
		echo "Cron Ended";
	}
	  
	function klockjatten_get_product_by_sku( $sku ) {
	  global $wpdb;
	  $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
	  if ( $product_id ) return $product_id;
	  return null;
	}
	
	function klock_create_attribute( $raw_name) {
		
		if(empty($raw_name)){
			return;
		}
		
		global $wpdb, $wc_product_attributes;

		// Make sure caches are clean.
		delete_transient( 'wc_attribute_taxonomies' );
		WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );

		// These are exported as labels, so convert the label to a name if possible first.
		$attribute_labels = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name' );
		$attribute_name   = array_search( $raw_name, $attribute_labels, true );

		if ( ! $attribute_name ) {
			$attribute_name = wc_sanitize_taxonomy_name( $raw_name );
		}

		$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
		
		if ( ! $attribute_id ) {
			$taxonomy_name = wc_attribute_taxonomy_name( $attribute_name );

			// Degister taxonomy which other tests may have created...
			unregister_taxonomy( $taxonomy_name );

			$attribute_id = wc_create_attribute(
				array(
					'name'         => $raw_name,
					'slug'         => $attribute_name,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => 0,
				)
			);

			// Register as taxonomy.
			register_taxonomy(
				$taxonomy_name,
				apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy_name, array( 'product' ) ),
				apply_filters(
					'woocommerce_taxonomy_args_' . $taxonomy_name,
					array(
						'labels'       => array(
							'name' => $raw_name,
						),
						'hierarchical' => false,
						'show_ui'      => false,
						'query_var'    => true,
						'rewrite'      => false,
					)
				)
			);

		//Clear caches
			delete_transient( 'wc_attribute_taxonomies' );
			// Set product attributes global.
			$wc_product_attributes = array();

			foreach ( wc_get_attribute_taxonomies() as $taxonomy ) {
				$wc_product_attributes[ wc_attribute_taxonomy_name( $taxonomy->attribute_name ) ] = $taxonomy;
			}
		}

		$attribute = wc_get_attribute( $attribute_id );
		$return    = array(
			'attribute_name'     => $attribute->name,
			'attribute_taxonomy' => $attribute->slug,
			'attribute_id'       => $attribute_id,
			'term_ids'           => array(),
		);

		return $return;
	}

}
$product_obj = new KlockjattenProduct();
?>