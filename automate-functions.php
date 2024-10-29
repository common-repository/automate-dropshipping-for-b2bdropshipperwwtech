<?php
/***** Plugin All Functions *****/

/*---Add to cart validation ---- */

add_filter( 'woocommerce_add_to_cart_validation', 'klockjatten_add_to_cart_validation', 10, 5 );
function klockjatten_add_to_cart_validation( $passed, $product_id, $quantity ) { 
	if(is_dropshipping_product($product_id) == false){
		return $passed;
	}
    $products = wc_get_product($product_id);
	$sku_value = $products->get_sku();
	if(empty($sku_value)){
		wc_add_notice( __( 'Sorry Product not available at a moment!', 'woocommerce' ), 'error' );
		$passed = false;		
	}else{
    $product_stock = get_post_meta( $product_id,'_stock',true);
	$product_price = get_post_meta($product_id,'_custom_price',true);
	$product_retail_price = get_post_meta($product_id,'_custom_retail_price',true);
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
if ( is_wp_error( $response ) ) {
	wc_add_notice( __( 'There is something wrong! Please try again later.', 'woocommerce' ), 'error' );
	$passed = false;
}else{
	$body = wp_remote_retrieve_body( $response );
	$result = array('data' => json_decode( $body ));	
	$passed = true;
	if(!empty($result['data']->rows)){
		foreach($result['data']->rows as $res){
			$dropship_stock=$res->stock;
			$dropship_price=$res->price;
			$dropship_retail_price=$res->retail_price;
			update_post_meta($product_id,'_stock',$dropship_stock);
			if($dropship_stock < 1){		
				update_post_meta($product_id,'_stock_status','outofstock');			
				wc_add_notice( __( 'Sorry the product is out of stock!', 'woocommerce' ), 'error' );
				$passed = false;
			}
			if(($product_price != $dropship_price) ||  ($product_retail_price != $dropship_retail_price)){
				wp_update_post( array( 
					 'ID' => sanitize_text_field($product_id),
					 'post_status' => 'draft'
				));
				update_post_meta($product_id,'_custom_price',sanitize_text_field($dropship_price));
				update_post_meta($product_id,'_custom_retail_price',sanitize_text_field($dropship_retail_price));			
				update_post_meta($product_id,'_regular_price',sanitize_text_field($dropship_retail_price));			
				update_post_meta($product_id,'_sale_price',sanitize_text_field($dropship_price));			
				wc_add_notice( __( 'Sorry Product not available at a moment!', 'woocommerce' ), 'error' );
				$passed = false;		
			}
			if ($products->get_status() != 'publish'){
				wc_add_notice( __( 'Sorry the product is no longer available!', 'woocommerce' ), 'error' );
				$passed = false;
			}
		}
	}
	}
}
return $passed;
           
}

/*---Add to basket Api after order is completion ---- */
if(get_option('klock_crete_order_mode') == "processing"){
	add_action('woocommerce_order_status_processing', 'klockjatten_woocommerce_payment_complete', 10, 1);
}else{
	add_action( 'woocommerce_payment_complete', 'klockjatten_woocommerce_payment_complete', 10, 1 );
}
function klockjatten_woocommerce_payment_complete( $order_id ) {
	global $woocommerce;
	$order = wc_get_order( $order_id );
	$order_data = $order->get_data();
	$order_id = $order_data['id'];
	if(wc_get_order_item_meta($order_id,'api_add_to_basket_response',true)){
		return;
	}
	$user_id   = $order->get_user_id();
	if(isset($order_data['shipping']) && !empty($order_data['shipping'])){   
		$order_shipping_first_name = $order_data['shipping']['first_name'];
		$order_shipping_last_name = $order_data['shipping']['last_name'];
		$order_shipping_company = $order_data['shipping']['company'];
		$order_shipping_address_1 = $order_data['shipping']['address_1'];
		$order_shipping_address_2 = $order_data['shipping']['address_2'];
		$order_shipping_city = $order_data['shipping']['city'];
		$order_shipping_state = $order_data['shipping']['state'];
		$order_shipping_postcode = $order_data['shipping']['postcode'];
		$order_shipping_country = $order_data['shipping']['country'];			
	}else{
		$order_shipping_first_name = $order_data['billing']['first_name'];
		$order_shipping_last_name = $order_data['billing']['last_name'];
		$order_shipping_company = $order_data['billing']['company'];
		$order_shipping_address_1 = $order_data['billing']['address_1'];
		$order_shipping_address_2 = $order_data['billing']['address_2'];
		$order_shipping_city = $order_data['billing']['city'];
		$order_shipping_state = $order_data['billing']['state'];
		$order_shipping_postcode = $order_data['billing']['postcode'];
		$order_shipping_country = $order_data['billing']['country'];
	}
	$order_billing_email = $order_data['billing']['email'];
	$order_billing_phone = $order_data['billing']['phone'];
	
	//API Credentials
	$uid = get_option('api_userid');
	$pid = get_option('api_pid');
	$lid = get_option('api_lid');
	$key = get_option('api_key');
	$api_version = get_option('api_version');
	$api_url =get_option('api_url'); 
	$add_to_basket_item=array();
	foreach( $order->get_items() as $item_id => $item ){  	
		$product_id = $item->get_product_id();
		if(is_dropshipping_product($product_id) == true){
			$product = $item->get_product();
			$product_quantity = $item->get_quantity();
			$product_name = $item->get_name(); 
			$sku_id = $product->get_sku();
			$price = get_post_meta($product_id , '_price', true);
			$retail_price = get_post_meta($product_id, '_regular_price', true);
			$basket_item[]= array(
				   'id_product'         => $sku_id ,
				   'qty'                => $product_quantity,
				   'user_id_order'      => $order_id,
				   'user_id_user'       => $user_id,
				   'user_company_name'  => $order_shipping_company,
				   'user_first_name'    => $order_shipping_first_name,
				   'user_last_name'     => $order_shipping_last_name,
				   'user_address'       => $order_shipping_address_1,
				   'user_city'          => $order_shipping_city,
				   'user_state'         => $order_shipping_state,
				   'user_country'       => '21',
				   'user_phone'         => $order_billing_phone,
				   'user_zipcode'       => $order_shipping_postcode,		   
				   'user_mobile'        => $order_billing_phone,		   
				   'user_mail'          => $order_billing_email,
				   'user_retail_price'  => $retail_price,
				   'user_price'         => $price,
			   );
		}
	}

    /* Add to basket */
	if(isset($basket_item) && !empty($basket_item)){
		$data = array(
		   "uid"          => $uid,
		   "pid"          => $pid ,
		   "lid"          => $lid,
		   "key"          => $key ,
		   "api_version"  => $api_version ,
		   "request"      => "add_items_to_basket",
		   "items"    	  => $basket_item
		);

		$data = array('data' => json_encode($data));
		$args = array(
				'method' => 'POST',
				'body' => $data,
				'timeout' => 120
			);
		$response = wp_remote_get($api_url,$args);
		if ( is_wp_error( $response ) ) {
			echo 'There is something wrong! Please try again later.';
		}else{
			$body = wp_remote_retrieve_body( $response );
			wc_add_order_item_meta($order_id,'api_add_to_basket_response',sanitize_text_field($body),true);		
		}

		$basket_res_data=wc_get_order_item_meta($order_id,'api_add_to_basket_response',true);
		$basket_res_data = json_decode($basket_res_data);
		$id_baskets=array();
		if($basket_res_data->items){
			foreach($basket_res_data->items as $item){
				$id_baskets[]=$item->id_basket;				
			}
		}
		if(count($id_baskets)>1){
			$id_basket_data = implode(';',$id_baskets);

			//Add To Bundle
			$data = array(
			   "uid"          => $uid,
			   "pid"          => $pid,
			   "lid"          => $lid,
			   "key"          => $key,
			   "api_version" => $api_version,
			   "request"       => "add_bundle",
			   "items"         => $id_basket_data,
			   "shipping_item" => $id_baskets[0]
			);
			
			$data = array('data' => json_encode($data));
			$args = array(
					'method' => 'POST',
					'body' => $data,
					'timeout' => 120
				);
			$response = wp_remote_get($api_url,$args);
			if ( is_wp_error( $response ) ) {
				echo 'There is something wrong! Please try again later.';
			}else{
				$body = wp_remote_retrieve_body( $response );
				wc_add_order_item_meta($order_id,'api_bundle_response',sanitize_text_field($body),true);
				
			}
		}
		   

		//Dropship Api for create order
		$payment_method = esc_attr( get_option('klock_payment_method') ); 
		$cc_number = esc_attr( get_option('klock_cc_number') ); 
		$cc_exp_month = esc_attr( get_option('klock_cc_exp_month') ); 
		$cc_exp_year = esc_attr( get_option('klock_cc_exp_year') ); 
		$klock_payment = new Klock_payment();
		$cc_ccv = $klock_payment->klock_decrypt(get_option('klock_cc_ccv'), 'xxsuperencryptxx'); 
		$data = array(

				   "uid"             => $uid,
				   "pid"             => $pid,
				   "lid"             => $lid,
				   "key"             => $key,
				   "api_version"     => $api_version,
				   "request"         => 'checkout',
				   "payment_method"  => preg_replace('/\s+/', '', $payment_method),
		);
		
		if($payment_method == 'V' || $payment_method == 'M' || $payment_method == 'A' || $payment_method == 'D'){
			
			$data['cc_number']     = preg_replace('/\s+/', '', $cc_number);
			$data['cc_exp_month']  = preg_replace('/\s+/', '', $cc_exp_month);
			$data['cc_exp_year']   = preg_replace('/\s+/', '', $cc_exp_year);
			$data['cc_ccv']        = preg_replace('/\s+/', '', $cc_ccv);
			
		}
		

		$data = array('data' => json_encode($data));
		$args = array(
				'method' => 'POST',
				'body' => $data,
				'timeout' => 120
			);
		$response = wp_remote_get($api_url,$args);
		if ( is_wp_error( $response ) ) {
			echo 'There is something wrong! Please try again later.';
		}else{
			$body = wp_remote_retrieve_body( $response );
			wc_add_order_item_meta($order_id,'api_order_create_response',sanitize_text_field($body),false);
			wc_add_order_item_meta($order_id,'is_dropshipping_order','yes',false);
			$dropship_order_ids = array();
			$responseorder    = json_decode(sanitize_text_field($body));
			$dropship_order_ids[] = $responseorder->items[0]->id_order_api;
			$dropship_order_id = $responseorder->items[0]->id_order_api;
			klockjatten_change_order_status_on_create_order($uid,$pid,$lid,$key,$api_version,$api_url,$order_id,$dropship_order_id);
            wc_add_order_item_meta($order_id,'api_dropship_order_ids',sanitize_text_field(json_encode($dropship_order_ids)),false);
			
		}
	}
}

