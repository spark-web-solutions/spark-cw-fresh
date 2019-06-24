<?php
/**
 * Custom template for displaying an archive of FRESH posts in a particular category.
 * Override this by copying it to your theme and making the desired changes.
 *
 * @link       https://sparkweb.com.au
 * @since      1.0.0
 *
 * @package    Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/public/templates
 */

get_header();

$post_type_obj = get_post_type_object(get_post_type());
$term = get_queried_object();
?>
<div class="fresh-wrapper">
    <h1><?php echo $post_type_obj->labels->singular_name; ?>: <?php echo $term->name; ?></h1>
    <div class="fresh-posts-wrapper">
<?php
if (have_posts()) {
    while(have_posts()) {
        the_post();
?>
        <div class="fresh-preview">
            <a href="<?php the_permalink(); ?>">
                <img src="<?php echo wp_get_attachment_image_url(get_post_thumbnail_id(), 'large'); ?>" alt="">
                <h2><?php the_title(); ?></h2>
                <p class="read-more"><?php _e('Read more', 'spark-cw-fresh'); ?></p>
            </a>
        </div>
<?php
    }
?>
    </div>
    <div class="nav-previous alignleft"><?php previous_posts_link(__('Newer posts', 'spark-cw-fresh')); ?></div>
    <div class="nav-next alignright"><?php next_posts_link(__('Older posts', 'spark-cw-fresh')); ?></div>
<?php
} else {
?>
<p><?php _e('Sorry, no items found.', 'spark-cw-fresh'); ?></p>
<?php
}
?>
</div>
<?php

get_footer();
