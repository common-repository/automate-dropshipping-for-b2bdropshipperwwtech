<?php 
class Klock_general {

	public function __construct() {
		add_action( 'admin_init', array($this,'klock_register_general_settings' ) );
	}

	public function klock_register_general_settings() {
		//register our settings
		register_setting( 'klock-general-settings-group', 'klock_ship_title' );
		register_setting( 'klock-general-settings-group', 'klock_ship_desc' );
		register_setting( 'klock-general-settings-group', 'klock_ship_company_name' );
		register_setting( 'klock-general-settings-group', 'klock_ship_tracking_no' );
		register_setting( 'klock-general-settings-group', 'klock_ship_not_available' );
		register_setting( 'klock-general-settings-group', 'klock_crete_order_mode' );
		register_setting( 'klock-general-settings-group', 'klock_crete_product_mode' );
		register_setting( 'klock-general-settings-group', 'klock_crete_product_status' );
		register_setting( 'klock-general-settings-group', 'klock_crete_product_image_mode' );
		
		
	}  
	
    public function klock_create_general_page(){

        ?>
		<div class="main-section tabcontent active" id="setting">
			<div class="setting-formsection-container">
			<?php  settings_errors(); ?>
			<div class="klock_center klock_center_style">
				<form method="post" action="options.php">
					<div class="brand_name_select_sec1 brand-title-sec"><h3>General Settings</h3></div>
					<?php settings_fields( 'klock-general-settings-group' ); ?>
					<?php do_settings_sections( 'klock-general-settings-group' ); ?>
					<?php 
						$order_mode =  (esc_attr( get_option('klock_crete_order_mode') ) == "processing") ? 'checked' : '';
						
                        $product_mode =  (esc_attr( get_option('klock_crete_product_mode') ) == "yes") ? 'checked' : '';
						
                        $product_status =  (esc_attr( get_option('klock_crete_product_status') ) == "yes") ? 'checked' : '';
						$product_image_mode =  (esc_attr( get_option('klock_crete_product_image_mode') ) == "yes") ? 'checked' : '';

					?>
					<div class="section-style-common section-divider-bg">
						<h2 class="title">About Product Prices - Regular Price vs Selling Price: </h2>
						<table class="form-table table_style_klock_setting klock_radio klock_general-setting">
							<tr valign="top" class="klock_gn_set">
							<th style="white-space:nowrap" scope="row">Do you want to sell the products on “SRP (Suggested Retail Price)” without Discount price?
							<span class="info-detail-style">
								<i class="fa fa-info-circle" aria-hidden="true"></i>
								<span class="toltip-show-style">This will allow you to sell the products on regular prices of woocoomerce without any discounts. According to wwt.it drop shipper n data flow (all available formats: CSV, XML, JSON and API) there three fields related to products prices:<br>
								SRP (Suggest Retail Price) - it is the price suggest from product Mother House<br>
								Discount - in % the discount we apply to our resellers<br>
								Net Price - resellers purchase price</span>
							</span>
							</th>
							<td>
							<input type="checkbox" name="klock_crete_product_mode" value="yes" class="regular-text code" <?= $product_mode ?>/>
							</td>
							</tr>
						</table>
					</div>
					
					<div class="section-style-common section-divider-bg">
						<h2 class="title">About Product Publishing: </h2>
						<table class="form-table  klock_radio klock_about_publish_prod">
							<tr valign="top" class="klock_gn_set">
							<th style="white-space:nowrap" scope="row">Want to publish the product directly on save without updating regular price, discount price etc?
							</th>
							</tr>
							<tr>
							<td>
							   <input type="radio" name="klock_crete_product_status" value="" class="regular-text code" checked/>Yes
							   <span class="info-detail-style">
									<i class="fa fa-info-circle" aria-hidden="true"></i>
									<span class="toltip-show-style"> This will allow the products to be saved directly on the store without making any changes in the regular price and selling price.</span>
								</span>
								<input type="radio" name="klock_crete_product_status" value="yes" class="regular-text code not_first_side_radio"<?= $product_status?>>No
								<span class="info-detail-style">
									<i class="fa fa-info-circle" aria-hidden="true"></i>
									<span class="toltip-show-style">This will allow you to save the products in woocommerce draft first. Whenever you want to publish the products, you can do it.  In this case, you have a freedom to sell the products at your prices. </span>
								</span>
							</td>
							</tr>
						</table>
					</div>
					
					<div class="section-style-common section-divider-bg">
						<h2 class="title">About Importing Product Images: </h2>
						<table class="klock_radio klock_about_publish_prod">
							<tr valign="top" class="klock_gn_set">
							<th style="white-space:nowrap" scope="row">
							</th>
							</tr>
							<tr>
								<td style="padding-top:10px;">
									<input type="radio" name="klock_crete_product_image_mode" value="" class="regular-text code" checked/><strong style="padding-left:5px;">By Default, all  the product images wil be save in Wordpress Media.</strong><br>
								</td>
							</tr>
							<tr>
								<td style="padding-top:6px;">
									<input type="radio" name="klock_crete_product_image_mode" value="yes" class="regular-text code not_first_side_radio" <?= $product_image_mode ?>><strong style="padding-left:5px;">Do You Want To Save Images Url?</strong>
								</td>
							</tr>
						</table>
					</div>
					<div class="section-style-common section-divider-bg">
						<h2 class="title">Woocommerce Order Status Settings:</h2>
						<p class="content-section">Please choose the order status while sending your order to the drop shipper.</p>
						<table class="form-table klock_radio klock_dropshiping_style">
							<tr valign="top">
								<th scope="row">Create Order :</th>
							</tr>
							<tr>
								<td>
									<input type="radio" name="klock_crete_order_mode" value="" class="regular-text code" checked/> When the order status is complete ?
									<span class="info-detail-style">
										<i class="fa fa-info-circle" aria-hidden="true"></i>
										<span class="toltip-show-style">This option works when the merchant has received the payments from the customer and in woocommerce the order status will appear as “ready” initially.</span>
									</span>
								</td>
								<td style="padding-left:30px;">
									<input type="radio" name="klock_crete_order_mode" value="processing" class="regular-text code" <?php echo $order_mode; ?>/> When the order status in process ? 
									<span class="info-detail-style">
										<i class="fa fa-info-circle" aria-hidden="true"></i>
										<span class="toltip-show-style"> This option is used only for the testing purpose. This option allows the customer to place an order and send to drop shipper without payments. In this case, the American Bank wire payment method works by default.</span>
									</span>
								</td>
							</tr>
						</table>
					</div>
					<div class="section-style-common">
						<h2 class="title">Shipping Email Template:</h2>
						<p class="content-section">Please draft your email template which you want to send to customers when their order has been confirmed and shipped by wwt.it dropshipper. </p>
						<table class="form-table klock_inner">
							<tr valign="top">
							<th scope="row">Subject/ Title:</th>
							<td><input type="text" name="klock_ship_title" value="<?php echo esc_attr( get_option('klock_ship_title') ); ?>" class="regular-text code"/></td>
							</tr>
							
							<tr valign="top">
							<th scope="row">Body Text:</th>
							<td><textarea rows="8" cols="50" name="klock_ship_desc"><?php echo esc_attr( get_option('klock_ship_desc') ); ?></textarea></td>
							</tr>
							
							<tr valign="top">
							<th scope="row">Tracking Number:</th>
							<td><input type="text" name="klock_ship_tracking_no" value="<?php echo esc_attr( get_option('klock_ship_tracking_no') ); ?>" class="regular-text code"/></td>
							</tr>

							<tr valign="top">
							<th scope="row">Company Name:</th>
							<td><input type="text" name="klock_ship_company_name" value="<?php echo esc_attr( get_option('klock_ship_company_name') ); ?>" class="regular-text code"/></td>
							</tr>
							
							
							<tr valign="top">
							<th scope="row">Not Available:</th>
							<td><input type="text" name="klock_ship_not_available" value="<?php echo esc_attr( get_option('klock_ship_not_available') ); ?>" class="regular-text code"/></td>
							</tr>
						</table>
					</div>
					<?php submit_button(); ?>

				</form>
			</div>
			</div>
		</div>
        <?php
    }


}
if ( is_admin() )
	$general = new Klock_general();
?>