/****** Dropship Api Responce On Creating New Order ********/

function klockjatten_change_order_status_on_create_order($uid,$pid,$lid,$key,$api_version,$api_url,$order_id,$dropship_order_id){
	
	          $data = array(
								"uid"             => $uid,
								"pid"             => $pid,
								"lid"             => $lid,
								"key"             => $key,
								"api_version" 	  => $api_version,
								"request"         => "update_order_status",
								"id_order"        => $dropship_order_id,
								"order_status"    => 1,
							);
							$data = array('data' => json_encode($data));
							$args = array(
									'method' => 'POST',
									'body' => $data,
									'timeout' => 120
								);
							$response = wp_remote_get($api_url,$args);
							if ( is_wp_error( $response ) ) {
								echo 'There is something wrong! Please try again later.';
							}else{
								$body = wp_remote_retrieve_body( $response );
								wc_update_order_item_meta($order_id,'api_update_order_status',sanitize_text_field($body),false);		
							}
							
}

/****** Dropship Api Responce in order page********/

add_action( 'woocommerce_admin_order_data_after_billing_address', 'klockjatten_woocommerce_admin_order_data_after_billing_address', 10, 1 );

function klockjatten_woocommerce_admin_order_data_after_billing_address($order){
	//API Credentials
	$uid = get_option('api_userid');
	$pid = get_option('api_pid');
	$lid = get_option('api_lid');
	$key = get_option('api_key');
	$api_version = get_option('api_version');
	$api_url =get_option('api_url'); 	
	$order_id=$order->get_id();
	if(is_dropshipping_order($order_id) == true){
		$dropship_order_ids=json_decode(wc_get_order_item_meta($order_id,'api_dropship_order_ids',true));
		if($dropship_order_ids){
			foreach($dropship_order_ids as $id){
				echo '<p><strong>'.__('Dropship Order ID').':</strong> ' . $id . '</p>';
						$data = array(
							"uid"           => $uid,
							"pid"           => $pid ,
							"lid"           => $lid,
							"key"          	=> $key ,
							"api_version" 	=> $api_version ,
							"request"       => "get_order_items",
							"search"        => array( 
													"id_order" => $id,
													"row_max" => '',
												),
							"display"        => array(
													"id_order_store" => true,
												)   
						);
						$data = array('data' => json_encode($data));					
						$args = array(
								'method' => 'POST',
								'body' => $data,
								'timeout' => 120
							);
						$response = wp_remote_get($api_url,$args);
						if ( is_wp_error( $response ) ) {
							echo 'There is something wrong! Please try again later.';
						}else{
							$body = wp_remote_retrieve_body( $response );
							$order_data=json_decode($body);	
						}
						if(isset($order_data->rows)){
							foreach($order_data->rows as $row){
								if($row->order_status == 1){
									echo '<p>'.__('Order Status').': Ready </p>';
								}elseif($row->order_status == 2){
									echo '<p>'.__('Order Status').': Logistic Locked </p>';
								}elseif($row->order_status == 3){
									echo '<p>'.__('Order Status').': Order Shipped </p>';
								}elseif($row->order_status == 4){
									echo '<p>'.__('Order Status').': Order Shipped with Zero Items </p>';
								}else{
									echo '<p>'.__('Order Status').': New </p>';
								}
								if($row->order_tracking_company){
								echo '<p>'.__('Tracking Company').':' . $row->order_tracking_company . '</p>';
								}
								if($row->order_tracking_number){
								echo '<p>'.__('Tracking Number').':' . $row->order_tracking_number . '</p>';
								}
							}
						}										
			}
		}
	}
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'klockjatten_editable_order_meta_general' );
 
function klockjatten_editable_order_meta_general( $order ){ 
	$order_id=$order->get_id();
	if(is_dropshipping_order($order_id) == true){
		$basket_res_data=json_decode(wc_get_order_item_meta($order_id,'api_add_to_basket_response',true));
		$bundle_res_data=wc_get_order_item_meta($order_id,'api_bundle_response',true);
		$order_res_data=wc_get_order_item_meta($order_id,'api_order_create_response',true);
		?>
			<br class="clear" />
			<h4>Dropship API Response</h4>
			<h5>Add to basket response:</h5>
			<?php
			if($basket_res_data){
				?>
				<?php if(isset($basket_res_data->success)){ ?> <p><strong>Success : </strong><?php echo $basket_res_data->success; ?></p> <?php } ?>
				<?php if(isset($basket_res_data->rc)){ ?> <p><strong>Rc : </strong><?php echo $basket_res_data->rc; ?></p> <?php } ?>
				<?php if(isset($basket_res_data->message)){ ?> <p><strong>Message : </strong><?php echo $basket_res_data->message; ?></p> <?php } ?>
				<?php if(isset($basket_res_data->items)){ ?> 
				<p><strong>Items : </strong>
				<?php foreach($basket_res_data->items as $items){
					echo "<p>";
					if(isset($items->success)){ echo "<span> Success: ".$items->success."</span>"; }
					if(isset($items->rc)){ echo "<span> RC: ".$items->rc."</span>"; }
					if(isset($items->message)){ echo "<span> Message: ".$items->message."</span>"; }
					if(isset($items->id_basket)){ echo "<span> Basket ID: ".$items->id_basket."</span>"; }
					if(isset($items->id_product)){ echo "<span> Product ID: ".$items->id_product."</span>"; }
					if(isset($items->qty_requested)){ echo "<span> Qty Requested: ".$items->qty_requested."</span>"; }
					if(isset($items->qty_available)){ echo "<span> Qty Available: ".$items->qty_available."</span>"; }
					if(isset($items->minimum_qty)){ echo "<span> Minimum Qty: ".$items->minimum_qty."</span>"; }
					if(isset($items->qty_reserved)){ echo "<span> Qty Reserved: ".$items->qty_reserved."</span>"; }
					if(isset($items->retail_price)){ echo "<span> Retail Price: ".$items->retail_price."</span>"; }
					if(isset($items->discount)){ echo "<span> Discount: ".$items->discount."</span>"; }
					if(isset($items->price)){ echo "<span>Price: ".$items->price."</span>"; }
					if(isset($items->extra_discount)){ echo "<span>Extra Discount: ".$items->extra_discount."</span>"; }
					if(isset($items->net_price)){ echo "<span>Net Price: ".$items->net_price."</span>"; }
					echo "</p>";
				} ?>
				</p> 
				<?php } 
			}
			?>
			<?php if($bundle_res_data){ ?>
			<h5>Bundle response:</h5>
			<?php  echo $bundle_res_data; } ?>
			<h5>Order response:</h5>
			<?php if($order_res_data){ 
			   echo $order_res_data;
			} ?>
		<?php 
	}
}

