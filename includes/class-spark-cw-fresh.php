<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 1.0.0
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh {
    /**
     * The unique identifier of this plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct() {
        if (defined('SPARK_CW_FRESH_VERSION')) {
            $this->version = SPARK_CW_FRESH_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'spark-cw-fresh';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_cron_hooks();
        $this->define_ia();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function load_dependencies() {
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-spark-cw-fresh-i18n.php';

        /**
         * Class for simplifying creation of custom CPTs
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-spark-cw-fresh-cpt.php';

        /**
         * Class for simplifying creation of custom taxonomies
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-spark-cw-fresh-tax.php';

        /**
         * Class for simplifying creation of custom post meta
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-spark-cw-fresh-meta.php';

        /**
         * Class for handling of recurring tasks
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-spark-cw-fresh-cron.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-spark-cw-fresh-admin.php';

        /**
         * The class responsible for handling plugin updates.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-spark-cw-fresh-updates.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'public/class-spark-cw-fresh-public.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Spark_Cw_Fresh_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since 1.0.0
     * @access private
     */
    private function set_locale() {
        $plugin_i18n = new Spark_Cw_Fresh_i18n();

        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Spark_Cw_Fresh_Admin($this->get_plugin_name(), $this->get_version());

        add_action('admin_init', array($plugin_admin, 'updates'));
        add_action('admin_init', array($plugin_admin, 'register_settings'));
        add_action('admin_menu', array($plugin_admin, 'menu'));
//         add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
//         add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_public_hooks() {
        $plugin_public = new Spark_Cw_Fresh_Public($this->get_plugin_name(), $this->get_version());

        add_action('init', array($plugin_public, 'register_shortcodes'));
        add_action('init', array($plugin_public, 'register_feeds'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
//         add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        add_action('template_include', array($plugin_public, 'template_include'));
    }

    /**
     * Register all of the recurring tasks for the plugin
     *
     * @since 1.0.0
     * @access private
     */
    private function define_cron_hooks() {
        $plugin_cron = new Spark_Cw_Fresh_Cron($this->get_plugin_name(), $this->get_version());

        add_action('admin_init', array('Spark_Cw_Fresh_Cron', 'register_cron'));
        add_action('spark_cw_fresh_get_latest_fresh', array($plugin_cron, 'get_latest_fresh'));
    }

    /**
     * Register our custom information architecture
     *
     * @since 1.0.0
     * @access private
     */
    private function define_ia() {
        $args = array(
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_position' => 20,
                'menu_icon' => 'dashicons-book-alt',
                'show_in_nav_menus' => true,
                'has_archive' => true,
                'hierarchical' => false,
                'show_in_rest' => false,
        );
        new Spark_Cw_Fresh_Cpt('FRESH', 'FRESH', $args, 'fresh');

        $meta_fields = array(
                array(
                        'title' => __('Video URL', 'spark-cw-fresh'),
                        'field_name' => 'video',
                        'type' => 'text',
                ),
                array(
                        'title' => __('Audio URL', 'spark-cw-fresh'),
                        'field_name' => 'audio',
                        'type' => 'text',
                ),
                array(
                        'title' => __('Scripture Reference', 'spark-cw-fresh'),
                        'field_name' => 'scripture_reference',
                        'type' => 'text',
                ),
                array(
                        'title' => __('Scripture Quote', 'spark-cw-fresh'),
                        'field_name' => 'scripture_quote',
                        'type' => 'textarea',
                ),
        );
        new Spark_Cw_Fresh_Meta(__('Episode Details', 'spark-cw-fresh'), array('fresh'), $meta_fields);

        $args = array(
                'rewrite' => array(
                        'slug' => 'fresh/category',
                        'with_front' => false,
                ),
        );
        new Spark_Cw_Fresh_Tax('FRESH Category', 'FRESH Categories', array('fresh'), $args, 'fresh_cat');

        $ia_ver = get_option('spark-cw-fresh-version-ia', 0);
        if (version_compare($ia_ver, $this->version, '<')) {
            update_option('spark-cw-fresh-version-ia', $this->version);
            add_action('init', 'flush_rewrite_rules');
        }
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since 1.0.0
     * @return string The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since 1.0.0
     * @return string The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
