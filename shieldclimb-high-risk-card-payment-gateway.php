<?php
/**
 * Plugin Name: ShieldClimb – Card Payment Gateway with Instant Payouts and Chargeback Protection
 * Plugin URI: https://shieldclimb.com/high-risk-card-payment-gateway/
 * Description: High-Risk Business Card Payment Gateway with Instant Payouts to Your USDC Wallet and Full Chargeback Protection – Includes Automatic Order Processing and Auto-Hide Provider Options by Region and Minimum Balance (For setting up go to > Woocommerce > Setting > Payments tab).
 * Version: 1.2.4
 * Requires Plugins: woocommerce
 * Requires at least: 5.8
 * Tested up to: 6.8
 * WC requires at least: 5.8
 * WC tested up to: 9.9.3
 * Requires PHP: 7.2
 * Author: shieldclimb.com
 * Author URI: https://shieldclimb.com/about-us/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

    // Exit if accessed directly.
    if (!defined('ABSPATH')) {
        exit;
    }

    add_action('before_woocommerce_init', function() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });
	
	add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

/**
 * Enqueue block assets for the gateway.
 */
function shieldclimbgateway_enqueue_block_assets() {
    // Fetch all enabled WooCommerce payment gateways
    $shieldclimbgateway_available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    $shieldclimbgateway_gateways_data = array();

    foreach ($shieldclimbgateway_available_gateways as $gateway_id => $gateway) {
		if (strpos($gateway_id, 'shieldclimb') === 0) {
        $icon_url = method_exists($gateway, 'shieldclimb_instant_payment_gateway_get_icon_url') ? $gateway->shieldclimb_instant_payment_gateway_get_icon_url() : '';
        $shieldclimbgateway_gateways_data[] = array(
            'id' => sanitize_key($gateway_id),
            'label' => sanitize_text_field($gateway->get_title()),
            'description' => wp_kses_post($gateway->get_description()),
            'icon_url' => sanitize_url($icon_url),
        );
		}
    }

    wp_enqueue_script(
        'shieldclimbgateway-block-support',
        plugin_dir_url(__FILE__) . 'assets/js/shieldclimbgateway-block-checkout-support.js',
        array('wc-blocks-registry', 'wp-element', 'wp-i18n', 'wp-components', 'wp-blocks', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/shieldclimbgateway-block-checkout-support.js'),
        true
    );

    // Localize script with gateway data
    wp_localize_script(
        'shieldclimbgateway-block-support',
        'shieldclimbgatewayData',
        $shieldclimbgateway_gateways_data
    );
}
add_action('enqueue_block_assets', 'shieldclimbgateway_enqueue_block_assets');

/**
 * Enqueue styles for the gateway on checkout page.
 */
function shieldclimbgateway_enqueue_styles() {
    if (is_checkout()) {
        wp_enqueue_style(
            'shieldclimbgateway-styles',
            plugin_dir_url(__FILE__) . 'assets/css/shieldclimbgateway-payment-gateway-styles.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/shieldclimbgateway-payment-gateway-styles.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'shieldclimbgateway_enqueue_styles');

	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-stripe.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-rampnetwork.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-mercuryo.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-transak.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-guardarian.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-utorg.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-transfi.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-sardine.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-unlimit.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-bitnovo.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-robinhood.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-coinbase.php'); // Include the payment gateway class
	include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-simplex.php'); // Include the payment gateway class
    include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-swipelux.php'); // Include the payment gateway class
    include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-revolut.php'); // Include the payment gateway class
    include_once(plugin_dir_path(__FILE__) . 'includes/class-shieldclimb-finchpay.php'); // Include the payment gateway class

	// Conditional function that check if Checkout page use Checkout Blocks
function shieldclimbgateway_is_checkout_block() {
    return WC_Blocks_Utils::has_block_in_page( wc_get_page_id('checkout'), 'woocommerce/checkout' );
}

function shieldclimbgateway_add_notice($shieldclimbgateway_message, $shieldclimbgateway_notice_type = 'error') {
    // Check if the Checkout page is using Checkout Blocks
    if (shieldclimbgateway_is_checkout_block()) {
        // For blocks, throw a WooCommerce exception
        if ($shieldclimbgateway_notice_type === 'error') {
            throw new \WC_Data_Exception('checkout_error', esc_html($shieldclimbgateway_message)); 
        }
        // Handle other notice types if needed
    } else {
        // Default WooCommerce behavior
        wc_add_notice(esc_html($shieldclimbgateway_message), $shieldclimbgateway_notice_type); 
    }
}	

include_once(plugin_dir_path(__FILE__) . 'includes/shieldclimb-payment-functions.php'); 

?>