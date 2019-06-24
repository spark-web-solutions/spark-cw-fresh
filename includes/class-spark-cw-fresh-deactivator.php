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
     * Perform necessary operations to clean up when deactivating the plugin
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        require_once plugin_dir_path(__FILE__).'class-spark-cw-fresh-cron.php';
        Spark_Cw_Fresh_Cron::unregister_cron();
        flush_rewrite_rules();
    }
}
