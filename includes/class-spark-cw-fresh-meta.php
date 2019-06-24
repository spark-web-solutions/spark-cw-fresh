<?php
/**
 * The file that defines the meta helper class
 *
 * A class definition that simplifies creating custom post meta
 *
 * @link https://sparkweb.com.au
 * @since 1.0.0
 *
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 */

/**
 * The meta helper class.
 *
 * This is a helper class to simplify the creation of custom post meta
 *
 * @since 1.0.0
 * @package Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/includes
 * @author Spark Web Solutions <plugins@sparkweb.com.au>
 */
class Spark_Cw_Fresh_Meta {
    public function __construct($label, array $posttypes, array $fields) {
        $this->label = $label;
        $this->slug = str_replace(' ','',strtolower($label));
        $this->posttypes = $posttypes;
        $this->fields = $fields;
        add_action('add_meta_boxes', array($this, 'metabox'));
        add_action('save_post', array($this, 'metabox_save'));
    }

    public function metabox() {
        foreach ($this->posttypes as $post_type) {
            add_meta_box('meta_'.$this->slug.'_box', __($this->label, ''), array($this, 'metabox_content'), $post_type, 'normal', 'default');
        }
    }

    public function metabox_content($post) {
        $meta_fields = array();
        wp_nonce_field('spark_cw_fresh_meta', 'metabox_content_nonce');

        foreach ($this->fields as $field) {
            $meta_fields[$this->new_field($field)] = $field;
        }

        set_transient($this->slug.'_meta_fields', serialize($meta_fields), 3600);
    }

    function metabox_save($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!wp_verify_nonce($_POST['metabox_content_nonce'], 'spark_cw_fresh_meta')) {
            return;
        }
        if ('page' == $_POST['post_type'] && (!current_user_can('edit_page', $post_id) || !current_user_can('edit_post', $post_id))) {
            return;
        }

