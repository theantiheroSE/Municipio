<?php

namespace Municipio\Theme;

class Navigation
{
    public function __construct()
    {
        $this->registerMenus();

        if (get_field('header_layout', 'option') == 'casual') {
            add_filter('wp_nav_menu_items', array($this, 'addSearchMagnifier'), 10, 2);
        }
    }

    public function registerMenus()
    {
        register_nav_menus(array(
            'main-menu' => __('Main menu', 'municipio'),
            'help-menu' => __('Help menu', 'municipio')
        ));
    }

    /**
     * Adds a search icon to the main menu
     * @param string $items Menu items html markup
     * @param object $args  Menu args
     */
    public function addSearchMagnifier($items, $args)
    {
        if ($args->theme_location != apply_filters('Municipio/main_menu_theme_location', 'main-menu')) {
            return $items;
        }

        $search = '<li><a href="#" class="search-icon-btn toggle-search" aria-label="' . __('Sök') . '"><i class="fa fa-search"></i></a></li>';

        $items .= $search;
        return $items;
    }

    /**
     * Outputs the html for the breadcrumb
     * @return void
     */
    public static function outputBreadcrumbs()
    {
        global $post;

        $title = get_the_title();
        $output = '';

        echo '<ol class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';

        if (!is_front_page()) {
            if (is_category() || is_single()) {
                echo '<li>';
                the_category('<li>');

                if (is_single()) {
                    echo '<li>';
                    the_title();
                    echo '</li>';
                }
            } elseif (is_page()) {
                if ($post->post_parent) {
                    $anc = get_post_ancestors($post->ID);
                    $title = get_the_title();

                    $int = 1;
                    foreach ($anc as $ancestor) {
                        if (get_post_status($ancestor) != 'private') {
                            $output = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                                            <a itemprop="item" href="' . get_permalink($ancestor) . '" title="' . get_the_title($ancestor) . '">
                                                <span itemprop="name">' . get_the_title($ancestor) . '</span>
                                                <meta itemprop="position" content="' . $int . '" />
                                            </a>
                                       </li>' . $output;

                            $int++;
                        }
                    }

                    echo $output;
                    echo '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                            <span itemprop="name" class="breadcrumbs-current" title="' . $title . '">' . $title . '</span>
                            <meta itemprop="position" content="' . ($int+1) . '" />
                          </li>';
                } else {
                    echo '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem">
                            <span itemprop="name" class="breadcrumbs-current">' . get_the_title() . '</span>
                            <meta itemprop="position" content="1" />
                          </li>';
                }
            }
        }
        echo '</ol>';
    }
}
