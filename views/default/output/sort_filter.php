<?php
/************
 * filter_and_sort - elgg plugin
 * outputs a button to allow active filters to be cancelled
 * @Author - ura soul
 * @website - https://www.ureka.org
 *
 *******************/

if ((elgg_in_context('discussion'))||(elgg_in_context('discussion-groups')))
{
    $comment_name = 'replies';
}
else
{
    $comment_name = 'comments';
}
switch($vars['name'])
{
  case 'contain':
  {
    $guid = (int) $vars['value'];

    if ($entity = get_entity($guid))
        $name = elgg_echo('group') . ': ' . elgg_get_excerpt($entity->name,25);
    else
        $name = elgg_echo('sort:' . $vars['value']);
    break;
  }
  case 'sort':
  {
    // replace 'comments' string with 'replies' for discussions
    if (substr($vars['value'],0,8) == 'comments')
    {
        $name = elgg_echo('sort:' . $comment_name . '_' .substr($vars['value'],(strlen($vars['value'])-1),1));
    }
    else
        $name = elgg_echo('sort:' . $vars['value']);
    break;
  }
  case 'timing-from':
  case 'timing-to':
  {
        $name = elgg_echo('sort:' . timing);
    break;
  }
  default:
  {
      $name = elgg_echo('sort:' . $vars['value']);
      break;
  }
}

$output = '<div data="' . $vars['name'] . '" class="elgg-active-sort-filter">' . $name . elgg_view_icon('delete') . '</div>';
echo $output;
