<?php

/**
 * Class Onfact_Stock
 *
 * Replace the stock values and stock status from the native WooCommerce values with
 * onFact values.
 */
class Onfact_Stock
{

    private static $initiated = false;

    /**
     * Only retrieve a product once per request.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Initialize the class
     */
    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    /**
     * Register hooks to change the stock quantity and stock status
     */
    public static function init_hooks() {
        self::$initiated = true;

        add_filter( 'woocommerce_product_get_stock_quantity', array('Onfact_Stock', 'product_get_stock_quantity'));
        add_filter( 'woocommerce_product_get_stock_status', array('Onfact_Stock', 'product_get_stock_status'));
    }

    /**
     * Find the product in onFact based on the eid (external id), number or name.
     *
     * @param $stock
     * @return int|mixed
     */
    public static function product_get_stock_quantity($stock) {
        global $product;

        if (!get_option('use_onfact_stock')) {
            return $stock;
        }

        if ($product && isset(static::$cache[$product->get_id()])) {
            return static::$cache[$product->get_id()];
        }

        if ($product) {
            $stock = 0;
            \OnFact\Endpoint\Api::setApiKey(get_option('api_key'));
            \OnFact\Endpoint\Api::setCompanyUuid(get_option('company_uuid'));
            $onFactApi = new \OnFact\Endpoint\Products();
            $products = $onFactApi->index([
                'q' => 'number:' . $product->get_sku() . ' OR eid:' . $product->get_sku() . ''
            ], ['X-FORCE-CACHE' => 300]);

            if ($products->getCount() == 0) {
                $products = $onFactApi->index([
                    'q' => 'name:"' . $product->get_name() . '" OR number:*' . $product->get_sku() . '*  OR eid:*' . $product->get_sku() . '*'
                ], ['X-FORCE-CACHE' => 300]);
            }

            if ($products->getCount() > 0) {
                $stock = $products->getItems()[0]->getActualStock();
                static::$cache[$product->get_id()] = $stock;
                $product->set_stock_quantity($stock);
                $product->set_stock_status($stock > 0 ? 'instock' : 'outofstock');
            }

        }

        return $stock;
    }

    /**
     * Get the stock status.
     *
     * @param $status
     * @return string
     */
    public static function product_get_stock_status($status) {
        global $product;

        if (!get_option('use_onfact_stock')) {
            return $status;
        }

        if (!$product) {
            return 'instock';
        }

        $stock = self::product_get_stock_quantity(null);

        return $stock > 0 ? 'instock' : 'outofstock';
    }
}
