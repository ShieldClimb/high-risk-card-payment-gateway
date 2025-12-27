<?php
add_filter('woocommerce_available_payment_gateways', 'shieldclimbgateway_hide_payment_methods');

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
        'shieldclimb-stripe' => 2,
        'shieldclimb-robinhood' => 5,
        'shieldclimb-rampnetwork' => 4,
        'shieldclimb-unlimit' => 10,
        'shieldclimb-bitnovo' => 10,
        'shieldclimb-guardarian' => 20,
        'shieldclimb-sardine' => 30,
        'shieldclimb-transak' => 15,
        'shieldclimb-simplex' => 50,
        'shieldclimb-banxa' => 20,
        'shieldclimb-cryptix' => 20,
        'shieldclimb-kryptonim' => 10,
        'shieldclimb-moonpay' => 20,
        'shieldclimb-topper' => 10,
        'shieldclimb-binance' => 15,
        'shieldclimb-utorg' => 50,
        'shieldclimb-revolut' => 8,
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
?>