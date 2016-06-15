<div class="filter_and_sort-panel">
<?php
elgg_require_js('filter_and_sort/admin_ui');
        $supported_list_handlers = array(
                                         'activity' => 'activity',
                                         'blog' => 'blog',
                                         'pages' => 'pages',
                                         'file' => 'file',
                                         'bookmarks' => 'bookmarks',
                                         'thewire' => 'thewire',
                                         'photos' => 'photos',
                                         'pinboards' => 'pinboards',
                                         'discussion' => 'discussion',
                                         'members' => 'members',
                                         'groups' => 'groups',
                                      //   'search' => 'search',
                                       );

        $optional_list_handlers = array('videolist',
                                        'poll');

        // build list of list handlers that can be used
        foreach ($optional_list_handlers as $list_handler)
        {
            if (elgg_is_active_plugin($list_handler))
              $supported_list_handlers[$list_handler] = $list_handler;
        }

        $limit_1 = elgg_get_plugin_setting('limit_1','filter_and_sort');
        if (!$limit_1) {
            $limit_1 = 10;
            elgg_set_plugin_setting('limit_1',$limit_1,'filter_and_sort');
        }
        $limit_2 = elgg_get_plugin_setting('limit_2','filter_and_sort');
        if (!$limit_2) {
            $limit_2 = 20;
            elgg_set_plugin_setting('limit_2',$limit_2,'filter_and_sort');
        }
        $limit_3 = elgg_get_plugin_setting('limit_3','filter_and_sort');
        if (!$limit_3) {
            $limit_3 = 30;
            elgg_set_plugin_setting('limit_3',$limit_3,'filter_and_sort');
        }
        $limit_4 = elgg_get_plugin_setting('limit_4','filter_and_sort');
        if (!$limit_4) {
            $limit_4 = 50;
            elgg_set_plugin_setting('limit_4',$limit_4,'filter_and_sort');
        }
        $limit_5 = elgg_get_plugin_setting('limit_5','filter_and_sort');
        if (!$limit_5) {
            $limit_5 = 100;
            elgg_set_plugin_setting('limit_5',$limit_5,'filter_and_sort');
        }
	$limit_by_date = elgg_get_plugin_setting('limit_by_date','filter_and_sort');
	if (!$limit_by_date) {
		$limit_by_date = 'no';
		elgg_set_plugin_setting('limit_by_date',$limit_by_date,'filter_and_sort');
	}

	$list_handlers = elgg_get_plugin_setting('list_handlers','filter_and_sort');
	if (!$list_handlers) {
		$list_handlers = array('');
	}

  $hypelists_refresh = elgg_get_plugin_setting('hypelists_refresh','filter_and_sort');
  if (!$hypelists_refresh) {
    $hypelists_refresh = '20';
    elgg_set_plugin_setting('hypelists_refresh',$hypelists_refresh,'filter_and_sort');
  }

  $hypelists_lazy = elgg_get_plugin_setting('hypelists_lazy','filter_and_sort');
  if (!$hypelists_lazy) {
    $hypelists_lazy = '6';
    elgg_set_plugin_setting('hypelists_lazy',$hypelists_lazy,'filter_and_sort');
  }


        echo "<h4>";
        echo elgg_echo('filter_and_sort:admin:limits');
        echo "</h4><br/>";
        echo elgg_echo('filter_and_sort:admin:limit_1') . ' ' . elgg_view('input/text', array('name'=>'params[limit_1]', 'value'=>$limit_1));
	echo "<br/>";
        echo elgg_echo('filter_and_sort:admin:limit_2') . ' ' . elgg_view('input/text', array('name'=>'params[limit_2]', 'value'=>$limit_2));
	echo "<br/>";
        echo elgg_echo('filter_and_sort:admin:limit_3') . ' ' . elgg_view('input/text', array('name'=>'params[limit_3]', 'value'=>$limit_3));
	echo "<br/>";
        echo elgg_echo('filter_and_sort:admin:limit_4') . ' ' . elgg_view('input/text', array('name'=>'params[limit_4]', 'value'=>$limit_4));
	echo "<br/>";
        echo elgg_echo('filter_and_sort:admin:limit_5') . ' ' . elgg_view('input/text', array('name'=>'params[limit_5]', 'value'=>$limit_5));
	echo "<br/><br/>";
	echo "<h4>";
	echo elgg_echo('filter_and_sort:admin:list_select');
	echo "</h4><br/>";
        echo elgg_echo('filter_and_sort:admin:list_types');
        echo "<br/><br/>";


	$list_handlers = array_filter(explode(',', $vars["entity"]->list_handlers));

  /*$checked_handlers = array();
  foreach ($supported_list_handlers as $supported_list_handler)
  {
      if (in_array($supported_list_handler, $list_handlers))
      {
          $checked_handlers[] = $supported_list_handler;
      }
  }*/
  $content = '<div class="list_types">';
	$content .= elgg_view('input/checkboxes',array(
                'name'=>'subtypes',
                'value'=>$list_handlers,
                'options'=>$supported_list_handlers,
                'default' => false));
	$content .= '</div>';
	$content .= elgg_view('input/hidden',array(
                'id'=>'list_types_hidden',
                'class'=>'list_types_hidden',
                'name'=>'params[list_handlers]',
                'value'=>$vars["entity"]->list_handlers)
                            );
	echo $content;
	echo "<br/>";

// support for hype lists

if (elgg_is_active_plugin('hypeLists'))
{
  $hypelists_class = 'hypelists-enabled';
}
else {
  $hypelists_class = 'hypelists-disabled';
}
echo '<div id="filter_and_sort-hypelists-panel" class="' . $hypelists_class . '">';
echo "<h4>";
echo elgg_echo('filter_and_sort:admin:hypelists');
echo "</h4><br/>";
echo '<label>' . elgg_echo('filter_and_sort:admin:hypelists:refresh') . ' </label>';
echo elgg_view('input/text', array('value' => $hypelists_refresh,
                                   'name'=>'params[hypelists_refresh]',
                                   'class' => $hypelists_class));
echo '<br/>';
echo '<label>' . elgg_echo('filter_and_sort:admin:hypelists:lazy') . ' </label>';
echo elgg_view('input/text', array('value' => $hypelists_lazy,
                                   'name'=>'params[hypelists_lazy]',
                                   'class' => $hypelists_class));
echo '</div>';
echo '<br/>IF YOU VALUE THIS PLUGIN, CONSIDER MAKING A DONATION AT <a href="www.ureka.org/donation">WWW.UREKA.ORG/DONATION<a/>.';


?>
</div>
