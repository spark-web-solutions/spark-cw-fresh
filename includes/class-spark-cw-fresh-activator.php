<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sparkweb.com.au
 * @since      1.0.0
 *
 * @package    Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author     Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
    public static function activate() {
        require_once plugin_dir_path(__FILE__).'class-spark-cw-fresh-cron.php';
        Spark_Cw_Fresh_Cron::register_cron();
        add_action('init', 'flush_rewrite_rules');
	}

}
