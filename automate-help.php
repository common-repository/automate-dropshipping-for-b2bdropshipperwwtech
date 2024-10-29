<div class="main-section tabcontent active" id="setting">
	<div class="setting-formsection-container">
		<div class="klock_center klock_center_full">
			<div class="brand_name_select_sec1">
				<h3>Automate Dropshipping Guide (Overview)</h3>
			</div>
			<div class="klock_100 klock_style_main">
				<ul class="klock_list klock_style_tab">
					<li class="klock_list_style active klock_about_tab"><a class="active" href="javascript:void(0);" data-tag="about">About</a></li>
					<li class="klock_list_style klock_installation_tab"><a href="javascript:void(0);" data-tag="installation">Installation</a></li>
					<li class="klock_list_style klock_faq_tab"><a href="javascript:void(0);" data-tag="faq_tab">FAQ</a></li>
					<li class="klock_list_style klock_support_tab"><a href="javascript:void(0);" data-tag="support">Support</a></li>
					<li class="klock_list_style klock_cron_tab"><a href="javascript:void(0);" data-tag="cron_setting_tab">Cron Setting</a></li>
				</ul>
				<div class="klock_100 klock_style_content">
					<div class="klock_about klock_similr" id="about">
						<h2>Description</h2>
						<p>Automate Dropshipping for B2BDropshipper(WWTech) plugin provides fully integration with woocommerce to automate import products and manage orders.</p>
						<h2>FEATURES AND OPTIONS:</h2>
						<ul>
							<li>Easily to install & Configured.</li>
							<li>It automatically imports all the products from WWTech Dropshipper to the woocomerce store.</li>
							<li>Once all the products are imported, it is saved as draft so that the store admin can easily publish the products with the provided information or can update it the content before publish.</li>
							<li>Once the products are published and the store start receiving an orders, each order the store received will be saved in the woocommerce orders as well sent to the WWTech Dropshipper for further processing automatically.</li>
							<li>As the order(s) is received by WWTech Droppshipper, when you start processing the order (for example, they start shipping the products to the customer, update the stock etc). With the help of cron, the order status updates automatically in woocomerce and a customer will receive an email about the order has been shipped with the “Shipping Company & Tracking ID”. </li>
							<li>To synchronize the woocomerce inventory, orders and shipment, the cron will be setup on the server and will run in the background according to the duration setup.</li>
							<li>All the product images are saved in the Wordpress > Media.</li>						
						</ul>
						<h2>Version: <?php echo KLOCK_VERSION; ?></h2>
					</div>

					<div class="klock_installation klock_similr hide" id="installation">
						<h2>Installation</h2>
						<h4>REQUIRES WOOCOMMERCE</h4>
						<ul>
							<li>Upload the plugin files to the ‘/wp-content/plugins/automate-dropshippingb2b’ directory, or install the plugin through the WordPress plugins screen directly.</li>
							<li>Activate the plugin through the ‘Plugins’ screen in WordPress</li>
							<li>Go to API settings page from Admin menu > Automate B2B > API Settings and fill the required fileds.</li>
							<li>Go to Automate B2B > Brand Names and assign your brand name to category.</li>
							<li>Go to Automate B2B > Payment and fill your credentials to create order in dropshippingB2B.</li>
							<li>Go to API settings page from Admin menu > Automate B2B > API Settings copy cron job url and setup it with cron job.</li>
							<li>Done.</li>
						</ul>
						<h2>Screenshots</h2>
						<h2 class="klock_stp">1. Step</h2>
						<p class="klock_p">Upload the plugin files to the ‘/wp-content/plugins/automate-dropshippingb2b’ directory, or install the plugin through the WordPress plugins screen directly.</p>
						<img src="<?php echo plugins_url('assets/img/d-1.JPG', __FILE__); ?>">
						<br>
						<h2 class="klock_stp">2. Step</h2>
						<p class="klock_p">Activate the plugin through the ‘Plugins’ screen in WordPress</p>
						<img src="<?php echo plugins_url('assets/img/d-2.JPG', __FILE__); ?>">

						<br>
						<h2 class="klock_stp">3. Step</h2>
						<p class="klock_p">Go to settings page from Admin menu > Automate B2B > API Settings and fill the required fileds.</p>
						<img src="<?php echo plugins_url('assets/img/d-3.JPG', __FILE__); ?>">

						<br>
						<h2 class="klock_stp">4. Step</h2>
						<p class="klock_p">Go to Automate B2B > Brand Names and assign your brand name to category. Note - You need to create at least one category in woocoomerce then select brand name.</p>
						<img src="<?php echo plugins_url('assets/img/d-4.JPG', __FILE__); ?>">

						<br>
						<h2 class="klock_stp">5. Step</h2>
						<p class="klock_p">Go to Automate B2B > Payment and fill your credentials to create order in dropshippingB2B.</p>
						<img src="<?php echo plugins_url('assets/img/d-5.JPG', __FILE__); ?>">

						<br>
						<h2 class="klock_stp">6. Step</h2>
						<p class="klock_p">Go to settings page from Admin menu > Automate B2B > Settings copy cron job url and setup it with cron job.</p>
						<img src="<?php echo plugins_url('assets/img/d-6.JPG', __FILE__); ?>">

						<br>
						<h2 class="klock_stp">7. Done</h2>

						<br>
						<br>
						<h2>Cron job Settings</h2>
						<ul>
							<li>Cron job will be set on the server. Settings differs server by server. So, suggested is to please read the manuals of hosting provider for this.</li>
							<li>You need to setup a 3 crons here. 1. For Add New Products , 2. For Order Status Change, and 3. For send Order Shipped Email.</li>
							<li>To setup a cron job, you need 2 mandatory information. 1. Future Time ( 1 hr, 2 hrs etc...), and 2. Crons URLs we provided in Admin menu > Automate B2B > Settings.  Copy these URLS and paste in Cronjob> Settings. </li>					
							<li>Note: Cron job will be set on the server. Settings differs server by server. So, suggested is to please read the manuals of hosting provider for this.</li>

						</ul>
					</div>


					<div class="klock_faq klock_similr hide" id="faq_tab">
						<h2>FAQ</h2>
						<div class="faq__accordian-main-wrapper" id="faq__accordian-main-wrapper">
								<div class="faq__accordion-wrapper">
									<a class="faq__accordian-heading active" href="#">How to setup?</a>
									<div class="faq__accordion-content" style="display: block;">
										<p>Just activate the plugin, go to plugin settings and fill the required fileds.</p>
									</div>
								</div>
								<div class="faq__accordion-wrapper">
									<a class="faq__accordian-heading" href="#">How to import products?</a>
									<div class="faq__accordion-content" style="display: none;">
										<p>Go to API settings page from Admin menu > Automate B2B > API Settings copy add new product url and setup it with cron job for automate import product or manually hit the url.</p>
									</div>
								</div>
								<div class="faq__accordion-wrapper">
									<a class="faq__accordian-heading" href="#">How to change order status new to ready?</a>
									<div class="faq__accordion-content" style="display: none;">
										<p>Go to API settings page from Admin menu > Automate B2B > API Settings copy order status change url and setup it with cron job for automate change status or manually hit the url.</p>
									</div>
								</div>
								<div class="faq__accordion-wrapper">
									<a class="faq__accordian-heading" href="#">How Automate Dropshipping for B2B Dropshipper works?</a>
									<div class="faq__accordion-content" style="display: none;">
										<p>Our Plugin is compatible with woocoomerce and easily installed & configured on any of the store that has wordpress and woocommerce setup. Any potential customer who used WWTech services can use it with the Development Keys Or live keys.</p>
									</div>
								</div>
								<div class="faq__accordion-wrapper">
									<a class="faq__accordian-heading" href="#">Which Payment Methods Supported by plugin?</a>
									<div class="faq__accordion-content" style="display: none;">
										<h3 style="margin-top:0;">American Bank Wire</h3>  
										<p>American Bank Wire payment method works by default. In this case, the customer do not need to submit any card or other details. From the General Settings of the Order Settings, for the testing purpose, you can choose “ when order is in process. 
										Suggestion: Use order Settings > When order status is in complete state according to woocommerce order statuses, when you are using a dev ambient access.</p>
										<h3>American Express</h3>
										<p>American Express payment method is used when you want to accept payments from the customer directly.  For this, you will have to provide the complete American Express Card Details, mainly, Card Number, Expiration Month, Expiration Year and CCV/CCV2 Code.
										Suggestion: Use order Settings > When order status is in complete state according to woocommerce order statuses, when you are using a prod ambient access</p>
									</div>
								</div>
								<div class="faq__accordion-wrapper">
									<a class="faq__accordian-heading" href="#">How to test the plugin?</a>
									<div class="faq__accordion-content" style="display: none;">
										<p><strong>The basic requirements to test the WWTech Dropshipper: </strong></p>
										<p>
											<ul>
												<li>Wordpress and Woocomerce Installed.</li>
												<li> Install our plugin - <a href="https://wordpress.org/plugins/automate-dropshipping-for-b2bdropshipperwwtech/">Automate Dropshipping for B2BDropshipper(WWTech)</a> </li>
												<li> Requires WWTech Dropshipper User ID and Development Keys</li>
												<li> Requires Products Information ( Brand Categories etc..)</li>
												<li> Setup a cronjob on the service with the preschedule time for each cron to run. On server, 3 crons need to setup:	
													<ol>
													<li>For Add new product, manage stock and price</li>
													<li>For order status change new to ready.</li>
													<li>For Shipped Email</li>
													</ol>
												</li>
												<li> Setup the Payment Account: For the testing purpose you can add these details to submit the order to WWTech Dropshipper, these are just a test details once you start using the plugin, you can replace with the actual payment methods which the dropshipper provided. 
													<ol>
														<li>Payment Method: American Express</li>
														<li>CC_Number:  378282246310005 (For credit Cards)</li>
														<li>CC_Exp_Month: Any Future Month</li>
														<li>CC_Exp_Year: Any Future Year</li>
														<li>CC_CCV: 1325</li>
														<li>OR you can fill the Advance Bank Wire </li>
														<li>Payment Method: Advanced Bank Wire</li>
													</ol>
												</li>	
												<li> Once all the information is added in the plugin and cron is setup on server. The next step is to how to import products and place an order
													<ol>
													<li>Go to the shop page of the store</li>
													<li>Choose a product to purchase</li>
													<li>Add the product to the cart or tab on “Checkout”</li>
													<li>Complete the checkout process</li>
													<li>Upon successful submission of order, the order will be visible in the Woocommerce > Orders </li>
													<li>Admin can check the same order in WWTech Dropshipper account</li>
													<li>When the WWTech received an order and updated the product.</li>
													<ol>
												</li>												
											</ul>
										</p>
									</div>
								</div>
							</div>
						</div>

						<div class="klock_support klock_similr hide" id="support">
							<h2>Having trouble using the plugin? Please contact us:</h2>
							<a href="https://midriffinfosolution.org/" target="_blank"><i class="fa fa-globe" aria-hidden="true"></i></a>
							<!-- <a href="tel:+1(209) 20314-8375"><i class="fa fa-volume-control-phone" aria-hidden="true"></i></a> -->
							<a href="mailto:i.midriff@gmail.com"><i class="fa fa-envelope" aria-hidden="true"></i></a>
							<a href="skype:i.midriff?add"><i class="fa fa-skype" aria-hidden="true"></i></a>
						</div>

						<div class="klock_cron klock_similr hide" id="cron_setting_tab">
							Text Not Available
						</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('.faq__accordian-heading').click(function(e){
        e.preventDefault();
        if (!jQuery(this).hasClass('active')) {
            jQuery('.faq__accordian-heading').removeClass('active');
            jQuery('.faq__accordion-content').slideUp(500);
            jQuery(this).addClass('active');
            jQuery(this).next('.faq__accordion-content').slideDown(500);
        }
        else if (jQuery(this).hasClass('active')) {
            jQuery(this).removeClass('active');
            jQuery(this).next('.faq__accordion-content').slideUp(500);
        }
    });
	jQuery('.klock_list_style a').click(function(){
		jQuery('.klock_list_style a').removeClass('active');
		jQuery(this).addClass('active');
		var tagid = jQuery(this).data('tag');
		jQuery('.klock_similr').removeClass('activeTab').addClass('hide');
		jQuery('#'+tagid).addClass('activeTab').removeClass('hide');
	});
});
</script>