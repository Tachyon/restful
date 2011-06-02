<?php
/**
 * Elgg RESTful webservices
 *
 * Example: http://elgg.org/api/v1/json/thewire/:username/
 *
 */

elgg_register_event_handler('init', 'system', 'restful_init', 1);

function restful_init() {
	elgg_register_page_handler('api', 'restful_ws_route');

	elgg_register_ws_method('thewire/:username', 'GET', 'restful_get_latest_wire_posts');
}

function restful_ws_route($segments) {
	$version = array_shift($segments);
	$viewtype = array_shift($segments);
	
	elgg_set_viewtype($viewtype);

	$info = restful_get_ws_object($segments);

	$params = restful_get_params();

	$results = call_user_func($info->callback, $params);
	
}

function restful_get_ws_object($segments) {
	global $CONFIG;

	$method = $_SERVER['METHOD'];

	$type = array_shift($segments);

	if (!isset($CONFIG->ws[$method][$type])) {
		return;
	}

	$possibilities = $CONFIG->ws[$method][$type];
	$num_matches = 0;
	foreach ($possibilities as $object) {

	}

	return $possibilities[0];
}

function elgg_register_ws_method($address, $method, $callback) {
	global $CONFIG;

	$method = strtoupper($method);

	if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE'))) {
		return false;
	}
	
	$first = strpos($address, '/');
	$first = $first ? $first : strlen($address);

	if (!isset($CONFIG->ws)) {
		$CONFIG->ws = array(
			'GET' => array(),
			'POST' => array(),
			'PUT' => array(),
			'DELETE' => array(),
		);
	}

	if (!isset($CONFIG->ws[$method][$first])) {
		$CONFIG->ws[$method][$first] = array();
	}

	$info = new stdClass();
	$info->address = $address;
	$info->callback = $callback;

	$CONFIG->ws[$method][$first][] = $info;

	return true;
}

function restful_get_latest_wire_posts($params) {
	extract($params);

	$user = get_user_by_username($username);
	$posts = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'thewire',
		'owner_guid' => $user->getGUID(),
	));

	return $posts;
}
