<?php
/**
 * Content filter for river
 *
 * @uses $vars[]
 */
if (elgg_is_logged_in())
{
    // create selection array
    $options = array();
    $options['type=all'] = elgg_echo('river:select', array(elgg_echo('all')));
    $registered_entities = elgg_get_config('registered_entities');
    // remove categories from type selector
    if (in_array('hjcategory', $registered_entities['object']))
    {
      unset($registered_entities['object'][array_search('hjcategory',$registered_entities['object'])]);
    }

    if (!empty($registered_entities)) {
    	foreach ($registered_entities as $type => $subtypes) {
    		// subtype will always be an array.
    		if (!count($subtypes)) {
    			$label = elgg_echo('river:select', array(elgg_echo("item:$type")));
    			$options["type=$type"] = $label;
    		} else {
    			foreach ($subtypes as $subtype) {
    				$label = elgg_echo('river:select', array(elgg_echo("item:$type:$subtype")));
    				$options["type=$type&subtype=$subtype"] = $label;
    			}
    		}
    	}
    }

    $params = array(
    	'id' => 'elgg-river-selector',
    	'options_values' => $options,
    );
    $selector = $vars['selector'];
    if ($selector) {
    	$params['value'] = $selector;
    }
    echo elgg_view('input/select', $params);
}
