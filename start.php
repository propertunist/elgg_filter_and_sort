<?php

/************
 * filter and sort - elgg plugin
 *
 * adds sorting and filtering capabilities to entity lists in elgg
 *
 * @Author - ura soul
 * @website - https://www.ureka.org
 *******************/

function filter_and_sort_init()
{
    $functions_lib = elgg_get_plugins_path() . 'filter_and_sort/lib/functions.php';
    elgg_register_library('filter_and_sort-functions', $functions_lib);
    elgg_load_library('filter_and_sort-functions');

    $hooks_lib = elgg_get_plugins_path() . 'filter_and_sort/lib/hooks.php';
    elgg_register_library('filter_and_sort-hooks', $hooks_lib);
    elgg_load_library('filter_and_sort-hooks');

    // retrieve list of lists to add sorting filters to
    $list_handlers = elgg_get_plugin_setting('list_handlers','filter_and_sort');
    $list_handlers = array_filter(explode(',', $list_handlers));

    if (elgg_is_active_plugin('pagehandler_hijack'))
    {
        // get pagehandler hijacks if they exist
        $mods = filter_and_sort_map_hijacks();
    }

    // add route hooks for each list type (priority is lower than 500 to ensure other hooks run afterwards - needed for group_tools)
    foreach ($list_handlers as $list_handler)
    {
        if ($mods)
        {
            if (in_array($list_handler, $mods))
            $list_handler = $mods[$list_handler];
        }

        // river page is handled in the resource view
        if ($list_handler != 'activity')
            elgg_register_plugin_hook_handler("route", $list_handler, "filter_and_sort_route_hook", 100);
    }

    // add hooks for members plugin
    if ((elgg_is_active_plugin('members'))&&(in_array('members',$list_handlers)))
    {
        //members tab hook over-rides
        elgg_unregister_plugin_hook_handler('members:list', 'popular', "members_list_popular");
        elgg_register_plugin_hook_handler('members:list', 'popular', "filter_and_sort_members_list_popular");            elgg_unregister_plugin_hook_handler('members:list', 'newest', "members_list_newest");
        elgg_register_plugin_hook_handler('members:list', 'newest', "filter_and_sort_members_list_newest");
        elgg_unregister_plugin_hook_handler('members:list', 'online', "members_list_online");
        elgg_register_plugin_hook_handler('members:list', 'online', "filter_and_sort_members_list_online");
    }

    // add support for groups
    if (elgg_is_active_plugin('groups'))
    {
        elgg_register_plugin_hook_handler('prepare','menu:page','filter_and_sort_groups_page_menu_config');
    }

    // add support for tag_tools
    if (elgg_is_active_plugin('tag_tools'))
    {
        elgg_unregister_plugin_hook_handler('route', 'activity', 'tag_tools_route_activity_hook');
        elgg_register_plugin_hook_handler('route', 'activity', 'filter_and_sort_tag_tools_route_activity_hook');

        elgg_unregister_plugin_hook_handler('register', 'menu:filter', 'tag_tools_activity_filter_menu_hook_handler');
    }

    elgg_extend_view('elgg.css', 'filter_and_sort/css');
    elgg_extend_view('admin.css', 'filter_and_sort/admin');
}

elgg_register_event_handler('init', 'system', 'filter_and_sort_init');
