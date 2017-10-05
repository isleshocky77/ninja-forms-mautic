<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Ninja Forms - Mautic
 * Plugin URI: http://github.com/isleshocky77/ninja-forms-session
 * Description: Ninja Forms plugin for integrating Mautic as a post submit action.
 * Version: 3.1.1
 * Requires at least: 4.3
 * Tested up to: 4.7
 * Author: Stephen Ostrow <stephen@ostrow.tech>
 * Author URI: http://ostrow.tech
 * Text Domain: ninja-forms-mautic
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright 2017 Stephen Ostrow .
 */

if( version_compare( get_option( 'ninja_forms_version', '0.0.0' ), '3', '<' ) || get_option( 'ninja_forms_load_deprecated', FALSE ) ) {

    throw new \Exception("Must update Ninja Forms to version 3.0 or later");

} else {

    /**
     * Class NF_Mautic
     */
    final class NF_Mautic
    {
        const VERSION = '3.1.1';
        const SLUG    = 'mautic';
        const NAME    = 'Mautic';
        const AUTHOR  = 'Stephen Ostrow <stephen@ostrow.tech>';
        const PREFIX  = 'NF_Mautic';
        const OPTION  = 'ninja_forms_mautic_options';

        /**
         * @var string ID of Salesforce settings section for redirects
         */
        const BOOKMARK = 'ninja_forms_metabox_mautic_settings';

        /**
         * @since 1.4.0
         * @var array $_settings
         */
        private $_settings;

        /**
         * @since 1.4.0
         * @var array $_api
         */
        private $_api;

        /**
         * @var NF_Mautic
         * @since 3.0
         */
        private static $instance;

        /**
         * Plugin Directory
         *
         * @since 3.0
         * @var string $dir
         */
        public static $dir = '';

        /**
         * Plugin URL
         *
         * @since 3.0
         * @var string $url
         */
        public static $url = '';

        /**
         * Main Plugin Instance
         *
         * Insures that only one instance of a plugin class exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 3.0
         * @static
         * @static var array $instance
         * @return NF_Mautic Highlander Instance
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof NF_Mautic)) {
                self::$instance = new NF_Mautic();

                self::$dir = plugin_dir_path(__FILE__);

                self::$url = plugin_dir_url(__FILE__);

                /*
                 * Register our autoloader
                 */
                spl_autoload_register(array(self::$instance, 'autoloader'));

                new NF_Mautic_Admin_Settings();
            }

            return self::$instance;
        }

        public function __construct()
        {
            /*
             * Required for all Extensions.
             */
            add_action( 'admin_init', array( $this, 'setup_license') );

            /*
             * Optional. If your extension processes or alters form submission data on a per form basis...
             */
            add_filter( 'ninja_forms_register_actions', array($this, 'register_actions'));
        }

        /**
         * Optional. If your extension processes or alters form submission data on a per form basis...
         */
        public function register_actions($actions)
        {
            $actions[ 'mautic' ] = new NF_Mautic_Actions_SendToMautic(); // includes/Actions/MauticExample.php

            return $actions;
        }

        /*
         * Optional methods for convenience.
         */

        public function autoloader($class_name)
        {
            if (class_exists($class_name)) return;

            if ( false === strpos( $class_name, self::PREFIX ) ) return;

            $class_name = str_replace( self::PREFIX, '', $class_name );
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

            if (file_exists($classes_dir . $class_file)) {
                require_once $classes_dir . $class_file;
            }
        }

        /**
         * Config
         *
         * @param $file_name
         * @return mixed
         */
        public static function config( $file_name )
        {
            return include self::$dir . 'includes/Config/' . $file_name . '.php';
        }

        /*
         * Required methods for all extension.
         */

        public function setup_license()
        {
            if ( ! class_exists( 'NF_Extension_Updater' ) ) return;

            new NF_Extension_Updater( self::NAME, self::VERSION, self::AUTHOR, __FILE__, self::SLUG );
        }

        /**
         * Gets the default callback page for OAuth authorization
         * @return string
         */
        public function getDefaultCallback() {
            $pageURL = 'http';
            if ((isset($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] == "on")) {
                $pageURL .= "s";
            }
            $pageURL .= "://";
            if ($_SERVER["SERVER_PORT"] != "80" && ($_SERVER["SERVER_PORT"] != "443")) {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }

            return $pageURL;
        }

        /**
         * Creates a template for display
         *
         * @param string $file_name
         * @param array $data
         */
        public static function template($file_name = '', array $data = array()) {

            if (!$file_name) {
                return;
            }
            extract($data);

            include self::$dir . 'includes/Templates/' . $file_name;
        }
    }

    /**
     * The main function responsible for returning The Highlander Plugin
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     * @since 3.0
     * @return {class} Highlander Instance
     */
    function NF_Mautic()
    {
        return NF_Mautic::instance();
    }

    NF_Mautic();
}