        $meta_fields = unserialize(get_transient($this->slug.'_meta_fields'));
        foreach ($meta_fields as $field_name => $meta_field) {
            $value = $_POST[$field_name];
            switch ($meta_field['type']) {
                case 'wp-editor':
                    $value = wp_kses_post($value);
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
                default:
                    $value = sanitize_text_field($value);
                    break;
            }
            update_post_meta($post_id, $field_name, $value);
        }
    }

    function new_field($args) {
        is_array($args) ? extract($args) : parse_str($args);

        //set defaults
        if (!$title && !$field_name) {
            return;
        }
        if (!$title && $field_name) {
            $title = $field_name;
        }
        $title = ucfirst(strtolower($title));
        if (!$field_name && $title) {
            $field_name = $title;
        }
        $field_name = strtolower(str_replace(' ', '_', $field_name));
        if (empty($size)) {
            $size = '100%'; // accepts valid css for all types expect textarea. Textarea expects an array where [0] is width and [1] is height
        }
        if (empty($max_width)) {
            $max_width = '100%';
        }
        if (!$type) {
            $type = 'text';
        }

        $script = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
        $source = ($script == 'post.php' || $script == 'post-new.php') ? 'meta' : 'option';
        if (!$source == 'option' && !$group)
            return;
        $field_name = ($source == 'option') ? $group . "[" . $name . "]" : $field_name;

        echo ' <div style="display:block;width:100%;padding-bottom:1rem;">' . "\n";
        switch ($type) {
            case 'checkbox':
                $checked = ($source == 'meta' && get_post_meta($_GET['post'], $field_name, true) == 'true') ? 'checked="checked"' : '';
                if ($source == 'option') {
                    $option = get_option($group);
                    $value = $option[$name];
                    $checked = ($value == 'true') ? 'checked="checked"' : '';
                }
                echo '   <label style="color:rgba(0,0,0,0.75);"><input type="checkbox" name="' . $field_name . '" value="true" ' . $checked . ' style="margin: 0 5px 0px 0;">' . $title . '</label>' . "\n";
                break;

            case 'radio':
                // expects an $options array of arrays as follows
                // $options = array (
                //      array ( 'label' => 'aaa', 'value' => '1' ),
                //      array ( 'label' => 'aaa', 'value' => '1' ),
                //      );
                if ($source == 'option') {
                    $option = get_option($group);
                    $value = $option[$name];
                } else {
                    $value = get_post_meta($_GET['post'], $field_name, true);
                }
                if ($default && !$value) {
                    $value = $default;
                }
                echo '  <label for="' . $field_name . '">' . "\n";
                echo '      <sub style="color:rgba(0,0,0,0.75);display:block;">' . $title . '</sub>' . "\n";
                echo '   </label>' . "\n";
                foreach ($options as $option) {
                    echo '   <label style="color:rgba(0,0,0,0.75);"><input type="radio" name="'.$field_name.'" value="'.$option['value'].'" '.checked($option['value'], $value, false).' style="margin: 0 5px 0px 0;">'.$option['label'].'</label><br>' . "\n";
                }
                break;

            case 'textarea':
                if ($source == 'meta')
                    $value = get_post_meta($_GET['post'], $field_name, true);
                if ($source == 'option') {
                    $option = get_option($group);
                    $value = $option[$name];
                }
                if ($default && !$value)
                    $value = $default;
                echo '  <label for="' . $field_name . '">' . "\n";
                echo '      <sub style="color:rgba(0,0,0,0.75);display:block;">' . $title . '</sub>' . "\n";
                echo '   </label>' . "\n";
                if (!is_array($size))
                    $size = explode(',', $size);
                $style = 'width:' . $size[0] . ';height:' . $size[1] . ';max-width:' . $max_width . ';';
                echo '   <textarea id="' . $field_name . '" name="' . $field_name . '" style="' . $style . ';" placeholder="' . $placeholder . '" >' . esc_attr($value) . '</textarea>' . "\n";
                break;

            case 'select':
                // expects an $options array of arrays as follows
                // $options = array (
                //      array ( 'label' => 'aaa', 'value' => '1' ),
                //      array ( 'label' => 'aaa', 'value' => '1' ),
                //      );
                $current = get_post_meta($_GET['post'], $field_name, true);
                echo '  <sub style="color:rgba(0,0,0,0.75);display:block;width:100%;max-width:' . $max_width . ';">' . $title . '</sub>' . "\n";
                echo '      <select name="' . $field_name . '" id="' . $field_name . '">' . "\n";
                foreach ($options as $option)
                    echo '      <option value="' . $option['value'] . '" ' . selected($option['value'], $current, false) . '>' . $option['label'] . '</option>' . "\n";
                echo '  </select>' . "\n";
                break;

            case 'color-picker':
                echo '  <label for="meta-color" class="prfx-row-title" style="display:block;width:100%;max-width:' . $max_width . ';">' . $title . '</label>' . "\n";
                echo '  <input name="' . $field_name . '" type="text" value="' . get_post_meta($_GET['post'], $field_name, true) . '" class="meta-color" />' . "\n";
                break;

            case 'wp-editor':
                if ($source == 'meta')
                    $value = get_post_meta($_GET['post'], $field_name, true);
                if ($source == 'option') {
                    $option = get_option($group);
                    $value = $option[$name];
                }
                if ($default && !$value)
                    $value = $default;
                echo '  <label for="' . $field_name . '">' . "\n";
                echo '      <sub style="color:rgba(0,0,0,0.75);display:block;">' . $title . '</sub>' . "\n";
                echo '   </label>' . "\n";
                wp_editor($value, $field_name, $settings);
                break;

            case 'text':
            default:
                if ($source == 'meta')
                    $value = get_post_meta($_GET['post'], $field_name, true);
                if ($source == 'option') {
                    $option = get_option($group);
                    $value = $option[$name];
                }
                if ($default && !$value)
                    $value = $default;
                echo '  <label for="' . $field_name . '">' . "\n";
                echo '      <sub style="color:rgba(0,0,0,0.75);display:block;">' . $title . '</sub>' . "\n";
                echo '  </label>' . "\n";
                echo '   <input type="' . $type . '" id="' . $field_name . '" name="' . $field_name . '" style="display:block;max-width:' . $max_width . ';width:' . $size . ';" placeholder="' . $placeholder . '" value="' . esc_attr($value) . '" />' . "\n";
                break;
        }
        if ($description)
            echo '   <div style="position:relative;top:-3px;display:block;width:100%;color:#ddd;font-size:0.8em;">' . $description . '</div>' . "\n";
        echo ' </div>' . "\n";
        return $field_name;
    }

    /**
     * Gets meta for a post
     * @param integer|WP_Post $post Optional. Post to retrieve meta for
     * @return array|boolean List of meta values already single-ised or false on error
     * @since 1.0.0
     */
    public static function get_post_meta($post = null) {
        $post = get_post($post);
        $meta = false;
        if ($post instanceof WP_Post) {
            $meta = get_post_meta($post->ID);
            if (is_array($meta)) {
                $meta = self::rationalise_meta($meta);
            }
        }
        return $meta;
    }

    /**
     * Clean up meta array to include single values as direct value, like $single parameter to get_post_meta()
     * @param array $meta
     * @return array
     * @since 1.0.0
     */
    public static function rationalise_meta(array $meta) {
        $clean_meta = array();
        foreach ($meta as $k => $v) {
            if (is_array($v) && count($v) == 1) {
                $clean_meta[$k] = $v[0];
            } else {
                $clean_meta[$k] = $v;
            }
        }
        return $clean_meta;
    }
}
