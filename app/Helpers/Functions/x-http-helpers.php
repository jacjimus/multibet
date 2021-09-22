<?php

/*
 * curl request helper
 * opts = [
 * 		'url' => string,
 * 		'post' => bool, //true=POST|false=GET
 * 		'data' => string, //string (post data formatted to Content-Type)
 * 		'headers' => [
 * 			'Content-Type' => 'application/x-www-form-urlencoded',
 * 			...
 * 		],
 * ]
 */
function x_curl_request(array $opts, &$info=null){
	$info = null;
	
	//opts
	if (!x_is_assoc($opts)) throw new Exception('Invalid x_curl_request opts.');
	if (!x_has_key($opts, 'url')) throw new Exception('Undefined x_curl_request url.');
	if (!x_is_url($url = x_tstr($opts['url']))) throw new Exception('Invalid x_curl_request url.');
	
	//vars
	$headers = isset($opts['headers']) ? $opts['headers'] : [];
	$post = isset($opts['post']) ? !!$opts['post'] : [];
	$data = isset($opts['data']) ? $opts['data'] : '';
	
	//curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	if ($post){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	if (x_is_assoc($headers)){
		$header = [];
		foreach($headers as $key => $val){
			$header[] = "$key: $val";
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$body = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	$status = $info['http_code'];
	$content_type = $info['content_type'];
	$json = null;
	if (strpos(strtolower($content_type), 'application/json') !== false){
		$json = json_decode($body, 1);
	}
	
	//result
	return [
		'body' => $body,
		'json' => $json,
		'status' => $status,
	];
}
