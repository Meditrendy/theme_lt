<?php

// =============================================================================
// VIEWS/GLOBAL/_CONTENT-404.PHP
// -----------------------------------------------------------------------------
// Child theme override for the 404 page content.
// =============================================================================

?>

<?php
$site_language = strtolower( substr( determine_locale(), 0, 2 ) );
$not_found_messages = array(
    'et' => 'Lehte, mida otsite, ei leitud. Proovige kasutada otsingut või naaske avalehele.',
    'lt' => 'Puslapis, kurio ieškote, nerastas. Pabandykite pasinaudoti paieška arba grįžkite į pagrindinį puslapį.',
    'lv' => 'Lapa, kuru meklējat, nav atrasta. Mēģiniet izmantot meklēšanu vai atgriezieties sākumlapā.',
    'pl' => 'Nie znaleziono szukanej strony. Spróbuj skorzystać z wyszukiwarki lub wróć na stronę główną.',
);
?>
<p class="center-text"><?php echo esc_html( $not_found_messages[ $site_language ] ?? $not_found_messages['lt'] ); ?></p>
<?php get_search_form(); ?>
