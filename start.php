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
	
	$params = restful_get_params($segments);

	$results = call_user_func($info->callback, $params);
	echo var_dump($results);
}

function restful_get_params($input) {
	$param[0] = array_shift($input);
	$param[1] = array_shift($input);
	$param[1] = substr($param[1], 1, strlen($param[1]));
	return $param;
}

function restful_get_ws_object($segments) {
	global $CONFIG;

	$method = $_SERVER['REQUEST_METHOD'];
	
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
	$first = $first ? substr ( $address , 0, $first ) : $address;

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
	$username = $params[1];
	$user = get_user_by_username($username);
	
	if(!$user) {
		$posts['success']= false;
		$posts['error'] = "Invalid Username";
		return $post;
	}
	
	$posts = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'thewire',
		'owner_guid' => $user->getGUID(),
	));
	$post['success']= true;
	$post['wire']= $posts->attributes;
	
	return $post;
}