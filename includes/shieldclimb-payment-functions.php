<?php
add_filter('woocommerce_available_payment_gateways', 'shieldclimbgateway_hide_payment_methods');
add_filter('woocommerce_available_payment_gateways', 'shieldclimbgateway_payment_gateway_for_us_ip_customers');

function shieldclimbgateway_hide_payment_methods($available_gateways) {
    if (!is_checkout() && !is_wc_endpoint_url('order-pay')) {
        return $available_gateways;
    }

    // Get the current WooCommerce currency
    $currency = get_woocommerce_currency();

    // Fetch the exchange rate from the Frankfurter API with caching
    $rate = get_transient('shieldclimbgateway_exchange_rate_' . $currency);
    if ($rate === false) {
        $response = wp_remote_get('https://api.frankfurter.dev/latest?from=' . $currency . '&to=USD');
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($data['rates']['USD'])) {
                $rate = $data['rates']['USD'];
                set_transient('shieldclimbgateway_exchange_rate_' . $currency, $rate, HOUR_IN_SECONDS);
            }
        }
    }
    $rate = $rate ?: 1; // Default rate

    // Determine the total (cart total for checkout, order total for order-pay)
    if (is_wc_endpoint_url('order-pay')) {
        global $wp;
        $order_id = absint($wp->query_vars['order-pay']);
        $order = wc_get_order($order_id);
        if (!$order || !$order->needs_payment()) {
            return $available_gateways; // Skip if order doesn't require payment
        }
        $order_total = $order->get_total();
    } else {
        $order_total = WC()->cart->total;
    }

    $cart_total_in_usd = $order_total * $rate;

    // Payment gateway restrictions based on USD total
    $gateway_conditions = [
        'shieldclimb-werteur' => 1.09,
        'shieldclimb-stripe' => 1.09,
        'shieldclimb-coinbase' => 2,
        'shieldclimb-robinhood' => 5,
        'shieldclimb-rampnetwork' => 4.08,
        'shieldclimb-topper' => 10,
        'shieldclimb-unlimit' => 10,
        'shieldclimb-kado' => 16.99,
        'shieldclimb-bitnovo' => 17.99,
        'shieldclimb-guardarian' => 20,
        'shieldclimb-swipelux' => 22.99,
        'shieldclimb-mercuryo' => 30,
        'shieldclimb-sardine' => 30,
        'shieldclimb-transak' => 30,
        'shieldclimb-simplex' => 50,
        'shieldclimb-utorg' => 50,
        'shieldclimb-transfi' => 70
    ];

    // Loop through all conditions to unset gateways
    foreach ($gateway_conditions as $gateway_slug => $min_amount) {
        if ($cart_total_in_usd < $min_amount && isset($available_gateways[$gateway_slug])) {
            unset($available_gateways[$gateway_slug]);
        }
    }

    return $available_gateways;
}

function shieldclimbgateway_payment_gateway_for_us_ip_customers($available_gateways) {
    if ((!is_checkout() && !is_wc_endpoint_url('order-pay')) || is_admin()) {
        return $available_gateways;
    }

    // Get the user's country based on IP address
    $user_country = WC_Geolocation::geolocate_ip();

    if (isset($user_country['country']) && $user_country['country'] !== 'US') {
        // List of payment gateways to remove for non-US customers
        unset($available_gateways['shieldclimb-stripe']);
        unset($available_gateways['shieldclimb-robinhood']);
        // Add more unset() calls here if needed
    }

    return $available_gateways;
}
?>