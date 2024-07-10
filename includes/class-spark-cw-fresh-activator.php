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
     * Do necessary stuff on activation
     *
     * @param boolean $network_wide True if the plugin has been activated for the entire network (multisite only) else false
     * @since 1.0.0
	 */
	public static function activate($network_wide = false) {
		if (is_multisite() && $network_wide) {
			// Get all blogs in the network and run our logic on each one
			global $wpdb;
			$blog_ids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
			foreach ($blog_ids as $blog_id) {
				switch_to_blog($blog_id);
				self::set_up();
				restore_current_blog();
			}
		} else {
			self::set_up();
		}
	}

	/**
	 * Run the initial setup that we need for the plugin to operate
	 *
	 * @since 1.3.3
	 */
	private static function set_up() {
		require_once plugin_dir_path(__FILE__).'class-spark-cw-fresh-cron.php';
		Spark_Cw_Fresh_Cron::register_cron();
		if (!get_option('spark_cw_fresh_flush_rewrite_rules')) {
			add_option('spark_cw_fresh_flush_rewrite_rules', true);
		}
	}
}