function klockjatten_upload_mimes ( $mime_types =array() ) {
	$mimes['txt'] = "text/plain";
	return $mime_types;
}

add_filter('upload_mimes', 'klockjatten_upload_mimes');


/* User View Order Addition Details */
add_action( 'woocommerce_view_order', 'klock_view_order_additional_info', 20 );
 
function klock_view_order_additional_info( $order_id ){
	//API Credentials
	if(is_dropshipping_order($order_id) == true){
		$uid = get_option('api_userid');
		$pid = get_option('api_pid');
		$lid = get_option('api_lid');
		$key = get_option('api_key');
		$api_version = get_option('api_version');
		$api_url =get_option('api_url'); 	
		$dropship_order_ids=json_decode(wc_get_order_item_meta($order_id,'api_dropship_order_ids',true));
		if(isset($dropship_order_ids[0])){
						$data = array(
							"uid"           => $uid,
							"pid"           => $pid ,
							"lid"           => $lid,
							"key"          	=> $key ,
							"api_version" 	=> $api_version ,
							"request"       => "get_order_items",
							"search"        => array( 
													"id_order" => $dropship_order_ids[0],
													"row_max" => '',
												),
							"display"        => array(
													"id_order_store" => true,
												)   
						);
						$data = array('data' => json_encode($data));
						$args = array(
								'method' => 'POST',
								'body' => $data,
								'timeout' => 120
							);
						$response = wp_remote_get($api_url,$args);
						if ( is_wp_error( $response ) ) {
							echo 'There is something wrong! Please try again later.';
						}else{
							$body = wp_remote_retrieve_body( $response );
							$order_data=json_decode($body);
						}              
						if(isset($order_data->rows)){
							foreach($order_data->rows as $row){
								?>
								<h2>Additional Information</h2>
									<table class="woocommerce-table shop_table additional_info">
										<tbody>							
								<?php 
									$order_status=$row->order_status;
									if($order_status == 1){  $order_status_txt='Ready';	}
									elseif($order_status == 2){	 $order_status_txt='Logistic Locked'; }
									elseif($order_status == 3){	 $order_status_txt='Order Shipped'; }
									elseif($order_status == 4){	 $order_status_txt='Order Shipped with Zero Items'; }
									else{ $order_status_txt='New'; }
									?>
									<tr>
									<th>Order Status:</th>
									<td><?php echo $order_status_txt; ?></td>
									</tr>
									<?php 
								if($row->order_tracking_company){
									?>
									<tr>
									<th>Tracking Company:</th>
									<td><?php echo $row->order_tracking_company; ?></td>
									</tr>
									<?php 
								}
								if($row->order_tracking_number){
									?>
									<tr>
									<th>Tracking Number:</th>
									<td><?php echo $row->order_tracking_number; ?></td>
									</tr>
									<?php 
								}
								?>
										</tbody>
									</table>						
								<?php 														
							}
						}										

		}
	} 	
}

/* Cron Function for Order Status Chnage */
function klockjatten_order_status_change_cron(){
	if(isset($_GET['action'])){
		if($_GET['action']=='klockjatten_order_status_change_cron'){
			$uid = get_option('api_userid');
			$pid = get_option('api_pid');
			$lid = get_option('api_lid');
			$key = get_option('api_key');
			$api_version = get_option('api_version');
			$api_url =get_option('api_url'); 
			$date =  date('Y-m-d 00:00:00');
			$previousDay = date('Y-m-d 00:00:00', strtotime('-1 day', strtotime($date)));
			$data = array(
				"uid"           => $uid,
				"pid"           => $pid ,
				"lid"           => $lid,
				"key"          	=> $key ,
				"api_version" 	=> $api_version ,
				"request"       => "get_order_items",
				"search"        => array( 
										"from" => $previousDay,
									),
				"display"        => array(
										"id_order_store" => true,
									)   
			);

			$data = array('data' => json_encode($data));
			$args = array(
					'method' => 'POST',
					'body' => $data,
					'timeout' => 120
				);
			$response = wp_remote_get($api_url,$args);
			if ( is_wp_error( $response ) ) {
				echo 'There is something wrong! Please try again later.';
			}else{
				$body = wp_remote_retrieve_body( $response );
				$all_data=json_decode($body);	
			}
			
			if(isset($all_data->rows)){
				foreach($all_data->rows as $data){
						$dropship_order_id = $data->id_order;
						$user_order_id = $data->user_id_order;
						$dropship_order_ids=json_decode(wc_get_order_item_meta($user_order_id,'api_dropship_order_ids',true));
						$alreay_run = false;
						if($dropship_order_ids){
							if (!in_array($dropship_order_id, $dropship_order_ids)){
								$dropship_order_ids[]=$dropship_order_id;
							}else{
								$alreay_run = true;
							}
						}else{
							$dropship_order_ids[]=$dropship_order_id;
						}
						wc_update_order_item_meta($user_order_id,'api_dropship_order_ids',sanitize_text_field(json_encode($dropship_order_ids)),false);
						if($alreay_run == false){
							$data = array(
								"uid"             => $uid,
								"pid"             => $pid,
								"lid"             => $lid,
								"key"             => $key,
								"api_version" 	  => $api_version,
								"request"         => "update_order_status",
								"id_order"        => $dropship_order_id,
								"order_status"    => 1,
							);
							$data = array('data' => json_encode($data));
							$args = array(
									'method' => 'POST',
									'body' => $data,
									'timeout' => 120
								);
							$response = wp_remote_get($api_url,$args);
							if ( is_wp_error( $response ) ) {
								echo 'There is something wrong! Please try again later.';
							}else{
								$body = wp_remote_retrieve_body( $response );
								wc_update_order_item_meta($user_order_id,'api_update_order_status',sanitize_text_field($body),false);		
							}							
						}
				}	
			
			}
			exit;
		}
	}
}
//add_action('wp_head','klockjatten_order_status_change_cron');
add_action('wp_ajax_klockjatten_order_status_change_cron', 'klockjatten_order_status_change_cron');
add_action('wp_ajax_nopriv_klockjatten_order_status_change_cron', 'klockjatten_order_status_change_cron');	

