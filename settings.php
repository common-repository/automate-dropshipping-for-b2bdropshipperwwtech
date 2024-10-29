<?php 

class KlockjattenSettings{

	function __construct() {
		add_action( 'admin_init', array($this,'klock_register_plugin_settings') );
    }

    function klock_register_plugin_settings() {

		register_setting( 'klock_settings_group', 'api_userid' );
		register_setting( 'klock_settings_group', 'api_pid' );
		register_setting( 'klock_settings_group', 'api_key' );
		register_setting( 'klock_settings_group', 'api_lid' );
		register_setting( 'klock_settings_group', 'api_version' );
		register_setting( 'klock_settings_group', 'api_url' );
	}

	function klock_add_setting_options(){ 	

	?>
		<style>
		.tooltip {
			position: relative;
			display: inline-block;
		}

		.tooltip .tooltiptext {
			visibility: hidden;
			width: 140px;
			background-color: #555;
			color: #fff;
			text-align: center;
			border-radius: 6px;
			padding: 5px;
			position: absolute;
			z-index: 1;
			bottom: 150%;
			left: 50%;
			margin-left: -75px;
			opacity: 0;
			transition: opacity 0.3s;
		}

		.tooltip .tooltiptext::after {
			content: "";
			position: absolute;
			top: 100%;
			left: 50%;
			margin-left: -5px;
			border-width: 5px;
			border-style: solid;
			border-color: #555 transparent transparent transparent;
		}

		.tooltip:hover .tooltiptext {
			visibility: visible;
			opacity: 1;
		}
		</style>
    	<div class="main-section tabcontent active" id="setting">
    	<div class="setting-formsection-container">
		<?php  settings_errors(); ?></p>
	    	<form class="setting-section_form" method="post" action="options.php">
			<div class="brand_name_select_sec1"><h3>API Settings</h3></div>
	    		<?php  settings_fields( 'klock_settings_group' ); ?>
		    	<?php do_settings_sections( 'klock_settings_group' ); ?>
				<div class="form-api-setting">
					<h3 class="sub-title-section">About  WWT.it Drop shipper Keys</h3>
					<p class="content-section">Please enter the API Keys( Prod or Dev Ambient) provided by wwt.it provided in email.</p>
					<ul class="multi-column">
						<li>
							<label>User Id</label>
							<input type="text" name="api_userid" placeholder="Enter User Id"  value="<?php echo esc_attr( get_option('api_userid') ); ?>" required>
						</li>
						<li>
							<label>Portal Id</label>
							<input type="text" name="api_pid" placeholder="Enter PID" value="<?php echo esc_attr( get_option('api_pid') ); ?>" required>
						</li>
					</ul>
					<ul class="multi-column">
						<li>
							<label>API Key</label>
							<input type="text" name="api_key" placeholder="Enter API key" value="<?php echo esc_attr( get_option('api_key') ); ?>" required>
						</li>
						<li>
							<label>Language Id</label>
							<input type="text" name="api_lid" placeholder="Enter LID" value="<?php echo esc_attr( get_option('api_lid') ); ?>" required>
						</li>
					</ul>	

					<ul class="multi-column">
						<li>
							<label> API URL</label>
							<input type="text" name="api_url" placeholder="Enter Api Url" value="<?php echo esc_attr( get_option('api_url') ); ?>" required>
						</li>
						<li>
							<label>API Version</label>
							<input type="text" name="api_version" placeholder="Enter Api version" value="<?php echo esc_attr( get_option('api_version') ); ?>" required>
						</li>
					</ul>
						<input type="submit" class="klock_settings_btn" name="add_category" value="SAVE" />
				</div>

				<div class='cron_setting'>
					<div class="sec-title-main">
						<h3 class="sub-title-section">About Cron Job Settings</h3>
						<p class="content-section">Below are the links to setup a cron on server side. You can copy and paste these links to your server Cpanel > Cron Settings. You need to setup a 4 cron jobs in the background.</p>
					</div>
					<div class="labelStyleprod">
					 <strong>Cron to add a new product? 
					 <span class="info-detail-style">
						<i class="fa fa-info-circle" aria-hidden="true"></i>
						<span class="toltip-show-style">This cron is used to import new and update the products from the drop shiper. You can hit this link manually too to import products.</span>
					 </span>
					 </strong>
					 <input type='text' size="80px" id="copy_add_product" value="<?php echo admin_url('admin-ajax.php?action=klock_add_new_products_cron'); ?>" readonly>
					 <div class="tooltip"><button onclick="CopyTextFunction('copy_add_product'); return false;" onmouseout="outFunc('copy_add_product')"><span class="tooltiptext" id="myTooltip-copy_add_product">Copy to clipboard</span>COPY</button></div> 
					</div>
				    <div class="labelStyleprod">
					<strong>Cron to manage a removed product? 
					 <span class="info-detail-style">
						<i class="fa fa-info-circle" aria-hidden="true"></i>
						<span class="toltip-show-style">This cron is used/ run when the drop shiper either removed the products or out of stock from there. This will be automatically moved the published products in the trash so no order will be accepted further until the product comes in stock. </span>
					 </span>
					</strong>
					 <input type='text' size="80px" id="copy_manage_product" value="<?php echo admin_url('admin-ajax.php?action=klock_manage_removed_product'); ?>" readonly>
					 <div class="tooltip"><button onclick="CopyTextFunction('copy_manage_product'); return false;" onmouseout="outFunc('copy_manage_product')"><span class="tooltiptext" id="myTooltip-copy_manage_product">Copy to clipboard</span>COPY</button></div> 
					</div>					 
				    <div class="labelStyleprod">
					<strong>Cron to change order status ?
					<span class="info-detail-style">
						<i class="fa fa-info-circle" aria-hidden="true"></i>
						<span class="toltip-show-style">This cron is used/run when the dropshiper change the order status from ready to ship. By default, the customer places an order, the order will be in “ready state” initially.</span>
					</span>
					</strong>
					 <input type='text' size="80px" id="copy_order_status" value='<?php echo admin_url('admin-ajax.php?action=klockjatten_order_status_change_cron'); ?>' readonly> 
					 <div class="tooltip"><button onclick="CopyTextFunction('copy_order_status'); return false;" onmouseout="outFunc('copy_order_status')"><span class="tooltiptext" id="myTooltip-copy_order_status">Copy to clipboard</span>COPY</button></div>
					</div>
				    <div class="labelStyleprod">
						<strong>Cron to send an email to customer when the order is shipped by drop shiper with the Shipping Company and  Order Tracking ID ?
						<span class="info-detail-style">
							<i class="fa fa-info-circle" aria-hidden="true"></i>
								<span class="toltip-show-style"> This cron will send an email to customer when the order is shipped by drop shipper with their shipping company and tracking id. You can customize the content of the email from General Settings > Email Template section.</span>
						</span>
						</strong>
						<input type='text' size="80px" id="copy_shipped_mail" value='<?php echo admin_url('admin-ajax.php?action=klockjatten_order_shipped_mail_sender'); ?>' readonly> 
						<div class="tooltip"><button onclick="CopyTextFunction('copy_shipped_mail'); return false;" onmouseout="outFunc('copy_shipped_mail')"><span class="tooltiptext" id="myTooltip-copy_shipped_mail">Copy to clipboard</span>COPY</button></div>
					</div>
					<div class="note-content-style">
						<p><strong>Note: </strong>By default, you need to setup the above 4 cron jobs on the server side. While setting up the cron jobs you need to choose the time (hours, minutes, seconds etc.; how frequently you want to synchronize the store with the drop shipper) as well as paste the above link. </p>
					</div>
				</div>				
			</form>			
    	</div>
		</div>
		<script>
		function CopyTextFunction(id) {
			var copyText = document.getElementById(id);
			copyText.select();
			copyText.setSelectionRange(0, 99999);
			document.execCommand("copy");
			var tooltip = document.getElementById("myTooltip-"+id);
			tooltip.innerHTML = "Copied";
		}

		function outFunc(id) {
			var tooltip = document.getElementById("myTooltip-"+id);
			tooltip.innerHTML = "Copy to clipboard";
		}
		</script>
<?php

    }	
}
$klock_setting = new KlockjattenSettings();