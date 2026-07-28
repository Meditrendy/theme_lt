<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add structural column parents immediately before Pro renders an inline menu.
 *
 * Pro omits the empty WordPress column items and promotes their children. This
 * restores the intended four-column hierarchy server-side for the two desktop
 * product menus. The first column already has its visible colour parent.
 */
function meditrendy_build_desktop_menu_columns( $items, $args ) {
    if (
        is_admin() ||
        empty( $items ) ||
        empty( $args->walker ) ||
        ! is_object( $args->walker ) ||
        ! isset( $args->walker->x_menu_type ) ||
        'inline' !== $args->walker->x_menu_type
    ) {
        return $items;
    }

    $menu_columns = array(
        5089 => array(
            array( 5134, 5135, 5123 ),
            array( 18380, 23480, 5092, 5090, 5104 ),
            array( 5132, 15918, 22652 ),
        ),
        5097 => array(
            array( 5143, 5144, 5124 ),
            array( 5107, 5101, 5100, 5106 ),
            array( 5131, 15919, 22669 ),
        ),
    );

    $items_by_id = array();

    foreach ( $items as $item ) {
        $items_by_id[ (int) $item->ID ] = $item;
    }

    foreach ( $menu_columns as $root_item_id => $columns ) {
        if ( ! isset( $items_by_id[ $root_item_id ] ) ) {
            continue;
        }

        foreach ( $columns as $column_index => $column_item_ids ) {
            $column_items = array();

            foreach ( $column_item_ids as $column_item_id ) {
                if ( isset( $items_by_id[ $column_item_id ] ) ) {
                    $column_items[] = $items_by_id[ $column_item_id ];
                }
            }

            if ( empty( $column_items ) ) {
                continue;
            }

            $virtual_item_id = -1 * ( ( $root_item_id * 10 ) + $column_index + 1 );
            $column_parent   = clone $column_items[0];

            $column_parent->ID               = $virtual_item_id;
            $column_parent->db_id            = $virtual_item_id;
            $column_parent->object_id         = $virtual_item_id;
            $column_parent->menu_item_parent  = $root_item_id;
            $column_parent->menu_order        = (int) $column_items[0]->menu_order;
            $column_parent->type              = 'custom';
            $column_parent->object            = 'custom';
            $column_parent->title             = "\u{200B}";
            $column_parent->url               = '#';
            $column_parent->target            = '';
            $column_parent->attr_title        = '';
            $column_parent->description       = '';
            $column_parent->xfn               = '';
            $column_parent->current           = false;
            $column_parent->current_item_parent = false;
            $column_parent->current_item_ancestor = false;
            $column_parent->classes           = array(
                'menu-item',
                'menu-item-type-custom',
                'menu-item-object-custom',
                'menu-item-has-children',
                'mt-desktop-menu-column',
            );

            foreach ( $column_items as $column_item ) {
                $column_item->menu_item_parent = $virtual_item_id;
            }

            $items[] = $column_parent;
        }
    }

    return $items;
}

add_filter( 'wp_nav_menu_objects', 'meditrendy_build_desktop_menu_columns', 999, 2 );
