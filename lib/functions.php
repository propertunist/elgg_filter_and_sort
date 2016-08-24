<?php
/************
 * filter_and_sort - elgg plugin
 *
 * functions used in the filter_and_sort plugin.
 *
 * @author - ura soul
 * @website - https://www.ureka.org
 *******************/

 /*************
  * filter_and_sort_count_list()
  *
  * workaround for the lack of support for a 'getter' variable in elgg_get_entities
  *
  * $param  string the function to use as a getter
  * $param  array  options to use when retrieving data to count
  * @return int    a count of the number of entities found
  **/

 function filter_and_sort_count_list($getter, $options)
 {
    // count the list size
    $options['count'] = TRUE;
    switch ($getter)
    {
        case 'elgg_get_entities_from_metadata':
        {
          $count = elgg_get_entities_from_metadata($options);
          break;
        }
        case 'elgg_get_entities_from_annotations':
        {
          $count = elgg_get_entities_from_annotations($options);
          break;
        }
        case 'elgg_get_entities_from_annotation_calculation':
        {
          $count = elgg_get_entities_from_annotation_calculation($options);
          break;
        }
        default:
        {
          $count = elgg_get_entities($options);
          break;
        }
    }
    return $count;
}

/*************
 * filter_and_sort_isValidTimeStamp()
 *
 * perform simple tests to ensure a variable is a valid unix timestamp
 *
 * $param  string a string representing a UNIX timestamp.
 * @return bool   true if timestamp is a valid one, false if not.
 **/

 function filter_and_sort_isValidTimeStamp($timestamp)
 {
     return ((string) (int) $timestamp === $timestamp)
         && ($timestamp <= PHP_INT_MAX)
         && ($timestamp >= ~PHP_INT_MAX);
 }

/******
 * casort
 *
 * - sort an array of strings in alphabetical order
 *
 * $param  array  the array of strings to sort
 * $param  string the key against which to sort
 * @return array  the sorted array of strings
 */

if (!function_exists('casort'))
 {
     function casort($arr, $var) {
       $tarr = array();
       $rarr = array();
       for($i = 0; $i < count($arr); $i++) {
          $element = $arr[$i];
          $tarr[] = strtolower($element->{$var});
       }

       reset($tarr);
       asort($tarr);
       $karr = array_keys($tarr);
       for($i = 0; $i < count($tarr); $i++) {
          $rarr[] = $arr[intval($karr[$i])];
       }

       return $rarr;
    }
}

/******
 * filter_and_sort_set_cookie()
 *
 * - create browser cookie for given context and group
 *
 * @uses $vars  input variables for cookie
 * @return bool true if successful, false if not
 */

function filter_and_sort_set_cookie($vars){
    if ($context = $vars['context'])
    {
        $site_url = elgg_get_site_url();
        $site_domain = parse_url($site_url,PHP_URL_HOST);
        $cookie = new  ElggCookie('filter_and_sort-' . $context . $vars['group_guid']);
        $cookie->httpOnly = TRUE;
        $cookie->setExpiresTime('+365 Days');
        $filter_and_sort_data = array();
        $filter_and_sort_data['context'] = $context;

        if ($vars['list_type'])
            $filter_and_sort_data['list_type'] = $vars['list_type'];
        else
            $filter_and_sort_data['list_type'] = 'list';
        if ($vars['limit'])
            $filter_and_sort_data['limit'] = $vars['limit'];
        else
        {
            $limit_1 = elgg_get_plugin_setting('limit_1','filter_and_sort');
            $filter_and_sort_data['limit'] = $limit_1;
        }
        if ($vars['contain'])
            $filter_and_sort_data['contain'] = $vars['contain'];
        else
            $filter_and_sort_data['contain'] = 'all';
        if ($vars['sort'])
            $filter_and_sort_data['sort'] = $vars['sort'];
        else
            $filter_and_sort_data['sort'] = 'created_d';
        if ($vars['timing-from'])
            $filter_and_sort_data['timing-from'] = $vars['timing-from'];
        else
            $filter_and_sort_data['timing-from'] = 'all';
        if ($vars['timing-to'])
            $filter_and_sort_data['timing-to'] = $vars['timing-to'];
        else
            $filter_and_sort_data['timing-to'] = 'all';
        if (($vars['status'])&&($vars['status'] != 'null'))
            $filter_and_sort_data['status'] = $vars['status'];
        else
            $filter_and_sort_data['status'] = 'all';
        if ($vars['show_icon'] == 'true')
            $filter_and_sort_data['show_icon'] = $vars['show_icon'];
        if ($vars['type'])
            $filter_and_sort_data['type'] = $vars['type'];
        if ($vars['subtype'])
                $filter_and_sort_data['subtype'] = $vars['subtype'];

        $cookie->value = json_encode($filter_and_sort_data);

        if (elgg_set_cookie($cookie))
            return true;
        else
            return false;
    }
    else
        return false;
}

