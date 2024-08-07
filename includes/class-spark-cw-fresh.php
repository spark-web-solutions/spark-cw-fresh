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
 * @since	  1.0.0
 * @package	Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author	 Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var	string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var	string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * List of supported languages
	 *
	 * @since  1.2.0
	 * @access public
	 * @var	array $languages The list of languages - key is ISO language code and value is language name
	 */
	public static $languages = array(
			'en-au' => 'English',
			'af-za' => 'Afrikaans',
			'zh-hans' => 'Chinese (Simplified)',
			'hi-in' => 'Hindi',
			'sw-ke' => 'Swahili',
			'vi-vn' => 'Vietnamese',
			'zu-za' => 'Zulu',
	);

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
		add_filter('plugin_action_links_'.$this->plugin_name.'/'.$this->plugin_name.'.php', array($plugin_admin, 'plugin_action_links'));
		add_filter('manage_fresh-banner_posts_columns', array($plugin_admin, 'banner_admin_columns_register'));
		add_action('manage_fresh-banner_posts_custom_column', array($plugin_admin, 'banner_admin_column_display'), 10, 2);
		add_filter('manage_edit-fresh-banner_sortable_columns', array($plugin_admin, 'banner_admin_column_register_sortable'));
		add_filter('request', array($plugin_admin, 'banner_column_orderby'));
//		 add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
//		 add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
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
		add_action('init', array($plugin_public, 'maybe_flush_rewrite_rules'), 20);
		add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
