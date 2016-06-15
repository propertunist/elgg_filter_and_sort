<?php
/**
 * Elgg filter_and_sort plugin: members index page
 *
 * @Author - ura soul
 * @website - https://www.ureka.org
 * @uses $params - array of variables for use when rendering the page structure
 * @uses $options - array of variables for use when executing the list query in the database
 * @uses $filter_params - array of variables for use when building the sort/filter controls
 */

//$tabs = elgg_trigger_plugin_hook('members:config', 'tabs', null, array());
/*
foreach ($tabs as $type => $values) {
        $tabs[$type]['selected'] = ($page[0] == $type);
        $tabs[$type]['text'] = $tabs[$type]['title'];
        $tabs[$type]['href'] = $tabs[$type]['url'];
}
*/
// options array to pass to getter functions
$options = array('type' => 'user', 'full_view' => false);

// array for defining sorting/filtering options and associated UI element states
$filter_params = array();

$filter_params = filter_and_sort_get_input_data($filter_params);

$filter_params['context'] = 'members';
$filter_params['filter_context'] = $page[0];
$sort_filter_options = elgg_get_sort_filter_options(array('options' => $options,
                                                          'filter_params' => $filter_params,
                                                          'page_type' => 'members',
                                                          'context' => 'members'));

if($sort_filter_options['options']['list_type']=='gallery')
    $sort_filter_options['options']['gallery_class'] = 'users-gallery';

$content = elgg_trigger_plugin_hook('members:list', $page[0], null, array('options' => $sort_filter_options['options'], 'getter' => $sort_filter_options['getter']));

if ($content === null) {
	forward('', '404');
}
if (elgg_is_xhr())
{
    echo $content['list'];
}
else
{
    $params = array(
            'sidebar' => elgg_view('members/sidebar'),
            'title' => $title,
    );

    //if ($content['count'] == 0) {
//        if ($sort_filter_options['no-items'])
//            $no_items = $sort_filter_options['no-items'];

//        $params['content'] = '<ul class="elgg-list elgg-sync elgg-list-entity elgg-no-items"><li>' . $no_items . '</li></ul>';
//    } else {
        $params['content'] = $content['list'];
//    }

    $filter_params = $sort_filter_options['filter_params'];
    $filter_params['cookie_loaded'] = $sort_filter_options['cookie_loaded'];
    $filter_params['toggle'] = elgg_filter_and_sort_register_toggle($filter_params['list_type']);
    //$filter_params['context'] = 'members';
//    $filter_params['filter_override'] = $tabs;
    $filter_params['filter_context'] = $page[0];
    // store the current list filter options to a cookie
    filter_and_sort_set_cookie(array('context' => elgg_get_context(),
                            'list_type' => $filter_params['list_type'],
                            'limit' => $filter_params['limit'],
                            'sort' => $filter_params['sort'],
                            'show_icon' => $filter_params['show_icon']));

    $params['filter'] = elgg_view('page/layouts/content/sort_filter',array('filter_params' => $filter_params));

    $title = elgg_echo("members:title:{$page[0]}");
    $params['title'] = $title;

    $body = elgg_view_layout('content', $params);

    echo elgg_view_page($title, $body);
}