/******
 * filter_and_sort_get_cookie
 *
 * - retrieve cookie for given context and group
 *
 * @uses $context - the context for which to retrieve cookie data
 * @uses $group_guid - the group guid for which to retrieve cookie data
 * @return array|bool - the cookie data or false if none found
 */

function filter_and_sort_get_cookie($context, $group_guid){
    if ($context)
    {
            $filter_and_sort_cookie = $_COOKIE['filter_and_sort-' . $context. $group_guid];
            if ($filter_and_sort_cookie)
            {
                    $cookie_data = json_decode($filter_and_sort_cookie, true);
                    return $cookie_data;
            }
            else
                return false;
    }
    else
        return false;
}

/******
 * elgg_get_sort_filter_options
 *
 * - configures and prepares input fields for use in the filtering/sorting panel for entity lists
 * - builds relevant query parameters for retrieving list data
 *
 * @uses $vars['filter_params'] - parameters for specific filter options
 * @uses $vars['options'] - elgg/sql query options for list retrieval
 * @uses $vars['page_type'] - the type of page being processed (group/all etc.)
 * @return array ['options'] - elgg/sql query options
 *         string ['getter'] - which elgg getter function to use to get data
 *         array ['filter_params'] - parameters for specific filter options
 *         bool['cookie_loaded'] - was the cookie found and loaded?
 */

