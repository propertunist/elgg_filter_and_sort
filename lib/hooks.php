<?php
/************
 * filter_and_sort - elgg plugin
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 *******************/

 /**
  * route activity page to custom resource if needed
  *
  * @param string $hook         the name of the hook
  * @param string $type         the type of the hook
  * @param array  $return_value the current return value
  * @param array  $params       supplied params
  *
  * @return array|bool
  */

 function filter_and_sort_alter_river($hook, $type, $return_value, $params){
  if (($hook !== 'view')||($type !== 'resources/river')) {
      return $returnvalue;
  }
  else {
    return elgg_view_resource('filter_and_sort_river', $params['vars']);
  }
 }

 /**
  * route tag tools' tag tab on activity page to the correct filter_and_sort page
  *
  * @param string $hook         the name of the hook
  * @param string $type         the type of the hook
  * @param array  $return_value the current return value
  * @param array  $params       supplied params
  *
  * @return array|bool
  */
 function filter_and_sort_tag_tools_route_activity_hook($hook, $type, $return_value, $params) {

 	if (empty($return_value) || !is_array($return_value)) {
 		return $return_value;
 	}

 	$page = elgg_extract('segments', $return_value);

 	switch ($page[0]) {
 		case 'tags':
    {
 			$return_value = false;
      $params['page_type'] = 'tags';
 			echo elgg_view_resource("river", $params);
 			break;
    }
 	}

 	return $return_value;
 }


/**
 * filter_and_sort_groups_page_menu_config()
 *
 * removes menu items from the groups page sidebar menu that filter_and_sort adds to the list of tabs at the top
 * of group lists
 *
 * @param string      $hook        "prepare:menu"
 * @param string      $type        "page]"
 * @param string|null $returnvalue list content (null if not set)
 * @return string
 */

 function filter_and_sort_groups_page_menu_config($hook, $type, $params, $returnvalue) {
  $menus = $params['default'];
  $count = 0;
  if ($menus)
  {
    foreach ($menus as $menu)
    {
      if (($menu->getName() == 'groups:all')||($menu->getName() == 'groups:member'))
          unset($params['default'][$count]);

      $count ++;
    }
  }
  return $params;
}

/**
 * filter_and_sort_members_list_newest()
 *
 * Returns content for the "newest" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "newest"
 * @param string|null $returnvalue list content (null if not set)
 * @return string
 */

function filter_and_sort_members_list_newest($hook, $type, $params, $returnvalue) {
  if ($returnvalue !== null) {
		return;
	}
  $options = $params['options'];
  $list = $params['getter']($options);
  $options['count'] = TRUE;
  $count = $params['getter']($options);
  $options['count'] = $count;
  $list = elgg_view_entity_list($list, $options);
	return array('list' => $list,'count' => $count);
}

/*******
 * filter_and_sort_members_list_popular()
 *
 * - return list of most popular members, based on how many friends they have
 */

function filter_and_sort_members_list_popular($hook, $type, $params, $returnvalue) {
	if ($returnvalue !== null) {
		return;
	}

	$options = $params['options'];
	$options['relationship'] = 'friend';
	$options['inverse_relationship'] = false;
  $returnvalue['list'] = elgg_list_entities_from_relationship_count($options);
  $returnvalue['count'] = count($returnvalue['list']);
	return $returnvalue;
}

/*******
 * filter_and_sort_members_list_online()
 *
 * - return list of recently online members
 */

function filter_and_sort_members_list_online($hook, $type, $params,$returnvalue) {
	if ($returnvalue !== null) {
		return;
	}
  $params['options']['seconds'] = 600;
  $returnvalue = array();
  $returnvalue['list'] = elgg_list_entities($params['options'], 'find_active_users');
  $returnvalue['count'] = count($returnvalue['list']);
	return $returnvalue;
}

/************
 * filter_and_sort_route_hook
 *
 * - determine which page template to render, based on the page handler of the current page
 * entity lists for objects with subtypes get routed to the standard 'all', 'owner' + 'friends' pages.
 * members and groups have their own templates.
 * blog_tools' 'featured' page is also supported with it's own template.
 */

