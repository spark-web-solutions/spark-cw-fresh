<?php
/**
 * The file that defines the CPT helper class
 *
 * A class definition that simplifies creating custom post types
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * The CPT helper class.
 *
 * This is a helper class to simplify the creation of custom post types
 *
 * @since 1.0.0
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Cpt {
    public function __construct($singular, $plural, array $args = array(), $slug = '') {
        $this->plural = $plural;
        $this->singular = $singular;
        $this->slug = !empty($slug) ? $slug : str_replace(' ', '', strtolower($singular));
        $this->args = $args;
        add_action('init', array($this, 'cpt_mycpt'));
        add_filter('post_updated_messages', array($this, 'cpt_mycpt_messages'));
    }

    public function cpt_mycpt() {
        $labels = array(
                'name' => __(ucfirst($this->plural), 'spark-cw-fresh'),
                'singular_name' => __(ucfirst($this->singular), 'spark-cw-fresh'),
                'add_new' => __('Add New', ucfirst($this->singular), 'spark-cw-fresh'),
                'add_new_item' => __('Add New '.ucfirst($this->singular), 'spark-cw-fresh'),
                'edit_item' => __('Edit '.ucfirst($this->singular), 'spark-cw-fresh'),
                'new_item' => __('New '.ucfirst($this->singular), 'spark-cw-fresh'),
                'all_items' => __('All '.ucfirst($this->plural), 'spark-cw-fresh'),
                'view_item' => __('View '.ucfirst($this->singular), 'spark-cw-fresh'),
                'search_items' => __('Search '.ucfirst($this->plural), 'spark-cw-fresh'),
                'not_found' => __('No '.ucfirst($this->plural).' found', 'spark-cw-fresh'),
                'not_found_in_trash' => __('No '.ucfirst($this->plural).' found in the Trash', 'spark-cw-fresh'),
                'parent_item_colon' => '',
                'menu_name' => __(ucfirst($this->plural), 'spark-cw-fresh'),
        );

        $default_args = array(
                'labels' => $labels,
                'description' => __('Holds our '.ucfirst($this->singular).' posts', 'spark-cw-fresh'),
                'public' => true,
                'menu_position' => 20,
                'supports' => array(
                        'title',
                        'editor',
                        'thumbnail',
                        'comments',
                ),
                'has_archive' => true,
                'hierarchical' => true,
                'show_in_rest' => true,
        );

        $args = array_replace_recursive($default_args, $this->args);

        register_post_type($this->slug, $args);
    }

    // Set Messages
    public function cpt_mycpt_messages($messages) {
        // http://codex.wordpress.org/Function_Reference/register_post_type
        global $post, $post_ID;
        $cpttype = strtolower($this->singular);
        $messages[$cpttype] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => sprintf(__(ucfirst($this->singular) . ' Post updated.', 'spark-cw-fresh'), esc_url(get_permalink($post_ID))),
                2 => __(ucfirst($this->singular) . ' updated.', 'spark-cw-fresh'),
                3 => __(ucfirst($this->singular) . ' deleted.', 'spark-cw-fresh'),
                4 => __(ucfirst($this->singular) . ' Post updated.', 'spark-cw-fresh') ,
                /* translators: %s: date and time of the revision */
                5 => isset($_GET['revision']) ? sprintf(__(ucfirst($this->singular) . ' Post restored to revision from %s', 'spark-cw-fresh'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
                6 => sprintf(__(ucfirst($this->singular) . ' Post published. <a href="%s">View ' . ucfirst($this->singular) . ' Post</a>', 'spark-cw-fresh'), esc_url(get_permalink($post_ID))),
                7 => __(ucfirst($this->singular) . ' Post saved.', 'spark-cw-fresh'),
                8 => sprintf(__(ucfirst($this->singular) . ' Post submitted. <a target="_blank" href="%s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'spark-cw-fresh'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
                9 => sprintf(__(ucfirst($this->singular) . ' Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'spark-cw-fresh'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
                10 => sprintf(__(ucfirst($this->singular) . ' Post draft updated. <a target="_blank" href="%s">Preview ' . ucfirst($this->singular) . ' Post</a>', 'spark-cw-fresh'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))))
        );
        return $messages;
    }
}