function elgg_get_sort_filter_options($vars)
{
    $options = $vars['options'];
    $page_type = $vars['page_type'];

    // define array for defining sorting/filtering options and associated UI element states
    if (!$vars['filter_params'])
    {
      $filter_params = array();
      $filter_params = filter_and_sort_get_input_data($filter_params);
    }
    else {
      $filter_params = $vars['filter_params'];
      $filter_params['page_type'] = $page_type;
    }

    $cookie_loaded = false;

    // if a container was provided, check if it is a group
    $container = get_entity($options['container_guid']);
    if (elgg_instanceof($container, 'group'))
            $group_guid = $options['container_guid'];

    // retrieve any cookie for this context that may exist
    if ($cookie_data = filter_and_sort_get_cookie(elgg_get_context(),$group_guid))
    {
            $cookie_loaded = true;
            // combine cookie data with existing filter params if possible
            if (is_array($filter_params))
            {
                // the + operator adds to the left array, any keys from the right array that are not present in the left array. duplicates are NOT overwritten. therefore, parameters that originate in the UI or address bar will take priority over cookie values. defaults therefore need to be blank for cookie values to be used.
                $filter_params = $filter_params + $cookie_data;

                if (($filter_params['type'] == 'all')||($filter_params['type'] == 'group')||($filter_params['type'] == 'user'))
                {
                  $filter_params['subtype'] = NULL;
                }
            }
            else
                $filter_params = $cookie_data;
    }

    if (elgg_is_logged_in())
    {
        // build query string for object/subtype selector input control
        if ($filter_params['type'])
        {
            if (in_array($filter_params['type'], array('group', 'user', 'object')))
            {
                // check that subtype is a valid one and add to selector string if valid
                $registered_subtypes = elgg_get_config('registered_entities');
            	if (($filter_params['subtype'])&&(in_array($filter_params['subtype'],$registered_subtypes['object']))) {
            		$selector = "type=" . $filter_params['type'] . "&subtype=" . $filter_params['subtype'];
            	} else {
            		$selector = "type=" . $filter_params['type'];
            	}
            }
        }

        // pass object and subtype to the options array
      	if (($filter_params['type'] != 'all')&&($filter_params['type'])) {
      		$options['type'] = $filter_params['type'];
      		if ($filter_params['subtype']) {
      			$options['subtype'] = $filter_params['subtype'];
      		}
      	}

        if ($selector)
        	$filter_params['objtype'] = $selector;

        // if objtype selector is set to user or group, remove the container variable and thus also remove the input control completely
        if (($filter_params['objtype'] == 'type=user')||($filter_params['objtype']== 'type=group'))
          unset ($filter_params['contain']);
    }

    $dbprefix = elgg_get_config("dbprefix");

    // set defaults
    if (!$vars['getter'])
        $getter = 'elgg_get_entities';
    else
        $getter = $vars['getter'];

    $no_items = elgg_echo('sort:no-items');
    $limit_1 = elgg_get_plugin_setting('limit_1','filter_and_sort');
    $limit_2 = elgg_get_plugin_setting('limit_2','filter_and_sort');
    $limit_3 = elgg_get_plugin_setting('limit_3','filter_and_sort');
    $limit_4 = elgg_get_plugin_setting('limit_4','filter_and_sort');
    $limit_5 = elgg_get_plugin_setting('limit_5','filter_and_sort');

    // if the limit is not a valid one then use the smallest
    if (!in_array($filter_params['limit'],array($limit_1,$limit_2,$limit_3,$limit_4,$limit_5)))
    {
        $filter_params['limit'] = $limit_1;
    }

    // set query's limit and offset
    $options['limit'] = $filter_params['limit'];
    $options['offset'] = $filter_params['offset'];

    // set default limit to be the lowest limit from the admin options
    if ((!is_int($options['limit']))||($options['limit'] == '')||($options['limit'] == 0))
        $options['limit'] = $limit_1;

    // add filter for only showing entities that have icons
    if ($filter_params['show_icon'] == 'true')
    {
        $options['metadata_name'] = 'icontime';
        $options['joins'][] = 'JOIN users_entity st ON (e.guid = st.guid)';
        $getter = 'elgg_get_entities_from_metadata';
    }

    // bult query parameters for various list views

    // online view for members does not have any special parameters so skip it
    if ($filter_params['filter_context'] != 'online')
    {
            // set default sort order
            if (($filter_params['context'] == 'groups')&&($filter_params['filter_context'] == 'popular'))
              $filter_params['sort'] = 'created_d';

            if ($filter_params['sort'])
                if (($filter_params['sort'] != 'alpha_a')&&($filter_params['sort'] != 'alpha_d')&&($filter_params['sort'] != 'created_a')&&($filter_params['sort'] != 'created_d')&&($filter_params['sort'] != 'changed_a')&&($filter_params['sort'] != 'changed_d')&&($filter_params['sort'] != 'likes_a')&&($filter_params['sort'] != 'likes_d')&&($filter_params['sort'] != 'views_a')&&($filter_params['sort'] != 'views_d')&&($filter_params['sort'] != 'comments_a')&&($filter_params['sort'] != 'comments_d'))
                    $filter_params['sort'] = 'created_d';

            if($filter_params['filter_context'] != 'popular')
            {
                // set default timing values
                if ($filter_params['timing-to'])
                    if (!filter_and_sort_isValidTimeStamp($filter_params['timing-to']))
                      $filter_params['timing-to'] = 'all';

                if ($filter_params['timing-from'])
                    if (!filter_and_sort_isValidTimeStamp($filter_params['timing-from']))
                      $filter_params['timing-from'] = 'all';

                // set default container value
                if ($filter_params['contain'])
                    if (($filter_params['contain'] != 'all')&&($filter_params['contain'] != 'groups')&&($filter_params['contain'] != 'profile'))
                    {
                        $filter_params['contain'] = (int) $filter_params['contain'];
                        $container = get_entity($filter_params['contain']);
                        if(($container instanceof ElggGroup)||($container instanceof ElggUser))
                            $filter_params['contain'] = 'container';
                        else
                            $filter_params['contain'] = 'all';
                    }
                // handle blog status variable
                if ($filter_params['status'])
                {
                    if (($filter_params['status'] == 'draft')||($filter_params['status'] == 'published'))
                    {
                        if (($filter_params['sort'] == 'likes_a')||($filter_params['sort'] == 'likes_d')||($filter_params['sort'] == 'views_a')||($filter_params['sort'] == 'views_d')||($filter_params['sort'] == 'comments_a')||($filter_params['sort'] == 'comments_d'))
                            $filter_params['sort'] = 'created_d';
                    }
                    if ($filter_params['status'] == 'null')
                        $filter_params['status'] = 'all';
                }

            }

            // define the timing type for river and non-river pages
            if ($filter_params['context'] == 'activity')
              $time_bound = 'posted_time_';
            else
              $time_bound = 'created_time_';

            if ($filter_params['timing-from'] != 'all')
              $options[$time_bound . 'lower'] = (int) $filter_params['timing-from'];

            if ($filter_params['timing-to'] != 'all')
              $options[$time_bound . 'upper'] = (int) $filter_params['timing-to'];
            else {
              $options[$time_bound . 'upper'] = time();
            }

            // build query parameters for various sort types
            switch($filter_params['sort'])
            {
              case 'alpha_a':
              {
                switch($filter_params['context']){
                    case 'members':
                    {
                        $options['order_by'] = "st.name DESC";
                        break;
                    }
                    case 'groups':
                    {
                        $options['joins']	= [
                        "JOIN {$dbprefix}groups_entity ge ON e.guid = ge.guid",
                      ];
                        $options['order_by'] = 'ge.name DESC';
                        break;
                    }
                    default:{
                        $options['joins'] = array("JOIN " . $dbprefix . "objects_entity oe ON e.guid = oe.guid");
                        if ($subtype == 'thewire')
                            $options['order_by'] = "oe.description DESC";
                        else
                            $options['order_by'] = "oe.title DESC";
                        break;
                    }
                }
                break;
              }
             case 'alpha_d':
              {
                switch($filter_params['context']){
                    case 'members':
                    {
                        $options['order_by'] = "st.name ASC";
                        break;
                    }
                    case 'groups':
                    {
                        $options['joins']	= [
                        "JOIN {$dbprefix}groups_entity ge ON e.guid = ge.guid",
                      ];
                        $options['order_by'] = 'ge.name ASC';
                        break;
                    }
                    default:{
                        $options['joins'] = array("JOIN " . $dbprefix . "objects_entity oe ON e.guid = oe.guid");
                        if ($options['subtype'] == 'thewire')
                            $options['order_by'] = "oe.description ASC";
                        else
                            $options['order_by'] = "oe.title ASC";
                        break;
                    }
                }
                break;
              }
              case 'created_a':
              {
                if ($filter_params['context'] != 'activity')
                  $options['order_by'] = "e.time_created ASC";

                break;
              }
             case 'created_d':
              {
                if ($filter_params['context'] != 'activity')
                  $options['order_by'] = "e.time_created DESC";
                break;
              }
              case 'changed_a':
              {
                if ($filter_params['context'] != 'activity')
                  $options['order_by'] = "e.time_updated ASC";
                //$time_bound = 'modified_time_lower';
                break;
              }
             case 'changed_d':
              {
                if ($filter_params['context'] != 'activity')
                  $options['order_by'] = "e.time_updated DESC";
                //$time_bound = 'modified_time_lower';
                break;
              }
             case 'comments_a':
              {
                if ($options['subtype'] != 'discussion')
                {
                        $options['selects'][] = "count( * ) AS views";
                        $options['order_by'] = "views DESC";
                        $options['joins'][] = "JOIN {$dbprefix}entities ce ON ce.container_guid = e.guid";
                        $options['joins'][] = "JOIN {$dbprefix}entity_subtypes cs ON ce.subtype = cs.id AND cs.subtype = 'comment'";
                        $options['group_by'] = 'e.guid';
                }
                else
                {
                        $options['selects'][] = "count(dr.guid) AS replies";
                        $options['joins'][] = "JOIN entities dr ON (dr.container_guid = e.guid)";
                        $options['order_by'] = "replies DESC";
                        $options['group_by'] = "e.guid";
                }
                $no_items = elgg_echo('sort:no-comments');
                break;
              }
             case 'comments_d':
              {
                if ($options['subtype'] != 'discussion')
                {
                        $options['selects'][] = "count( * ) AS views";
                        $options['order_by'] = "views ASC";
                        $options['joins'][] = "JOIN {$dbprefix}entities ce ON ce.container_guid = e.guid";
                        $options['joins'][] = "JOIN {$dbprefix}entity_subtypes cs ON ce.subtype = cs.id AND cs.subtype = 'comment'";
                        $options['group_by'] = 'e.guid';
                }
                else
                {
                        $options['selects'][] = "count(dr.guid) AS replies";
                        $options['joins'][] = "JOIN entities dr ON (dr.container_guid = e.guid)";
                        $options['order_by'] = "replies ASC";
                        $options['group_by'] = "e.guid";
                }
                $no_items = elgg_echo('sort:no-comments');
                break;
              }
             case 'likes_a':
              {
                $options['order_by'] = "annotation_calculation DESC";
                $options['calculation'] = 'count';
                $options['annotation_name'] = 'likes';
                $options['selects'][] = 'count(CAST(a_msv.string AS signed)) AS annotation_calculation';
                $getter = 'elgg_get_entities_from_annotation_calculation';
                $no_items = elgg_echo('sort:no-likes');
                break;
              }
             case 'likes_d':
              {
                $options['order_by'] = "annotation_calculation ASC";
                $options['calculation'] = 'count';
                $options['annotation_name'] = 'likes';
                $options['selects'][] = 'count(CAST(a_msv.string AS signed)) AS annotation_calculation';
                $getter = 'elgg_get_entities_from_annotation_calculation';
                $no_items = elgg_echo('sort:no-likes');
                break;
              }
              case 'views_a':
              {
                $options['order_by'] = "annotation_calculation DESC";
                $options['calculation'] = 'count';
                if (elgg_is_active_plugin('views_counter'))
                    $options['annotation_name'] = 'views_counter';
                else
                $options['annotation_name'] = ENTITY_VIEW_COUNTER_ANNOTATION_NAME;
                $getter = 'elgg_get_entities_from_annotation_calculation';
                $no_items = elgg_echo('sort:no-views');
                break;
              }
             case 'views_d':
              {
                $options['order_by'] = "annotation_calculation ASC";
                $options['calculation'] = 'count';
                if (elgg_is_active_plugin('views_counter'))
                    $options['annotation_name'] = 'views_counter';
                else
                    $options['annotation_name'] = ENTITY_VIEW_COUNTER_ANNOTATION_NAME;
                $getter = 'elgg_get_entities_from_annotation_calculation';
                $no_items = elgg_echo('sort:no-views');
                break;
              }
             default:
                 break;
            }

            // build query paramters for container options
            switch($filter_params['contain'])
            {
              case 'profile':
              {
                  $options['container_guid'] = elgg_get_logged_in_user_guid();
                  break;
              }
              case 'groups':
              {
                $current_user = elgg_get_logged_in_user_entity();
                $groups = casort($current_user->getGroups(array('limit' => 0),0), "name");

                  foreach ($groups as $group)
                  {
                    $options['container_guids'][] = $group->guid;
                  }

                  break;
              }
              case 'container':
              {
                  $options['container_guid'] = $container->guid;
                  $filter_params['contain'] = $container->guid;
                  break;
              }
              case 'all':
              default:
                  break;
            }
    }

    // add query options for hypelists
    if (elgg_is_active_plugin('hypeLists'))
    {
      $options['auto_refresh'] = elgg_get_plugin_setting('hypelists_refresh', 'filter_and_sort');
      $options['lazyLoad'] = elgg_get_plugin_setting('hypelists_lazy', 'filter_and_sort');
      $options['pagination'] = true;
      $options['pagination_type'] = 'infinite';
      $options['list_id'] = 'main_content_list';
      $options['position'] = 'after';
  	  $options['offset'] = get_input('offset', $count - $limit);
      //   $options['data-baseUrl'] = current_page_url();
    }

    // build query parameters for blog published status
    if ($getter == 'elgg_get_entities')
    {
        switch($filter_params['status'])
        {
          case 'draft':
          {
              $getter = 'elgg_get_entities_from_metadata';
              break;
          }
          case 'published':
          {
              $getter = 'elgg_get_entities_from_metadata';
              break;
          }
          case 'all':
          default:
              break;
        }
    }
    // subtype specific query parameters
    switch ($options['subtype'])
    {
        case 'image':
        {
            if ((($filter_params['contain']) &&($filter_params['contain'] != 'all'))||($page_type == 'group'))
            {
                if (!$options['joins'])
                    $options['joins'] = array("join {$dbprefix}entities u on e.container_guid = u.guid");
                else
                    $options['joins'][] = "join {$dbprefix}entities u on e.container_guid = u.guid";

                if (count($options['container_guids'])> 0)
                {
                    $where_counter = 0;
                    foreach ($options['container_guids'] as $container_guid)
                    {
                        $containers .= "'" . $container_guid . "'";
                        if ($where_counter < (count($options['container_guids'])-1))
                            $containers .= ',';
                        $where_counter++;
                    }
                    $options['wheres'] = array("u.container_guid IN ({$containers})");
                }
                else
                {
                    if (!$options['wheres'])
                    {
                        $options['wheres'] = array("u.container_guid = {$options['container_guid']}");
                    }
                }
            }
            $options['container_guids'] = null;
            $options['container_guid'] = null;
            $filter_params['list_type'] = 'gallery';
            break;
        }
        case 'album':
        {
            $filter_params['list_type'] = 'gallery';
            break;
        }
        case 'videolist_item':
        {
           // get video files that have been uploaded if file plugin is active
           if (elgg_is_active_plugin('file'))
           {
                unset($options['subtype']);
                $options['subtypes'][] = 'videolist_item';
                $options['subtypes'][] = 'file';
              //  elgg_dump($filter_params['context']);
                if ($filter_params['context'] == 'activity')
                  $options['joins'][] = 'JOIN metadata m_table1 on oe.guid = m_table1.entity_guid';
                else
                  $options['joins'][] = 'JOIN metadata m_table1 on e.guid = m_table1.entity_guid';
                $options['joins'][] = 'JOIN metastrings msn1 on m_table1.name_id = msn1.id';
                $options['joins'][] = 'JOIN metastrings msv1 on m_table1.value_id = msv1.id';
                $options['wheres'][] = 'msn1.string = "simpletype"';
                $options['wheres'][] = 'BINARY msv1.string = "video"';
                $options['wheres'][] = 'm_table1.enabled = "yes"';
           }
           break;
        }
    }

    if ($filter_params['list_type'])
        $options['list_type'] = $filter_params['list_type'];

    $options['no_results'] = $no_items;

    return array('options' => $options,
                 'getter' => $getter,
                 'filter_params' => $filter_params,
                 'cookie_loaded' => $cookie_loaded);
}

