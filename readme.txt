=== ShieldClimb – Card Payment Gateway with Instant Payouts and Chargeback Protection ===
Contributors: shieldclimb
Donate link: https://shieldclimb.com/
Tags: payment gateway, high-risk payment, woocommerce, credit card, payment
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.2.6
Requires PHP: 7.2
WC requires at least: 5.8
WC tested up to: 10.2.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Card payment gateway with USDC wallet instant payouts, chargeback protection, auto order processing, and region/amount-based provider options.

== Description ==

Card Payment Gateway with Instant Payouts to your USDC wallet and Chargeback Protection - Automatic order processing and auto hide the provider option by region and by minimum amount Get paid instantly to your USDC Polygon (MATIC) wallet.

== Features ==

* Auto-Hide Providers if below minimum or unavailable
* Carefully Selected Providers
* Automatic Order Processing
* Instant Access – No Sign-Up, No KYB, Full Anonymity
* Accept Visa, Mastercard, Amex, Google Pay & Apple Pay Instantly
* Instant Payouts in USDC
* No Chargebacks – All Payments Are Final & Irreversible
* Works on Any High-Risk Website
* Privacy First: Unique Temporary Wallet Per Order
* Track Payouts from WordPress Admin
* Low Minimum Orders (from $1 USD)
* Custom Payment Icons Per Provider
* Multi-Currency Support

📺 **Watch the full install and setup tutorial here:**  
[youtube https://www.youtube.com/watch?v=Z9CLAPr6heA]

== ShieldClimb API - Third-Party Service Documentation ==

The plugin and offered service through [shieldclimb.com Instant Payment Gateway API](https://shieldclimb.com/) is subject to the [service terms](https://shieldclimb.com/terms-of-service/) and [Privacy Policy](https://shieldclimb.com/privacy-policy/).

== Frankfurter API - Third-Party Service Documentation ==

This plugin integrates the [Frankfurter API](https://frankfurter.dev/) to fetch exchange rates for determining when to hide payment options below a minimum threshold.

= Terms of Service & Privacy Policy =

* The Frankfurter API does not have official Terms of Service or a standalone Privacy Policy.
* According to their website, the API does not collect personal data, but it runs behind Cloudflare for performance, which may collect basic analytics data.
* More details can be found on their website: https://frankfurter.dev/

= Data Usage & Processing =

* This plugin does not send any personal user data to the [Frankfurter API](https://frankfurter.dev/).
* Only currency codes and requested exchange rates are sent in API requests.
* All data comes from the European Central Bank, and the API provides it as-is.

== Installation == 

1. After installing and activating this plugin go to WooCommerce > Settings > Payments > shieldclimb Payments gateway
2. Activate the desired payment provider gateway and insert your USDC (Polygon) wallet address to receive instant payouts.
3. Insert desired display label and description for the payment gateway.
4. Save settings and you will be ready to accept Credit Cards or Debit Cards Visa, Mastercard, Amex, Google Pay and Apple Pay instantly!

📺 **Watch the full install and setup tutorial here:**  
[youtube https://www.youtube.com/watch?v=Z9CLAPr6heA]

== Minimum Requirements ==

* WordPress 3.8 or greater
* PHP version 5.2.4 or greater

== Frequently Asked Questions ==

= Do I need to sign up as a merchant to use the plugin? =

No, the plugin is available to be used to accept credit card payments instantly without sign up because it depends on the fiat to crypto onramp providers.

= When will I receive payouts? =

You will receive payouts instantly to your USDC wallet with every order.

= How to fix There Are No Payment Methods Available Error? =

Follow the guide to [Fix WooCommerce There Are No Payment Methods Available Error](https://shieldclimb.com/blog/fix-no-payment-methods-available-error/)

= I have a problem with one of my orders? =

Please contact shieldclimb.com support team to guide you.

= I'm receiving payments to my wallet but orders are still pending payment? =

Our plugin is tested to mark orders as processing automatically after payment. You can follow our [guide for fixing payout wallet address error](https://shieldclimb.com/blog/troubleshooting-payout-wallet-address-error/).

= Are there any restricted businesses? =

Anyone can use our payment plugin instantly without providing any information. However if your website category falls under our [prohibited business list](https://shieldclimb.com/high-risk-card-payment-gateway/#prohibited-business-list) your domain will be blocked.

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png

== Changelog ==

= V1.2.6 =

* **Updated**: Tested up to WooCommerce 10.2.2
* **Updated**: Payment providers list

= V1.2.5 =

* **Updated**: Tested up to WooCommerce 9.9.5
* **Updated**: Payment providers list

= V1.2.4 =

* **Updated**: Tested up to WooCommerce 9.9.3
* **Fixed**: Adjusted the payment gateway title to display correctly in WooCommerce settings after changes introduced in WooCommerce 9.9.3
* **Updated**: Payment providers list

= V1.2.3 =

* **Updated**: Readme file to include tutorial video link.

= V1.2.2 =

* **Updated**: Tested up to WordPress 6.8 and WooCommerce 9.8.1
* **Improved**: Code organization and optimization.
* **Updated**: Heading format corrections.

= V1.2.1 =

* **Fixed**: Payment options not appearing on the "Pay for Order" page.

= V1.2.0 =

* Automatically hide Stripe and Robinhood payment methods if the buyer is not from the US, as these options only support the US region.

= V1.1.0 =

* Ability to auto-hide payment providers by region and minimum balance.

= V1.0.0 =

* Initial release, providing a high-risk payment gateway with USDC Polygon Wallet payouts.
* Includes full chargeback protection and automatic order processing.


== Upgrade Notice ==

Checkout new plugin features. Always make sure to insert your payout wallet for active gateways.