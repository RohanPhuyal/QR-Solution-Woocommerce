<?php
	
	if ( !function_exists( 'kwp_yape_peru_admin_script' ) ) {
		function kwp_yape_peru_admin_script() {

			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
			wp_enqueue_script( 'kwp-yape-peru-admin', plugins_url( '/assets/woopro.js', __FILE__ ), array( 'jquery' ), '1.1', false );
			wp_enqueue_style( 'kwp-yape-peru-admin', plugins_url( '/assets/woopro.css', __FILE__ ), array(), '1.1' );
		}
	}
	add_action( 'admin_enqueue_scripts', 'kwp_yape_peru_admin_script' );

	if ( !function_exists( 'kwp_yape_peru_payment_popup' ) ) {
		function kwp_yape_peru_payment_popup() {
			
			$options = get_option( 'woocommerce_wocommerce_yape_peru_settings' );
			$qr_options = isset( $options['qr_options'] ) ? $options['qr_options'] : array();
			
			// Get color options
			$bg_color = isset( $options['popup_bg_color'] ) ? $options['popup_bg_color'] : '#ffffff';
			$text_color = isset( $options['popup_text_color'] ) ? $options['popup_text_color'] : '#000000';
			$close_btn_color = isset( $options['popup_close_btn_color'] ) ? $options['popup_close_btn_color'] : '#000000';
			$continue_btn_color = isset( $options['popup_continue_btn_color'] ) ? $options['popup_continue_btn_color'] : '#00bcd4';
			
			// If no QR options, don't show popup
			if( empty( $qr_options ) ) {
				return;
			}
			
			// Get first option for default display
			$first_option = reset( $qr_options );
			?>
			
			<!-- Hidden data divs for JavaScript to read -->
			<div class="kwp-qr-data-hidden" style="display: none;">
				<?php foreach( $qr_options as $index => $qr_option ) : ?>
					<div class="kwp-qr-data-item" 
						data-index="<?php echo esc_attr( $index ); ?>"
						data-option-name="<?php echo esc_attr( $qr_option['name'] ); ?>" 
						data-qr-image="<?php echo esc_url( $qr_option['qr_image'] ); ?>" 
					data-popup-description="<?php echo isset( $qr_option['popup_description'] ) ? esc_attr( $qr_option['popup_description'] ) : ''; ?>" 
					data-phone="<?php echo isset( $qr_option['phone_number'] ) ? esc_attr( $qr_option['phone_number'] ) : ''; ?>" 
					data-limit="<?php echo isset( $qr_option['limit_amount'] ) ? esc_attr( $qr_option['limit_amount'] ) : ''; ?>" 
					data-limit-message="<?php echo isset( $qr_option['limit_message'] ) ? esc_attr( $qr_option['limit_message'] ) : ''; ?>">
				</div>
			<?php endforeach; ?>
		</div>
		
			<div class="popup-wrapper">
				<span class="helper"></span>
				<div class="popup-main-wrapper" style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
					<div class="popupCloseButton" style="color: <?php echo esc_attr( $close_btn_color ); ?>;">&times;</div>
					<div class="first-step">
						<?php if( count( $qr_options ) > 1 ) : ?>
							<div class="kwp-popup-qr-selector">
								<h3 style="font-size: 18px; margin-bottom: 15px;"><?php echo __( 'Select Payment Method', 'payment-qr-woo' ); ?></h3>
								<div class="kwp-popup-qr-options">
									<?php foreach( $qr_options as $index => $qr_option ) : 
										if( empty( $qr_option['qr_image'] ) ) continue;
										$is_first = ( $index === array_key_first( $qr_options ) );
									?>
										<div class="kwp-popup-option-item <?php echo $is_first ? 'active' : ''; ?>" data-index="<?php echo esc_attr( $index ); ?>">
											<?php if( !empty( $qr_option['icon_image'] ) ) : ?>
												<img src="<?php echo esc_url( $qr_option['icon_image'] ); ?>" alt="<?php echo esc_attr( $qr_option['name'] ); ?>" />
											<?php endif; ?>
											<span><?php echo esc_html( $qr_option['name'] ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						
						<div class="kwp-qr-display">
							<?php if( !empty( $first_option['qr_image'] ) ) : ?>
								<img src="<?php echo esc_url( $first_option['qr_image'] ); ?>" class="popup-qr" />
								<?php if ( isset( $first_option['phone_number'] ) && !empty( $first_option['phone_number'] ) ) : ?>
									<span class="telephone-number"><a href="tel:<?php echo esc_attr( $first_option['phone_number'] ); ?>"><?php echo __( 'Add Contact:', 'payment-qr-woo' ); ?> <?php echo esc_attr( $first_option['phone_number'] ); ?></a></span>
								<?php endif; ?>
								<span class="price"><?php echo __( 'Amount to Pay', 'payment-qr-woo' ); ?></span>
								<?php if ( isset( $first_option['limit_message'] ) && !empty( $first_option['limit_message'] ) ) : ?>
									<p class="message-limit-amount" style="display: none;"><?php echo esc_attr( $first_option['limit_message'] ); ?></p>
								<?php endif; ?>
								<?php if ( isset( $first_option['popup_description'] ) && !empty( $first_option['popup_description'] ) ) : ?>
									<p><?php echo esc_html( $first_option['popup_description'] ); ?></p>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="popup-price-wrapper" data-price-limit="<?php echo isset( $first_option['limit_amount'] ) ? esc_attr( $first_option['limit_amount'] ) : ''; ?>"></div>
					</div>
					<div class="second-step">
						<form method="post" enctype="multipart/form-data" novalidate="" class="box has-advanced-upload">
							<div class="box__input">
								<input type="file" name="files" id="file" class="box__file" accept=".png, .jpg, .jpeg, .gif">
								<label for="file"><?php echo __( 'Drag and Drop File to Upload', 'payment-qr-woo' ); ?> <br/><br/> <?php echo __( 'or', 'payment-qr-woo' ); ?></label>
								<button type="submit" class="box__button"><?php echo __( 'Select File', 'payment-qr-woo' ); ?></button>
						</div>
						<input type="hidden" name="ajax" value="1">
						<input type="hidden" name="selected_qr_option" class="selected-qr-option-input" value="<?php echo esc_attr( $first_option['name'] ); ?>">
					</form>
					<div class="box__preview">
						<div class="box__filename"></div>
						<div class="box__image-preview"></div>
					</div>
						<div class="error"><?php echo __( 'Please Upload Your Receipt', 'payment-qr-woo' ); ?></div>
						<img src="<?php echo plugins_url( '/assets/loader.gif', __FILE__ ) ?>" class="loader" />
						<input type="submit" name="final_order" class="finalized_order btn_submit" value="<?php echo __( 'Complete Purchase', 'payment-qr-woo' ); ?>" style="background-color: <?php echo esc_attr( $continue_btn_color ); ?>;">
					</div>
				</div>
			</div>
		<?php
	}
}
add_action( 'wp_footer', 'kwp_yape_peru_payment_popup' );

	if ( !function_exists( 'kwp_yape_peru_front_script' ) ) {
		function kwp_yape_peru_front_script() {

			wp_enqueue_script( 'kodewp_payment_qr', plugins_url( 'assets/woopro-front.js', __FILE__ ), array( 'jquery' ), '1.2.4', true );
			wp_enqueue_style( 'kodewp_payment_qr', plugins_url( 'assets/woopro-front.css', __FILE__ ), array(), '1.2.4' );
			wp_localize_script( 'kodewp_payment_qr', 'kwajaxurl', 
				array( 
					'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
				)
			);
			
			wp_localize_script('kodewp_payment_qr', 'kwp_translate',
				array(
					'kwp_pqr_btn_continue' => __('Continue', 'payment-qr-woo'),
					'kwp_pqr_upload_images' => __('Please only upload images', 'payment-qr-woo'),
				)
			);

		}
	}
	add_action( 'wp_enqueue_scripts', 'kwp_yape_peru_front_script' );

	function kwp_yape_peru_qr_code_upload_dir( $dir ) {

		$dir_name = 'yape-peru-qrcode';

		if ( !is_dir( $dir['basedir']."/".$dir_name ) ) {
			//Create our directory if it does not exist
			mkdir( $dir['basedir']."/".$dir_name );
			$createfile = fopen( $dir['basedir']."/".$dir_name.'/index.html', 'wb' );
		}

		return array(
			'path'	 => $dir['basedir'] . '/yape-peru-qrcode',
			'url'	 => $dir['baseurl'] . '/yape-peru-qrcode',
			'subdir' => '/yape-peru-qrcode',
		) + $dir;
	}

	if ( !function_exists( 'kwp_yape_peru_qr_code_callback' ) ) {
		function kwp_yape_peru_qr_code_callback() {

			if( ! isset( $_FILES ) ) {
				return;
			}
			
			session_start();

			foreach( $_FILES as $file ) {  
				if( is_array( $file ) ) {
			
					require_once( ABSPATH . 'wp-admin/includes/admin.php' );
					
					// Register our path override.
					add_filter( 'upload_dir', 'kwp_yape_peru_qr_code_upload_dir' );

    				$overrides = array( 'test_form' => false, 'mimes' => $allowed_file_types );

					// Do our thing. WordPress will move the file to 'uploads/yape-peru-qrcode'.
					$file_return = wp_handle_upload( $file, $overrides );

					// Set everything back to normal.
					remove_filter( 'upload_dir', 'kwp_yape_peru_qr_code_upload_dir' );
					
					if( isset( $file_return['url'] ) ) {
						$_SESSION['yape-peru-qrcode'] = $file_return['url'];
						
						// Save selected QR option name
						if( isset( $_POST['selected_qr_option'] ) ) {
							$_SESSION['yape-peru-qr-option-name'] = sanitize_text_field( $_POST['selected_qr_option'] );
						}
						
						echo 'yes';
						die();
					}
				}
			}
			echo 'no';
			die();
		}
	}
	add_action( 'wp_ajax_kwp_yape_peru_qr_code', 'kwp_yape_peru_qr_code_callback' );
	add_action( 'wp_ajax_nopriv_kwp_yape_peru_qr_code', 'kwp_yape_peru_qr_code_callback' );

	/* Add meta box for edit order */
	if ( !function_exists( 'kwp_yape_peru_meta_box' ) ) {
		function kwp_yape_peru_meta_box() {
			if (version_compare(WC_VERSION, '7.0.0', '>=')) {
				add_meta_box( 'kwp-yape-peru-meta-box', __( 'QR Code Payment Receipt', 'payment-qr-woo' ), 'kwp_yape_peru_meta_box_callback', 'woocommerce_page_wc-orders', 'normal' );
		    } else {
                 add_meta_box( 'kwp-yape-peru-meta-box', __( 'QR Code Payment Receipt', 'payment-qr-woo' ), 'kwp_yape_peru_meta_box_callback', 'shop_order', 'normal' );
		    }
		}
	}
	add_action( 'add_meta_boxes', 'kwp_yape_peru_meta_box' );

	/* Meta box callback */
	if ( !function_exists( 'kwp_yape_peru_meta_box_callback' ) ) {
		function kwp_yape_peru_meta_box_callback( $post ) {

			$yape_peru_qrcode = get_post_meta( $post->ID, 'yape-peru-qrcode', true );
			$qr_option_name = get_post_meta( $post->ID, 'yape-peru-qr-option-name', true );
			
			if ( ! empty( $qr_option_name ) ) {
				echo '<p><strong>' . __( 'Payment Method Used:', 'payment-qr-woo' ) . '</strong> ' . esc_html( $qr_option_name ) . '</p>';
			}
			
			if ( ! empty( $yape_peru_qrcode ) && esc_url( $yape_peru_qrcode ) ) {
				echo '<p><strong>' . __( 'Payment Receipt:', 'payment-qr-woo' ) . '</strong></p>';
				echo '<a href="'.esc_url( $yape_peru_qrcode ).'" target="_blank">';
					echo '<img src="'.esc_url( $yape_peru_qrcode ).'" alt="" width="200" height="200" />';
				echo '</a>';
			}
		}
	}