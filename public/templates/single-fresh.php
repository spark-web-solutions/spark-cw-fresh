<?php
/**
 * Custom template for displaying a single FRESH post.
 * Override this by copying it to your theme and making the desired changes.
 *
 * @link       https://sparkweb.com.au
 * @since      1.0.0
 *
 * @package    Spark_Cw_Fresh
 * @subpackage Spark_Cw_Fresh/public/templates
 */

get_header();

global $post;
$meta = Spark_Cw_Fresh_Meta::get_post_meta();
?>
<article <?php post_class('fresh-wrapper'); ?>>
    <h1><?php the_title(); ?></h1>
    <p class="scripture"><span class="reference"><?php echo $meta['scripture_reference']; ?></span> <?php echo $meta['scripture_quote']; ?></p>
    <div class="video">
        <iframe src="<?php echo $meta['video']; ?>" allowfullscreen="" width="100%" height="auto" frameborder="0"></iframe>
    </div>
    <div class="audio">
        <audio src="<?php echo $meta['audio']; ?>" controls="controls"></audio>
    </div>
    <?php the_content(); ?>
</article>
<?php

get_footer();
