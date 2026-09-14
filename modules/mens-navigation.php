<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Keep the men's desktop and mobile navigation in sync with the catalogue.
 *
 * Existing manual labels, links and ordering are retained. The filter only
 * hides empty or unrelated product-category links and appends catalogue items
 * which are missing from the men's branch.
 */
function meditrendy_sync_mens_navigation( $items, $args ) {
    if ( is_admin() || empty( $items ) || ! taxonomy_exists( 'product_cat' ) ) {
        return $items;
    }

    $root_item = meditrendy_mens_navigation_root_item( $items );
    $root_term = $root_item ? meditrendy_mens_navigation_term_from_url( $root_item->url ) : null;

    if ( ! $root_item || ! $root_term ) {
        return $items;
    }

    $root_id        = (int) $root_item->ID;
    $descendant_ids = meditrendy_mens_navigation_descendant_ids( $items, $root_id );
    $headings       = meditrendy_mens_navigation_heading_templates( $items, $root_id );
    $categories     = meditrendy_mens_navigation_children( $root_term->term_id );
    $group_labels   = meditrendy_mens_navigation_group_labels( $root_term->slug );

    if ( empty( $categories ) ) {
        return $items;
    }

    $colour_category      = null;
    $discover_categories  = array();
    $clothing_categories  = array();
    $accessory_categories = array();

    foreach ( $categories as $category ) {
        $kind = meditrendy_mens_navigation_category_kind( $category );

        if ( 'colour' === $kind ) {
            $colour_category = $category;
        } elseif ( 'discover' === $kind ) {
            $discover_categories[] = $category;
        } elseif ( 'accessories' === $kind ) {
            $accessory_categories[] = $category;
        } else {
            $clothing_categories[] = $category;
        }
    }

    $colour_categories = $colour_category
        ? array_values(
            array_filter(
                meditrendy_mens_navigation_children( $colour_category->term_id ),
                'meditrendy_mens_navigation_is_bilingual_colour'
            )
        )
        : array();
    $accessory_items = array();

    foreach ( $accessory_categories as $category ) {
        $accessory_items[] = $category;

        foreach ( meditrendy_mens_navigation_children( $category->term_id ) as $child ) {
            $accessory_items[] = $child;
        }
    }

    $existing_term_ids = array();
    $removed_item_ids  = array();

    foreach ( $items as $item ) {
        $item_id = (int) $item->ID;

        if ( ! isset( $descendant_ids[ $item_id ] ) ) {
            continue;
        }

        if ( '#' === substr( trim( $item->url ), -1 ) ) {
            continue;
        }

        $term = meditrendy_mens_navigation_term_from_url( $item->url );

        if ( ! $term ) {
            continue;
        }

        if ( $colour_category && (int) $term->parent === (int) $colour_category->term_id ) {
            if ( ! meditrendy_mens_navigation_is_bilingual_colour( $term ) ) {
                $removed_item_ids[ $item_id ] = true;
                continue;
            }

            $item->title = $term->name;
        }

        if ( ! meditrendy_mens_navigation_term_belongs_to_root( $term, $root_term ) || (int) $term->count < 1 ) {
            $removed_item_ids[ $item_id ] = true;
            continue;
        }

        $existing_term_ids[ (int) $term->term_id ] = true;
        $canonical_url = get_term_link( $term );

        if ( ! is_wp_error( $canonical_url ) ) {
            $item->url = $canonical_url;
        }
    }

    $removed_item_ids = meditrendy_mens_navigation_expand_removed_items( $items, $removed_item_ids );
    $additions        = array();
    $colour_parent_id = 0;

    if ( $colour_category ) {
        $colour_parent_id = meditrendy_mens_navigation_existing_term_item_id( $items, $descendant_ids, $removed_item_ids, $colour_category->term_id );

        if ( ! $colour_parent_id ) {
            $colour_item       = meditrendy_mens_navigation_term_item( $root_item, $colour_category, $root_id, ! empty( $colour_categories ) );
            $colour_parent_id  = (int) $colour_item->ID;
            $additions[]       = $colour_item;
            $existing_term_ids[ (int) $colour_category->term_id ] = true;
        }

        meditrendy_mens_navigation_append_missing_terms( $colour_categories, $colour_parent_id, $root_item, $existing_term_ids, $additions );
    }

    if ( ! empty( $discover_categories ) ) {
        $discover_heading = meditrendy_mens_navigation_group_parent(
            $root_item,
            isset( $headings[0] ) ? $headings[0] : null,
            $root_id,
            $group_labels['discover'],
            1,
            $additions
        );
        meditrendy_mens_navigation_append_missing_terms( $discover_categories, $discover_heading, $root_item, $existing_term_ids, $additions );
    }

    if ( ! empty( $clothing_categories ) ) {
        $clothing_heading = meditrendy_mens_navigation_group_parent(
            $root_item,
            isset( $headings[1] ) ? $headings[1] : null,
            $root_id,
            $group_labels['clothing'],
            2,
            $additions
        );
        meditrendy_mens_navigation_append_missing_terms( $clothing_categories, $clothing_heading, $root_item, $existing_term_ids, $additions );
    }

    if ( ! empty( $accessory_items ) ) {
        $accessories_heading = meditrendy_mens_navigation_group_parent(
            $root_item,
            isset( $headings[2] ) ? $headings[2] : null,
            $root_id,
            $group_labels['accessories'],
            3,
            $additions
        );
        meditrendy_mens_navigation_append_missing_terms( $accessory_items, $accessories_heading, $root_item, $existing_term_ids, $additions );
    }

    $synced_items = array();

    foreach ( $items as $item ) {
        if ( isset( $removed_item_ids[ (int) $item->ID ] ) ) {
            continue;
        }

        if ( (int) $item->ID === $root_id ) {
            $item->classes = array_values( array_unique( array_merge( (array) $item->classes, array( 'menu-item-has-children' ) ) ) );
        }

        $synced_items[] = $item;
    }

    foreach ( $additions as $addition ) {
        $synced_items[] = $addition;
    }

    return $synced_items;
}

