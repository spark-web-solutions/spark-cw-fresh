<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/admin
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Admin {

    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     * @since 1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/spark-cw-fresh-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/spark-cw-fresh-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Register our update handler
     * @since 1.0.0
     */
    public function updates() {
        new Spark_Cw_Fresh_Updates(SPARK_CW_FRESH_PATH, 'spark-web-solutions', 'spark-cw-fresh');
    }

    /**
     * Add our custom menu items
     * @since 1.0.0
     */
    public function menu() {
        remove_submenu_page('edit.php?post_type=fresh', 'post-new.php?post_type=fresh');
        add_submenu_page('edit.php?post_type=fresh', __('FRESH Settings', 'spark-cw-fresh'), __('Settings', 'spark-cw-fresh'), 'manage_options', 'spark-cw-fresh-settings', array($this, 'settings_page'));
    }

    /**
     * Settings page content
     * @since 1.0.0
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'spark-cw-fresh'));
        }

        echo '<div class="wrap">';
        echo '<h1>'.esc_html(get_admin_page_title()).'</h1>';
        echo '<form action="options.php" method="post">';
        do_settings_sections('spark-cw-fresh-settings');
        settings_fields('spark-cw-fresh-settings');
        echo "<p class='submit'>";
        submit_button(__('Save Settings', 'spark-cw-fresh'), 'primary', 'submit', false);
        echo "</p>";
        echo '</form>';
        echo '</div>';
    }

    /**
     * Register the plugin settings
     * @since 1.0.0
     */
    public function register_settings() {
        $languages = Spark_Cw_Fresh::$languages;
        unset($languages['en-au']); // We always want English, so don't include it as an option
        $section_cfg = array(
			array(
				'key' => 'spark-cw-fresh-general-settings',
				'title' => __('General Settings', 'spark-cw-fresh'),
				'page' => 'spark-cw-fresh-settings',
				'wp_option' => 'spark-cw-fresh-settings',
				'callback' => false,
				'fields' => array(
					array(
						'key' => 'spark-cw-fresh-settings-language',
						'title' => __('Additional Languages', 'spark-cw-fresh'),
						'type' => 'checkboxes',
						'args' => array(),
						'choices' => $languages,
						'register_settings_args' => array(
							'type' => 'string',
							'sanitize_callback' => null,
							'default' => array(),
						),
					),
				),
			),
        );

        foreach ($section_cfg as $section) {
            add_settings_section($section['key'], $section['title'], $section['callback'], $section['page']);
            foreach ($section['fields'] as $section_field_value) {
                register_setting($section['wp_option'], $section_field_value['key'], $section_field_value['register_settings_args']);
                add_settings_field($section_field_value['key'], $section_field_value['title'], array($this, 'render_section_fields'), $section['page'], $section['key'], $section_field_value);
            }
        }
    }

    /**
     * The callback to render settings fields
     * @since 1.0.0
     */
    public function render_section_fields($param) {
        $value = maybe_unserialize(get_option($param['key']));
        if (empty($value)) {
            $value = $param['register_settings_args']['default'];
        }

        $prop = '';
        foreach ($param['args'] as $prop_key => $prop_value) {
            $prop .= $prop_key.'="'.$prop_value.'" ';
        }

        switch ($param['type']) {
            case 'checkboxes':
                foreach ($param['choices'] as $val => $text) {
                    $selected = is_array($value) ? in_array($val, $value) : $val == $value;
                    echo "<label><input type='checkbox' name='{$param['key']}[]' id='{$param['key']}' value='$val' ".checked(true, $selected, false)." $prop> $text</label><br>";
                }
                break;
            case 'select':
                echo '<select name="'.$param['key'].'" id="'.$param['key'].'" '.$prop.'>';
                foreach ($param['choices'] as $val => $text) {
                    echo '<option value="'.$val.'" '.selected($val, $value, false).'>'.$text.'</option>';
                }
                echo '</select>';
                break;
            case 'textarea':
                echo "<textarea name='{$param['key']}' id='{$param['key']}' {$prop}>{$value}</textarea>";
                break;
            case 'wp-editor':
                wp_editor($value, $param['key'], array_merge($param['args'], array('textarea_name' => $param['key'])));
                break;
            default:
                echo "<input type='{$param['type']}' name='{$param['key']}' id='{$param['key']}' value='{$value}' {$prop}>";
                break;
        }
    }

	/**
	 * Register custom banner admin columns
	 * @param array $columns
	 * @return array
	 * @since 1.4.0
	 */
	public function banner_admin_columns_register($columns) {
		$columns['Active Dates'] = 'Active Dates';
		$columns['Display'] = 'Display';

		return $columns;
	}

	/**
	 * Output content for custom banner admin columns
	 * @param string $column_name
	 * @param int $post_id
	 * @since 1.4.0
	 */
	public function banner_admin_column_display($column_name, $post_id) {
		switch ($column_name) {
			case 'Active Dates':
				$start = get_post_meta($post_id, 'start_date', true);
				$end = get_post_meta($post_id, 'end_date', true);
				if (!empty($start) && !empty($end)) {
					$start_date = new DateTime($start);
					$end_date = new DateTime($end);
					$now = new DateTime(current_time('mysql'));
					$value = $start_date->format(get_option('date_format'));
					if ($end_date > $start_date) {
						$value .= ' to '.$end_date->format(get_option('date_format'));
					}
				} else {
					$value = '-';
				}
				echo $value;
				break;
			case 'Display':
				$fresh_languages = Spark_Cw_Fresh::$languages;
				foreach ($fresh_languages as $code => $lang) {
					$shortcode = substr($code, 0, strpos($code, '-'));
					$language_codes[$shortcode] = $lang;
				}
				$languages = get_post_meta($post_id, 'languages');
				$languages = array_map(function($lang) use ($language_codes) {
					return $language_codes[$lang];
				}, $languages);
				echo implode('/', $languages).'<br>';
				$location = get_post_meta($post_id, 'location');
				echo strtoupper(implode('/', $location)).'<br>';
				$days = get_post_meta($post_id, 'days');
				$days = array_map(function($day) {
					return jddayofweek($day - 1, 2);
				}, $days);
				echo implode('/', $days);
				break;
		}
	}

	/**
	 * Register custom banner admin column as sortable
	 * @param array $columns
	 * @return array
	 * @since 1.4.0
	 */
	public function banner_admin_column_register_sortable($columns) {
		$columns['Active Dates'] = 'active_dates';

		return $columns;
	}

	/**
	 * Allow sorting banners by start date in admin
	 * @param array $vars
	 * @return array
	 * @since 1.4.0
	 */
	public function banner_column_orderby($vars) {
		if (is_admin() && $vars['post_type'] == 'fresh-banner' && isset($vars['orderby'])) {
			switch ($vars['orderby']) {
				case 'active_dates':
					$vars = array_merge($vars, array(
							'meta_key' => 'start_date',
							'orderby' => 'meta_value',
					));
					break;
			}
		}
		return $vars;
	}
}
