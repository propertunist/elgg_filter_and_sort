<?php
/**
 * filter_and_sort - elgg plugin - groups index page
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 * @uses $params - array of variables for use when rendering the page structure
 * @uses $options - array of variables for use when executing the list query in the database
 * @uses $filter_params - array of variables for use when building the sort/filter controls
 */

 // remove breadcrumb from base page
 elgg_pop_breadcrumb();

 // only register title button if allowed
 if ((elgg_get_plugin_setting('limited_groups', 'groups') != 'yes') || elgg_is_admin_logged_in()) {
 	elgg_register_title_button();
 }

 $selected_tab = get_input('filter');
 $group_filters = array('all','discussion','featured','open','closed','yours','suggested', 'popular', 'ordered');
 if (!in_array($selected_tab,$group_filters))
  $selected_tab = 'all';
 // array for defining sorting/filtering options and associated UI element states
 $filter_params = array();
 $filter_params = filter_and_sort_get_input_data($filter_params);
 if ($selected_tab == 'discussion')
 {
  $filter_params['context'] = 'discussion-groups';
  elgg_push_context('discussion-groups');
  $subtype = 'discussion';
 }
 else
 {
  $filter_params['context'] = 'groups';
  $subtype = 'groups';
 }

$filter_params['filter_context'] = $selected_tab;
 //elgg_dump(print_r($sort_filter_options,true));

 // default group options
 $options = [
 	'type' => 'group',
 	'full_view' => false,
 ];

 $dbprefix = elgg_get_config('dbprefix');

 switch ($selected_tab) {
  case 'popular':
  {
    $options['relationship'] = 'member';
    $options['inverse_relationship'] = false;
    $getter = 'elgg_get_entities_from_relationship_count';
    break;
  }
  case 'discussion':
  {
      $options['type'] = 'object';
      $options['subtype'] = 'discussion';
    //  $options['preload_owners'] = true;
    //  $options['preload_containers'] = true;
    break;
  }
 	case 'ordered':
  {
 		$order_id = elgg_get_metastring_id('order');

 		$options['limit'] = false;
 		$options['pagination'] = false;
 		$options['selects'] = [
 			"IFNULL((SELECT order_ms.string as order_val
 			FROM {$dbprefix}metadata mo
 			JOIN {$dbprefix}metastrings order_ms ON mo.value_id = order_ms.id
 			WHERE e.guid = mo.entity_guid
 			AND mo.name_id = {$order_id}), 99999) AS order_val",
 		];
    $getter = 'elgg_get_entities_from_metadata';
/*
 		if (elgg_is_admin_logged_in()) {
 			elgg_require_js('group_tools/ordered_list');
 			$options['list_class'] = 'group-tools-list-ordered';
 		}
*/
 		break;
  }
 	case 'yours':
  {
 		elgg_gatekeeper();
 		$options['relationship'] = 'member';
 		$options['relationship_guid'] = elgg_get_logged_in_user_guid();
 		$options['inverse_relationship'] = false;

 		break;
  }
 	case 'featured':
  {
 		$options['metadata_name'] = 'featured_group';
    $options['metadata_value'] = 'yes';
    $getter = 'elgg_get_entities_from_metadata';
 		break;
  }
 	case 'open':
  {
    $getter = 'elgg_get_entities_from_metadata';
 		$options['metadata_name_value_pairs'] = [
 			'name' => 'membership',
 			'value' => ACCESS_PUBLIC,
 		];

 		break;
  }
 	case 'closed':
  {
    $getter = 'elgg_get_entities_from_metadata';
 		$options['metadata_name_value_pairs'] = [
 			'name' => 'membership',
 			'value' => ACCESS_PUBLIC,
 			'operand' => '<>',
 		];

 		break;
  }
  case 'suggested':
  {
    $groups = group_tools_get_suggested_groups(elgg_get_logged_in_user_entity(), 9);
    if (!empty($groups)) {
    	// list suggested groups
    	$content = elgg_view('output/text', [
    		'value' => elgg_echo('group_tools:suggested_groups:info'),
    	]);
    	$content .= elgg_view('group_tools/suggested', [
    		'groups' => $groups,
    	]);
    } else {
    	$content = elgg_echo('group_tools:suggested_groups:none');
    }
    break;
  }
  default:
  {
      $options['distinct'] = false;
      break;
  }
 }

$filter_params['selected'] = $selected_tab;

 $sort_filter_options = elgg_get_sort_filter_options(array('options' => $options,
                                                           'getter' => $getter,
                                                           'filter_params' => $filter_params,
                                                           'page_type' => $subtype));

 $options = array_merge($options, $sort_filter_options['options']);

switch($selected_tab){
  case 'ordered':
  {
    // add in order clause since it will have been overwritten in elgg_get_sort_filter_options
    $options['order_by'] = 'CAST(order_val AS SIGNED) ASC, e.time_created DESC';
    unset ($options['joins']);
    break;
  }
  case 'discussion':
    break;
  default:
  {
    // only discussions need to support times
    unset ($options['created_time_lower']);
    unset ($options['created_time_upper']);
    break;
  }
}

// process filter options and cookie data
 $filter_params = $sort_filter_options['filter_params'];
 $filter_params['cookie_loaded'] = $sort_filter_options['cookie_loaded'];
 $filter_params['toggle'] = elgg_filter_and_sort_register_toggle($filter_params['list_type']);

//elgg_dump($options);

 // content variable may be set by suggested groups code (or other code)
 if (!$content)
 {
    $content = elgg_list_entities($options,$sort_filter_options['getter']);
  //  $count = filter_and_sort_count_list($sort_filter_options['getter'],
    //                                    $options);
 }
/*
 if ($count == 0)
 {
   $content = '<ul class="elgg-list elgg-sync elgg-list-entity elgg-no-items"><li>' . $sort_filter_options['no-items'] . '</li></ul>';
 }
*/
 // store the current list filter options to a cookie
 filter_and_sort_set_cookie(array('context' => elgg_get_context(),
                         'list_type' => $filter_params['list_type'],
                         'limit' => $filter_params['limit'],
                         'contain' => $filter_params['contain'],
                         'sort' => $filter_params['sort'],
                         'timing-from' => $filter_params['timing-from'],
 											   'timing-to' => $filter_params['timing-to']));

 $filter = elgg_view('page/layouts/content/sort_filter',array('filter_params' =>  $filter_params));
 $title = elgg_echo("groups:title:{$filter_params['filter_context']}");

 $sidebar = elgg_view('groups/sidebar/find');
 $sidebar .= elgg_view('groups/sidebar/featured');

 // build page
 $body = elgg_view_layout('content', [
 	'content' => $content,
 	'sidebar' => $sidebar,
 	'filter' => $filter,
  'title' => $title
 ]);

 // draw page
 echo elgg_view_page($title, $body);
 if ($selected_tab == 'discussion')
 {
   elgg_pop_context();
 }
