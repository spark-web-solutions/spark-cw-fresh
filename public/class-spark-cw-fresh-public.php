<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/public
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Public {

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
	 * Where our custom templates are stored
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $templates_path;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->templates_path = trailingslashit(trailingslashit(dirname(__FILE__)).'templates');
	}

	/**
	 * Register our custom shortcodes
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode('fresh_today', array($this, 'shortcode_fresh_today'));
	}

	/**
	 * Register our custom RSS feed endpoints
	 * @since 1.1.0
	 */
	public function register_feeds() {
		add_feed('fresh/mailchimp', array($this, 'mailchimp_rss'));
		$additional_languages = maybe_unserialize(get_option('spark-cw-fresh-settings-language'));
		if (is_array($additional_languages)) {
			foreach ($additional_languages as $code) {
				$shortcode = substr($code, 0, strpos($code, '-'));
				add_feed('fresh/'.$shortcode.'/mailchimp', array($this, 'mailchimp_rss'));
			}
		}
	}

	/**
	 * Use our templates for FRESH posts unless overridden in the theme
	 * @param string $template Path to template file
	 * @return string Path to template file
	 * @since 1.0.0
	 */
	public function template_include($template) {
		$post_types = array('fresh');
		$additional_languages = maybe_unserialize(get_option('spark-cw-fresh-settings-language'));
		if (is_array($additional_languages)) {
			foreach ($additional_languages as $code) {
				$shortcode = substr($code, 0, strpos($code, '-'));
				$post_types[] = 'fresh-'.$shortcode;
			}
		}
		$template_name = null;
		if (is_singular($post_types)) {
			$template_name = 'single-fresh.php';
		} elseif (is_post_type_archive($post_types)) {
			$template_name = 'archive-fresh.php';
		} elseif (is_tax('fresh_cat')) {
			$template_name = 'taxonomy-fresh_cat.php';
		}

		if (!is_null($template_name) && basename($template) != $template_name && file_exists($this->templates_path.$template_name)) {
			$template = $this->templates_path.$template_name;
		}

		return $template;
	}

	/**
	 * Check whether we need to flush rewrite rules (e.g. following plugin activation)
	 *
	 * @since 1.3.3
	 */
	public function maybe_flush_rewrite_rules() {
		if (get_option('spark_cw_fresh_flush_rewrite_rules') == true) {
			flush_rewrite_rules();
			delete_option('spark_cw_fresh_flush_rewrite_rules');
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/spark-cw-fresh-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/spark-cw-fresh-public.js', array('jquery'), $this->version, false);
	}

	/**
	 * Generate output of today's FRESH post
	 * @param array $shortcode_atts {
	 *     Optional. An array of arguments.
	 *     @type string $lang       Which language to use. Accepts any valid 2-character language code. Default 'en'.
	 *     @type string $display    What to display. Accepts 'full', 'preview', 'title', 'image'. Default 'full'.
	 *     @type string $image_size Which version of image to use. Only used when display is 'full' and no video is available, or when display is 'preview' or 'image'. Accepts any registered image size. Default 'full'.
	 * }
	 * @return string
	 * @since 1.0.0
	 * @since 1.4.0 Added support for display and image_size parameters
	 */
	public function shortcode_fresh_today(array $atts = array()) {
		$atts = shortcode_atts(array(
				'lang' => 'en',
				'display' => 'full',
				'image_size' => 'full'
		), $atts, 'fresh_today');
		$fresh = $this->get_todays_fresh($atts['lang']);

		ob_start();
		$meta = Spark_Cw_Fresh_Meta::get_post_meta($fresh);
		switch ($atts['display']) {
			case 'preview':
?>
<div class="fresh-wrapper">
<?php
				if (has_post_thumbnail($fresh)) {
?>
	<div class="image">
		<img src="<?php echo wp_get_attachment_image_url(get_post_thumbnail_id($fresh), $atts['image_size']); ?>" alt="">
	</div>
<?php
				}
?>
	<span class="devotional-title"><?php echo get_the_title($fresh->ID); ?></span>
</div>
<?php
				break;
			case 'title':
?>
<div class="fresh-wrapper">
	<span class="devotional-title"><?php echo get_the_title($fresh->ID); ?></span>
</div>
<?php
				break;
			case 'image':
				if (has_post_thumbnail($fresh)) {
?>
<div class="fresh-wrapper">
	<div class="image">
		<img src="<?php echo wp_get_attachment_image_url(get_post_thumbnail_id($fresh), $atts['image_size']); ?>" alt="">
	</div>
</div>
<?php
				}
				break;
			case 'full':
			default:
?>
<article <?php post_class('fresh-wrapper'); ?>>
	<h2 class="devotional-title"><?php echo get_the_title($fresh->ID); ?></h2>
	<p class="scripture"><span class="reference"><?php echo $meta['scripture_reference']; ?></span> <?php echo $meta['scripture_quote']; ?></p>
<?php
				if (!empty($meta['video'])) {
?>
	<div class="video">
		<iframe src="<?php echo $meta['video']; ?>" allowfullscreen="" width="100%" height="auto" frameborder="0"></iframe>
	</div>
<?php
				} elseif (has_post_thumbnail($fresh)) {
?>
	<div class="image">
		<img src="<?php echo wp_get_attachment_image_url(get_post_thumbnail_id($fresh), $atts['image_size']); ?>" alt="">
	</div>
<?php
				}
				if (!empty($meta['audio'])) {
?>
	<div class="audio">
		<audio src="<?php echo $meta['audio']; ?>" controls="controls"></audio>
	</div>
<?php
				}
				echo apply_filters('the_content', $fresh->post_content); ?>
</article>
<?php
				break;
		}
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Generate MailChimp RSS feed
	 * @since 1.1.0
	 */
	public function mailchimp_rss() {
		header('Content-type: application/rss+xml; charset=utf-8');
		header("Pragma: 0");
		header("Expires: 0");

		$lang = 'en';
		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$parts = explode('/', $path);
		if (strlen($parts[3]) == 2) {
			$lang = $parts[3];
		}

		$now = current_time(DATE_RSS, false);
		$fresh = $this->get_todays_fresh($lang);

		$rss  = '<?xml version="1.0"?>'."\n";
		$rss .= '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">'."\n";
		$rss .= '  <channel>'."\n";
		$rss .= '	<title>FRESH</title>'."\n";
		$rss .= '	<link>'.site_url('/fresh/').'</link>'."\n";
		$rss .= '	<description>Today\'s FRESH</description>'."\n";
		$rss .= '	<language>'.get_post_meta($fresh->ID, '_lang', true).'</language>'."\n";
		$rss .= '	<pubDate>'.$now.'</pubDate>'."\n";
		$rss .= '	<lastBuildDate>'.$now.'</lastBuildDate>'."\n";
		$rss .= '	<managingEditor>'.get_option('admin_email').'</managingEditor>'."\n";
		$rss .= '	<item>'."\n";
		$rss .= '	  <title>'.$fresh->post_title.'</title>'."\n";
		$rss .= '	  <link>'.get_permalink($fresh->ID).'</link>'."\n";
		$rss .= '	  <author>Berni Dymet</author>'."\n";
		$post_content = apply_filters('the_content', $fresh->post_content);
		$post_content .= $this->get_banner_content($lang, 'rss');
		$rss .= '	  <description>'."\n";
		$rss .= '		<![CDATA['.$post_content.']]>'."\n";
		$rss .= '	  </description>'."\n";
		$rss .= '	  <pubDate>'.$now.'</pubDate>'."\n";
		if (has_post_thumbnail($fresh->ID)) {
			$featured_image = get_post_thumbnail_id($fresh->ID);
			$image = wp_get_attachment_image_src($featured_image, 'full');
			$rss .= '	  <media:content url="'.$image[0].'" fileSize="'.filesize(get_attached_file($featured_image)).'" type="'.get_post_mime_type($featured_image).'" medium="image" width="'.$image[1].'" height="'.$image[2].'" />'."\n";
		}
		$cats = wp_get_post_terms($fresh->ID, 'fresh_cat', array('fields' => 'names'));
		foreach ($cats as $cat) {
			$rss .= '	  <category>'.$cat.'</category>'."\n";
		}
		$rss .= '	</item>'."\n";
		$rss .= '  </channel>'."\n";
		$rss .= '</rss>'."\n";

		echo $rss;
	}

	/**
	 * Generate HTML for banners
	 * @param string $lang Shortcode for language. Default 'en'
	 * @param string $context Accepts 'rss' or 'web'. Default 'rss'.
	 * @return string
	 * @since 1.3.0
	 * @since 1.4.0 Added $context parameter
	 */
	public function get_banner_content($lang = 'en', $context = 'rss') {
		$html = '';
		$banners = $this->get_todays_banners($lang, $context);
		foreach ($banners as $banner) {
			if (has_post_thumbnail($banner->ID)) {
				$html .= '<p class="fresh-banner">';
				$url = get_post_meta($banner->ID, 'url', true);
				if (!empty($url)) {
					$html .= '<a href="'.$url.'">';
				}
				$featured_image = get_post_thumbnail_id($banner->ID);
				$image = wp_get_attachment_image_src($featured_image, 'full');
				$html .= '<img src="'.$image[0].'" width="'.$image[1].'" height="'.$image[2].'" alt="'.esc_attr($banner->post_title).'">';
				if (!empty($url)) {
					$html .= '</a>';
				}
				$html .= '</p>'."\n";
			}
		}
		return $html;
	}

	/**
	 * Get list of banners to include in today's RSS
	 * @param string $lang Shortcode for language. Default 'en'
	 * @param string $context Accepts 'rss' or 'web'. Default 'rss'.
	 * @return WP_Post[] Array of banner posts
	 * @since 1.3.0
	 * @since 1.4.0 Added $context parameter
	 */
	private function get_todays_banners($lang = 'en', $context = 'rss') {
		$now = new DateTime(current_time('mysql'));
		$args = array(
			'post_type' => 'fresh-banner',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'languages',
					'value' => $lang,
				),
				array(
					'type' => 'DATE',
					'key' => 'start_date',
					'value' => current_time('Y-m-d'),
					'compare' => '<=',
				),
				array(
					'key' => 'end_date',
					'value' => current_time('Y-m-d'),
					'compare' => '>=',
				),
				array(
					'key' => 'location',
					'value' => $context,
				),
				array(
					'key' => 'days',
					'value' => $now->format('w'),
				),
			),
		);
		return get_posts($args);
	}

	/**
	 * Get today's FRESH post
	 * @param string $lang Optional. Language code. Default 'en'.
	 * @return WP_Post|boolean Post object or false on failure
	 * @since 1.1.0
	 */
	private function get_todays_fresh($lang = 'en') {
		$post_type = 'fresh';
		if ($lang != 'en' && post_type_exists($post_type.'-'.$lang)) {
			$post_type = $post_type.'-'.$lang;
		}
		$args = array(
				'posts_per_page' => 1,
				'post_type' => $post_type,
				'orderby' => 'post_date',
				'order' => 'DESC',
		);
		$freshes = get_posts($args);
		foreach ($freshes as $fresh) {
			return $fresh;
		}

		return false;
	}
}
