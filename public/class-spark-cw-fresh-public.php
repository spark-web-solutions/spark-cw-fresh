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
     * Use our templates for FRESH posts unless overridden in the theme
     * @param string $template Path to template file
     * @return string Path to template file
     * @since 1.0.0
     */
    public function template_include($template) {
        $template_name = null;
        if (is_singular('fresh')) {
            $template_name = 'single-fresh.php';
        } elseif (is_post_type_archive('fresh')) {
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
     * @param array $shortcode_atts
     * @return string
     * @since 1.0.0
     */
    public function shortcode_fresh_today($shortcode_atts) {
        $args = array(
                'posts_per_page' => 1,
                'post_type' => 'fresh',
                'orderby' => 'post_date',
                'order' => 'DESC',
        );
        $freshes = get_posts($args);

        ob_start();
        foreach ($freshes as $fresh) {
            $meta = Spark_Cw_Fresh_Meta::get_post_meta($fresh);
?>
<article <?php post_class('fresh-wrapper'); ?>>
    <h2><?php echo get_the_title($fresh->ID); ?></h2>
    <p class="scripture"><span class="reference"><?php echo $meta['scripture_reference']; ?></span> <?php echo $meta['scripture_quote']; ?></p>
    <div class="video">
        <iframe src="<?php echo $meta['video']; ?>" allowfullscreen="" width="100%" height="auto" frameborder="0"></iframe>
    </div>
    <div class="audio">
        <audio src="<?php echo $meta['audio']; ?>" controls="controls"></audio>
    </div>
    <?php echo apply_filters('the_content', $fresh->post_content); ?>
</article>
<?php
        }
        $content = ob_get_clean();
        return $content;
    }
}