add_action( 'woocommerce_email_order_meta', 'klockjatten_add_email_order_meta', 10, 4 );
function klockjatten_add_email_order_meta( $order_obj, $sent_to_admin, $plain_text, $email ){
    if ( $email->id == 'customer_completed_order' ) {
		if(is_dropshipping_order($order_obj->get_order_number()) == true || $order_obj->get_order_number() == 1){
			// ok, we will add the separate version for plaintext emails
			if($order_obj->get_order_number() == 1){
				$tracking_company = 'FedEx';
				$tracking_number = '123456789';
			}else{
				$tracking_company = wc_get_order_item_meta( $order_obj->get_order_number(), 'dropship_order_tracing_company', true );
				$tracking_number = wc_get_order_item_meta( $order_obj->get_order_number(), 'dropship_order_tracing_number', true );
				if(empty($tracking_company)){
					$tracking_company = (esc_attr( get_option('klock_ship_not_available') )) ? esc_attr( get_option('klock_ship_not_available') ) : "Not Available"; 
				}
				if(empty($tracking_number)){
					$tracking_number = (esc_attr( get_option('klock_ship_not_available') )) ? esc_attr( get_option('klock_ship_not_available') ) : "Not Available"; 
				}			
			}			
				$ship_title = ( get_option('klock_ship_title')) ?  get_option('klock_ship_title') : "Shipping Details" ; 				
				$ship_desc = get_option('klock_ship_desc') ; 				
				$company_text  = (get_option('klock_ship_company_name')) ? get_option('klock_ship_company_name') : "Company Name"; 				
				$tracking_text  = (get_option('klock_ship_tracking_no')) ? get_option('klock_ship_tracking_no') : "Tracking Number"; 				
				
				if ( $plain_text === false ) {					
						// you shouldn't have to worry about inline styles, WooCommerce adds them itself depending on the theme you use
						echo '<h2>'.$ship_title.'</h2>';
						echo '<h3>'.$ship_desc.'</h3>';
						echo '<ul>';
						echo '<li><strong>'.$company_text.':</strong> '.$tracking_company.'</li>';
						echo '<li><strong>'.$tracking_text.':</strong> '.$tracking_number.'</li>';
						echo '</ul>';	
				} else {

					echo $ship_title."\n";
					echo $company_text." : ".$tracking_company."\n";
					echo $tracking_text." : ".$tracking_number;		 
				}
								
		}		
	}
}

/* Cron Function for shipped mail */
function klockjatten_order_shipped_mail_sender(){
	if(isset($_GET['action'])){
		if($_GET['action']=='klockjatten_order_shipped_mail_sender'){
			$uid = get_option('api_userid');
			$pid = get_option('api_pid');
			$lid = get_option('api_lid');
			$key = get_option('api_key');
			$api_version = get_option('api_version');
			$api_url =get_option('api_url'); 
			$date =  date('Y-m-d 00:00:00');
			$previousDay = date('Y-m-d 00:00:00', strtotime('-30 days', strtotime($date)));
			$data = array(
				"uid"           => $uid,
				"pid"           => $pid ,
				"lid"           => $lid,
				"key"          	=> $key ,
				"api_version" 	=> $api_version ,
				"request"       => "get_order_items",
				"search"        => array( 
										"from" => $previousDay,
									),
				"display"        => array(
										"id_order_store" => true,
									)   
			);

			$data = array('data' => json_encode($data));
			$args = array(
					'method' => 'POST',
					'body' => $data,
					'timeout' => 120
				);
			$response = wp_remote_get($api_url,$args);
			if ( is_wp_error( $response ) ) {
				echo 'There is something wrong! Please try again later.';
			}else{
				$body = wp_remote_retrieve_body( $response );
				$all_data=json_decode($body);	
			}		
			if(isset($all_data->rows)){
				foreach($all_data->rows as $data){
						$order_status = $data->order_status;
						$user_order_id = $data->user_id_order;
						if($order_status == 3 && !empty($user_order_id) ){
							if(wc_get_order_item_meta($user_order_id,'shipped_mail_send',true) != true){
								$tracking_company = $data->order_tracking_company;
								$tracking_number = $data->order_tracking_number;						
								wc_add_order_item_meta($user_order_id,'dropship_order_tracing_company',$tracking_company,true);
								wc_add_order_item_meta($user_order_id,'dropship_order_tracing_number',$tracking_number,true);
								$order = wc_get_order($user_order_id);								
								if($order){
									$order->update_status('completed');
								}
							}
						}
				}
			}
			exit;
		}
	}
}
add_action('wp_ajax_klockjatten_order_shipped_mail_sender', 'klockjatten_order_shipped_mail_sender');
add_action('wp_ajax_nopriv_klockjatten_order_shipped_mail_sender', 'klockjatten_order_shipped_mail_sender');

function count_klockjatten_array_flatten($array)
{
    if (!is_array($array)) {
        return FALSE;
    }
    $result = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, count_klockjatten_array_flatten($value));
        } else {
            $result[$key] = $value;
        }
    }
    return $result;
}

function count_klockjatten_get_category_name_by_brand_id($id = null)
{
    if (empty($id)) {
        return false;
    }
    $brands_data_arr = get_option('klock_brand_name_array');
    $cat_id          = array();
    foreach ($brands_data_arr as $key => $brand) {
        if (in_array($id, $brand)) {
            $category = get_term_by('slug', $key, 'product_cat');
            $cat_id[] = $category->term_id;
        }
    }
    return $cat_id;
}

add_action("wp_ajax_nopriv_count_klock_add_product", "count_klock_add_product");
add_action("wp_ajax_count_klock_add_product", "count_klock_add_product");
function count_klock_add_product()
{
    $uid          = get_option('api_userid');
    $pid          = get_option('api_pid');
    $lid          = get_option('api_lid');
    $key          = get_option('api_key');
    $api_version  = get_option('api_version');
    $api_url      = get_option('api_url');
    $import_limit = 0;
	$return_arr = array();
	$found_product = false;
    if (!empty($uid) && !empty($pid) && !empty($lid) && !empty($key) && !empty($api_version)) {
        $brands_data_arr = get_option('klock_brand_name_array');
        $input           = count_klockjatten_array_flatten($brands_data_arr);
        $brand_ids       = array_unique($input);
		$brand_ids 		 = array_values($brand_ids);
		$brand_add_data  = count($brand_ids) - 1;
		update_option('klock_count_brand_add_total_data',$brand_add_data);
		$current_brand = get_option('klock_count_brand_add_current_index');
		if(empty($current_brand)){
			$current_brand = 0;
		}
		$id = $brand_ids[$current_brand];	
		$data     = array(
			"uid" => $uid,
			"pid" => $pid,
			"lid" => $lid,
			"key" => $key,
			"api_version" => $api_version,
			"request" => "get_brand_items",
			"id_brand" => $id,
			"display_brand_name"        => true,
			"display_reference"         => true,
			"display_name"              => true,
			"display_stock"             => true,
			"display_weight"            => true,
			"display_retail_price"      => true,
			"display_discount"          => true,
			"display_price"             => true,
			"display_id_supplier"       => true,
			"display_speed_shipping"    => true,
			"display_ean"               => true,
			"display_currency"          => true,
			"display_icon_path"         => true,
			"display_image_path"        => true,
			"display_image_last_update" => true,
			"display_attributes"        => true,				
		);
		$data     = array(
			'data' => json_encode($data)
		);
		$args     = array(
			'method' => 'POST',
			'body' => $data,
			'timeout' => 120
		);
		$response = wp_remote_get($api_url, $args);
		if (is_wp_error($response)) {
		} else {
			$body     = wp_remote_retrieve_body($response);
			$products = array(
				'data' => json_decode($body)
			);
		}
		if (isset($products['data']->rows) && !empty($products['data']->rows)) {
			foreach ($products['data']->rows as $product) {
				if($product->stock > 0){
					$chk_exist = count_klockjatten_get_product_by_sku($product->id_product);
					if (!$chk_exist) {
						$found_product = true;
						break;
					}
				}
			}
		}
		if($found_product == true){			
			$return_arr['status'] = "found";					
			$return_arr['html']   = "<strong><span>New products founds! That's need to be upload!</span></strong>";					
		}else{			
			if( $current_brand >= $brand_add_data ){
				update_option('klock_count_brand_add_current_index','');
				$return_arr['status'] = "done";
			}else{
				$return_arr['status'] = "continue";
				update_option('klock_count_brand_add_current_index',$current_brand+1);
			}			
		}		
        echo json_encode($return_arr);
		
    }
    exit;
}
function count_klockjatten_get_product_by_sku($sku)
{
    global $wpdb;
    $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku));
    if ($product_id)
        return $product_id;
    return null;
}
add_action("wp_ajax_nopriv_klock_upload_brand_product_on_demand_2", "klock_upload_brand_product_on_demand_2");
add_action("wp_ajax_klock_upload_brand_product_on_demand_2", "klock_upload_brand_product_on_demand_2");

