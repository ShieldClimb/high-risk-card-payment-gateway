<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'shieldclimbgateway_transficom_gateway');

function shieldclimbgateway_transficom_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

class shieldclimb_Instant_Payment_Gateway_Transfi extends WC_Payment_Gateway {

    protected $icon_url;
    protected $transficom_wallet_address;

    public function __construct() {
        $this->id                 = 'shieldclimb-transfi';
        $this->icon = sanitize_url($this->get_option('icon_url'));
        $this->method_title       = esc_html__('ShieldClimb – transfi.com | Min USD70 | Auto Hide If Below Min', 'shieldclimb-high-risk-card-payment-gateway'); // Escaping title
        $this->method_description = esc_html__('High Risk Business Card Payment Gateway with Chargeback Protection and Instant USDC POLYGON Wallet Payouts using transfi.com infrastructure', 'shieldclimb-high-risk-card-payment-gateway'); // Escaping description
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'));

        // Use the configured settings for redirect and icon URLs
        $this->transficom_wallet_address = sanitize_text_field($this->get_option('transficom_wallet_address'));
        $this->icon_url     = sanitize_url($this->get_option('icon_url'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => esc_html__('Enable/Disable', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping title
                'type'    => 'checkbox',
                'label'   => esc_html__('Enable transfi.com payment gateway', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping label
                'default' => 'no',
            ),
            'title' => array(
                'title'       => esc_html__('Title', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Payment method title that users will see during checkout.', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping description
                'default'     => esc_html__('Credit Card', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__('Description', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping title
                'type'        => 'textarea',
                'description' => esc_html__('Payment method description that users will see during checkout.', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping description
                'default'     => esc_html__('Pay via credit card', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping default value
                'desc_tip'    => true,
            ),
            'transficom_wallet_address' => array(
                'title'       => esc_html__('Wallet Address', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping title
                'type'        => 'text',
                'description' => esc_html__('Insert your USDC (Polygon) wallet address to receive instant payouts. Payouts maybe sent in USDC or USDT (Polygon or BEP-20) or POL native token. Same wallet should work to receive all. Make sure you use a self-custodial wallet to receive payouts.', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
            'icon_url' => array(
                'title'       => esc_html__('Icon URL', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping title
                'type'        => 'url',
                'description' => esc_html__('Enter the URL of the icon image for the payment method.', 'shieldclimb-high-risk-card-payment-gateway'), // Escaping description
                'desc_tip'    => true,
            ),
        );
    }
	 // Add this method to validate the wallet address in wp-admin
    public function process_admin_options() {
		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'woocommerce-settings')) {
    WC_Admin_Settings::add_error(__('Nonce verification failed. Please try again.', 'shieldclimb-high-risk-card-payment-gateway'));
    return false;
}
        $transficom_admin_wallet_address = isset($_POST[$this->plugin_id . $this->id . '_transficom_wallet_address']) ? sanitize_text_field( wp_unslash( $_POST[$this->plugin_id . $this->id . '_transficom_wallet_address'])) : '';

        // Check if wallet address starts with "0x"
        if (substr($transficom_admin_wallet_address, 0, 2) !== '0x') {
            WC_Admin_Settings::add_error(__('Invalid Wallet Address: Please insert your USDC Polygon wallet address.', 'shieldclimb-high-risk-card-payment-gateway'));
            return false;
        }

        // Check if wallet address matches the USDC contract address
        if (strtolower($transficom_admin_wallet_address) === '0x3c499c542cef5e3811e1192ce70d8cc03d5c3359') {
            WC_Admin_Settings::add_error(__('Invalid Wallet Address: Please insert your USDC Polygon wallet address.', 'shieldclimb-high-risk-card-payment-gateway'));
            return false;
        }

        // Proceed with the default processing if validations pass
        return parent::process_admin_options();
    }
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $shieldclimbgateway_transficom_currency = get_woocommerce_currency();
		$shieldclimbgateway_transficom_total = $order->get_total();
		$shieldclimbgateway_transficom_nonce = wp_create_nonce( 'shieldclimbgateway_transficom_nonce_' . $order_id );
		$shieldclimbgateway_transficom_callback = add_query_arg(array('order_id' => $order_id, 'nonce' => $shieldclimbgateway_transficom_nonce,), rest_url('shieldclimbgateway/v1/shieldclimbgateway-transficom/'));
		$shieldclimbgateway_transficom_email = urlencode(sanitize_email($order->get_billing_email()));
		
		if ($shieldclimbgateway_transficom_currency === 'USD') {
        $shieldclimbgateway_transficom_final_total = $shieldclimbgateway_transficom_total;
		} else {
		
$shieldclimbgateway_transficom_response = wp_remote_get('https://api.shieldclimb.com/control/convert.php?value=' . $shieldclimbgateway_transficom_total . '&from=' . strtolower($shieldclimbgateway_transficom_currency), array('timeout' => 30));

if (is_wp_error($shieldclimbgateway_transficom_response)) {
    // Handle error
    shieldclimbgateway_add_notice(__('Payment error:', 'shieldclimb-high-risk-card-payment-gateway') . __('Payment could not be processed due to failed currency conversion process, please try again', 'shieldclimb-high-risk-card-payment-gateway'), 'error');
    return null;
} else {

$shieldclimbgateway_transficom_body = wp_remote_retrieve_body($shieldclimbgateway_transficom_response);
$shieldclimbgateway_transficom_conversion_resp = json_decode($shieldclimbgateway_transficom_body, true);

if ($shieldclimbgateway_transficom_conversion_resp && isset($shieldclimbgateway_transficom_conversion_resp['value_coin'])) {
    // Escape output
    $shieldclimbgateway_transficom_final_total	= sanitize_text_field($shieldclimbgateway_transficom_conversion_resp['value_coin']);      
} else {
    shieldclimbgateway_add_notice(__('Payment error:', 'shieldclimb-high-risk-card-payment-gateway') . __('Payment could not be processed, please try again (unsupported store currency)', 'shieldclimb-high-risk-card-payment-gateway'), 'error');
    return null;
}	
		}
		}
if ($shieldclimbgateway_transficom_final_total < 70) {
shieldclimbgateway_add_notice(__('Payment error:', 'shieldclimb-high-risk-card-payment-gateway') . __('Order total for this payment provider must be $70 USD or more.', 'shieldclimb-high-risk-card-payment-gateway'), 'error');
return null;
}	
		
$shieldclimbgateway_transficom_gen_wallet = wp_remote_get('https://api.shieldclimb.com/control/wallet.php?address=' . $this->transficom_wallet_address .'&callback=' . urlencode($shieldclimbgateway_transficom_callback), array('timeout' => 30));

if (is_wp_error($shieldclimbgateway_transficom_gen_wallet)) {
    // Handle error
    shieldclimbgateway_add_notice(__('Wallet error:', 'shieldclimb-high-risk-card-payment-gateway') . __('Payment could not be processed due to incorrect payout wallet settings, please contact website admin', 'shieldclimb-high-risk-card-payment-gateway'), 'error');
    return null;
} else {
	$shieldclimbgateway_transficom_wallet_body = wp_remote_retrieve_body($shieldclimbgateway_transficom_gen_wallet);
	$shieldclimbgateway_transficom_wallet_decbody = json_decode($shieldclimbgateway_transficom_wallet_body, true);

 // Check if decoding was successful
    if ($shieldclimbgateway_transficom_wallet_decbody && isset($shieldclimbgateway_transficom_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $shieldclimbgateway_transficom_gen_addressIn = wp_kses_post($shieldclimbgateway_transficom_wallet_decbody['address_in']);
        $shieldclimbgateway_transficom_gen_polygon_addressIn = sanitize_text_field($shieldclimbgateway_transficom_wallet_decbody['polygon_address_in']);
		$shieldclimbgateway_transficom_gen_callback = sanitize_url($shieldclimbgateway_transficom_wallet_decbody['callback_url']);
		// Save $transficomresponse in order meta data
    $order->add_meta_data('shieldclimb_transficom_tracking_address', $shieldclimbgateway_transficom_gen_addressIn, true);
    $order->add_meta_data('shieldclimb_transficom_polygon_temporary_order_wallet_address', $shieldclimbgateway_transficom_gen_polygon_addressIn, true);
    $order->add_meta_data('shieldclimb_transficom_callback', $shieldclimbgateway_transficom_gen_callback, true);
	$order->add_meta_data('shieldclimb_transficom_converted_amount', $shieldclimbgateway_transficom_final_total, true);
	$order->add_meta_data('shieldclimb_transficom_nonce', $shieldclimbgateway_transficom_nonce, true);
    $order->save();
    } else {
        shieldclimbgateway_add_notice(__('Payment error:', 'shieldclimb-high-risk-card-payment-gateway') . __('Payment could not be processed, please try again (wallet address error)', 'shieldclimb-high-risk-card-payment-gateway'), 'error');

        return null;
    }
}

// Check if the Checkout page is using Checkout Blocks
if (shieldclimbgateway_is_checkout_block()) {
    global $woocommerce;
	$woocommerce->cart->empty_cart();
}

        // Redirect to payment page
        return array(
            'result'   => 'success',
            'redirect' => 'https://payment.shieldclimb.com/process-payment.php?address=' . $shieldclimbgateway_transficom_gen_addressIn . '&amount=' . (float)$shieldclimbgateway_transficom_final_total . '&provider=transfi&email=' . $shieldclimbgateway_transficom_email . '&currency=' . $shieldclimbgateway_transficom_currency,
        );
    }

public function shieldclimb_instant_payment_gateway_get_icon_url() {
        return !empty($this->icon_url) ? esc_url($this->icon_url) : '';
    }
}

function shieldclimb_add_instant_payment_gateway_transficom($gateways) {
    $gateways[] = 'shieldclimb_Instant_Payment_Gateway_Transfi';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'shieldclimb_add_instant_payment_gateway_transficom');
}

// Custom permission callback for the REST endpoint
function shieldclimbgateway_transficom_permission_callback($request) {
    $order_id = absint($request->get_param('order_id'));
    $nonce = sanitize_text_field($request->get_param('nonce'));

    // Check for missing parameters
    if (empty($order_id) || empty($nonce)) {
        return new WP_Error(
            'rest_forbidden',
            __('Missing order or nonce parameter.', 'shieldclimb-high-risk-card-payment-gateway'),
            array('status' => 403)
        );
    }

    // Retrieve the order
    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error(
            'rest_forbidden',
            __('Order not found.', 'shieldclimb-high-risk-card-payment-gateway'),
            array('status' => 404)
        );
    }

    // Validate the nonce stored in order meta
    if ($order->get_meta('shieldclimb_transficom_nonce', true) !== $nonce) {
        return new WP_Error(
            'rest_forbidden',
            __('Invalid nonce.', 'shieldclimb-high-risk-card-payment-gateway'),
            array('status' => 403)
        );
    }

    return true;
}

// Register custom endpoint with permission callback
function shieldclimbgateway_transficom_change_order_status_rest_endpoint() {
    register_rest_route(
        'shieldclimbgateway/v1',
        '/shieldclimbgateway-transficom/',
        array(
            'methods'             => 'GET',
            'callback'            => 'shieldclimbgateway_transficom_change_order_status_callback',
            'permission_callback' => 'shieldclimbgateway_transficom_permission_callback',
        )
    );
}
add_action('rest_api_init', 'shieldclimbgateway_transficom_change_order_status_rest_endpoint');

// Simplified callback function
function shieldclimbgateway_transficom_change_order_status_callback($request) {
    $order_id = absint($request->get_param('order_id'));
    $order = wc_get_order($order_id);
    
    $shieldclimbgateway_transficompaid_txid_out = sanitize_text_field($request->get_param('txid_out'));
    $shieldclimbgateway_transficompaid_value_coin = sanitize_text_field($request->get_param('value_coin'));
    $shieldclimbgateway_transficomfloatpaid_value_coin = (float)$shieldclimbgateway_transficompaid_value_coin;

    // Check order status and payment method
    if ($order->get_status() !== 'processing' && $order->get_status() !== 'completed' && 'shieldclimb-transfi' === $order->get_payment_method()) {
        $shieldclimbgateway_transficomexpected_amount = (float)$order->get_meta('shieldclimb_transficom_expected_amount', true);
        $shieldclimbgateway_transficomthreshold = 0.60 * $shieldclimbgateway_transficomexpected_amount;

        if ($shieldclimbgateway_transficomfloatpaid_value_coin < $shieldclimbgateway_transficomthreshold) {
            $order->update_status('failed', __('Payment received is less than 60% of the order total. Customer may have changed the payment values on the checkout page.', 'shieldclimb-high-risk-card-payment-gateway'));
            /* translators: %1$s: Transaction ID from payment gateway */
            $order->add_order_note(sprintf(__('Order marked as failed: Payment received is less than 60%% of the order total. Customer may have changed the payment values on the checkout page. TXID: %1$s', 'shieldclimb-high-risk-card-payment-gateway'), $shieldclimbgateway_transficompaid_txid_out));
            return array('message' => 'Order status changed to failed due to partial payment.');
        } else {
            $order->payment_complete();
            /* translators: %1$s: Transaction ID from payment gateway */
            $order->add_order_note(sprintf(__('Payment completed by the provider TXID: %1$s', 'shieldclimb-high-risk-card-payment-gateway'), $shieldclimbgateway_transficompaid_txid_out));
            return array('message' => 'Order marked as paid and status changed.');
        }
    }

    return new WP_Error('order_not_eligible', __('Order is not eligible for status change.', 'shieldclimb-high-risk-card-payment-gateway'), array('status' => 400));
}
?>