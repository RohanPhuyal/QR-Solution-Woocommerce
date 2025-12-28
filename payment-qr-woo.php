<?php
/*
 * Plugin Name: QR Solution Woocommerce
 * Description: QR Payment Solution for WooCommerce. Modified from Miguel Fuentes' work.
 * Requires at least: 5.2
 * Tested up to: 6.9
 * Requires PHP: 7.0
 * Version: 1.0.0
 * Author: Rohan Phuyal
 * Plugin URI: #
 * Author URI: https://rohanphuyal.com.np/
 * Text Domain: qr-payment-solution
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
/*
 * Define the constant name for the language
 */

function kwp_yape_peru_load_textdomain()
{
	load_plugin_textdomain('qr-payment-solution', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'kwp_yape_peru_load_textdomain');

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'kwp_yape_peru_add_gateway_class');
function kwp_yape_peru_add_gateway_class($gateways)
{
	$gateways[] = 'Kwp_Yape_Peru_WC_Gateway';
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'kwp_yape_peru_init_gateway_class');
function kwp_yape_peru_init_gateway_class()
{

	if (class_exists('WC_Payment_Gateway')) {

		require plugin_dir_path(__FILE__) . 'functions.php';

		class Kwp_Yape_Peru_WC_Gateway extends WC_Payment_Gateway
		{

			public function __construct()
			{

				$this->id = 'wocommerce_yape_peru'; // payment gateway plugin ID
				$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
				$this->has_fields = true; // in case you need a custom credit card form
				$this->method_title = __('Payment QR WooCommerce', 'qr-payment-solution');
				$this->method_description = __('QR Payment Method.', 'qr-payment-solution'); // will be displayed on the options page

				// gateways can support subscriptions, refunds, saved payment methods,
				// but in this tutorial we begin with simple payments
				$this->supports = array(
					'products'
				);

				// Method with all the options fields
				$this->init_form_fields();

				// Load the settings.
				$this->init_settings();
				$this->title = $this->get_option('title');
				$this->description = $this->get_option('description');
				$this->enabled = $this->get_option('enabled');

				// This action hook saves the settings
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			}

			/**
			 * Plugin options, we deal with it in Step 3 too
			 */
			public function init_form_fields()
			{

				$this->form_fields = array(
					'enabled' => array(
						'title' => __('Enable/Disable', 'qr-payment-solution'),
						'label' => __('Enable Payment QR WooCommerce', 'qr-payment-solution'),
						'type' => 'checkbox',
						'description' => '',
						'default' => 'no'
					),
					'title' => array(
						'title' => __('Title', 'qr-payment-solution'),
						'type' => 'text',
						'description' => __('This controls the title the user sees during checkout.', 'qr-payment-solution'),
						'default' => __('Select Payment Method', 'qr-payment-solution'),
						'desc_tip' => true,
					),
					'qr_options_repeater' => array(
						'type' => 'qr_options_repeater',
					),
					// COD Mode Settings
					'enable_cod_mode' => array(
						'title' => __('Enable COD Mode', 'qr-payment-solution'),
						'type' => 'checkbox',
						'label' => __('Enable COD vs Full Payment Toggle', 'qr-payment-solution'),
						'default' => 'no',
					),
					'cod_title' => array(
						'title' => __('COD Button Title', 'qr-payment-solution'),
						'type' => 'text',
						'default' => __('Cash on Delivery', 'qr-payment-solution'),
					),
					'cod_icon' => array(
						'title' => __('COD Button Icon URL', 'qr-payment-solution'),
						'type' => 'text',
					),
					'qr_group_title' => array(
						'title' => __('Full Payment Button Title', 'qr-payment-solution'),
						'type' => 'text',
						'default' => __('Full Payment', 'qr-payment-solution'),
					),
					'qr_group_icon' => array(
						'title' => __('Full Payment Button Icon URL', 'qr-payment-solution'),
						'type' => 'text',
					),
					'popup_bg_color' => array(
						'title' => __('Popup Background Color', 'qr-payment-solution'),
						'type' => 'text',
						'default' => '#ffffff',
						'class' => 'color-picker',
					),
					'popup_text_color' => array(
						'title' => __('Popup Text Color', 'qr-payment-solution'),
						'type' => 'text',
						'default' => '#000000',
						'class' => 'color-picker',
					),
					'popup_close_btn_color' => array(
						'title' => __('Close Button Color', 'qr-payment-solution'),
						'type' => 'text',
						'default' => '#000000',
						'class' => 'color-picker',
					),
					'popup_continue_btn_color' => array(
						'title' => __('Continue Button Color', 'qr-payment-solution'),
						'type' => 'text',
						'default' => '#00bcd4',
						'class' => 'color-picker',
					),
					'enable_cod_prepayment' => array(
						'title' => __('COD Pre-Payment', 'qr-payment-solution'),
						'label' => __('Enable COD Pre-Payment (Remaining Amount)', 'qr-payment-solution'),
						'type' => 'checkbox',
						'description' => __('When enabled, only subtotal + VAT will be marked as remaining to pay. Used by Nepal Can Move API.', 'qr-payment-solution'),
						'default' => 'no',
						'desc_tip' => true,
					),
				);
			}

			public function generate_kwp_yape_peru_icon_html($key, $data)
			{
				$field = $this->plugin_id . $this->id . '_' . $key;
				$defaults = array(
					'class' => 'button-secondary',
					'css' => '',
					'custom_attributes' => array(),
					'desc_tip' => false,
					'description' => '',
					'title' => '',
				);

				$data = wp_parse_args($data, $defaults);

				ob_start();
				?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>
						<?php echo $this->get_tooltip_html($data); ?>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
							<div class="upload_area woocommerce-yape-peru-upload-wrapper">
								<span><?php echo __('Upload application logo', 'qr-payment-solution'); ?></span>
								<button class="<?php echo esc_attr($data['class']); ?>" type="button"
									name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>"
									style="<?php echo esc_attr($data['css']); ?>" <?php echo $this->get_custom_attribute_html($data); ?>><?php echo wp_kses_post($data['title']); ?></button>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr($field); ?>"><?php echo __('Preview', 'qr-payment-solution'); ?></label>
					</th>
					<td class="forminp yape-preview-area">
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo __('Preview', 'qr-payment-solution'); ?></span></legend>
							<div class="preview_icon_area">
								<?php
								$options = get_option('woocommerce_wocommerce_yape_peru_settings');
								if (isset($options['preview_icon']) && !empty($options['preview_icon'])) {
									?>
									<img src="<?php echo esc_url($options['preview_icon']); ?>" class="upload_icon">
									<button class="remove_icon button-secondary"
										type="button"><?php echo __('Remove', 'qr-payment-solution'); ?></button>
									<?php echo esc_html($this->get_description_html($data)); ?>
								<?php } ?>
							</div>
						</fieldset>
					</td>
				</tr>
				<?php
				return ob_get_clean();
			}

			public function generate_button_html($key, $data)
			{
				$field = $this->plugin_id . $this->id . '_' . $key;
				$defaults = array(
					'class' => 'button-secondary',
					'css' => '',
					'custom_attributes' => array(),
					'desc_tip' => false,
					'description' => '',
					'title' => '',
				);

				$data = wp_parse_args($data, $defaults);

				ob_start();
				?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>
						<?php echo $this->get_tooltip_html($data); ?>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
							<div class="upload_area woocommerce-yape-peru-upload-wrapper">
								<span><?php echo __('Upload the QR here', 'qr-payment-solution'); ?></span>
								<button class="<?php echo esc_attr($data['class']); ?>" type="button"
									name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>"
									style="<?php echo esc_attr($data['css']); ?>" <?php echo $this->get_custom_attribute_html($data); ?>><?php echo wp_kses_post($data['title']); ?></button>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr($field); ?>"><?php echo __('Preview', 'qr-payment-solution'); ?></label>
					</th>
					<td class="forminp yape-preview-area">
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo __('Preview', 'qr-payment-solution'); ?></span></legend>
							<div class="preview_area">
								<?php
								$options = get_option('woocommerce_wocommerce_yape_peru_settings');
								if (isset($options['preview_qr']) && !empty($options['preview_qr'])) {
									?>
									<img src="<?php echo $options['preview_qr'] ?>" class="upload_qr">
									<button class="remove_qr button-secondary"
										type="button"><?php echo __('Remove', 'qr-payment-solution'); ?></button>
									<?php echo $this->get_description_html($data); ?>
								<?php } ?>
							</div>
						</fieldset>
					</td>
				</tr>
				<?php
				return ob_get_clean();
			}

			public function generate_qr_options_repeater_html($key, $data)
			{
				$field = $this->plugin_id . $this->id . '_' . $key;
				$defaults = array(
					'title' => '',
					'class' => '',
					'css' => '',
					'desc_tip' => false,
					'description' => '',
					'custom_attributes' => array(),
				);

				$data = wp_parse_args($data, $defaults);
				$options = get_option('woocommerce_wocommerce_yape_peru_settings');
				$qr_options = isset($options['qr_options']) ? $options['qr_options'] : array();

				ob_start();
				?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr($field); ?>"><?php echo wp_kses_post($data['title']); ?></label>
						<?php echo $this->get_tooltip_html($data); ?>
					</th>
					<td class="forminp">
						<fieldset class="kwp-qr-options-wrapper">
							<legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
							<div class="kwp-qr-options-container">
								<?php
								if (!empty($qr_options) && is_array($qr_options)) {
									foreach ($qr_options as $index => $qr_option) {
										$this->render_qr_option_row($index, $qr_option);
									}
								} else {
									// Render at least one empty row
									$this->render_qr_option_row(0, array());
								}
								?>
							</div>
							<button type="button"
								class="button button-secondary kwp-add-qr-option"><?php echo __('Add QR Option', 'qr-payment-solution'); ?></button>
							<?php echo $this->get_description_html($data); ?>
						</fieldset>
					</td>
				</tr>
				<?php
				return ob_get_clean();
			}

			private function render_qr_option_row($index, $qr_option)
			{
				$option_name = isset($qr_option['name']) ? esc_attr($qr_option['name']) : '';
				$qr_image = isset($qr_option['qr_image']) ? esc_url($qr_option['qr_image']) : '';
				$icon_image = isset($qr_option['icon_image']) ? esc_url($qr_option['icon_image']) : '';
				$popup_description = isset($qr_option['popup_description']) ? esc_attr($qr_option['popup_description']) : '';
				?>
				<div class="kwp-qr-option-row" data-index="<?php echo esc_attr($index); ?>">
					<div class="kwp-qr-option-header">
						<h4><?php echo sprintf(__('Payment Option #%d', 'qr-payment-solution'), $index + 1); ?></h4>
						<button type="button"
							class="button button-link-delete kwp-remove-qr-option"><?php echo __('Remove', 'qr-payment-solution'); ?></button>
					</div>
					<div class="kwp-qr-option-fields">
						<p>
							<label><?php echo __('Option Name (e.g., Yape, Plin, Bank)', 'qr-payment-solution'); ?></label>
							<input type="text" name="woocommerce_wocommerce_yape_peru_qr_options[<?php echo esc_attr($index); ?>][name]"
								value="<?php echo $option_name; ?>"
								placeholder="<?php echo esc_attr__('E.g., Yape', 'qr-payment-solution'); ?>" class="kwp-option-name" />
						</p>
						<p>
							<label><?php echo __('Icon Image', 'qr-payment-solution'); ?></label>
							<input type="hidden"
								name="woocommerce_wocommerce_yape_peru_qr_options[<?php echo esc_attr($index); ?>][icon_image]"
								value="<?php echo $icon_image; ?>" class="kwp-option-icon-url" />
							<button type="button"
								class="button button-secondary kwp-upload-option-icon"><?php echo __('Select Icon', 'qr-payment-solution'); ?></button>
							<?php if ($icon_image): ?>
							<div class="kwp-option-icon-preview">
								<img src="<?php echo $icon_image; ?>" style="max-width: 80px; display: block; margin-top: 5px;" />
								<button type="button"
									class="button button-link-delete kwp-remove-option-icon"><?php echo __('Remove Icon', 'qr-payment-solution'); ?></button>
							</div>
						<?php else: ?>
							<div class="kwp-option-icon-preview" style="display: none;"></div>
						<?php endif; ?>
						</p>
						<p>
							<label><?php echo __('QR Code Image', 'qr-payment-solution'); ?></label>
							<input type="hidden"
								name="woocommerce_wocommerce_yape_peru_qr_options[<?php echo esc_attr($index); ?>][qr_image]"
								value="<?php echo $qr_image; ?>" class="kwp-option-qr-url" />
							<button type="button"
								class="button button-secondary kwp-upload-option-qr"><?php echo __('Select QR Code', 'qr-payment-solution'); ?></button>
							<?php if ($qr_image): ?>
							<div class="kwp-option-qr-preview">
								<img src="<?php echo $qr_image; ?>" style="max-width: 150px; display: block; margin-top: 5px;" />
								<button type="button"
									class="button button-link-delete kwp-remove-option-qr"><?php echo __('Remove QR', 'qr-payment-solution'); ?></button>
							</div>
						<?php else: ?>
							<div class="kwp-option-qr-preview" style="display: none;"></div>
						<?php endif; ?>
						</p>
						<p>
							<label><?php echo __('Popup Description', 'qr-payment-solution'); ?></label>
							<textarea
								name="woocommerce_wocommerce_yape_peru_qr_options[<?php echo esc_attr($index); ?>][popup_description]"
								rows="3" style="width: 100%;"
								placeholder="<?php echo esc_attr__('Description to show in payment popup', 'qr-payment-solution'); ?>"><?php echo $popup_description; ?></textarea>
						</p>
					</div>
				</div>
				<?php
			}

			public function validate_qr_options_repeater_field($key, $value)
			{
				// Return the raw value - we'll sanitize it in the sanitize method
				return $value;
			}

			public function sanitize_qr_options_repeater_field($value)
			{
				if (!is_array($value)) {
					return array();
				}

				$sanitized = array();
				foreach ($value as $index => $qr_option) {
					if (!is_array($qr_option)) {
						continue;
					}

					$sanitized_option = array();

					if (isset($qr_option['name'])) {
						$sanitized_option['name'] = sanitize_text_field($qr_option['name']);
					}

					if (isset($qr_option['icon_image'])) {
						$sanitized_option['icon_image'] = esc_url_raw($qr_option['icon_image']);
					}

					if (isset($qr_option['qr_image'])) {
						$sanitized_option['qr_image'] = esc_url_raw($qr_option['qr_image']);
					}

					if (isset($qr_option['popup_description'])) {
						$sanitized_option['popup_description'] = sanitize_textarea_field($qr_option['popup_description']);
					}


					// Only add if at least name and QR image are present
					if (!empty($sanitized_option['name']) || !empty($sanitized_option['qr_image'])) {
						$sanitized[] = $sanitized_option;
					}
				}

				return $sanitized;
			}

			public function process_admin_options()
			{
				// Get the posted data
				$post_data = $this->get_post_data();

				// Handle qr_options separately
				if (isset($_POST['woocommerce_wocommerce_yape_peru_qr_options'])) {
					$qr_options = $_POST['woocommerce_wocommerce_yape_peru_qr_options'];
					$sanitized_qr_options = $this->sanitize_qr_options_repeater_field($qr_options);
					$this->update_option('qr_options', $sanitized_qr_options);
				}

				// Process other options normally
				foreach ($this->get_form_fields() as $key => $field) {
					if ($key === 'qr_options') {
						continue; // Already handled above
					}

					if ('title' !== $this->get_field_type($field)) {
						try {
							$this->settings[$key] = $this->get_field_value($key, $field, $post_data);
						} catch (Exception $e) {
							$this->add_error($e->getMessage());
						}
					}
				}

				return update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings), 'yes');
			}
			/* You will need it if you want your custom credit card form, Step 4 is about it
			 */
			public function payment_fields()
			{
				$options = get_option('woocommerce_wocommerce_yape_peru_settings');
				$qr_options = isset($options['qr_options']) ? $options['qr_options'] : array();

				// COD Mode Logic
				$enable_cod = isset($options['enable_cod_mode']) ? $options['enable_cod_mode'] : 'no';

				if ($enable_cod === 'yes') {
					$cod_title = !empty($options['cod_title']) ? $options['cod_title'] : 'Cash on Delivery';
					$cod_icon = !empty($options['cod_icon']) ? $options['cod_icon'] : '';

					$qr_title = !empty($options['qr_group_title']) ? $options['qr_group_title'] : 'Full Payment';
					$qr_icon = !empty($options['qr_group_icon']) ? $options['qr_group_icon'] : '';

					?>
					<div class="kwp-checkout-qr-selector kwp-cod-mode">
						<div class="kwp-checkout-qr-options">
							<!-- COD Option -->
							<label class="kwp-checkout-qr-option active" data-type="cod">
								<input type="radio" name="kwp_payment_type" value="cod" checked style="display: none;" />
								<?php if ($cod_icon): ?>
									<img src="<?php echo esc_url($cod_icon); ?>" alt="<?php echo esc_attr($cod_title); ?>" />
								<?php endif; ?>
								<span class="kwp-option-name"><?php echo esc_html($cod_title); ?></span>
							</label>

							<!-- Full Payment / QR Option -->
							<label class="kwp-checkout-qr-option" data-type="qr">
								<input type="radio" name="kwp_payment_type" value="qr" style="display: none;" />
								<?php if ($qr_icon): ?>
									<img src="<?php echo esc_url($qr_icon); ?>" alt="<?php echo esc_attr($qr_title); ?>" />
								<?php endif; ?>
								<span class="kwp-option-name"><?php echo esc_html($qr_title); ?></span>
							</label>
						</div>

						<!-- Container for QR Options -->
						<div class="kwp-qr-options-container" style="margin-top: 15px;">
							<?php
							if (empty($qr_options)) {
								echo '<p>' . __('No payment options configured.', 'qr-payment-solution') . '</p>';
							} else {
								echo '<p style="margin-bottom: 5px;"><strong>' . __('Select Bank/Wallet:', 'qr-payment-solution') . '</strong></p>';
								echo '<div class="kwp-checkout-qr-options">';
								$index = 0;
								foreach ($qr_options as $qr_option):
									$option_name = isset($qr_option['name']) ? $qr_option['name'] : '';
									$icon_image = isset($qr_option['icon_image']) ? $qr_option['icon_image'] : '';
									$checked = $index === 0 ? 'checked' : '';
									$active_class = $index === 0 ? 'active' : '';
									?>
									<label class="kwp-checkout-qr-option kwp-sub-option <?php echo esc_attr($active_class); ?>"
										data-index="<?php echo esc_attr($index); ?>">
										<input type="radio" name="kwp_selected_qr_option" value="<?php echo esc_attr($index); ?>" <?php echo $checked; ?> style="display: none;" />
										<?php if ($icon_image): ?>
											<img src="<?php echo esc_url($icon_image); ?>" alt="<?php echo esc_attr($option_name); ?>" />
										<?php endif; ?>
										<span class="kwp-option-name"><?php echo esc_html($option_name); ?></span>
									</label>
									<?php
									$index++;
								endforeach;
								echo '</div>';
							}
							?>
						</div>
						<div id="kwp-payment-info-box" style="margin-top: 15px; font-weight: bold; color: #333; line-height: 1.6;"></div>
					</div>
					<?php
					return;
				}

				if (empty($qr_options)) {
					echo '<p>' . __('No payment options configured. Please contact the site administrator.', 'qr-payment-solution') . '</p>';
					return;
				}
				?>
				<div class="kwp-checkout-qr-selector">
					<div class="kwp-checkout-qr-options">
						<?php
						$index = 0;
						foreach ($qr_options as $qr_option):
							$option_name = isset($qr_option['name']) ? $qr_option['name'] : '';
							$icon_image = isset($qr_option['icon_image']) ? $qr_option['icon_image'] : '';
							$checked = $index === 0 ? 'checked' : '';
							$active_class = $index === 0 ? 'active' : '';
							?>
							<label class="kwp-checkout-qr-option kwp-sub-option <?php echo esc_attr($active_class); ?>"
								data-index="<?php echo esc_attr($index); ?>">
								<input type="radio" name="kwp_selected_qr_option" value="<?php echo esc_attr($index); ?>" <?php echo $checked; ?> style="display: none;" />
								<?php if ($icon_image): ?>
									<img src="<?php echo esc_url($icon_image); ?>" alt="<?php echo esc_attr($option_name); ?>" />
								<?php endif; ?>
								<span class="kwp-option-name"><?php echo esc_html($option_name); ?></span>
							</label>
							<?php
							$index++;
						endforeach;
						?>
					</div>
					<div id="kwp-payment-info-box" style="margin-top: 15px; font-weight: bold; color: #333; line-height: 1.6;"></div>
				</div>
				<?php
			}

			/*
			 * We're processing the payments here, everything about it is in Step 5
			 */
			public function process_payment($order_id)
			{

				if (!session_id()) {
					session_start();
				}
				$order = wc_get_order($order_id);

				if (isset($_SESSION['yape-peru-qrcode'])) {
					update_post_meta($order_id, 'yape-peru-qrcode', esc_url_raw($_SESSION['yape-peru-qrcode']));
					unset($_SESSION['yape-peru-qrcode']);
				}

				if (isset($_SESSION['yape-peru-qr-option-name'])) {
					$option_name = sanitize_text_field($_SESSION['yape-peru-qr-option-name']);
					update_post_meta($order_id, 'yape-peru-qr-option-name', $option_name);

					// Update the main payment method title to the specific option name
					$order->set_payment_method_title($option_name);
					$order->save();

					unset($_SESSION['yape-peru-qr-option-name']);
				}

				// Handle COD/QR Mode and Remaining Amount
				if (isset($_POST['kwp_payment_type'])) {
					$payment_type = sanitize_text_field($_POST['kwp_payment_type']);
					update_post_meta($order_id, 'kwp_payment_type', $payment_type);

					// Handle COD Pre-payment Logic
					$options = get_option('woocommerce_wocommerce_yape_peru_settings');
					$enable_cod_prepayment = isset($options['enable_cod_prepayment']) ? $options['enable_cod_prepayment'] : 'yes';
					$enable_cod_mode = isset($options['enable_cod_mode']) ? $options['enable_cod_mode'] : 'no';

					if ($enable_cod_mode === 'yes' && $payment_type === 'cod') {
						$cod_title = isset($options['cod_title']) && !empty($options['cod_title']) ? $options['cod_title'] : 'Cash on Delivery';
						$order->set_payment_method_title($cod_title);

						if ($enable_cod_prepayment === 'yes') {
							// User paid Shipping via QR. Remaining is (Total - Shipping).
							$total = $order->get_total();
							$shipping = $order->get_shipping_total() + $order->get_shipping_tax();
							$remaining = $total - $shipping;

							// Ensure remaining matches logic
							if ($remaining < 0)
								$remaining = 0;

							$order->update_meta_data('_remaining_to_pay', $remaining);
							$order->update_meta_data('_is_partial_cod', 'yes');
							$order->save();
						} else {
							// Full COD. No pre-payment.
							$order->update_meta_data('_remaining_to_pay', $order->get_total());
							$order->save();
						}
					} else {
						// Full QR Payment. Remaining is 0 (Paid in full).
						$order->update_meta_data('_remaining_to_pay', 0);
						$order->save();
					}
				} elseif ($this->get_option('enable_cod_prepayment') === 'yes') {
					// Fallback for legacy flow (if kwp_payment_type not set but pre-payment is ON globally)
					// This handles case where maybe COD Mode is OFF but Pre-payment logic was used?
					// But if COD Mode is OFF, how do we distinguish? 
					// Actually, if COD Mode is OFF, we treat as standard QR (Full payment).
					// But the legacy pre-payment logic (from previous task) was applied globally?
					// The previous logic calculated remaining = subtotal + tax.
					// Let's preserve legacy fallthrough just in case, or assume new logic takes over.
					// If new JS is used, we receive kwp_payment_type (if default 'qr' is selected).
					// If using old JS/cached, we might skip this.
					// Let's strictly rely on kwp_payment_type if present.
				}

				// Mark as on-hold (we're awaiting the payment)
				$order->update_status('on-hold', __('Awaiting offline payment', 'qr-payment-solution'));

				// Reduce stock levels
				$order->reduce_order_stock();

				// Remove cart
				WC()->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order)
				);

			}

		}

	}
}

