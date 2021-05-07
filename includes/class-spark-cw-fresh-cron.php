<?php
/**
 * Class responsible for scheduling and un-scheduling events (cron jobs).
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * Class responsible for scheduling and un-scheduling events (cron jobs).
 *
 * This class defines all code necessary to schedule and un-schedule cron jobs.
 *
 * @since 1.0.0
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Cron {
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
     * URL for the RSS feed for each supported language
     *
     * @since 1.0.0
     * @access private
     * @var array
     */
    private $rss_feeds = array();

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

        foreach (Spark_Cw_Fresh::$languages as $code => $name) {
            $base_url = 'https://christianityworks.com/wp-content/plugins/bb-rss-mailchimp/feeds/fresh-unbranded';
            $feed_lang = $code == 'en-au' ? '' : '-'.strtolower($name);
            $feed_url = $base_url.$feed_lang.'.xml';
            $this->rss_feeds[$code] = $feed_url;
        }
    }

    /**
     * Check if already scheduled, and schedule if not.
     *
     * @since 1.0.0
     */
    public static function register_cron() {
        if (!wp_next_scheduled('spark_cw_fresh_get_latest_fresh')) {
            // CW RSS is generated around 4am Sydney time, so we'll check a couple of hours later at 8pm GMT (6am Sydney time)
            wp_schedule_event(strtotime('8pm'), 'daily', 'spark_cw_fresh_get_latest_fresh');
        }
    }

    /**
     * Unschedule.
     *
     * @since 1.0.0
     */
    public static function unregister_cron() {
        wp_clear_scheduled_hook('spark_cw_fresh_get_latest_fresh');
    }

    /**
     * Get latest FRESH from RSS and store locally
     *
     * @since 1.0.0
     */
    public function get_latest_fresh() {
        $languages = maybe_unserialize(get_option('spark-cw-fresh-settings-language', array()));
        if (!is_array($languages)) {
            $languages = array();
        }
        $english = 'en-au';
        array_unshift($languages, $english);

        foreach ($languages as $lang) {
            if (!array_key_exists($lang, $this->rss_feeds)) {
                continue;
            }
            $feed_url = $this->rss_feeds[$lang];
            if (false === $x = simplexml_load_file($feed_url)) {
                continue;
            }

            $namespaces = $x->getNamespaces(true);
            foreach ($x->channel->item as $item) {
                $post_type = 'fresh';
                if ($lang != $english) {
                    $post_type .= '-'.substr($lang, 0, strpos($lang, '-'));
                }
                $post_content = (string)$item->description;
                $contents = $item->children($namespaces['content']);
                if (!empty($contents->encoded)) {
                	$post_content = (string)$contents->encoded;
                }
                $post = array(
                        'post_type' => $post_type,
                        'post_status' => 'publish',
                        'post_date' => date('Y-m-d', strtotime((string)$item->pubDate)),
                        'post_title' => (string)$item->title,
                		'post_content' => $post_content,
                );

                // Make sure we haven't already added this post
                $existing_post = get_page_by_title((string)$item->title, OBJECT, $post_type);
                if ($existing_post instanceof WP_Post && strtotime($existing_post->post_date) == strtotime((string)$item->pubDate)) {
                    continue;
                }

                // Insert new post
                $post_id = wp_insert_post($post, true);
                if (is_int($post_id)) {
                    add_post_meta($post_id, '_lang', $lang);
                    add_post_meta($post_id, 'scripture_reference', (string)$item->children($namespaces['fresh'])->scriptureReference);
                    add_post_meta($post_id, 'scripture_quote', (string)$item->children($namespaces['fresh'])->scriptureQuote);
                    $media = $item->children($namespaces['media']);
                    foreach ($media as $media_item) {
                        $atts = $media_item->attributes();
                        if ($atts->medium == 'image') { // Featured image
                            $thumbnail_id = media_sideload_image((string)$atts->url, $post_id, null, 'id');
                            set_post_thumbnail($post_id, $thumbnail_id);
                        } elseif ($atts->medium == 'video') { // Video
                            add_post_meta($post_id, 'video', (string)$atts->url);
                        } elseif ($atts->medium == 'audio') { // Audio
                            add_post_meta($post_id, 'audio', (string)$atts->url);
                        }
                    }

                    // Categories
                    $categories = array();
                    foreach ($item->category as $category) {
                        $categories[] = (string)$category;
                    }
                    wp_set_object_terms($post_id, $categories, 'fresh_cat');
                }
            }
        }
    }
}