function klock_upload_brand_product_on_demand_2() {
$uid = get_option('api_userid');
$pid = get_option('api_pid');
$lid = get_option('api_lid');
$key = get_option('api_key');
$api_version = get_option('api_version');
$api_url = get_option('api_url');
$return_arr = array();
$updated_products = array();
$trashed_products = array();
if(!empty($uid) && !empty($pid) && !empty($lid) && !empty($key) && !empty($api_version)){			
	$brands_data_arr = get_option('klock_brand_name_array');
	$input = count_klockjatten_array_flatten($brands_data_arr);
	$brand_ids=array_unique($input);
	$brand_ids = array_values($brand_ids);
	$brand_add_data  = count($brand_ids) - 1;
	update_option('klock_brand_add_total_data_cron_update',$brand_add_data);	
	$current_brand = get_option('klock_brand_add_current_index_cron_update');
	if(!$current_brand){
	$current_brand = 0; 
	}	
	$id = $brand_ids[$current_brand];
	$return_arr['brandId'] = $id;
			$data = array(
						   "uid"          => $uid,

						   "pid"          => $pid,

						   "lid"          => $lid,

						   "key"          => $key,

						   "api_version"  => $api_version,

						   "request"      => "get_brand_items",
						  
						   "id_brand"     => $id,
							  

						   "display_brand_name"        => true,

						   "display_reference"         => true,

						   "display_name"              => true,

						   "display_stock"             => true,

						   "display_weight"            => true,

						   "display_retail_price"      => true,

						   "display_discount"          => true,

						   "display_price"             => true,

						   "display_id_supplier"       => true,

						   "display_speed_shipping"    => true,

						   "display_ean"               => true,

						   "display_currency"          => true,

						   "display_icon_path"         => true,

						   "display_image_path"        => true,

						   "display_image_last_update" => true,

						   "display_attributes"        => true
						   
				);
				$data = array('data' => json_encode($data));
				$args = array(
						'method' => 'POST',
						'body' => $data,
						'timeout'=> 120,
					);
				$response = wp_remote_get($api_url,$args);
				if ( is_wp_error( $response ) ) {
					echo 'There is something wrong! Please try again later.';
				}else{
					$body = wp_remote_retrieve_body( $response );
					$products = array('data' => json_decode( $body ));
				}
				
				$api_product_data=array();

				if(isset($products['data']->rows) && !empty($products['data']->rows)){
					
					$return_arr['no_products_api'] = count($products['data']->rows);
					$brandName=$products['data']->rows[0]->brand;
					foreach($products['data']->rows as $product){
						$sku =$product->id_product;
						$api_stock = $product->stock;
						$drop_retial_price=$product->retail_price;
						$drop_price=$product->price;
						if($drop_price < $drop_retial_price){ 
						   $price = $drop_price;
						}else{
							$price = $drop_retial_price;
						}
						$api_product_data[$sku]=$sku.'-'.$api_stock.'-'.$price.'-'.$drop_retial_price;
					}
				}
				
				
				if($brandName != ''){
					
				$database_product_data=array();
				$database_product_ids=array();
								$args = array(
							    'post_type' => 'product',
							    'post_status' => 'publish',
							    'posts_per_page' => -1,
							    'product_tag' => $brandName
							    
							);
							$the_query = new WP_Query( $args );
							$i=0;
							while ( $the_query->have_posts() ) : $the_query->the_post();
								global $post;
							   $product_id = $post->ID;
							$sku_value = get_post_meta($product_id,'_sku',true);			
							$stock = get_post_meta($product_id,'_stock',true);
							$custom_price = get_post_meta($product_id,'_custom_price',true);
							$custom_retail_price = get_post_meta($product_id,'_custom_retail_price',true);
							$database_product_data[$sku_value]=$sku_value.'-'.$stock.'-'.$custom_price.'-'.$custom_retail_price;
							$database_product_ids[$sku_value] = $product_id;
							    $i++;
							 endwhile;
							 $return_arr['no_products_database'] = $i;
							 $diff_data=array_diff($api_product_data,$database_product_data);
							  if(!empty($diff_data) && $i>0){
							  	foreach ($diff_data as $sku_key => $data_str) {
							  		$product_id= $database_product_ids[$sku_key];
							  		
							  		$data=explode('-', $data_str);

							  		update_post_meta($product_id,'_stock',$data[1]);
									update_post_meta($product_id,'_custom_price',$data[2]);
									update_post_meta($product_id,'_custom_retail_price',$data[3]);
									update_post_meta($product_id,'_regular_price',$data[3]);
									if(!$product_mode){
											update_post_meta($product_id,'_sale_price',$data[2]);
											update_post_meta($product_id,'_price',$data[2]);
										} else {
											update_post_meta($product_id,'_price',$data[3]);
										}
									$updated_products[]=$product_id;
							  		
									
								}
								
								
								
								
							  	
							}
							
							
							if(!empty($api_product_data)){
								foreach ($database_product_ids as $db_sku_key => $product_id) {
									if(!array_key_exists($db_sku_key, $api_product_data)){
										/*If product exists in Database but not in API then trash it*/
										$trashed_products[]=$product_id;
										wp_update_post( array(
											'ID' => $product_id,
											'post_status' => 'trash',
										) );
									}
								}
							}
							
							
							
						}

			if( $current_brand >= $brand_add_data ){
				 update_option('klock_brand_add_current_index_cron_update','');				
				 $return_arr['status'] = "done";
				
				}else{
					$return_arr['status'] = "continue";
					update_option('klock_brand_add_current_index_cron_update',$current_brand+1);
			$return_arr['current_index'] = get_option('klock_brand_add_current_index_cron_update');	
					
				}
				$return_arr['updated_products'] = $updated_products;
				$return_arr['trashed_products'] = $trashed_products;
				
				$return_arr['brandName'] = $brandName;
				echo json_encode($return_arr);
	}
					exit;
}


