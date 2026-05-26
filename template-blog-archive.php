<?php
/*
Template Name: Meditrendy Blog Archive
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="x-container max width offset">
    <main class="<?php x_main_content_class(); ?>" role="main">
        <?php
        while ( have_posts() ) :
            the_post();
            echo do_shortcode( '[meditrendy_blog_archive]' );
        endwhile;
        ?>
    </main>
</div>

<?php
get_footer();
