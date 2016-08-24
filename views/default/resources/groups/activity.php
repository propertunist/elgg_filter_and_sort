<?php
elgg_push_context('activity');

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'group');

elgg_set_page_owner_guid($guid);

elgg_group_gatekeeper();

$group = get_entity($guid);

$title = elgg_echo('groups:activity');

elgg_push_breadcrumb($group->name, $group->getURL());
elgg_push_breadcrumb($title);

$db_prefix = elgg_get_config('dbprefix');

$options = array();
$options['joins'] = array(
	"JOIN {$db_prefix}entities e ON e.guid = rv.object_guid",
	"LEFT JOIN {$db_prefix}entities e2 ON e2.guid = rv.target_guid",
);
$options['wheres'] = array(
	"(e.container_guid = $group->guid OR e2.container_guid = $group->guid)",
);


$sort_filter_options = elgg_get_sort_filter_options(array('options' => $options,
                                                          'page_type' => 'group',
																													'context' => 'activity'
																										));

$options = $sort_filter_options['options'];

//$options ['no_results'] = elgg_echo('groups:activity:none');
//elgg_dump($sort_filter_options['options']);
$content = elgg_list_river($sort_filter_options['options']);

$filter_params = $sort_filter_options['filter_params'];
$filter_params['cookie_loaded'] = $sort_filter_options['cookie_loaded'];
$filter_params['context'] = 'activity';
$filter_params['filter_context'] = 'group';
$filter_params['objtype_selector'] = elgg_view('core/river/filter', array('selector' => $filter_params['objtype']));

// store the current list filter options to a cookie
filter_and_sort_set_cookie(array('context' => elgg_get_context(),
												'list_type' => $filter_params['list_type'],
												'limit' => $filter_params['limit'],
												'contain' => $filter_params['contain'],
												'timing-from' => $filter_params['timing-from'],
												'timing-to' => $filter_params['timing-to'],
								  			'type' => $filter_params['type'],
												'subtype' => $filter_params['subtype']));

$sort_filter = elgg_view('page/layouts/content/sort_filter',array('filter_params' => $filter_params));

$params = array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
	'filter' => $sort_filter,
);
/*
$params = array(
	'title' => $title,
	'content' => $activity,
	'sidebar' => $sidebar,
	'filter_context' => $page_filter,
	'filter' => $sort_filter,
	'class' => 'elgg-river-layout',
);
*/
elgg_pop_context();

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
