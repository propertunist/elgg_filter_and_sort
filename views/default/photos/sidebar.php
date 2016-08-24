<?php

/************
 * filter_and_sort - elgg plugin
 *
 * Sidebar view for tidypics - cleaned up for filter_and_sort plugin
 *
 * @author - ura soul
 * @website - https://www.ureka.org
 *******************/

$current_user_guid = elgg_get_logged_in_user_guid();

$base = elgg_get_site_url() . 'photos/';

elgg_register_menu_item('page', array('name' => 'A10_tidypics_images',
                                      'text' => elgg_echo('tidypics:siteimagesall'),
                                      'href' => $base . 'siteimagesall',
                                      'section' => 'A'));
elgg_register_menu_item('page', array('name' => 'A20_tidypics_albums',
                                      'text' => elgg_echo('album:all'),
                                      'href' => $base . 'all',
                                      'section' => 'A'));

$page = elgg_extract('page', $vars);
$image = elgg_extract('image', $vars);
if ($page == 'upload') {
        if (elgg_get_plugin_setting('quota', 'tidypics')) {
                echo elgg_view('photos/sidebar/quota', $vars);
        }
} else if (($page == 'all') || ($page == 'owner') || ($page == 'friends')) {


/*
if(elgg_is_logged_in()) {
elgg_register_menu_item('page', array('name' => 'E10_tidypics_usertagged',
                        'text' => elgg_echo('tidypics:usertagged'),
                        'href' => $base . "tagged?guid=$current_user_guid",
                        'section' => 'E'));
}*/

} else if ($image && $page == 'tp_view') {
        if (elgg_get_plugin_setting('exif', 'tidypics')) {
                echo elgg_view('photos/sidebar/exif', $vars);
        }

        // list of tagged members in an image (code from Tagged people plugin by Kevin Jardine)
        if (elgg_get_plugin_setting('tagging', 'tidypics')) {
                $body = elgg_list_entities_from_relationship(array(
                        'relationship' => 'phototag',
                        'relationship_guid' => $image->guid,
                        'inverse_relationship' => true,
                        'type' => 'user',
                        'limit' => 15,
                        'list_type' => 'gallery',
                        'gallery_class' => 'elgg-gallery-users',
                        'pagination' => false
                ));
                if ($body) {
                        $title = elgg_echo('tidypics_tagged_members');
                        echo elgg_view_module('aside', $title, $body);
                }
        }
}
