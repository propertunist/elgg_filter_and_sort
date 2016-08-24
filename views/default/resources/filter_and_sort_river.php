<?php
/************
 * filter_and_sort - elgg plugin
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 * Main activity stream list page
 *
 * @uses $params - array of variables for use when rendering the page structure
 * @uses $options - array of variables for use when executing the list query in the database
 * @uses $filter_params - array of variables for use when building the sort/filter controls
 *******************/

$options = array(
	'distinct' => false
);
$db_prefix = elgg_get_config('dbprefix');
$page_type = preg_replace('[\W]', '', elgg_extract('page_type', $vars, 'all'));

switch ($page_type) {
	case 'mine':
		$title = elgg_echo('river:mine');
		$page_filter = 'mine';
		$options['subject_guid'] = elgg_get_logged_in_user_guid();
		break;
	case 'owner':
		$subject_username = elgg_extract('subject_username', $vars, '');
		$subject = get_user_by_username($subject_username);
		if (!$subject) {
			register_error(elgg_echo('river:subject:invalid_subject'));
			forward('');
		}
		elgg_set_page_owner_guid($subject->guid);
		$title = elgg_echo('river:owner', array(htmlspecialchars($subject->name, ENT_QUOTES, 'UTF-8', false)));
		$page_filter = 'owner';
		$options['subject_guid'] = $subject->guid;
		break;
	case 'friends':
		$title = elgg_echo('river:friends');
		$page_filter = 'friends';
		$options['relationship_guid'] = elgg_get_logged_in_user_guid();
		$options['relationship'] = 'friend';
		break;
  // support for tag_tools plugin
	case 'tags':
	{
		elgg_gatekeeper();
		elgg_set_page_owner_guid(elgg_get_logged_in_user_guid());
		$tags = tag_tools_get_user_following_tags();
		if (empty($tags)) {
			register_error(elgg_echo('tag_tools:notifications:empty'));
			forward('activity');
		}

		$name_ids = [];
		foreach ($tags as $tag) {
			$name_ids[] = elgg_get_metastring_id($tag);
		}

		$title = elgg_echo('tag_tools:activity:tags');
		$tags_id = elgg_get_metastring_id('tags');
		$page_filter = 'tags';
		$options['joins'] = ["JOIN {$dbprefix}metadata md ON rv.object_guid = md.entity_guid"];
		$options['wheres'] = ["(md.name_id = {$tags_id}) AND md.value_id IN (" . implode(',', $name_ids) . ")"];
		break;
	}
	default:
		$title = elgg_echo('river:all');
		$page_filter = 'all';
		break;
}

$sort_filter_options = elgg_get_sort_filter_options(array('options' => $options,
                                                          'page_type' => 'activity',
																													'context' => 'activity'
																										));


$options = $sort_filter_options['options'];
$options['joins'][] = "JOIN {$db_prefix}entities e ON e.guid = rv.object_guid";

if ($options['container_guid'])
{
  $options['wheres'][] = "e.container_guid IN({$options['container_guid']})";
}
elseif ($options['container_guids'])
{
	$counter = 1;
	foreach ($options['container_guids'] as $container_guid)
	{
		$container_list .= $container_guid;
		if ($counter < count($options['container_guids']))
			$container_list .= ',';
		$counter++;
	}
  $options['wheres'][] = "e.container_guid IN({$container_list})";
}

// GET RIVER ITEMS

$activity = elgg_list_river($options);

//$options['count'] = TRUE;
//$count = elgg_get_river($options);
//if ($count == 0) {
		//$activity = '<ul class="elgg-list elgg-sync elgg-list-entity elgg-no-items"><li>' . //elgg_echo('river:none') . '</li></ul>';
//}

$filter_params = $sort_filter_options['filter_params'];
$filter_params['cookie_loaded'] = $sort_filter_options['cookie_loaded'];
$filter_params['context'] = 'activity';
$filter_params['filter_context'] = $page_type;
if (elgg_is_logged_in())
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

$sidebar = elgg_view('core/river/sidebar');

$params = array(
	'title' => $title,
	'content' => $activity,
	'sidebar' => $sidebar,
	'filter_context' => $page_filter,
	'filter' => $sort_filter,
	'class' => 'elgg-river-layout',
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