// Add hidden input for Shipping Total AND Grand Total to be picked up by JS (Initial Load)
add_action('woocommerce_review_order_after_order_total', 'kwp_add_shipping_data_to_checkout');
if (!function_exists('kwp_add_shipping_data_to_checkout')) {
	function kwp_add_shipping_data_to_checkout()
	{
		if (!WC()->cart)
			return;
		// Use raw values to avoid formatting issues
		$shipping_total = WC()->cart->shipping_total + WC()->cart->shipping_tax_total;
		$grand_total = WC()->cart->get_total('edit');
		echo '<input type="hidden" id="kwp_shipping_data" value="' . esc_attr($shipping_total) . '" />';
		echo '<input type="hidden" id="kwp_grand_total_data" value="' . esc_attr($grand_total) . '" />';
	}
}

// Update hidden input via AJAX Fragment
add_filter('woocommerce_update_order_review_fragments', 'kwp_update_shipping_data_fragment');
if (!function_exists('kwp_update_shipping_data_fragment')) {
	function kwp_update_shipping_data_fragment($fragments)
	{
		if (!WC()->cart)
			return $fragments;
		// Use raw values to avoid formatting issues
		$shipping_total = WC()->cart->shipping_total + WC()->cart->shipping_tax_total;
		$grand_total = WC()->cart->get_total('edit');

		$fragments['#kwp_shipping_data'] = '<input type="hidden" id="kwp_shipping_data" value="' . esc_attr($shipping_total) . '" />';
		$fragments['#kwp_grand_total_data'] = '<input type="hidden" id="kwp_grand_total_data" value="' . esc_attr($grand_total) . '" />';

		return $fragments;
	}
}