function filter_and_sort_route_hook($hook, $type, $return_value, $params){
    if ($return_value)
    {
        $result = $return_value;
        $page = elgg_extract("segments", $return_value);
        $handler = elgg_extract("handler", $return_value);
//elgg_dump($handler);
//elgg_dump('context = ' . print_r(elgg_get_context_stack(),true));
//elgg_dump($page);
        switch ($handler) {
        // activity is now handled via a resource view
        /*  case 'activity':
          {
            $page_type = (isset($page[0])) ? $page[0] : 'all';
            if ($page_type == 'owner')
            set_input("subject_username", $page[1]);
            set_input("page_type",$page_type);
            // filter_and_sort js replaces the core js for handling subtypes on activity page

            $result = false;

            include(dirname(dirname(__FILE__)) . "/pages/river.php");
            break;
          }*/
          // the main groups listing page
          case 'groups':
          {
              switch($page[0])
              {
                case 'all':
                {
                  $selected_tab = get_input('filter', 'all');
                  $group_filters = array('all','discussion','featured','open','closed','suggested','yours', 'popular', 'ordered');
                  if (!in_array($selected_tab,$group_filters))
                  {
                      $selected_tab = 'all';
                  }
                  $result = false;
                  include(dirname(dirname(__FILE__)) . "/pages/groups.php");
                  break;
                }
                default:
                {
                  return $result;
                }
              }
              break;
          }
          default:
          {
            $object_type = (filter_and_sort_get_object_type_from_context(elgg_get_context()));

            switch ($object_type['object_type']){
                case 'user':
                {
                    if (count($page)==0)
                    {
                        $page[0]='newest';
                    }
                    if (($page[0]=='newest')||($page[0]=='popular')||($page[0]=='online'))
                    {
                        $result = false;
                        include(dirname(dirname(__FILE__)) . "/pages/members.php");
                        break;
                    }
                    else
                        return;
                }
                default:
                {
                    // setup default page if no specific page is provided
                    if (count($page)==0)
                    {
                        $page[0]='all';
                    }

                    switch ($page[0])
                    {
                        case 'owner':
                        {
                            $entity = get_entity($page[1]);
                            if($user = get_user_by_username($page[1])){
                                $result = false;
                                set_input("owner_guid", $user->guid);
                                include(dirname(dirname(__FILE__)) . "/pages/owner.php");
                            }
                            elseif (elgg_instanceof($entity, 'group'))
                            {
                                $result = false;
                                set_input("container_guid", $entity->guid);
                                set_input("owner_guid", $entity->guid);
                                include(dirname(dirname(__FILE__)) . "/pages/owner.php");
                            }
                            break;
                        }
                        case "friends":
                            {
                                if($user = get_user_by_username($page[1])){
                                    $result = false;
                                    set_input("owner_guid", $user->guid);
                                    include(dirname(dirname(__FILE__)) . "/pages/friends.php");
                                    break;
                                }
                                break;
                            }
                        case "all":
                            {
                                $result = false;
                                include(dirname(dirname(__FILE__)) . "/pages/all.php");
                                break;
                            }
                        case 'siteimagesowner':
                        {
                            if($user = get_user_by_username($page[1])){
                                if ($user instanceof ElggUser)
                                {
                                    $result = false;
                                    elgg_push_context('images');
                                    set_input("owner_guid", $user->guid);
                                    include(dirname(dirname(__FILE__)) . "/pages/owner.php");
                                }
                                break;
                            }
                        }
                        case "siteimagesfriends":
                            {
                                if($user = get_user_by_username($page[1])){
                                    $result = false;
                                    elgg_push_context('images');
                                    set_input("owner_guid", $user->guid);
                                    include(dirname(dirname(__FILE__)) . "/pages/friends.php");
                                    break;
                                }
                                break;
                            }
                        case "siteimagesall":
                            {
                                $result = false;
                                elgg_push_context('images');
                                include(dirname(dirname(__FILE__)) . "/pages/all.php");
                                break;
                            }
                        case "group":
                        {
                            $result = false;
                            $group = get_entity($page[1]);
                            if (!elgg_instanceof($group, 'group')) {
                                forward('', '404');
                            }

                            set_input("container_guid", $group->guid);

                            include(dirname(dirname(__FILE__)) . "/pages/owner.php");

                            break;
                        }
                        case "siteimagesgroup":
                        {
                            $result = false;
                            $group = get_entity($page[1]);
                            if (!elgg_instanceof($group, 'group')) {
                                forward('', '404');
                            }
                            elgg_push_context('images');
                            elgg_set_page_owner_guid($group->guid);
                            set_input("container_guid", $group->guid);

                            include(dirname(dirname(__FILE__)) . "/pages/owner.php");

                            break;
                        }
                        case "featured":
                        {
                            $result = false;
                            include(dirname(dirname(__FILE__)) . "/pages/featured.php");
                            break;
                        }
                    }
                    break;
                }
            }
            break;
          }
        }

        return $result;
    }
}

function filter_and_sort_empty_list_hook($hook, $type, $return_value, $params)
{
    if ($return_value['no_results'] == NULL)
    {
        $return_value['no_results'] = '<div class="elgg_no_items">' . elgg_echo('filter_and_sort:no_results') . '</div>';
    }

    return $return_value;
}
