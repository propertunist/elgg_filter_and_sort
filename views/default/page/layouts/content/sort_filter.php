<?php

/**
 * filter_and_sort - elgg plugin
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 * Main content filter + sort order option
 *
 * Select between relevant filter tabs for current context - e.g. user, friends, and all content
 * or other tabs for groups
 *
 * @uses $vars['filter_context']  Filter context: all, friends, mine
 * @uses $vars['filter_override'] HTML for overriding the default filter (override)
 * @uses $vars['context']         Page context (override)
 * @uses $vars['filter_params']     input variables for filtering options
 * @uses $vars['cookie_loaded']   flag to show whether any of the filter data has come from a cookie or not
 * @uses $vars['objtype_selector']HTML input selector for river to choose objects and subtypes by which to filter the river activity
 */

elgg_require_js('filter_and_sort/sort_ui');
$filter_params = $vars['filter_params'];
// bypass this file if a filter override is present
if ((isset($filter_params['filter_override']))&&(!is_array($filter_params['filter_override'])))
{
    echo $filter_params['filter_override'];
    return true;
}

$context = elgg_extract('context', $filter_params, elgg_get_context());
$filter_context = elgg_extract('filter_context', $filter_params, 'all');
$current_user = elgg_get_logged_in_user_entity();
$used_param = false;

// if a specific group is being viewed then disable the container option
if ($filter_context=='group')
{
    unset($filter_params['content']);
}

// no sorting of popular lists
if($filter_context == 'popular')
  unset($filter_params['sort']);

// disable status parameter if not needed
if (($filter_context =='friends')
    &&(!elgg_is_admin_logged_in())
    &&(array_key_exists('status', $filter_params)))
{
    unset($filter_params['status']);
}

$filter_param_names = array('sort',
                            'timing-from',
                            'timing-to',
                            'contain',
                            'status',
                            'limit',
                            'list_type',
                            'show_icon',
                            'filter',
                            'objtype');

// if there are sort/filter parameters available, build the parameter string for urls
foreach ($filter_param_names as $filter_param_name)
{
  if ($filter_params[$filter_param_name])
  {
    if (!$used_param)
      $href = '?';
    else
      $href .= '&';

    $href .= $filter_param_name . '=' . $filter_params[$filter_param_name];
    $used_param = true;
  }
}

// output the filter UI
echo '<div id="elgg-sort-options" data-cookie="' . $filter_params['cookie_loaded'] . '">';