add_filter( 'wp_nav_menu_objects', 'meditrendy_sync_mens_navigation', 900, 2 );

function meditrendy_mens_navigation_root_item( $items ) {
    $root_slugs = array( 'vyriska-medicinine-apranga', 'viriesiem', 'meestele' );

    foreach ( $items as $item ) {
        if ( ! empty( $item->menu_item_parent ) ) {
            continue;
        }

        $term = meditrendy_mens_navigation_term_from_url( $item->url );

        if ( $term && in_array( $term->slug, $root_slugs, true ) ) {
            return $item;
        }
    }

    return null;
}

function meditrendy_mens_navigation_term_from_url( $url ) {
    $path = wp_parse_url( $url, PHP_URL_PATH );

    if ( ! $path || false === strpos( $path, '/product-category/' ) ) {
        return null;
    }

    $segments = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
    $slug     = end( $segments );

    if ( ! $slug ) {
        return null;
    }

    $term = get_term_by( 'slug', sanitize_title( $slug ), 'product_cat' );

    return $term instanceof WP_Term ? $term : null;
}

function meditrendy_mens_navigation_children( $parent_id ) {
    $terms = get_terms(
        array(
            'taxonomy'   => 'product_cat',
            'parent'     => (int) $parent_id,
            'hide_empty' => true,
            'orderby'    => 'menu_order',
            'order'      => 'ASC',
        )
    );

    return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Only colour categories named in the "local language – English" format belong
 * in navigation. This removes single-language duplicates without affecting the
 * underlying WooCommerce category pages.
 */
function meditrendy_mens_navigation_is_bilingual_colour( $term ) {
    $parts = preg_split( '/\s+[\x{2013}\x{2014}-]\s+/u', trim( $term->name ), 2 );

    return 2 === count( $parts ) && '' !== trim( $parts[0] ) && '' !== trim( $parts[1] );
}

function meditrendy_mens_navigation_category_kind( $term ) {
    $value = strtolower( remove_accents( $term->slug . ' ' . $term->name ) );

    if ( preg_match( '/spalv|kras|varv/', $value ) ) {
        return 'colour';
    }

    if ( preg_match( '/naujien|jaunum|uudis|akcij|soodus|bestsel|popular|enimmuud/', $value ) ) {
        return 'discover';
    }

    if ( preg_match( '/aksesuar|aksesu|aksessuaar|stetoskop|kepur|cepur|muts/', $value ) ) {
        return 'accessories';
    }

    return 'clothing';
}

function meditrendy_mens_navigation_descendant_ids( $items, $root_id ) {
    $descendants = array();
    $changed     = true;

    while ( $changed ) {
        $changed = false;

        foreach ( $items as $item ) {
            $item_id   = (int) $item->ID;
            $parent_id = (int) $item->menu_item_parent;

            if ( isset( $descendants[ $item_id ] ) ) {
                continue;
            }

            if ( $parent_id === $root_id || isset( $descendants[ $parent_id ] ) ) {
                $descendants[ $item_id ] = true;
                $changed = true;
            }
        }
    }

    return $descendants;
}

function meditrendy_mens_navigation_heading_templates( $items, $root_id ) {
    $headings = array();

    foreach ( $items as $item ) {
        if ( (int) $item->menu_item_parent !== $root_id || empty( $item->classes ) ) {
            continue;
        }

        if ( ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
            continue;
        }

        $term = meditrendy_mens_navigation_term_from_url( $item->url );

        if ( $term && 'colour' === meditrendy_mens_navigation_category_kind( $term ) ) {
            continue;
        }

        $headings[] = $item;
    }

    return array_values( $headings );
}

function meditrendy_mens_navigation_group_labels( $root_slug ) {
    $labels = array(
        'vyriska-medicinine-apranga' => array(
            'discover'    => __( 'Atraskite', 'meditrendy-child' ),
            'clothing'    => __( 'Medicininė apranga', 'meditrendy-child' ),
            'accessories' => __( 'Aksesuarai', 'meditrendy-child' ),
        ),
        'viriesiem' => array(
            'discover'    => __( 'Atklājiet', 'meditrendy-child' ),
            'clothing'    => __( 'Medicīniskais apģērbs', 'meditrendy-child' ),
            'accessories' => __( 'Aksesuāri', 'meditrendy-child' ),
        ),
        'meestele' => array(
            'discover'    => __( 'Avastage', 'meditrendy-child' ),
            'clothing'    => __( 'Meditsiinirõivad', 'meditrendy-child' ),
            'accessories' => __( 'Aksessuaarid', 'meditrendy-child' ),
        ),
    );

    return isset( $labels[ $root_slug ] ) ? $labels[ $root_slug ] : $labels['vyriska-medicinine-apranga'];
}

function meditrendy_mens_navigation_term_belongs_to_root( $term, $root_term ) {
    if ( (int) $term->term_id === (int) $root_term->term_id ) {
        return true;
    }

    return in_array( (int) $root_term->term_id, array_map( 'intval', get_ancestors( $term->term_id, 'product_cat' ) ), true );
}

function meditrendy_mens_navigation_expand_removed_items( $items, $removed_ids ) {
    $changed = true;

    while ( $changed ) {
        $changed = false;

        foreach ( $items as $item ) {
            $item_id   = (int) $item->ID;
            $parent_id = (int) $item->menu_item_parent;

            if ( ! isset( $removed_ids[ $item_id ] ) && isset( $removed_ids[ $parent_id ] ) ) {
                $removed_ids[ $item_id ] = true;
                $changed = true;
            }
        }
    }

    return $removed_ids;
}

function meditrendy_mens_navigation_existing_term_item_id( $items, $descendant_ids, $removed_ids, $term_id ) {
    foreach ( $items as $item ) {
        $item_id = (int) $item->ID;

        if ( ! isset( $descendant_ids[ $item_id ] ) || isset( $removed_ids[ $item_id ] ) ) {
            continue;
        }

        $term = meditrendy_mens_navigation_term_from_url( $item->url );

        if ( $term && (int) $term->term_id === (int) $term_id ) {
            return $item_id;
        }
    }

    return 0;
}

function meditrendy_mens_navigation_term_item( $template, $term, $parent_id, $has_children ) {
    $item_id = -1000000 - (int) $term->term_id;
    $item    = clone $template;
    $url     = get_term_link( $term );

    $item->ID               = $item_id;
    $item->db_id            = $item_id;
    $item->object_id        = (int) $term->term_id;
    $item->object           = 'product_cat';
    $item->type             = 'taxonomy';
    $item->type_label       = __( 'Product category', 'woocommerce' );
    $item->title            = $term->name;
    $item->url              = is_wp_error( $url ) ? '#' : $url;
    $item->menu_item_parent = (int) $parent_id;
    $item->target           = '';
    $item->attr_title       = '';
    $item->description      = '';
    $item->xfn              = '';
    $item->classes          = array( 'menu-item', 'menu-item-type-taxonomy', 'menu-item-object-product_cat' );
    $item->current               = function_exists( 'is_tax' ) && is_tax( 'product_cat', $term->term_id );
    $item->current_item_parent   = false;
    $item->current_item_ancestor = false;

    if ( $has_children ) {
        $item->classes[] = 'menu-item-has-children';
    }

    if ( $item->current ) {
        $item->classes[] = 'current-menu-item';
    }

    return $item;
}

function meditrendy_mens_navigation_group_parent( $root_template, $heading_template, $root_id, $fallback_title, $position, &$additions ) {
    if ( $heading_template ) {
        return (int) $heading_template->ID;
    }

    $item_id = -2000000 - ( (int) $root_template->ID * 10 ) - (int) $position;
    $item    = clone $root_template;

    $item->ID               = $item_id;
    $item->db_id            = $item_id;
    $item->object_id        = $item_id;
    $item->menu_item_parent = (int) $root_id;
    $item->type             = 'custom';
    $item->object           = 'custom';
    $item->title            = __( $fallback_title, 'meditrendy-child' );
    $item->url              = '#';
    $item->target           = '';
    $item->attr_title       = '';
    $item->description      = '';
    $item->xfn              = '';
    $item->current          = false;
    $item->current_item_parent   = false;
    $item->current_item_ancestor = false;
    $item->classes          = array( 'menu-item', 'menu-item-type-custom', 'menu-item-object-custom', 'menu-item-has-children', 'mt-mens-menu-group' );

    $additions[] = $item;

    return $item_id;
}

function meditrendy_mens_navigation_append_missing_terms( $terms, $parent_id, $template, &$existing_ids, &$additions ) {
    foreach ( $terms as $term ) {
        $term_id = (int) $term->term_id;

        if ( isset( $existing_ids[ $term_id ] ) ) {
            continue;
        }

        $additions[] = meditrendy_mens_navigation_term_item( $template, $term, $parent_id, false );
        $existing_ids[ $term_id ] = true;
    }
}
