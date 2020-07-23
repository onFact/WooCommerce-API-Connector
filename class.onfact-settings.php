<?php

/**
 * Class Onfact_Settings
 *
 * Manage the plugin settings
 */
class Onfact_Settings
{

    private static $initiated = false;

    /**
     * Initizalize the class
     */
    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    /**
     * Register hooks to add menu item and initialize settings
     */
    public static function init_hooks() {
        self::$initiated = true;

        add_action( 'admin_menu', array('Onfact_Settings', 'admin_menu') );
        add_action( 'admin_init', array('Onfact_Settings', 'admin_init') );
    }

    /**
     * Add the admin page
     */
    public static function admin_menu() {
        add_menu_page('onFact', 'onFact', 'manage_options', 'onfact', array('Onfact_Views', 'settings'));
    }

    /**
     * Initialize settings
     */
    public static function admin_init() {
        register_setting( 'onfact-settings', 'api_key' );
        register_setting( 'onfact-settings', 'company_uuid' );
        register_setting( 'onfact-settings', 'use_onfact_stock' );
        register_setting( 'onfact-settings', 'document_to_create');

        self::get_onfact_languages();
    }

    /**
     * Get the list of languages available in onFact.
     *
     * @return \OnFact\Endpoint\Languages|\OnFact\Helper\Index
     */
    public static function get_onfact_languages() {
        try {
            \OnFact\Endpoint\Api::setApiKey(get_option('api_key'));
            \OnFact\Endpoint\Api::setCompanyUuid(get_option('company_uuid'));
            $languages = new \OnFact\Endpoint\Languages();
            $languages = $languages->index([], ['X-FORCE-CACHE' => 3000]);

            foreach ($languages->getItems() as $language) {
                register_setting( 'onfact-settings-orderslip', 'orderslip_description_' . $language->getId());
                register_setting( 'onfact-settings-orderslip', 'orderslip_emaildescription_' . $language->getId());
                register_setting( 'onfact-settings-deliveryslip', 'deliveryslip_description_' . $language->getId());
                register_setting( 'onfact-settings-deliveryslip', 'deliveryslip_emaildescription_' . $language->getId());
                register_setting( 'onfact-settings-invoice', 'invoice_description_' . $language->getId());
                register_setting( 'onfact-settings-invoice', 'invoice_emaildescription_' . $language->getId());
            }

            return $languages;
        }  catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get the descriptions. Descriptions are default pieces of text that
     * are displayed at the bottom of a document.
     *
     * @return \OnFact\Helper\Index
     */
    public static function get_onfact_descriptions() {
        \OnFact\Endpoint\Api::setApiKey(get_option('api_key'));
        \OnFact\Endpoint\Api::setCompanyUuid(get_option('company_uuid'));
        $descriptions = new \OnFact\Endpoint\Descriptions();

        return $descriptions->index([], ['X-FORCE-CACHE' => 300]);
    }

    /**
     * Get the list of default emaildescriptions. Emaildescriptions are default
     * subjects and texts.
     *
     * @return \OnFact\Helper\Index
     */
    public static function get_onfact_emaildescriptions() {
        \OnFact\Endpoint\Api::setApiKey(get_option('api_key'));
        \OnFact\Endpoint\Api::setCompanyUuid(get_option('company_uuid'));
        $emaildescriptions = new \OnFact\Endpoint\Emaildescriptions();

        return $emaildescriptions->index([], ['X-FORCE-CACHE' => 300]);
    }

}
