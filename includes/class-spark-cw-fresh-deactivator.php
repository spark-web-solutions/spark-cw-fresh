<?php

/**
 * Fired during plugin deactivation
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Deactivator {
    /**
     * Do not try and instantiate an instance of this class - all methods are static
     *
     * @since 1.3.3
     * @throws Exception
     * @return boolean
     */
    private function __construct() {
        /* translators: %s: Name of the PHP class */
        throw new Exception(sprintf(__('%s cannot be instantiated - all methods are static.', 'spark-cw-fresh'), __CLASS__));
        return false;
    }

    /**
     * Perform necessary operations to clean up when deactivating the plugin
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        require_once plugin_dir_path(__FILE__).'class-spark-cw-fresh-cron.php';
        Spark_Cw_Fresh_Cron::unregister_cron();
        self::clean_up_ia();
        flush_rewrite_rules();
    }

    /**
     * Unregister our custom post types and taxonomies
     *
     * @since 1.3.3
     */
    private static function clean_up_ia() {
    	unregister_post_type('fresh');

    	$selected_languages = maybe_unserialize(get_option('spark-cw-fresh-settings-language'));
    	if (is_array($selected_languages)) {
    		foreach ($selected_languages as $code) {
    			$shortcode = substr($code, 0, strpos($code, '-'));
    			$post_type = 'fresh-'.$shortcode;
    			unregister_post_type($post_type);
    		}
    	}

    	unregister_taxonomy('fresh_cat');

    	unregister_post_type('fresh-banner');
    }
}
