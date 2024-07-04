<?php
/**
 * The file that defines the TAX helper class
 *
 * A class definition that simplifies creating custom taxonomies
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * The TAX helper class.
 *
 * This is a helper class to simplify the creation of custom taxonomies
 *
 * @since 1.0.0
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Tax {
	private $plural;
	private $singular;
	private $taxonomy;
	private $args;
	private $posttypes;

    public function __construct($singular, $plural, array $posttypes, array $args = array(), $slug = '') {
        $this->plural = $plural;
        $this->singular = $singular;
        $this->taxonomy = !empty($slug) ? $slug : str_replace(' ', '', strtolower($singular));
        $this->args = $args;
        foreach($posttypes as $key => & $posttype) {
            $posttype = strtolower($posttype);
            add_post_type_support($posttype, 'page-attributes');
        }

        $this->posttypes = $posttypes;
        add_action('init', array($this, 'register'), 0);

        // User taxonomies are a bit special (i.e. there's no proper core support so we have to do a lot more work)
        if (in_array('user', $this->posttypes)) {
            /*
             * Add the taxonomy page in the admin
             */
            if (!isset($args['show_in_menu'])) {
                if (isset($args['show_ui'])) {
                    $args['show_in_menu'] = $args['show_ui'];
                } elseif (isset($args['public'])) {
                    $args['show_in_menu'] = $args['public'];
                } else {
                    $args['show_in_menu'] = true;
                }
            }
            if ($args['show_in_menu']) {
                add_action('admin_menu', array($this, 'add_user_tax_admin_page'));
            }
            add_action('admin_menu', array($this, 'add_user_tax_admin_page'));

            /*
             * Add section to the edit user page in the admin to select terms
             */
            add_action('show_user_profile', array($this, 'user_profile_tax_section'));
            add_action('edit_user_profile', array($this, 'user_profile_tax_section'));
            add_action('user_new_form', array($this, 'user_profile_tax_section'));

            /*
             * Update the terms when the edit user page is updated
             */
            add_action('personal_options_update', array($this, 'save_user_tax_terms'));
            add_action('edit_user_profile_update', array($this, 'save_user_tax_terms'));
            add_action('user_register', array($this, 'save_user_tax_terms'));
        }
    }

    public function register() {
        $labels = array(
                'name' => __(ucfirst($this->singular), 'spark-cw-fresh') ,
                'singular_name' => __(ucfirst($this->singular), 'spark-cw-fresh'),
                'search_items' => __('Search ' . ucfirst($this->plural), 'spark-cw-fresh'),
                'all_items' => __('All ' . ucfirst($this->plural), 'spark-cw-fresh'),
                'parent_item' => __('Parent ' . ucfirst($this->singular), 'spark-cw-fresh'),
                'parent_item_colon' => __('Parent ' . ucfirst($this->singular) . ':', 'spark-cw-fresh'),
                'edit_item' => __('Edit ' . ucfirst($this->singular), 'spark-cw-fresh'),
                'update_item' => __('Update ' . ucfirst($this->singular), 'spark-cw-fresh'),
                'add_new_item' => __('Add New ' . ucfirst($this->singular), 'spark-cw-fresh'),
                'new_item_name' => __('New ' . ucfirst($this->singular), 'spark-cw-fresh'),
                'menu_name' => __(ucfirst($this->plural), 'spark-cw-fresh'),
        );
        $default_args = array(
                'labels' => $labels,
                'hierarchical' => true, // true = categories & false = tags
                'public' => true,
                'show_ui' => true,
                'show_tagcloud' => true,
                'show_in_nav_menus' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_generic_term_count',
                'query_var' => $this->taxonomy,
                'rewrite' => array(
                        'slug' => $this->taxonomy,
                ),
        );
        $args = array_merge($default_args, $this->args);
        register_taxonomy($this->taxonomy, $this->posttypes, $args);
    }

    /**
     * Creates the admin page for the taxonomy under the 'Users' menu.  It works the same as any
     * other taxonomy page in the admin.  However, this is kind of hacky and is meant as a quick solution.  When
     * clicking on the menu item in the admin, WordPress' menu system thinks you're viewing something under 'Posts'
     * instead of 'Users'. We really need WP core support for this.
     */
    public function add_user_tax_admin_page() {
        $tax = get_taxonomy(strtolower($this->taxonomy));
        add_users_page(esc_attr($tax->labels->menu_name), esc_attr($tax->labels->menu_name), $tax->cap->manage_terms, 'edit-tags.php?taxonomy='.$tax->name);
    }

    /**
     * Adds an additional settings section on the edit user/profile page in the admin. This section allows users to
     * select checkboxes for terms from the taxonomy.
     *
     * @param object $user The user object currently being edited.
     */
    public function user_profile_tax_section($user) {
        $tax = get_taxonomy(strtolower($this->taxonomy));
        /* Make sure the user can assign terms of the taxonomy before proceeding. */
        if (!current_user_can($tax->cap->assign_terms)) {
            return;
        }
        /* Get the terms of the taxonomy. */
        $terms = get_terms(strtolower($this->taxonomy), array('hide_empty' => false));
?>
<h3><?php _e(ucfirst($this->singular)); ?></h3>
<table class="form-table">
    <tr>
        <th><label for="<?php echo strtolower($this->taxonomy); ?>"><?php _e('Select '.ucfirst($this->singular), 'spark-cw-fresh'); ?></label></th>
        <td><?php
        /* If there are any terms, loop through them and display checkboxes. */
        if (!empty($terms)) {
            foreach($terms as $term) {
?>
            <input type="checkbox" name="<?php echo strtolower($this->taxonomy);?>[]" value="<?php echo esc_attr($term->slug); ?>" <?php checked(true, $user instanceof WP_User && is_object_in_term($user->ID, strtolower($this->taxonomy), $term->term_id)); ?>> <label><?php echo $term->name; ?></label><br>
<?php
            }
        } else { /* If there are no terms, display a message. */
            _e('There are no '.ucfirst($this->plural).' available.', 'spark-cw-fresh');
        }
?></td>
    </tr>
</table>
<?php
    }

    /**
     * Saves the term(s) selected on the edit user/profile page in the admin. This function is triggered when the page
     * is updated.  We just grab the posted data and use wp_set_object_terms() to save it.
     *
     * @param int $user_id The ID of the user to save the terms for.
     */
    public function save_user_tax_terms($user_id) {
        $tax = get_taxonomy(strtolower($this->taxonomy));
        /* Make sure the current user can edit the user and assign terms before proceeding. */
        if (!current_user_can('edit_user', $user_id) && current_user_can($tax->cap->assign_terms)) {
            return false;
        }
        $term = $_POST[strtolower($this->taxonomy)];

        /* Sets the terms for the user. */
        wp_set_object_terms($user_id, $term, strtolower($this->taxonomy), false);
        clean_object_term_cache($user_id, strtolower($this->taxonomy));
    }
}
