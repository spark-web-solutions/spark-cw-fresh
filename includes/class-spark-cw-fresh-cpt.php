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
		add_action('init', array($this, 'register_cpt'));
		add_filter('post_updated_messages', array($this, 'messages'));
	}

	public function register_cpt() {
		$labels = array(
				'name' => _x(ucwords($this->plural), 'Post type general name', 'spark-cw-fresh'),
				'singular_name' => _x(ucwords($this->singular), 'Post type singular name', 'spark-cw-fresh'),
				'add_new' => _x('Add New', ucwords($this->singular), 'spark-cw-fresh'),
				'add_new_item' => __('Add New '.ucwords($this->singular), 'spark-cw-fresh'),
				'edit_item' => __('Edit '.ucwords($this->singular), 'spark-cw-fresh'),
				'new_item' => __('New '.ucwords($this->singular), 'spark-cw-fresh'),
				'view_item' => __('View '.ucwords($this->singular), 'spark-cw-fresh'),
				'view_items' => __('View '.ucwords($this->plural), 'spark-cw-fresh'),
				'search_items' => __('Search '.ucwords($this->plural), 'spark-cw-fresh'),
				'not_found' => __('No '.ucwords($this->plural).' found', 'spark-cw-fresh'),
				'not_found_in_trash' => __('No '.ucwords($this->plural).' found in the Trash', 'spark-cw-fresh'),
				'parent_item_colon' => __('Parent '.ucwords($this->singular), 'spark-cw-fresh'),
				'all_items' => __('All '.ucwords($this->plural), 'spark-cw-fresh'),
				'archives' => __(ucwords($this->plural).' Archives', 'spark-cw-fresh'),
				'attributes' => __(ucwords($this->singular).' Attributes', 'spark-cw-fresh'),
				'insert_into_item' => __('Insert into '.ucwords($this->singular), 'spark-cw-fresh'),
				'uploaded_to_this_item' => __('Uploaded to this '.ucwords($this->singular), 'spark-cw-fresh'),
				'filter_items_list' => __('Filter '.ucwords($this->plural).' list', 'spark-cw-fresh'),
				'items_list_navigation' => __(ucwords($this->plural).' list navigation', 'spark-cw-fresh'),
				'items_list' => __(ucwords($this->plural).' list', 'spark-cw-fresh'),
				'item_published' => __(ucwords($this->singular).' published', 'spark-cw-fresh'),
				'item_published_privately' => __(ucwords($this->singular).' published privately', 'spark-cw-fresh'),
				'item_reverted_to_draft' => __(ucwords($this->singular).' reverted to draft', 'spark-cw-fresh'),
				'item_scheduled' => __(ucwords($this->singular).' scheduled', 'spark-cw-fresh'),
				'item_updated' => __(ucwords($this->singular).' updated', 'spark-cw-fresh'),
		);

		$default_args = array(
				'labels' => $labels,
				'description' => __('Holds our '.ucfirst($this->singular).' posts', 'spark-cw-fresh'),
				'public' => true,
				'menu_position' => 20,
				'supports' => array(),
				'has_archive' => true,
				'hierarchical' => true,
				'show_in_rest' => true,
		);

		$args = array_replace_recursive($default_args, $this->args);

		register_post_type($this->slug, $args);
	}

	public function messages($messages) {
		global $post, $post_type_object, $post_ID;

		$permalink = get_permalink($post_ID);
		if (!$permalink) {
			$permalink = '';
		}

		$singular = $post_type_object->labels->singular_name;
		$preview_post_link_html   = '';
		$scheduled_post_link_html = '';
		$view_post_link_html	  = '';
		$preview_url = get_preview_post_link($post);
		$viewable = is_post_type_viewable($post_type_object);

		if ($viewable) {
			// Preview post link.
			$preview_post_link_html = sprintf(
					' <a target="_blank" href="%1$s">%2$s</a>',
					esc_url( $preview_url ),
					sprintf(__( 'Preview %s', 'spark-cw-fresh' ), $singular)
					);

			// Scheduled post preview link.
			$scheduled_post_link_html = sprintf(
					' <a target="_blank" href="%1$s">%2$s</a>',
					esc_url( $permalink ),
					sprintf(__( 'Preview %s', 'spark-cw-fresh' ), $singular)
					);

			// View post link.
			$view_post_link_html = sprintf(
					' <a href="%1$s">%2$s</a>',
					esc_url( $permalink ),
					sprintf(__( 'View %s', 'spark-cw-fresh' ), $singular)
					);
		}

		$scheduled_date = sprintf(
				/* translators: Publish box date string. 1: Date, 2: Time. */
				__( '%1$s at %2$s' ),
				/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
				date_i18n( _x( 'M j, Y', 'publish box date format' ), strtotime( $post->post_date ) ),
				/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
				date_i18n( _x( 'H:i', 'publish box time format' ), strtotime( $post->post_date ) )
				);

		$messages[$post_type_object->name] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => __($singular.' updated.', 'spark-cw-fresh').$view_post_link_html,
				2 => __('Custom field updated.', 'spark-cw-fresh'),
				3 => __('Custom field deleted.', 'spark-cw-fresh'),
				4 => __($singular.' updated.', 'spark-cw-fresh'),
				/* translators: %s: Date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf(__($singular.' restored to revision from %s', 'spark-cw-fresh'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
				6 => __($singular.' published.', 'spark-cw-fresh').$view_post_link_html,
				7 => __($singular.' saved.', 'spark-cw-fresh'),
				8 => __($singular.' submitted.', 'spark-cw-fresh').$preview_post_link_html,
				/* translators: %s: Scheduled date for the post. */
				9 => sprintf(__($singular.' scheduled for: %s.', 'spark-cw-fresh'), '<strong>'.$scheduled_date.'</strong>' ).$scheduled_post_link_html,
				10 => __($singular.' draft updated.', 'spark-cw-fresh').$preview_post_link_html,
		);

		return $messages;
	}
}