add_action("wp_ajax_klock_upload_brand_product_on_demand", "klock_upload_brand_product_on_demand");
function klock_upload_brand_product_on_demand() {
$uid = get_option('api_userid');
$pid = get_option('api_pid');
$lid = get_option('api_lid');
$key = get_option('api_key');
$api_version = get_option('api_version');
$api_url = get_option('api_url');
$return_arr = array();
$updated_products = array();
$trashed_products = array();
if(!empty($uid) && !empty($pid) && !empty($lid) && !empty($key) && !empty($api_version)){			
				$brands_data_arr = get_option('klock_brand_name_array');
				$input = count_klockjatten_array_flatten($brands_data_arr);
				$brand_ids=array_unique($input);
				$brand_ids = array_values($brand_ids);
				$brand_add_data  = count($brand_ids) - 1;
				update_option('klock_brand_add_total_data',$brand_add_data);
				$current_brand = get_option('klock_brand_add_current_index');
				if(!$current_brand){
				   $current_brand = 0; 
				}	
				$id = $brand_ids[$current_brand];
				$return_arr['brandId'] = $id;
			$data = array(
						   "uid"          => $uid,

						   "pid"          => $pid,

						   "lid"          => $lid,

						   "key"          => $key,

						   "api_version"  => $api_version,

						   "request"      => "get_brand_items",
						  
						   "id_brand"     => $id,
							  

						   "display_brand_name"        => true,

						   "display_reference"         => true,

						   "display_name"              => true,

						   "display_stock"             => true,

						   "display_weight"            => true,

						   "display_retail_price"      => true,

						   "display_discount"          => true,

						   "display_price"             => true,

						   "display_id_supplier"       => true,

						   "display_speed_shipping"    => true,

						   "display_ean"               => true,

						   "display_currency"          => true,

						   "display_icon_path"         => true,

						   "display_image_path"        => true,

						   "display_image_last_update" => true,

						   "display_attributes"        => true
						   
				);
				$data = array('data' => json_encode($data));
				$args = array(
						'method' => 'POST',
						'body' => $data,
						'timeout'=> 120,
					);
				$response = wp_remote_get($api_url,$args);
				if ( is_wp_error( $response ) ) {
					echo 'There is something wrong! Please try again later.';
				}else{
					$body = wp_remote_retrieve_body( $response );
					$products = array('data' => json_decode( $body ));
				}
				
				$api_product_data=array();
				$product_status = ( get_option('klock_crete_product_status') ) ? 'draft' : 'publish';
				$staock_status = 'instock';
				$product_mode = esc_attr( get_option('klock_crete_product_mode') );
				$product_image_mode  = get_option('klock_crete_product_image_mode');
                $import_limit = 0;
				if(isset($products['data']->rows) && !empty($products['data']->rows)){
					$return_arr['no_products_api'] = count($products['data']->rows);
					$brandName=$products['data']->rows[0]->brand;
					foreach($products['data']->rows as $product){
						
						$ean_length = strlen($product->ean);
						if($ean_length == 11){
							$new_ean = "00".$product->ean;
						}elseif($ean_length == 12){
							$new_ean = "0".$product->ean;
						}else{
							$new_ean = $product->ean;
						}
                        $sku = $product->id_product;
                        
						$api_product_data[$sku]['api_stock']=$product->stock;
						$api_product_data[$sku]['retial_price']=$product->retail_price;
						$api_product_data[$sku]['price']=($product->price < $product->retail_price) ? $product->price:$product->retail_price;
						$api_product_data[$sku]['sale_price']= $product->price;
						$api_product_data[$sku]['ean']=$new_ean;
						$api_product_data[$sku]['attributes']=$product->attributes;
						$api_product_data[$sku]['name']=esc_html($product->name);
						$api_product_data[$sku]['post_name']=sanitize_text_field($product->name);
						$api_product_data[$sku]['weight']=sanitize_text_field(($product->weight)/1000);
						$api_product_data[$sku]['currency']=sanitize_text_field($product->currency);
						$api_product_data[$sku]['speed_shipping']=sanitize_text_field($product->speed_shipping);
						$api_product_data[$sku]['id_supplier']=sanitize_text_field($product->id_supplier);
						$api_product_data[$sku]['image_path']=esc_url($product->image_path);
						$api_product_data[$sku]['attributes_array']= $product->attributes_array;
						
					}
				}
				
				if($brandName != ''){
				$database_product_data=array();
				
								$args = array(
							    'post_type' => 'product',
							    'post_status' => array('publish', 'draft', 'trash'),
							    'posts_per_page' => -1,
							    'product_tag' => $brandName
							    
							);
							$the_query = new WP_Query( $args );
							$i = 0;
							while ( $the_query->have_posts() ) : $the_query->the_post();
								global $post;
							$product_id = $post->ID;
							$sku_value = get_post_meta($product_id,'_sku',true);			
							$database_product_data[$sku_value]=$sku_value;
							    $i++;
							 endwhile;
							 $return_arr['no_products_database'] = $i; 
							 $diff_data = array_diff_key($api_product_data,$database_product_data);
					if(!empty($diff_data)){
						foreach ($diff_data as $api_sku => $product) {
							
							if ($import_limit >= 10) {
                                break;
						    }
							
							$post    = array(
								'post_content' => sanitize_text_field($product['attributes']),
								'post_title' => esc_html($product['name']),
								'post_name' => sanitize_text_field($product['name']),
								'post_status' => $product_status,
								'post_type' => 'product',
								'meta_input' => array(
									'_sku' => sanitize_text_field($api_sku),
									'_regular_price' => sanitize_text_field($product['retial_price']),
									'_custom_retail_price' => sanitize_text_field($product['retial_price']),
									'_custom_price' => sanitize_text_field($product['sale_price']),
									'_stock' => sanitize_text_field($product['api_stock']),
									'_manage_stock' => 'yes',
									'_stock_status' => sanitize_text_field($staock_status),
									'_brand_name' => sanitize_text_field($brandName),
									'_weight' => sanitize_text_field(($product['weight']/1000)),
									'_currency' => sanitize_text_field($product['currency']),
									'_speed_shipping' => sanitize_text_field($product['speed_shipping']),
									'id_supplier' => sanitize_text_field($product['id_supplier']),
									'ean' => sanitize_text_field($product['ean']),
									'dropshipping_api_product' => 'yes',
								)
							);
						$product_mode = esc_attr( get_option('klock_crete_product_mode') );
						
						$product_image_mode  = get_option('klock_crete_product_image_mode');
						
						if($product_image_mode){
							$post['meta_input']['_image_path'] = esc_url($product['image_path']);
						}
							
						
						
						$postdata = array();
						$postdata['post_content']   =  $post['post_content'];
						$postdata['post_title']     =  $post['post_title'];
						$postdata['_regular_price'] =  $post['meta_input']['_regular_price'];
						if(!$product_mode){
						  $postdata['_sale_price']    =  $product['sale_price'];
						}
						$postdata['_weight']        =  $post['meta_input']['_weight'];
						$postdata['_currency']      =  $post['meta_input']['_currency'];
						
						/*----------- Manage Post data filter  --------------------*/
						
						$postdata  =  apply_filters( 'klock_woo_drop_manage_post', $postdata );
						
						$post['post_content']                  = $postdata['post_content'];
						$post['post_title']                    = $postdata['post_title'];
						$post['meta_input']['_regular_price']  = $postdata['_regular_price'];
						$post['meta_input']['_weight']         = $postdata['_weight'];
						$post['meta_input']['_currency']       = $postdata['_currency'];
						if(!$product_mode){
							 if($postdata['_sale_price'] >= $postdata['_regular_price']){
								 $post['meta_input']['_regular_price'] = $postdata['_sale_price'];
								 $post['meta_input']['_price']      = $postdata['_sale_price'];
							 } else {
								 $post['meta_input']['_sale_price'] = $postdata['_sale_price'];
								 $post['meta_input']['_price']      = $postdata['_sale_price']; 
							 }
								 
						} else {
								 $post['meta_input']['_price']      =  $postdata['_regular_price'];
						}
								
						$post_id = wp_insert_post($post, true);
						
						if ($post_id) {
							
							/*-------- Manage products actions as new Product inserted ---------*/
							
							do_action('klock_woo_drop_after_product_inserted',$post_id,$post);
							
							
							$brand_cat = count_klockjatten_get_category_name_by_brand_id($id);
							if ($brand_cat) {
								wp_set_object_terms($post_id, $brand_cat, 'product_cat', true);
								wp_set_object_terms($post_id, sanitize_text_field($brandName), 'product_tag', true);
							}
							
							 
							$get_product = wc_get_product( $post_id );
							$attributes  = array();
							
							if($brandName){
								
								$attribute_data = klock_create_attribute('Brand'); 
								$attribute_data['term_ids'] = array($brandName);
								$attribute  = new WC_Product_Attribute();
								$attribute->set_id( $attribute_data['attribute_id'] );
								$attribute->set_name( $attribute_data['attribute_taxonomy'] );
								$attribute->set_options( $attribute_data['term_ids'] );
								$attribute->set_position( 1 );
								$attribute->set_visible( true );
								$attribute->set_variation( true );
								$attributes[] = $attribute;
								
							}
							
							if(!empty($product['attributes_array'])){
								
								    /*---------- Manage products attributes ------*/
								    
									$product_attributes  = apply_filters('klock_woo_drop_manage_product_attributes', $product['attributes_array'] );
									
								
									foreach($product_attributes as $attributes_array){

                                           										
										$attribute_data = klock_create_attribute($attributes_array->group_name); 
										$attribute_data['term_ids'] = array($attributes_array->value_name);
										$attribute      = new WC_Product_Attribute();
										$attribute->set_id( $attribute_data['attribute_id'] );
										$attribute->set_name( $attribute_data['attribute_taxonomy'] );
										$attribute->set_options( $attribute_data['term_ids'] );
										$attribute->set_position( 1 );
										$attribute->set_visible( true );
										$attribute->set_variation( true );
										$attributes[] = $attribute;		
										
									}
									
																		
							}
							
                            if(!empty($attributes)){							
								$get_product->set_attributes( $attributes );
								$get_product->save(); 
							}
							
							
							if(!$product_image_mode){
							   $image_url = $product['image_path'];
								if($image_url){
									$upload_dir = wp_upload_dir();
									$response = wp_remote_get($image_url, array( 'timeout' => 120 ) );									
									if( !is_wp_error( $response ) ){										
										$image_data = wp_remote_retrieve_body( $response );
										$filename = basename( $image_url );
										if ( wp_mkdir_p( $upload_dir['path'] ) ) {
										  $file = $upload_dir['path'] . '/' . $filename;
										}
										else {
										  $file = $upload_dir['basedir'] . '/' . $filename;
										}

										$content_handle = file_put_contents( $file, $image_data );
										if($content_handle){
											$wp_filetype = wp_check_filetype( $filename, null );

											$attachment = array(
											  'post_mime_type' => $wp_filetype['type'],
											  'post_title' => sanitize_file_name( $filename ),
											  'post_content' => '',
											  'post_status' => 'inherit'
											);
											$attach_id = wp_insert_attachment( $attachment, $file );
											if($attach_id ){
												require_once( ABSPATH . 'wp-admin/includes/image.php' );
												$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
												wp_update_attachment_metadata( $attach_id, $attach_data ); 
												set_post_thumbnail( $post_id, $attach_id );
											}
										}									}
								}
							}							
						} 
						  $import_limit++;
					   
				    }
				  }
								
							  	
			}
		}

			if ($import_limit < 10) {
						if( $current_brand >= $brand_add_data ){
							update_option('klock_brand_add_current_index','');
							$return_arr['status'] = "done";
						}else{
							$return_arr['status'] = "continue";
							update_option('klock_brand_add_current_index',$current_brand+1);
							$return_arr['current_index'] = get_option('klock_brand_add_current_index');
						}			
						$return_arr['count'] = $import_limit;
						
						echo json_encode($return_arr);
            } else {
						$return_arr['status'] = "continue";
						$return_arr['count'] = $import_limit;
						$return_arr['current_index'] = get_option('klock_brand_add_current_index');
						echo json_encode($return_arr);		
		    }
	
	exit;
}

