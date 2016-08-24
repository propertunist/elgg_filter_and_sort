<?php
/************
 * filter_and_sort - elgg plugin - show all friends' items for context
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 * @uses $params - array of variables for use when rendering the page structure
 * @uses $options - array of variables for use when executing the list query in the database
 * @uses $filter_params - array of variables for use when building the sort/filter controls
 *******************/

 // access check for closed groups
group_gatekeeper();
$context = elgg_get_context();
$object_type = filter_and_sort_get_object_type_from_context($context);

// array for defining sorting/filtering options and associated UI element states
$filter_params = array();
$filter_params['filter_context'] = 'friends';
$filter_params = filter_and_sort_get_input_data($filter_params);

if ($filter_params['contain'] == 'profile')
{
    $filter_params['contain'] = 'all';
}

// if called for tidypics images, remove the top context in the stack
if ($object_type['subtype'] == 'image')
{
    elgg_pop_context();
}

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner) {
    forward(REFERRER);
}
$page_owner_guid = $page_owner->getGUID();

if (!$friends = $page_owner->getFriends(array('limit'=> 0))) {
    $params['content'] =  '<ul class="elgg-list elgg-sync elgg-list-entity elgg-list-entity"><li>' . '<div class="elgg_no_items">' . elgg_echo('friends:none:you') . '</div>' . '</li></ul>';
} else
{
    $options = array(
    'type' => 'object',
    'subtype' => $object_type['subtype'],
    'full_view' => FALSE,
    'view_toggle_type' => false,
    );

    foreach ($friends as $friend) {
        $options['owner_guids'][] = $friend->getGUID();
    }

    // set subtype specific options
    switch ($object_type['subtype'])
    {
    case 'discussion':
        {
            // load lighbox files to support 'add discussion' dialog
           // elgg_require_js('elgg/lightbox');
           // elgg_load_css('lightbox');
           // elgg_register_title_button();

            $params['title'] = elgg_echo('discussion:title:friends');
            $no_items = elgg_echo('discussion:none');
            $options['list_class'] = 'elgg-list elgg-list-entity';
            break;
        }
        case 'blog':
            {
                elgg_register_title_button();
                if (($filter_params['status']=='published')||($filter_params['status']=='draft'))
                {
                    $options['metadata_name_value_pairs'] = array(
                        array('name' => 'status', 'value' => $filter_params['status']),
                    );
                }
                $current_user = elgg_get_logged_in_user_entity();
                // show all posts for admin
                // show only published posts for other users.
                $show_only_published = true;
                if ($current_user) {
                    if (($page_owner_guid == $current_user->guid) || $current_user->isAdmin())
                    {
                        $show_only_published = false;
                    }
                }
                if ($show_only_published)
                {
                    $options['metadata_name_value_pairs'] = array(
                        array('name' => 'status', 'value' => 'published'),
                    );
                }
                $no_items = elgg_echo('blog:none');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                $params['title'] = elgg_echo('blog:title:friends');
                break;
            }
        case 'bookmarks':
            {
                elgg_register_title_button();
                $params['sidebar'] = elgg_view('bookmarks/sidebar');
                $no_items = elgg_echo('bookmarks:none');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                $params['title'] = elgg_echo('bookmarks:friends');
                break;
            }
        case 'file':
            {
                elgg_register_title_button();
                $sidebar = file_get_type_cloud($owner->guid, true);
                $params['sidebar'] = $sidebar . elgg_view('file/sidebar');
                $params['sidebar'] = elgg_view('file/sidebar');
                $params['title'] = elgg_echo('file:friends');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                $no_items = elgg_echo('file:none');
                break;
            }
        case 'page_top':
            {
                elgg_register_title_button();
                $params['sidebar'] = elgg_view('pages/sidebar');
                $params['title'] = elgg_echo('pages:friends');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                $no_items = elgg_echo('pages:none');
                break;
            }
        case 'au_set':
            {
                elgg_register_title_button();
                $params['title'] = elgg_echo('au_sets:title:friends');
                $no_items = elgg_echo('au_sets:friends:none');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                break;
            }
        case 'thewire':
            {
                $params['title'] = elgg_echo('thewire:friends');
                $no_items = elgg_echo('thewire:noposts');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                if ($user = elgg_get_logged_in_user_entity()) {
                elgg_register_menu_item("page", array(
                   "name" => "mentions",
                   "href" => "thewire/search/@" . $user->username,
                   "text" => elgg_echo("thewire_tools:menu:mentions"),
              ));
            }

            elgg_register_menu_item("page", array(
                "name" => "search",
                "href" => "thewire/search",
                "text" => elgg_echo("search"),
            ));
                break;
            }
        case 'videolist_item':
            {
                elgg_register_title_button();
                $params['sidebar'] = elgg_view('videolist/sidebar');
                $params['title'] = elgg_echo('videolist:friends');
                $options['list_class'] = 'elgg-list elgg-list-entity';
                $no_items = elgg_echo('videolist:none');
                break;
            }
        case 'album':
            {
                $params['title'] = elgg_echo('album:yours:friends');
                $params['sidebar'] = elgg_view('photos/sidebar', array('page' => $page_type));
                $no_items = elgg_echo('tidypics:none');
                $options['list_type'] = 'gallery';
                $options['list_class'] = 'elgg-list elgg-list-entity';
                $options['gallery_class'] = 'tidypics-gallery tidypics-album-list';

                if (elgg_is_logged_in()) {
                        $logged_in_guid = elgg_get_logged_in_user_guid();
                        elgg_register_menu_item('title', array('name' => 'addphotos',
                        'href' => "ajax/view/photos/selectalbum/?owner_guid=" . $logged_in_guid,
                        'text' => elgg_echo("photos:addphotos"),
                        'link_class' => 'elgg-button elgg-button-action elgg-lightbox'));
                }
                break;
            }
        case 'image':
            {
                $params['title'] = elgg_echo('tidypics:siteimagesfriends');
                $params['sidebar'] = elgg_view('photos/sidebar', array('page' => $page_type));
                $no_items = elgg_echo('filter_and_sort:friends:none');
                $options['list_type'] = 'gallery';
                $options['list_class'] = 'elgg-list elgg-list-entity tidypics-image-list';
                $options['gallery_class'] = 'tidypics-gallery';

                if (elgg_is_logged_in()) {
                        $logged_in_guid = elgg_get_logged_in_user_guid();
                        elgg_register_menu_item('title', array('name' => 'addphotos',
                        'href' => "ajax/view/photos/selectalbum/?owner_guid=" . $logged_in_guid,
                        'text' => elgg_echo("photos:addphotos"),
                        'link_class' => 'elgg-button elgg-button-action elgg-lightbox'));
                }

                // only show slideshow link if slideshow is enabled in plugin settings and there are images
                if (elgg_get_plugin_setting('slideshow', 'tidypics') && !empty($list)) {
                        $url = elgg_get_site_url() . "photos/siteimagesall?limit=64&offset=$offset&view=rss";
                        $url = elgg_format_url($url);
                        $slideshow_link = "javascript:PicLensLite.start({maxScale:0, feedUrl:'$url'})";
                        elgg_register_menu_item('title', array('name' => 'slideshow',
                        'href' => $slideshow_link,
                        'text' => "<img src=\"".elgg_get_site_url() ."mod/tidypics/graphics/slideshow.png\" alt=\"".elgg_echo('album:slideshow')."\">",
                        'title' => elgg_echo('album:slideshow'),
                        'class' => 'elgg-button elgg-button-action'));
                }

                $logged_in_user = elgg_get_logged_in_user_entity();
                $logged_in_username = $logged_in_user->username;
                $filter_params['all_link'] = 'photos/siteimagesall/';
                $filter_params['mine_link'] = 'photos/siteimagesowner/' . $logged_in_username;
                $filter_params['friend_link'] = 'photos/siteimagesfriends/' . $logged_in_username;
                elgg_pop_context();
                break;
            }

        default:
            {
                break;
            }
    }
    $sort_filter_options = elgg_get_sort_filter_options(array('options' => $options,
            'filter_params' => $filter_params,
            'page_type' => $object_type['subtype']));

    $list = elgg_list_entities($sort_filter_options['options'],$sort_filter_options['getter']);
    if (elgg_is_xhr())
    {
        echo $list;
    }
    else
    {
        $params['content'] = $list;

            //$sort_filter_options['options']['count'] = TRUE;

            // count the list size
            //$count = filter_and_sort_count_list($sort_filter_options['getter'],
            //                                    $sort_filter_options['options']);

            //if ($count == 0) {
            //     if ($sort_filter_options['no-items'])
            //        $no_items = $sort_filter_options['no-items'];

            //    $params['content'] = '<ul class="elgg-list elgg-sync elgg-list-entity elgg-no-items"><li>' //. $no_items . '</li></ul>';
            //} else {
        //        $params['content'] = $list;
        //    }
    }
}
if (elgg_is_xhr())
{
        return;
}
else
{
        $filter_params = $sort_filter_options['filter_params'];
        $filter_params['cookie_loaded'] = $sort_filter_options['cookie_loaded'];
        if (($object_type['subtype'] != 'image')&& ($object_type['subtype'] != 'album'))
        {
            $filter_params['toggle'] = elgg_filter_and_sort_register_toggle($filter_params['list_type']);
        }
        else
        {
            elgg_load_css('slick');
            elgg_load_css('slick-theme');
            elgg_load_css('elgg.slick');
            if ($object_type['subtype'] == 'image')
            {
                if ((elgg_is_active_plugin('tidypics_plus'))&&('yes' == elgg_get_plugin_setting('justified_gallery_list', 'tidypics_plus')))
                {
                    	elgg_require_js('justifiedGallery');
                    	if (elgg_is_active_plugin('hypeLists'))
                    		elgg_require_js('init_justifiedGallery/init_justifiedGallery_hypeList');
                    	else
                    		elgg_require_js('init_justifiedGallery/init_justifiedGallery');
                    	elgg_load_css('justified-gallery-on');
                 }
            }
            if ($object_type['subtype'] == 'album')
            {
                if (('yes' == elgg_get_plugin_setting('album_masonry', 'tidypics_plus'))&&(elgg_is_active_plugin('tidypics_plus')))
                {
                    elgg_require_js('isotope');
                    if (elgg_is_active_plugin('hypeLists'))
               		   elgg_require_js('init_isotope/init_isotope-hypeList');
               	    else
               		   elgg_require_js('init_isotope/init_isotope');
                }
            }
            elgg_require_js('tidypics/tidypics');
            elgg_require_js('elgg/lightbox');
            elgg_load_css('lightbox');
            if (elgg_is_active_plugin('tidypics_plus'))
               elgg_require_js('tidypics_plus/tidypics_plus');
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
