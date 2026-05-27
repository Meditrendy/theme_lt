<?php
/*
Template Name: Meditrendy Cornerstone Canvas
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="x-main full mt-cornerstone-canvas" role="main">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php x_get_view( 'global', '_content', 'the-content' ); ?>
        </article>

        <?php
    endwhile;
    ?>
</div>

<?php
get_footer();