function klock_upload_brand_product_on_demand___original()
{
    $uid          = get_option('api_userid');
    $pid          = get_option('api_pid');
    $lid          = get_option('api_lid');
    $key          = get_option('api_key');
    $api_version  = get_option('api_version');
    $api_url      = get_option('api_url');
    $import_limit = 0;
	$return_arr = array();
    if (!empty($uid) && !empty($pid) && !empty($lid) && !empty($key) && !empty($api_version)) {
        $brands_data_arr = get_option('klock_brand_name_array');
        $input           = count_klockjatten_array_flatten($brands_data_arr);
        $brand_ids       = array_unique($input);
		$brand_ids 		 = array_values($brand_ids);
		$brand_add_data  = count($brand_ids) - 1;
		update_option('klock_brand_add_total_data',$brand_add_data);
		$current_brand = get_option('klock_brand_add_current_index');
		if(empty($current_brand)){
			$current_brand = 0;
		}
		$id = $brand_ids[$current_brand];	
		$data     = array(
			"uid" => $uid,
			"pid" => $pid,
			"lid" => $lid,
			"key" => $key,
			"api_version" => $api_version,
			"request" => "get_brand_items",
			"id_brand" => $id,
			"display_brand_name"        => true,
			"display_reference"         => true,
			"display_name"              => true,
			"display_stock"             => true,
			"display_weight"            => true,
			"display_retail_price"      => true,
			"display_discount"          => true,
			"display_price"             => true,
			"display_id_supplier"       => true,
			"display_speed_shipping"    => true,
			"display_ean"               => true,
			"display_currency"          => true,
			"display_icon_path"         => true,
			"display_image_path"        => true,
			"display_image_last_update" => true,
			"display_attributes"        => true,				
		);
		$data     = array(
			'data' => json_encode($data)
		);
		$args     = array(
			'method' => 'POST',
			'body' => $data,
			'timeout' => 120
		);
		$response = wp_remote_get($api_url, $args);
		if (is_wp_error($response)) {
		} else {
			$body     = wp_remote_retrieve_body($response);
			$products = array(
				'data' => json_decode($body)
			);
		}
		if (isset($products['data']->rows) && !empty($products['data']->rows)) {
			foreach ($products['data']->rows as $product) {
				if ($import_limit >= 5) {
					break;
				}
				if($product->stock > 0){
					$chk_exist = count_klockjatten_get_product_by_sku($product->id_product);
					if (!$chk_exist) {
						$api_stock         = $product->stock;
						$drop_retial_price = $product->retail_price;
						$drop_price        = $product->price;
						if ($drop_price < $drop_retial_price) {
							$price = $drop_price;
						} else {
							$price = $drop_retial_price;
						}
						$product_status = ( get_option('klock_crete_product_status') ) ? 'draft' : 'publish';
						$staock_status = 'instock'; 	
						$ean_length = strlen($product->ean);
						if($ean_length == 11){
							$new_ean = "00".$product->ean;
						}elseif($ean_length == 12){
							$new_ean = "0".$product->ean;
						}else{
							$new_ean = $product->ean;
						}								
						$post    = array(
							'post_content' => sanitize_text_field($product->attributes),
							'post_title' => esc_html($product->name),
							'post_name' => sanitize_text_field($product->name),
							'post_status' => $product_status,
							'post_type' => 'product',
							'meta_input' => array(
								'_sku' => sanitize_text_field($product->id_product),
								'_regular_price' => sanitize_text_field($product->retail_price),
								'_custom_retail_price' => sanitize_text_field($product->retail_price),
								'_custom_price' => sanitize_text_field($product->price),
								'_stock' => sanitize_text_field($product->stock),
								'_manage_stock' => 'yes',
								'_stock_status' => sanitize_text_field($staock_status),
								'_brand_name' => sanitize_text_field($product->brand_name),
								'_weight' => sanitize_text_field(($product->weight)/1000),
								'_currency' => sanitize_text_field($product->currency),
								'_speed_shipping' => sanitize_text_field($product->speed_shipping),
								'id_supplier' => sanitize_text_field($product->id_supplier),
								'ean' => sanitize_text_field($new_ean),
								'dropshipping_api_product' => 'yes',
							)
						);
						$product_mode = esc_attr( get_option('klock_crete_product_mode') );
						
						$product_image_mode  = get_option('klock_crete_product_image_mode');
						
						if($product_image_mode){
							$post['meta_input']['_image_path'] = esc_url($product->image_path);
						}
							
						if(!$product_mode){
								$post['meta_input']['_sale_price'] = sanitize_text_field($product->price);
								$post['meta_input']['_price'] = sanitize_text_field($price);
						} else {
								$post['meta_input']['_price'] = sanitize_text_field($product->retail_price);
						}
						
								
						$post_id = wp_insert_post($post, true);
						
						if ($post_id) {
							$brand_cat = count_klockjatten_get_category_name_by_brand_id($id);
							if ($brand_cat) {
								wp_set_object_terms($post_id, $brand_cat, 'product_cat', true);
								wp_set_object_terms($post_id, sanitize_text_field($product->brand_name), 'product_tag', true);
							}
							
							 
							$get_product = wc_get_product( $post_id );
							$attributes  = array();
							
							if($product->brand_name){
								
								$attribute_data = klock_create_attribute('Brand'); 
								$attribute_data['term_ids'] = array($product->brand_name);
								$attribute  = new WC_Product_Attribute();
								$attribute->set_id( $attribute_data['attribute_id'] );
								$attribute->set_name( $attribute_data['attribute_taxonomy'] );
								$attribute->set_options( $attribute_data['term_ids'] );
								$attribute->set_position( 1 );
								$attribute->set_visible( true );
								$attribute->set_variation( true );
								$attributes[] = $attribute;
								
							}
							
							if(!empty($product->attributes_array)){
								
									foreach($product->attributes_array as $attributes_array){											
										$attribute_data = klock_create_attribute($attributes_array->group_name); 
										$attribute_data['term_ids'] = array($attributes_array->value_name);
										$attribute      = new WC_Product_Attribute();
										$attribute->set_id( $attribute_data['attribute_id'] );
										$attribute->set_name( $attribute_data['attribute_taxonomy'] );
										$attribute->set_options( $attribute_data['term_ids'] );
										$attribute->set_position( 1 );
										$attribute->set_visible( true );
										$attribute->set_variation( true );
										$attributes[] = $attribute;		
										
									}
									
																		
							}
							
                            if(!empty($attributes)){							
								$get_product->set_attributes( $attributes );
								$get_product->save(); 
							}
							
							
							if(!$product_image_mode){
							   $image_url = $product->image_path;
								if($image_url){
									$upload_dir = wp_upload_dir();
									$response = wp_remote_get($image_url, array( 'timeout' => 120 ) );									
									if( !is_wp_error( $response ) ){										
										$image_data = wp_remote_retrieve_body( $response );
										$filename = basename( $image_url );
										if ( wp_mkdir_p( $upload_dir['path'] ) ) {
										  $file = $upload_dir['path'] . '/' . $filename;
										}
										else {
										  $file = $upload_dir['basedir'] . '/' . $filename;
										}

										$content_handle = file_put_contents( $file, $image_data );
										if($content_handle){
											$wp_filetype = wp_check_filetype( $filename, null );

											$attachment = array(
											  'post_mime_type' => $wp_filetype['type'],
											  'post_title' => sanitize_file_name( $filename ),
											  'post_content' => '',
											  'post_status' => 'inherit'
											);
											$attach_id = wp_insert_attachment( $attachment, $file );
											if($attach_id ){
												require_once( ABSPATH . 'wp-admin/includes/image.php' );
												$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
												wp_update_attachment_metadata( $attach_id, $attach_data ); 
												set_post_thumbnail( $post_id, $attach_id );
											}
										}									}
								}
							}							
						} 
						$import_limit++;
					}
				}
			}
		}

        if ($import_limit < 5) {
			if( $current_brand >= $brand_add_data ){
				update_option('klock_brand_add_current_index','');
				$return_arr['status'] = "done";
			}else{
				$return_arr['status'] = "continue";
				update_option('klock_brand_add_current_index',$current_brand+1);
				$return_arr['current_index'] = get_option('klock_brand_add_current_index');
			}			
			$return_arr['count'] = $import_limit;
			
			echo json_encode($return_arr);
        }else{
			$return_arr['status'] = "continue";
			$return_arr['count'] = $import_limit;
			$return_arr['current_index'] = get_option('klock_brand_add_current_index');
			echo json_encode($return_arr);		
		}
    }
    exit;
}