//		 add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
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
	 * @since 1.3.0 Added banner architecture
	 * @access private
	 */
	private function define_ia() {
		$args = array(
				'public' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 20,
				'menu_icon' => 'dashicons-book-alt',
				'supports' => array(
						'title',
						'editor',
						'thumbnail',
						'comments',
				),
				'show_in_nav_menus' => true,
				'has_archive' => true,
				'hierarchical' => false,
				'show_in_rest' => false,
		);
		new Spark_Cw_Fresh_Cpt(__('FRESH', 'spark-cw-fresh'), __('FRESH', 'spark-cw-fresh'), $args, 'fresh');

		$post_types = array('fresh');
		$translations = array();
		$selected_languages = maybe_unserialize(get_option('spark-cw-fresh-settings-language'));
		if (is_array($selected_languages)) {
			$args['show_in_menu'] = false; // We manually add it as a child of the main FRESH menu item below
			foreach ($selected_languages as $code) {
				$name = $this::$languages[$code];
				$shortcode = substr($code, 0, strpos($code, '-'));
				$post_type = 'fresh-'.$shortcode;
				$post_types[] = $post_type;
				$translations[$shortcode] = $name;
				$args['rewrite'] = array(
						'slug' => 'fresh/'.$shortcode,
				);
				/* translators: %s is the name of the language FRESH is translated into */
				new Spark_Cw_Fresh_Cpt(sprintf(__('FRESH %s', 'spark-cw-fresh'), $name), sprintf(__('FRESH %s', 'spark-cw-fresh'), $name), $args, $post_type);
			}
		}

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
		new Spark_Cw_Fresh_Meta(__('Episode Details', 'spark-cw-fresh'), $post_types, $meta_fields, 'normal');

		$args = array(
				'rewrite' => array(
						'slug' => 'fresh/category',
						'with_front' => false,
				),
		);
		new Spark_Cw_Fresh_Tax(__('FRESH Category', 'spark-cw-fresh'), __('FRESH Categories', 'spark-cw-fresh'), $post_types, $args, 'fresh_cat');

		foreach ($translations as $code => $name) {
			// Add translation posts page under main Fresh menu item
			add_action('admin_menu', function() use ($code, $name) {
				add_submenu_page('edit.php?post_type=fresh', $name, $name, 'edit_posts', 'edit.php?post_type=fresh-'.$code);
			}, 5);
			// Highlight correct menu item when editing translated posts
			add_filter('parent_file', function($parent_file) use ($code) {
				if ($parent_file == 'edit.php?post_type=fresh-'.$code) {
					return 'edit.php?post_type=fresh';
				}
				return $parent_file;
			});
		}

		$args = array(
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => false, // We manually add it as a child of the main FRESH menu item below
				'has_archive' => false,
				'hierarchical' => false,
				'show_in_rest' => false,
				'supports' => array(
						'title',
						'thumbnail',
				),
		);
		new Spark_Cw_Fresh_Cpt(__('Banner', 'spark-cw-fresh'), __('Banners', 'spark-cw-fresh'), $args, 'fresh-banner');

		// Add banner posts page under main Fresh menu item
		add_action('admin_menu', function() {
			add_submenu_page('edit.php?post_type=fresh', __('Banners', 'spark-cw-fresh'), __('Banners', 'spark-cw-fresh'), 'edit_posts', 'edit.php?post_type=fresh-banner');
		}, 5);
		// Highlight correct menu item when editing banner posts
		add_filter('parent_file', function($parent_file) {
			if ($parent_file == 'edit.php?post_type=fresh-banner') {
				return 'edit.php?post_type=fresh';
			}
			return $parent_file;
		});

		$language_choices = array(
				array(
						'label' => 'English',
						'value' => 'en',
				),
		);
		if (is_array($selected_languages)) {
			foreach ($selected_languages as $code) {
				$name = $this::$languages[$code];
				$shortcode = substr($code, 0, strpos($code, '-'));
				$language_choices[] = array(
						'label' => $name,
						'value' => $shortcode,
				);
			}
		}
		$meta_fields = array(
			array(
				'title' => __('Link', 'spark-cw-fresh'),
				'description' => 'The URL the user should be taken to if they click on the banner',
				'field_name' => 'url',
				'type' => 'url',
			),
			array(
				'title' => __('Languages', 'spark-cw-fresh'),
				'field_name' => 'languages',
				'type' => 'checkboxes',
				'options' => $language_choices,
			),
			array(
				'title' => __('Location', 'spark-cw-fresh'),
				'description' => 'Where the banner will be shown',
				'field_name' => 'location',
				'type' => 'checkboxes',
				'options' => array(
					array(
						'label' => 'In RSS Feed',
						'value' => 'rss',
					),
					array(
						'label' => 'On Website',
						'value' => 'web',
					),
				),
			),
			array(
				'title' => __('Start Date', 'spark-cw-fresh'),
				'description' => 'The first date the banner will be shown',
				'field_name' => 'start_date',
				'type' => 'date',
			),
			array(
				'title' => __('End Date', 'spark-cw-fresh'),
				'description' => 'The last date the banner will be shown',
				'field_name' => 'end_date',
				'type' => 'date',
			),
			array(
				'title' => __('Days', 'spark-cw-fresh'),
				'description' => 'Which days of the week the banner will be shown',
				'field_name' => 'days',
				'type' => 'checkboxes',
				'options' => array(
					array(
						'label' => 'Sunday',
						'value' => 0,
					),
					array(
						'label' => 'Monday',
						'value' => 1,
					),
					array(
						'label' => 'Tuesday',
						'value' => 2,
					),
					array(
						'label' => 'Wednesday',
						'value' => 3,
					),
					array(
						'label' => 'Thursday',
						'value' => 4,
					),
					array(
						'label' => 'Friday',
						'value' => 5,
					),
					array(
						'label' => 'Saturday',
						'value' => 6,
					),
				),
			),
		);
		new Spark_Cw_Fresh_Meta(__('Banner Details', 'spark-cw-fresh'), array('fresh-banner'), $meta_fields, 'normal');

		$ia_ver = get_option('spark-cw-fresh-version-ia', 0);
		if (version_compare($ia_ver, $this->version, '<')) {
			$this->apply_updates($ia_ver, $this->version);
			update_option('spark-cw-fresh-version-ia', $this->version);
			add_action('init', 'flush_rewrite_rules');
		}
	}

	/**
	 * Apply DB updates when plugin updated
	 * @param string $from_version
	 * @param string $to_version
	 * @since 1.4.0
	 */
	private function apply_updates($from_version, $to_version) {
		if (version_compare($from_version, '1.4.0', '<')) {
			// Add default values for new meta fields added in 1.4.0
			$args = array(
				'post_type' => 'fresh-banner',
				'posts_per_page' => -1,
				'fields' => 'ids',
			);
			$banners = get_posts($args);
			foreach ($banners as $banner_id) {
				if (!metadata_exists('post', $banner_id, 'location')) {
					add_post_meta($banner_id, 'location', 'rss');
				}
				if (!metadata_exists('post', $banner_id, 'days')) {
					add_post_meta($banner_id, 'days', 0);
					add_post_meta($banner_id, 'days', 1);
					add_post_meta($banner_id, 'days', 2);
					add_post_meta($banner_id, 'days', 3);
					add_post_meta($banner_id, 'days', 4);
					add_post_meta($banner_id, 'days', 5);
					add_post_meta($banner_id, 'days', 6);
				}
			}
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
