<?php

/**
 * Class Onfact_Views
 *
 * Displays views for the Admin settings page.
 */
class Onfact_Views
{
    public static function __callStatic($name, $arguments)
    {
        return include(ONFACT__PLUGIN_DIR . 'views/' . DIRECTORY_SEPARATOR . $name . '.php');
    }

}