/****
 * elgg_get_list_type_from_context
 *
 * @param string $context      the source context
 *
 * @return array|bool          object type and subtype that relate to the context or false in the case of error or no match.
 *
 */

function filter_and_sort_get_object_type_from_context($context)
{
    if (!$context)
        $context = elgg_get_context();

        $mods = array ( 'blog'=>'blog',
                'file'=>'file',
                'videolist'=>'videolist',
                'bookmarks'=>'bookmarks',
                'pages'=>'pages',
                'pinboards'=>'pinboards',
                'thewire'=>'thewire',
                'photos'=>'photos',
                'discussion' => 'discussion',
                'members'=> 'members',
                'poll' => 'poll',
                'groups' => 'groups');

    switch ($context)
    {
        case $mods['members']:
        {
            $return['object_type'] = 'user';
            break;
        }
        case $mods['groups']:
        {
            $return['object_type'] = 'group';
            break;
        }
        case $mods['videolist']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'videolist_item';
            break;
        }
        case $mods['pages']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'page_top';
            break;
        }
        case $mods['photos']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'album';
            break;
        }
        case 'images':
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'image';
            break;
        }
        case $mods['pinboards']:
        case 'au_set':
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'au_set';
            break;
        }
        case $mods['file']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'file';
            break;
        }
        case $mods['blog']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'blog';
            break;
        }
        case $mods['bookmarks']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'bookmarks';
            break;
        }
        case $mods['thewire']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'thewire';
            break;
        }
        case $mods['discussion']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'discussion';
            break;
        }
        case $mods['poll']:
        {
            $return['object_type'] = 'object';
            $return['subtype'] = 'poll';
            break;
        }
        default:
        {
            $return = false;
            break;
        }
    }

    return $return;
}