// build horizontal filter buttons for logged in, non group pages
if ((elgg_is_logged_in()) &&($filter_context != 'group'))
{
    if (is_array($filter_params['filter_override']))
    {
        $tabs = $filter_params['filter_override'];
        foreach ($tabs as $name => $tab) {
            $tab['name'] = $name;
            elgg_register_menu_item('filter', $tab);
        }
    }
    // handle tabs for the group index page
    elseif (($context == 'groups')||($context == 'discussion-groups'))
    {
        $tabs = [
        	'all' => [
        		'text' => elgg_echo('groups:all'),
        		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=all':  'groups/all' . '?filter=all',
        		'priority' => 200,
        	],
        	'yours' => [
        		'text' => elgg_echo('groups:yours'),
        		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=yours':  'groups/all' . '?filter=yours',
        		'priority' => 250,
        	],
        	'popular' => [
        		'text' => elgg_echo('sort:popular'),
        		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=popular':  'groups/all' . '?filter=popular',
        		'priority' => 300,
        	],
        	'discussion' => [
        		'text' => elgg_echo('discussion:latest'),
        		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=discussion':  'groups/all' . '?filter=discussion',
        		'priority' => 400,
        	]
        ];
        if (elgg_is_active_plugin('group_tools'))
        {
          $tabs['open'] = array(
      		'text' => elgg_echo('group_tools:groups:sorting:open'),
      		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=open':  'groups/all' . '?filter=open',
      		'priority' => 500,
      	  );
          $tabs['closed'] = array(
      		'text' => elgg_echo('group_tools:groups:sorting:closed'),
      		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=closed':  'groups/all' . '?filter=closed',
      		'priority' => 600,
      	  );
      	  $tabs['ordered'] = array(
      		'text' => elgg_echo('group_tools:groups:sorting:ordered'),
      		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=ordered':  'groups/all' . '?filter=ordered',
      		'priority' => 800,
          );
      	  $tabs['featured'] = array(
      		'text' => elgg_echo('status:featured'),
      		'href' =>(isset($href)) ? 'groups/all' . $href . '&filter=featured':  'groups/all' . '?filter=featured',
      		'priority' => 850,
          );
      	  $tabs['suggested'] = array(
      		'text' => elgg_echo('group_tools:groups:sorting:suggested'),
      		'href' => (isset($href)) ? 'groups/all' . $href . '&filter=suggested':  'groups/all' . '?filter=suggested',
      		'priority' => 900,
      	  );
        }

        foreach ($tabs as $name => $tab) {
        	$show_tab = false;
        	$show_tab_setting = elgg_get_plugin_setting("group_listing_{$name}_available", 'group_tools');
        	if (in_array($name, ['ordered', 'featured'])) {
        		if ($show_tab_setting == '1') {
        			$show_tab = true;
        		}
        	} elseif ($show_tab_setting !== '0') {
        		$show_tab = true;
        	}
        	if ($show_tab && in_array($name, ['yours', 'suggested']) && !elgg_is_logged_in()) {
        		continue;
        	}

        	if (!$show_tab) {
        		continue;
        	}
        	$tab['name'] = $name;
        	if ($filter_params['selected'] === $name) {
        		$tab['selected'] = true;
        	}

        	elgg_register_menu_item('filter', $tab);
        }
      }
      // handle tabs for members index page
      elseif ($context == 'members')
      {
        $tabs = [
          'all' => [
            'text' => elgg_echo('all'),
            'href' => (isset($href)) ? 'members' . $href :  'members',
            'selected' => ($filter_context == 'newest'),
            'priority' => 200,
          ],
          'popular' => [
            'text' => elgg_echo('sort:popular'),
            'href' => (isset($href)) ? 'members/popular' . $href :  'members/popular',
            'selected' => ($filter_context == 'popular'),
            'priority' => 250,
          ],
          'online' => [
            'text' => elgg_echo('members:label:online'),
            'href' => (isset($href)) ? 'members/online' . $href :  'members/online',
            'selected' => ($filter_context == 'online'),
            'priority' => 300,
          ],
        ];
        foreach ($tabs as $name => $tab) {
            $tab['name'] = $name;
            elgg_register_menu_item('filter', $tab);
        }
      }
      else // handle tabs for object entities with subtypes
      {
        $username = $current_user->username;
        // generate a list of default tabs
        $tabs = array(
            'all' => array(
                'text' => elgg_echo('all'),
                'href' => (isset($filter_params['all_link'])) ? $filter_params['all_link'] . $href : "$context/all" . $href,
                'selected' => ($filter_context == 'all'),
                'priority' => 200,
            ),
            'mine' => array(
                'text' => elgg_echo('mine'),
                'href' => (isset($filter_params['mine_link'])) ? $filter_params['mine_link'] . $href : "$context/owner/$username"  . $href,
                'selected' => (($filter_context == 'mine')||($filter_context == 'owner')),
                'priority' => 300,
            ),
            'friend' => array(
                'text' => elgg_echo('friends'),
                'href' => (isset($filter_params['friend_link'])) ? $filter_params['friend_link']   . $href: "$context/friends/$username" . $href,
                'selected' => ($filter_context == 'friends'),
                'priority' => 400,
            ),
        );

        if ((elgg_is_active_plugin('tag_tools'))&&($context == 'activity'))
            $tabs['tags'] = array(
                'text' => elgg_echo('tags'),
                'href' => "activity/tags" . $href,
                'selected' => ($filter_context == 'tags'),
                'priority' => 9999,
                "name" => "tags",
            );

        if ((elgg_is_active_plugin('blog_tools'))&&($context == 'blog'))
            $tabs['featured'] = array(
                'text' => elgg_echo('status:featured'),
                'href' => "blog/featured" . $href,
                'selected' => ($filter_context == 'featured'),
                'priority' => 600,
                "name" => "featured",
            );

        foreach ($tabs as $name => $tab) {
            $tab['name'] = $name;
            elgg_register_menu_item('filter', $tab);
        }
      }
    $menu_output = elgg_view_menu('filter', array('sort_by' => 'priority', 'class' => 'elgg-menu-hz'));
}
else
{
    $menu_output = '<ul class="elgg-menu elgg-menu-filter elgg-menu-hz elgg-menu-filter-default elgg-menu-filter-empty">'.'</ul>';
}

