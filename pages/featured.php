<?php
/************
 * filter_and_sort - elgg plugin
 * show all featured items for context
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 *******************/

 // access check for closed groups
group_gatekeeper();
$context = elgg_get_context();
$object_type = filter_and_sort_get_object_type_from_context($context);
if ($object_type['subtype']== 'image')
    elgg_pop_context();

// array for defining sorting/filtering options and associated UI element states
$filter_params = array();
$filter_params = filter_and_sort_get_input_data($filter_params);
$filter_params['filter_context'] = 'featured';

$options = array(
    'type' => 'object',
    'subtype' => $object_type['subtype'],
    'full_view' => false,
    'view_toggle_type' => false,
    'list_class' => 'elgg-list-entity',
);

$options['metadata_name_value_pairs'] = array(
		array('name' => 'status', 'value' => 'published'),
		array('name' => 'featured', 'value' => '0', 'operand' => " > "),
);

// do not support like, sort and comment sort modes on the featured page?

if (($filter_params['sort'] == 'likes_a')||($filter_params['sort'] == 'likes_d')||($filter_params['sort'] == 'views_a')||($filter_params['sort'] == 'views_d')||($filter_params['sort'] == 'comments_a')||($filter_params['sort'] == 'comments_d'))
{
        $filter_params['sort'] = 'created_d';
}

// set subtype specific options
if ($subtype == 'blog')
{
      elgg_register_title_button();
      $params['title'] = elgg_echo("blog_tools:menu:filter:featured");
      $no_items = elgg_echo('blog:none');
      $params['sidebar'] = elgg_view('blog/sidebar', array('page' => null));
}

$sort_filter_params = elgg_get_sort_filter_options(array('options' => $options,
                                                          'filter_params' => $filter_params,
                                                          'page_type' => $subtype));

$list = elgg_list_entities_from_metadata($sort_filter_params['options']);

if (elgg_is_xhr())
{
        echo $list;
}
else
{
    //$sort_filter_params['options']['count'] = TRUE;
    //$count = elgg_get_entities_from_metadata($sort_filter_params['options']);
    //if ($count == 0) {
//         if ($sort_filter_params['no-items'])
//            $no_items = $sort_filter_params['no-items'];

//        $params['content'] = '<ul class="elgg-list elgg-sync elgg-list-entity elgg-no-items"><li>' . $no_items . '</li></ul>';
//    } else {
        $params['content'] = $list;
//    }
    $filter_params = $sort_filter_params['filter_params'];
    $filter_params['cookie_loaded'] = $sort_filter_params['cookie_loaded'];
    if (($object_type['subtype'] != 'image')&& ($object_type['subtype'] != 'album'))
    {
        $filter_params['toggle'] = elgg_filter_and_sort_register_toggle($filter_params['list_type']);
    }
    else
    {
        elgg_require_js('tidypics/tidypics');
        elgg_require_js('elgg/lightbox');
        elgg_load_css('lightbox');
        if (elgg_get_plugin_setting('slideshow', 'tidypics')) {
            elgg_load_js('tidypics:slideshow');
        }
    }

    // store the current list filter options to a cookie
    filter_and_sort_set_cookie(array('context' => elgg_get_context(),
                            'list_type' => $filter_params['list_type'],
                            'limit' => $filter_params['limit'],
                            'contain' => $filter_params['contain'],
                            'sort' => $filter_params['sort'],
                            'timing-from' => $filter_params['timing-from'],
    												'timing-to' => $filter_params['timing-to'],
                            'status' => $filter_params['status']));

    $params['filter'] = elgg_view('page/layouts/content/sort_filter',array('filter_params' => $filter_params));
    $body = elgg_view_layout('content', $params);

    echo elgg_view_page($title, $body);
}
