# onFact WooCommerce plugin
Version: 0.0.1

## Disclaimer
This plugin is provided as an open source project. onFact does not provide any guarantee or support for
this plugin. You are free to use and modify this plugin as needed for your project.
 Please test extensively before use in your project.

## Installation
Upload the contents of this plugin to the `wp-content/plugins` folder. This can be done using FTP or using the admin interface. 
After uploading, install the composer modules.
After installation and activation of the plugin, an admin menu item "onFact" will be visible.

## Configuration
##### API-Key and Company-UUID
Open your onFact account and go to your personal settingsin the top-right menu. Here you can create an API-Key and see your Company-UUID. Copy both to the admin pagine in your WordPress installation.

##### Stock management
WooCommerce comes with native stock management. But it is possible to ignore the native stock values
and display the stock values directly from onFact. The correct product is found by entering the onFact product number in the 'sku' field for a product.

##### Documents
You can create 3 types of documents: order slips, delivery slips or invoices. Documents are created when 
an order gets the status 'complete'. This can be changed in the code by changing the event hook. A document
can be emailed to the customer after creation.