/*
 * filter_and_sort_map_hijacks
 *
 * returns a list of page handlers for entity types, including changed handlers that have been changed by the elgg plugin 'pagehandler hijack'. this is used to ensure that any handler that have been changed by pagehandler_hijack can be correctly referenced by other code.
 * @param array $mods     a list of page handler ID strings that are to be potentially hijacked and that we need to return the hijacked handlers for.
 *
 * @return array $mods    the array of page handler IDs, including any hijacked values
 */

function filter_and_sort_map_hijacks($mods = NULL)
{
    if ($mods == NULL)
            $mods = array ( 'blog'=>'blog',
                'file'=>'file',
                'videolist'=>'videolist',
                'bookmarks'=>'bookmarks',
                'pages'=>'pages',
                'pinboards'=>'pinboards',
                'thewire'=>'thewire',
                'photos'=>'photos',
                'discussion' => 'discussion',
                'members'=> 'members');

    $hijacks = MBeckett\pagehandler_hijack\get_replacement_handlers();
    if ($hijacks)
    {
        foreach ($mods as $mod)
        {
            // if a plugin's type is found in the hijacked list of pagehandlers, then replace it in the list of mods with the hijacked version
            if (array_key_exists($mod, $hijacks))
                $mods[$mod] = $hijacks[$mod];
        }
    }
    return $mods;
}

