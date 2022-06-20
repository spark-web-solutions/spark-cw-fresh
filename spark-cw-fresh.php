<?php
/**
 * @link        https://sparkweb.com.au
 * @since       1.0.0
 * @package     Spark_Cw_Fresh
 *
 * Plugin Name: FRESH by christianityworks
 * Plugin URI:  https://christianityworks.com
 * Update URI:  https://christianityworks.com
 * Description: Embed FRESH on your site.
 * Version:     1.3.0
 * Author:      Spark Web Solutions
 * Author URI:  https://sparkweb.com.au
 * Requires at least: 5.3.0
 * Requires PHP: 7.0
 * License:     Copyright Spark Web Solutions and christianityworks. Unauthorised distribution of this software, with or without modifications is expressly prohibited.
 * Text Domain: spark-cw-fresh
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die();
}

/**
 * Current plugin version.
 */
define('SPARK_CW_FRESH_VERSION', '1.3.0');
define('SPARK_CW_FRESH_PATH', __FILE__);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-spark-cw-fresh-activator.php
 */
function activate_spark_cw_fresh() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-spark-cw-fresh-activator.php';
    Spark_Cw_Fresh_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-spark-cw-fresh-deactivator.php
 */
function deactivate_spark_cw_fresh() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-spark-cw-fresh-deactivator.php';
    Spark_Cw_Fresh_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_spark_cw_fresh');
register_deactivation_hook(__FILE__, 'deactivate_spark_cw_fresh');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-spark-cw-fresh.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_spark_cw_fresh() {
    new Spark_Cw_Fresh();
}
run_spark_cw_fresh();