if (!$filter_params['no_sort'])
{
    // create list of sort orders
    $options = array(
        'created_d' => elgg_echo('sort:created_d'),
        'created_a' => elgg_echo('sort:created_a'),
    );

    if ($filter_context != 'featured')
    {
        if(($context != 'members')&&($context != 'groups'))
        {
              // check that a likes plugin is active
              if ((elgg_is_active_plugin('likes'))||(elgg_is_active_plugin('etklikes')))
              {
                    $options['likes_d'] = elgg_echo('sort:likes_d');
                    $options['likes_a'] = elgg_echo('sort:likes_a');
              }

              if (($context != 'discussion')&&($context != 'discussion-groups'))
              {
                      $options['comments_a'] = elgg_echo('sort:comments_a');
                      $options['comments_d'] = elgg_echo('sort:comments_d');
              }
              else
              {
                      $options['comments_a'] = elgg_echo('sort:replies_a');
                      $options['comments_d'] = elgg_echo('sort:replies_d');
              }
              if ((elgg_is_active_plugin('entity_view_counter'))||(elgg_is_active_plugin('views_counter')))
              {
                  $options['views_a'] = elgg_echo('sort:views_a');
                  $options['views_d'] = elgg_echo('sort:views_d');
              }
        }
    }

    // alphabetical sort mode
    $options ['alpha_d'] = elgg_echo('sort:alpha_d');
    $options ['alpha_a'] = elgg_echo('sort:alpha_a');

    // update time is not used for members and groups lists
    if(($context != 'members')&&($context != 'groups'))
    {
            $options ['changed_d'] = elgg_echo('sort:changed_d');
            $options ['changed_a'] = elgg_echo('sort:changed_a');
    }

    $params = array(
        'id' => 'sort',
        'options_values' => $options,
    );

    if ($filter_params['sort']) {
        $params['value'] = $filter_params['sort'];
    }

    $filters = array();
    // define filters based on context
    if (($context != 'members')&&($context != 'groups'))
    {
        $filters[] = 'timing-from';
        $filters[] = 'timing-to';
        $filters[] = 'status';
        $filters[] = 'contain';
    }

    // activity page filters by obj/subtype and does not sort
    if ($filter_context != 'online')
    {
    if ($context != 'activity')
      $filters[] = 'sort';
    else
      $filters[] = 'objtype';
    }

    $active_filters = array();
    $timing_done = false;
    // build list of active filter buttons
    foreach ($filters as $filter)
    {
        if ($filter_params[$filter]) // if the filter has been passed in here
        {
          // only show filters under correct conditions for each one
            if (
            // do not show filter for default container setting
            (($filter_params[$filter] != 'all')&&($filter == 'contain'))
            // do not show filter for default timing
            ||(($filter_params[$filter] != 'all')&&($filter == 'timing-from'))
            ||(($filter_params[$filter] != 'all')&&($filter == 'timing-to'))
            // do not show filter for default sort order
            ||(($filter_params[$filter] != 'created_d')&&($filter == 'sort'))
            // do not show filter for default published status
            ||(($filter_params[$filter] != 'all')&&($filter == 'status'))
            // do not show filter for default obj type selection
            ||(($filter_params[$filter] != 'type=all')&&($filter == 'objtype'))
            )
            {
              // if no timing filters have been processed yet and the current filter is a timing filter, then output the filter. plus, if the filter is not a timing filter, then output the filter.
              if (
              (($timing_done == false)&&(($filter == 'timing-to')||($filter =='timing-from')))
              ||(($filter != 'timing-to')&&($filter != 'timing-from'))
              )
                $active_filters[$filter] = elgg_view('output/sort_filter',array('name'=> $filter, 'value'=> $filter_params[$filter]));

              // only show the timing button once, regardless of whether 1 or 2 timing fields are set
              if ($filter == 'timing-from')
                $timing_done = true;
            }
        }
    }

    if (count($active_filters)>0)
    {
        $reset_all_filters = '<div id="elgg-reset-sort-filters">' . elgg_echo('filter_and_sort:clear_filters') . elgg_view_icon('delete-alt') . '</div>';
        $active_filter_list = '<span class="elgg-active-filter-label">' . elgg_echo('sort:active_filters') . '</span>';
        foreach ($active_filters as $active_filter)
        {
            $active_filter_list .= $active_filter;
        }
    }

    $more_options_link = '<span id="elgg-filter-options-link">';
    $more_options_link .= elgg_echo('filter_and_sort:show');
    $more_options_link .= '</span>';

    if ($filter_params['toggle'])
    {
        $toggle_button = '<div id="elgg-toggle-link">';
        $toggle_button .= $filter_params['toggle'];
        $toggle_button .= '</div>';
    }

    $active_filters = '<div id="elgg-sort-filtering">' . $active_filter_list . $reset_all_filters . '</div>' . $toggle_button . $more_options_link . '<div class="clearfloat"></div>';

    // ICON ONLY SELECTOR

    if ($context == 'members')
    {
        $icon_check = $filter_params['show_icon'] === 'true'? true: false;
        $icon_params = array('id' => 'icon', 'checked' => $icon_check);
        $filter_options = '<div class="elgg-filter-option-odd"><label title="' . elgg_echo('sort:title:label:icon') . '"><small>' . elgg_echo('sort:title:label:icon') . '</label></small> ' . elgg_view('input/checkbox', $icon_params) . '</div>';
    }

    // SORT SELECTOR

    if (($filter_context != 'online')
      &&($filter_context != 'popular')
      &&($filter_context != 'ordered')
      &&($context != 'activity'))
        $filter_options .= '<div class="elgg-filter-option-even"><label title="' . elgg_echo('sort:title:label:sort') . '"><small>' . elgg_echo('sort:label') . '</label></small> ' . elgg_view('input/dropdown', $params) . '</div>';

    // OBJECT TYPE SELECTOR

    if ($filter_params['context'] == 'activity')
    {
      if ($filter_params['objtype_selector'])
      {
        $filter_options .= '<div class="elgg-filter-option-odd">';
        $filter_options .= $filter_params['objtype_selector'];
        $filter_options .= '</div>';
      }
    }

    // CONTAINER SELECTOR
    // conditionally display the container selector only if user is logged into an appropriate context and the river selector is not set to user or group
    if ((elgg_is_logged_in())&&($filter_context!='group')&&($context != 'members')&&($context != 'groups')&&($filter_params['objtype']!= 'type=user')&&($filter_params['objtype']!= 'type=group'))
    {
        $groups = casort($current_user->getGroups(array('limit'=>0),0), "name");
        if ($groups)
        {
            $group_options = array(
                'all' => elgg_echo('sort:all'),
                'groups' => elgg_echo('sort:groups'));
            if (($filter_context != 'friends')&&($context != 'discussion')&&($context != 'discussion-groups')&&($context != 'activity'))
               $group_options['profile'] = elgg_echo('profile') . ': ' . $current_user->name;

            foreach ($groups as $group)
            {
                $group_options[$group->guid] = elgg_echo('group') . ': ' . elgg_get_excerpt($group->name, 25);
            }
            if ($filter_params) {
                $params['value'] = $filter_params['contain'];
            }
            $filter_options .= '<div class="elgg-filter-option-odd">';
            $filter_options .= '<label title="' . elgg_echo('sort:title:label:container') . '"><small>' . elgg_echo('sort:filter:container') . '</small></label>';
            $filter_options .= elgg_view('input/dropdown', array(
            'value' => $params['value'],
            'options_values' => $group_options,
            'id' => 'contain'
            ));
            $filter_options .= '</div>';
        }
    }

    if (($context != 'members')&&($context != 'groups'))
    {
        //define default times/dates for date range filtering
        $default_from_date = strtotime('-10 year');
        $default_to_date = time();



        // if the ui points to 'all' for the timing-from value then use the default from date
        if ($filter_params['timing-from'] == 'all')
        {
          $filter_params['timing-from'] = $default_from_date;
        }
         $params['value'] = $filter_params['timing-from'];
         $filter_options .= '<div class="clearfloat"></div>';
         $filter_options .= '<div class="elgg-filter-option-odd">';

         $filter_options .= '<label title="' . elgg_echo('sort:title:label:timing-from') . '"><small>' . elgg_echo('sort:filter:date_period-from') . '</small></label>';

         $filter_options .= elgg_view('input/date', array(
                 'value' => $params['value'],
                 'name' => 'timing-from',
                 'timestamp' => true,
                 'data-default_from' => $default_from_date,
               ));

         $filter_options .= '</div>';


         if ($filter_params['timing-to'] == 'all')
         {
           $params['value'] = elgg_echo('now');
           $filter_params['timing-to'] = $default_to_date;
         }
         else {
           $params['value'] = $filter_params['timing-to'];
         }

         $filter_options .= '<div class="elgg-filter-option-odd">';

         $filter_options .= '<label title="' . elgg_echo('sort:title:label:timing-to') . '"><small>' . elgg_echo('sort:filter:date_period-to') . '</small></label>';

         $filter_options .= elgg_view('input/date', array(
                   'value' => $params['value'],
                   'name' => 'timing-to',
                   'timestamp' => true,
                   'data-default_to' => $default_to_date,
                 ));
           $filter_options .= '</div>';
    }

    // status: if looking at blogs and user is logged in
    if (($context == 'blog')&&(elgg_is_logged_in())&&($filter_context!='featured'))
    {
        if ($filter_context!='friends')
        {
        $status_options =  array(
                'all' => elgg_echo('sort:status:all'),
                'draft' => elgg_echo('sort:status:draft_only'),
                'published' => elgg_echo('sort:status:published_only'));
        if ($filter_params) {
            $params['value'] = $filter_params['status'];
        }

        $filter_options .= '<div class="elgg-filter-option-even">';
        $filter_options .= '<label title="' . elgg_echo('sort:title:label:status') . '"><small>' . elgg_echo('sort:filter:status') . '</small></label>';
        $filter_options .= elgg_view('input/dropdown', array(
        'value' => $params['value'],
        'options_values' => $status_options,
        'id' => 'status'
        ));
        $filter_options .= '</div>';
        }
    }

    if ($filter_params) {
        $params['value'] = $filter_params['limit'];
    }
    $limit_1 = elgg_get_plugin_setting('limit_1','filter_and_sort');
    $limit_2 = elgg_get_plugin_setting('limit_2','filter_and_sort');
    $limit_3 = elgg_get_plugin_setting('limit_3','filter_and_sort');
    $limit_4 = elgg_get_plugin_setting('limit_4','filter_and_sort');
    $limit_5 = elgg_get_plugin_setting('limit_5','filter_and_sort');

    $limits = array($limit_1=>$limit_1,
                    $limit_2=>$limit_2,
                    $limit_3=>$limit_3,
                    $limit_4=>$limit_4,
                    $limit_5=>$limit_5);

    $filter_options .= '<div class="elgg-filter-option-even">';
    $filter_options .= '<label title="' . elgg_echo('sort:title:label:limit') . '"><small>' . elgg_echo('sort:filter:limit') . '</small></label>';

    $filter_options .= elgg_view('input/dropdown', array(
    'value' => $params['value'],
    'options_values' => $limits,
    'id' => 'limit'
    ));
    $filter_options .= '</div>';

    $options_output .= '<div id="elgg-filter-options">' . $filter_options . '<div class="clearfloat"></div>';
    $options_output .= elgg_view('input/submit', array('value' => 'go',
                                                       'id' => 'filter_options_go_btn',
                                                       'class' => 'elgg-button-submit'));
    $options_output .= '<div class="clearfloat"></div></div>';


}

echo $menu_output;
echo $active_filters;
echo '</div>';
echo $options_output;