/**
 * elgg_filter_and_sort_register_toggle
 *
 * - creates a toggle to extra menu for switching between list and gallery views
 *
 * - the current url is updated to contain a query parameter that points to the alternative list_type and returns a hyperlink for that new url
 *
 * @param string $list_type     either gallery or list - depending on the display mode that is currently being used for the list on the current page
 *
 * @return string output/url    a formatted button that reloads the page with the alternate list view (gallery/list) to the one currently displayed on the page
 */

function elgg_filter_and_sort_register_toggle($list_type) {
    $url = elgg_http_remove_url_query_element(current_page_url(), 'list_type');

    if ($list_type == 'gallery') {
        $list_type = "list";
        $icon = elgg_view_icon('list');
    } else {
        $list_type = "gallery";
        $icon = elgg_view_icon('grid');
    }
    if (substr_count($url, '?')) {
        $url .= "&list_type=" . $list_type;
    } else {
        $url .= "?list_type=" . $list_type;
    }

    $output = $icon . '<span class="list-type-label">' . elgg_echo('filter_and_sort:list:'. $list_type) . '</span>';

    return elgg_view('output/url', array('name' => 'list_type',
                                            'text' => $output,
                                            'href' => $url,
                                            'is_trusted' => TRUE,
                                            'title' => elgg_echo("filter_and_sort:list:$list_type")));

}