// Display Fields
add_action('woocommerce_product_options_inventory_product_data', 'klock_woocommerce_product_custom_fields');
// Save Fields
add_action('woocommerce_process_product_meta', 'klock_woocommerce_product_custom_fields_save');
function klock_woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // EAN Text Field
    woocommerce_wp_text_input(
        array(
            'id' => 'ean',
            'placeholder' => 'EAN',
            'label' => __('EAN', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
	
	// Checkbox
	woocommerce_wp_checkbox( 
		array( 
			'id'            => 'dropshipping_api_product', 
			'label' 		=> __('Dropshipping Product', 'woocommerce'),
			'description'   => __( 'Enable this if this product is DropshippingB2B Product', 'woocommerce' ),
			'value'         =>  get_post_meta($post->ID, 'dropshipping_api_product', true ),
			)
	);	
	
    echo '</div>';
}

function klock_woocommerce_product_custom_fields_save($post_id)
{
    $woocommerce_ean = $_POST['ean'];
    update_post_meta($post_id, 'ean', esc_attr($woocommerce_ean));
	update_post_meta( $post_id, 'dropshipping_api_product', $_POST['dropshipping_api_product']);
}

function is_dropshipping_product($product_id){
	return get_post_meta($product_id, 'dropshipping_api_product', true );
}

function is_dropshipping_order($order_id){
	return wc_get_order_item_meta($order_id,'is_dropshipping_order',true);
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

// functionalities in use if needed to show images by urls

function klockjatten_url_is_image( $url ) {
    if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return FALSE;
    }
    $ext = array( 'jpeg', 'jpg', 'gif', 'png' );
    $info = (array) pathinfo( parse_url( $url, PHP_URL_PATH ) );
    return isset( $info['extension'] )
        && in_array( strtolower( $info['extension'] ), $ext, TRUE );
}

add_filter( 'admin_post_thumbnail_html', 'klockjatten_thumbnail_url_field' );

add_action( 'save_post', 'klockjatten_thumbnail_url_field_save', 10, 2 );

function klockjatten_thumbnail_url_field( $html ) {
    global $post;
	
    $value = get_post_meta( $post->ID, '_image_path', TRUE ) ? : "";
	if($value){
		if ( isset( $value['img_url'] ) && $value['img_url'] != '' ){
					$value = $value['img_url'];
		}
		$nonce = wp_create_nonce( 'thumbnail_ext_url_' . $post->ID . get_current_blog_id() );
		$html .= '<input type="hidden" name="thumbnail_ext_url_nonce" value="' 
			. esc_attr( $nonce ) . '">';
		$html .= '<div><p>' . __('Or', 'txtdomain') . '</p>';
		$html .= '<p>' . __( 'Enter the url for external image', 'txtdomain' ) . '</p>';
		$html .= '<p><input type="url" name="thumbnail_ext_url" value="' . $value . '"></p>';
		if ( ! empty($value) && klockjatten_url_is_image( $value ) ) {
			$html .= '<p><img style="max-width:150px;height:auto;" src="' 
				. esc_url($value) . '"></p>';
			$html .= '<p>' . __( 'Leave url blank to remove.', 'txtdomain' ) . '</p>';
		}
		$html .= '</div>';
		return $html;
	} else {
		return $html;
	}
	
}


function klockjatten_thumbnail_url_field_save( $pid, $post ) {
	
	
	$check_meta_exits = get_post_meta( $pid, '_image_path', TRUE );
	
	if($check_meta_exits){
		$cap = $post->post_type === 'page' ? 'edit_page' : 'edit_post';
		if (
			! current_user_can( $cap, $pid )
			|| ! post_type_supports( $post->post_type, 'thumbnail' )
			|| defined( 'DOING_AUTOSAVE' )
		) {
			return;
		}
		$action = 'thumbnail_ext_url_' . $pid . get_current_blog_id();
		$nonce = filter_input( INPUT_POST, 'thumbnail_ext_url_nonce', FILTER_SANITIZE_STRING );
		$url = filter_input( INPUT_POST,  'thumbnail_ext_url', FILTER_VALIDATE_URL );
		if (
			empty( $nonce )
			|| ! wp_verify_nonce( $nonce, $action )
			|| ( ! empty( $url ) && ! klockjatten_url_is_image( $url ) )
		) {
			return;
		}

		if ( ! empty( $url ) ) {
			update_post_meta( $pid, '_image_path', esc_url($url) );
			if ( ! get_post_meta( $pid, '_thumbnail_id', TRUE ) ) {
				update_post_meta( $pid, '_thumbnail_id', 'by_url' );
			}
		} else {
			delete_post_meta( $pid, '_image_path' );
			if ( get_post_meta( $pid, '_thumbnail_id', TRUE ) === 'by_url' ) {
				delete_post_meta( $pid, '_thumbnail_id' );
			}
		}
	}
}


?>