<?php 
class Klock_payment {

	public function __construct() {
		add_action( 'admin_init', array($this,'klock_register_payment_settings' ) );
	}

	public function klock_register_payment_settings() {
		//register our settings
		register_setting( 'klock-payment-settings-group', 'klock_payment_method', array($this,'klock_payment_title_callback' ));
		register_setting( 'klock-payment-settings-group', 'klock_cc_number' );
		register_setting( 'klock-payment-settings-group', 'klock_cc_exp_month' );
		register_setting( 'klock-payment-settings-group', 'klock_cc_exp_year' );
		register_setting( 'klock-payment-settings-group', 'klock_cc_ccv', array($this,'klock_cc_ccv_callback' ));
	}
	public function klock_encrypt($plainText, $key) {
        $secretKey = md5($key);
        $iv = substr( hash( 'sha256', "aaaabbbbcccccddddeweee" ), 0, 16 );
        $encryptedText = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encryptedText);
    }   
	
	public function klock_decrypt($encryptedText, $key) {
        $key = md5($key);
        $iv = substr( hash( 'sha256', "aaaabbbbcccccddddeweee" ), 0, 16 );
        $decryptedText = openssl_decrypt(base64_decode($encryptedText), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decryptedText;
    }	
	
    public function klock_cc_ccv_callback($input){	
		if(empty($input)){
			return $input;
		}
		
		return $this->klock_encrypt($input, 'xxsuperencryptxx');
	}
	
    public function klock_create_payment_page(){
		$uid = get_option('api_userid');
		$pid = get_option('api_pid');
		$lid = get_option('api_lid');
		$key = get_option('api_key');
		$api_version = get_option('api_version');
		$api_url =get_option('api_url');
		$data = array(
				   "uid"          => $uid,
				   "pid"          => $pid,
				   "lid"          => $lid,
				   "key"          => $key,
				   "api_version"  => $api_version,	   
				   "request"      => "get_payment_method"
		);
		$data = array('data' => json_encode($data));
		$args = array(
				'method' => 'POST',
				'body' => $data,
				'timeout' => 120
		);
		$response = wp_remote_get($api_url,$args);
		if ( is_wp_error( $response ) ) {
				echo "<div class='brand_name_select_sec'><div class='api_error'>There is something wrong! Please try again later.</div></div>";						
				exit;			
		}else{
			$body = wp_remote_retrieve_body( $response );
			$payment_data = json_decode($body);			
			if($payment_data->rc !=0){
				echo "<div class='brand_name_select_sec'><div class='api_error'>".$payment_data->message." Check your API Setting.</div></div>";						
				exit;
			}
		}

				
        ?>
		<div class="main-section tabcontent active" id="setting">
			<div class="setting-formsection-container">
			<?php  settings_errors(); ?>
				<div class="klock_center">						
					<form method="post" action="options.php">
						<div class="brand_name_select_sec1"><h3>Payment Settings</h3></div>					
						<?php 
						settings_fields( 'klock-payment-settings-group' ); 
						do_settings_sections( 'klock-payment-settings-group' ); 
						$order_mode =  (esc_attr( get_option('klock_crete_order_mode') ) == "processing") ? 'checked' : ''; 
						$klock_cc_ccv = $this->klock_decrypt(get_option('klock_cc_ccv'), 'xxsuperencryptxx');
						?>
						
						<h2 class="title">Checkout Payment Methods</h2>
						<p class="content-section">Please choose the payment method to be used on the store for checkout.</p>
						<table class="form-table klock-tl">
							<tr valign="top">
							<th scope="row">Payment Method</th>
							<td>
							<select name="klock_payment_method" class="klock_payment_method_select">
							<option>Select Method</option>
							<?php
							if(isset($payment_data->rows) && !empty($payment_data->rows)){
								foreach($payment_data->rows as $method){
									$pay_sel = ((get_option('klock_payment_method')) == $method->cod) ? 'selected' : '';
								     echo "<option value='".$method->cod."' ".$pay_sel.">".$method->name."</option>";
								}
							}
							  
							?>
							</select>
							</td>
							</tr>
							<?php 
							$class = '';
							if(get_option('klock_payment_method') == 1 || get_option('klock_payment_method') == 'p'  ){
								$class = 'hidden';
							}
							?>
							<tr valign="top" class='klock_ax_d <?= $class; ?>'>
							<th scope="row">Credit card Number</th>
							<td><input type="text" placeholder="4111 1111 1111 1111" name="klock_cc_number" value="<?=      esc_attr( get_option('klock_cc_number') ) ?>" class="regular-text code"/></td>
							</tr>
							
							<tr valign="top" class='klock_ax_d <?= $class; ?>'>
							<th scope="row">Month Expiration</th>
							<td><input type="text" placeholder="MM" name="klock_cc_exp_month" value="<?= esc_attr( get_option('klock_cc_exp_month') )  ?>" class="regular-text code"/></td>
							</tr>
							
							<tr valign="top" class='klock_ax_d <?= $class; ?>'>
							<th scope="row">Year Expiration</th>
							<td><input type="text" placeholder="YYYY" name="klock_cc_exp_year" value="<?=  esc_attr( get_option('klock_cc_exp_year') )  ?>" class="regular-text code"/></td>
							</tr>
							
							<tr valign="top" class='klock_ax_d <?= $class; ?>'>
							<th scope="row">CCV/CCV2 Code
							<span class="info-detail-style">
								<i class="fa fa-info-circle" aria-hidden="true"></i>
								<span class="toltip-show-style">Look for the 4-digit code printed on the front of your card just above and to the right of your main credit card number. This 4-digit code is your Card Identification Number (CID). The CID is the four-digit code printed just above the Account Number.</span>
							</span>
							</th>
							<td><input type="text" placeholder="1234" name="klock_cc_ccv" value="<?=  $klock_cc_ccv; ?>" class="regular-text code"/></td>
							</tr>
						</table>
						
						<?php submit_button(); ?>

					</form>
				</div>
			</div>
		</div>
		<script>
		jQuery(function(){
			jQuery(".klock_payment_method_select").change(function(){
				var val = jQuery(this).val();
				if(val == 1 || val == 'p'){
					jQuery('.klock_ax_d').addClass('hidden');
					
				}else{
					jQuery('.klock_ax_d').removeClass('hidden');
				}
			});
			
			
			
			
			
		});
		</script>
        <?php
    }


}
if ( is_admin() )
	$klock_payment = new Klock_payment();
?>