/**
 * filter_and_sort_get_input_data
 *
 * - retrieve inputs from the browser for use in the filter and sort controls
 */

function filter_and_sort_get_input_data($filter_options){

    if ($sort = preg_replace('[\W]', '', get_input('sort', '')))
        $filter_options['sort'] = $sort;

    if ($timing_to = preg_replace('[\W]', '', get_input('timing_to', '')))
        $filter_options['timing-to'] = $timing_to;

    if ($timing_from = preg_replace('[\W]', '', get_input('timing_from', '')))
        $filter_options['timing-from'] = $timing_from;

    if ($status = preg_replace('[\W]', '', get_input('status', '')))
        $filter_options['status'] = $status;

    if ($contain = preg_replace('[\W]', '', get_input('contain', '')))
        $filter_options['contain'] = $contain;

    if ($list_type = preg_replace('[\W]', '', get_input('list_type', '')))
        $filter_options['list_type'] = $list_type;

    if ($show_icon = preg_replace('[\W]', '', get_input('show_icon', '')))
        $filter_options['show_icon'] = $show_icon;

    if ($limit = (int) get_input('limit', ''))
        $filter_options['limit'] = $limit;

    if ($offset = (int) get_input('offset', ''))
        $filter_options['offset'] = $offset;

    if ($page_type = preg_replace('[\W]', '', get_input('page_type', 'all')))
            $filter_options['page_type'] = $page_type;

    if ($type = preg_replace('[\W]', '', get_input('type')))
            $filter_options['type'] = $type;

    if ($subtype = preg_replace('[\W]', '', get_input('subtype')))
            $filter_options['subtype'] = $subtype;

    return $filter_options;
}
