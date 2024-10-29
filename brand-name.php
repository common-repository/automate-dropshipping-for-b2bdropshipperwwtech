<?php 

class KlockjattenBrand {

	function __construct() {

		add_action( 'admin_init', array($this,'klock_register_brand_name_setting') );

    }

	function klock_register_brand_name_setting(){

		register_setting( 'klock_brand_name_group', 'klock_brand_name_array' );

	}

   	function klock_add_brand(){  
		settings_errors(); 
		$uid = get_option('api_userid');
		$pid = get_option('api_pid');
		$lid = get_option('api_lid');
		$key = get_option('api_key');
		$api_version = get_option('api_version');
		$api_url =get_option('api_url');

		if(!empty($uid) && !empty($pid) && !empty($lid) && !empty($key) && !empty($api_version))
		{
			//Getting all categories		
			$args = array(
				'number'     => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false
			);

			$product_categories = get_terms( 'product_cat', $args );
			if(!$product_categories){
				echo "<div class='brand_name_select_sec'><div class='api_error'>Please Create Category !</div></div>";
				exit();
			}			

        ?>            
			<div class='klock_import_product notice notice-info'><div class="count_msg"></div><div class="klock_upload_btn">
			<button class="button button-primary" id="klock_upload_product">Upload Products</button>
			<div class="klock_loader"></div>		
			<span class="klock_load_process"></span></div><i>Note : Once clicked on upload do not close page or refresh untill process is done.</i>
			</div>
			<div class='notice notice-info'><div class="count_msg"><strong><span>To Update products in bulk, Use this feature! </span></strong></div><div class="klock_upload_btn">
			<button class="button button-primary" id="klock_update_product">Update Products</button>
			<div class="klock_update_loader"></div>		
			<span class="klock_update_process"></span></div><i>Note : Once clicked on "Update Products" do not close page or refresh untill process is done.</i>
			</div>
			
			
			<form class="setting-section_form" method="post" action="options.php" >
				<?php  settings_fields( 'klock_brand_name_group' ); ?>
		    	<?php do_settings_sections( 'klock_brand_name_group' ); ?>          
				<div class='brand_name_select_sec'><h3>Select Brand Name</h3></div>
					<?php 
						//Getting dropship brand name by api
						$data = array(
								   "uid"             => $uid,
								   "pid"             => $pid,
								   "lid"             => $lid,
								   "key"             => $key,
								   "api_version"     => $api_version ,
								   "request"         => "get_brands"
						);
						$data = array('data' => json_encode($data));
						$args = array(
								'method'	=> 'POST',
								'body' 		=> $data,								
								'timeout'	=> 120,
							);

						$response = wp_remote_get($api_url,$args);
						if ( is_wp_error( $response ) ) {
		
							echo 'There is something wrong! Please try again later.';
							
						}else{
							$body = wp_remote_retrieve_body( $response );
							$brand_name = array('data' => json_decode( $body ));
							if($brand_name['data']->rc !=0){
								echo "<div class='brand_name_select_sec'><div class='api_error'>".$brand_name['data']->message." Check your API Setting.</div></div>";						
							}
						}
               						

						if(isset($brand_name['data']->rows)){			
						echo "<div class='klockjatten_category_container'><img class='klock_brand_loader' src='".plugin_dir_url( dirname( __FILE__ ) ) ."automate-dropshipping-for-b2bdropshipperwwtech/assets/img/loading.gif'><div class='klockjatten_category_primary_container'>";
						$brands_data = get_option('klock_brand_name_array');
						if(empty($brands_data)){
							$brands_data = array();
						}
						
						foreach($product_categories as $category){
							/****** Start Add Product Dropship Api ********/
									
							$brand_ids='';
                            if(isset($brands_data[$category->slug])){
								$brand_ids = $brands_data[$category->slug];
							}

							?>
							<div class='klockjatten_single_category_container'>	
                              <div class='klockjatten_category_name'><?php echo $category->name; ?></div>							
							  <div class="multiselect klock-brand-select">							  
								<div class="selectBox">
									<select class="js-select2" multiple="multiple" name="klock_brand_name_array[<?php echo $category->slug; ?>][]">
										<?php
										foreach ( $brand_name['data']->rows as $res) { 
											$checked = '';
											if($brand_ids){
												if(in_array($res->id_brand,$brand_ids)){
													$checked = 'selected';
												}
											}
											?>
											<option value="<?php echo $res->id_brand;?>" <?php echo $checked; ?>><?php echo $res->name .' ( '. $res->group .' - '. $res->category .  ' )'; ?></option>
											<?php
										}
										?>
									</select>
								</div>
							  </div>
							</div>
							<?php				
							/****** End Add Product Dropship Api ********/
						}
						?>
						<input class="klock_brand_save_btn" type="submit" id="btnCheck" name="add_brand" value="Save"  />
						</div>
						</div>
						<?php } ?>
			</form>
			<?php 
        }else{
			echo "<h1>Please fill all the required fields in setting page. Go to <a href='".get_admin_url()."admin.php?page=klockjatten-menu'>setting</a></h1>";
		}	
		?>
			<script>
			jQuery(function() {
			jQuery(".js-select2").select2({
						closeOnSelect : true,
						placeholder : "Select Brands",
						allowHtml: true,
						allowClear: true,
						tags: true 
			});

			jQuery('.klockjatten_category_container img').hide();
			jQuery('.klockjatten_category_primary_container').show();
			jQuery('#klock_upload_product').click(function(){
				jQuery("#klock_upload_product").attr("disabled", true);
				jQuery(".klock_loader").show();
                run_klock_upload_ajax_on_demand(); 
			});
			jQuery('#klock_update_product').click(function(){
				jQuery("#klock_update_product").attr("disabled", true);
				jQuery(".klock_update_loader").show();
                run_klock_upload_ajax_on_demand_2(); 
			});

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
								jQuery(".klock_loader").hide();
							}else{
								run_klock_upload_ajax_on_demand();
							}
					  }
				});
			}

			function run_klock_upload_ajax_on_demand_2(){
				var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
				jQuery.ajax({
					  url:      ajaxurl,
					  data:    ({action  : 'klock_upload_brand_product_on_demand_2'}),
					  success: function(data){
							var data = JSON.parse(data);
							console.log(data);
							//on_demand += data.count;
							jQuery(".klock_update_process").text('Product Updating ... ');
							if(data.status == 'done'){
								jQuery(".klock_update_process").text('Process Completed.');
								jQuery(".klock_update_process").css("color", "#11a42f");
								jQuery(".klock_update_loader").hide();
							}else{
								run_klock_upload_ajax_on_demand_2();
							}
					  }
				});
			}									
			});			
			function klock_count_klock_add_product(){
				var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";			
				jQuery.ajax({
					  url:      ajaxurl,
					  data:    ({action  : 'count_klock_add_product',status:'start'}),
					  success: function(data){
						var data = JSON.parse(data); 
						if(data.status == 'continue'){
							klock_count_klock_add_product();
						}
						if(data.status == 'found'){
							jQuery('.klock_import_product .count_msg').html(data.html);
							jQuery('.klock_import_product').show();
						}
					  }
				});
			}
			klock_count_klock_add_product();
			</script>
		<?php	
	}
}
$brand_obj = new KlockjattenBrand();
?>