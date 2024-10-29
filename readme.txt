=== Automate Dropshipping for B2BDropshipper(WWTech) ===
Contributors: Midriff Web Services
Tags: woocoomerce dropshipping, DropshippingB2B, Dropshipper, e-commerce, store, sales, sell, woo, shop, cart, storefront
Author: Team MidriffAuthor URI: http://www.midriffinfosolution.org/
Tested up to: 5.6Stable tag: 3.0.7
== Description ==
Automate Dropshipping for B2BDropshipper(WWTech) plugin provides fully integration with woocommerce to automate import products and manage orders.
### Features And Options:
* Automatically Import Products From WWTech B2B Dropshipping to your woo-commerce store with cronjob.
* Automatically Fulfill Orders Once orders have been shipped, tracking details will be automatically extracted by WWIT and fulfilled in woocomerce.
* Customize Imported Products Edit imported products as you wish: change titles, descriptions, images, etc.!
* Automatic Inventory Management and prices manage.
== Installation === Requires WooCommerce =1. Upload the plugin files to the '/wp-content/plugins/automate-dropshippingb2b' directory, or install the plugin through the WordPress plugins screen directly.2. Activate the plugin through the 'Plugins' screen in WordPress3. Go to settings page from `Admin menu > Automate B2B > API Settings` and fill the required fileds.4. Go to `Automate B2B > Brand Names` and assign your brand name to category.5. Go to `Automate B2B > Payment and fill your credentials to create order in dropshipping.6. Go to settings page from `Admin menu > Automate B2B > API Settings` copy cron job url and setup it with cron job.6. Done.
== Frequently Asked Questions ==
= How to setup? =
Just activate the plugin, go to plugin settings and fill the required fileds.
= How to import products? =
Go to settings page from `Admin menu > Automate B2B > API Settings` copy add new product url and setup it with cron job for automate import product or manually hit the url.= How to change order status new to ready? =Go to settings page from `Admin menu > Automate B2B > API Settings` copy order status change  url and setup it with cron job for automate change status or manually hit the url.== Screenshots ==1. Install the plugin through the WordPress plugins screen directly.2. Activate the plugin through the ‘Plugins’ screen in WordPress.3. API Settings and fill the required fileds.4. Brand Names and assign your brand name to category.5. Payment and fill your credentials to create order in dropshippingB2B.6. Copy cron job url and setup it with cron job.== Changelog ==

= 3.0.7 =
* Big Fixes: Cron stops working while importing products and getting 500 gateway errors
* Performance Improvement of cronjobs while importing & Updating products 
* Additional Feature - Update products in bulk , Additional Payment methods: Paypal, Visa, Mastercard, Dinners
* Introduced the Hooks and Filters:
  Filter Hooks:
    - klock_woo_drop_manage_post : Hook for filtering product data like Product Name,Regular Price,Sale Price etc.
    - klock_woo_drop_manage_product_attributes : Hook for filtering product attributes.
  Action Hooks:
    - klock_woo_drop_after_product_inserted : Hook which calls immediately after new product inserted.



= 3.0.6 =
* Big Fixes : Cron stops working while importing products
= 3.0.5 =
* Bug Fixes for cronjobs
* Additional Feature - Attributes with Brand Names as default
* Performance Improvement while importing & Updating products 
* Additional Feature - Import Product images in Media or Product Image URL

= 3.0.4 =
* Improved performance of bulk product import in Brand name menu setting.
* Improved Payment setting page.

= 3.0.3 =
* Fixed issue bulk product import in Brand name menu setting.
* Implemented add Attributes - (Added Attributes also in taxonomy term)*

= 3.0.2 =
* Bug fixes.
* Added Attributes*

= 3.0.1 =
* Bug fixes.

= 3.0.0 =
* Major Update.
* Bug fixes.
* Direct upload image to wordpress media.
* Added additional features.
* Realible with other products.
* Upgrated version note - if you are using previous version do not ugrade.
= 2.0.0 =* Compatible to others them.* Bug fixes.
