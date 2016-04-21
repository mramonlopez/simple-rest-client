<?php
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	require_once('RESTcontroller.php');
	
	$params = array();
	$uri= explode('?', $_SERVER['REQUEST_URI']);
	$parts = explode('/', $uri[0]);
	
	$request_method = strtolower($_SERVER['REQUEST_METHOD']);
	$content_type = (strpos($_SERVER['HTTP_CONTENT_TYPE'], 'json')) ? 'json' : 'xml';

	$data = array();
	if ($request_method == "post" || $request_method == "put") {
		$values = file_get_contents('php://input');
		parse_str($values, $data);
	} else {
		$data = $_GET;
	}
	
	if (isset($data["signature"])) {
		$signature = $data["signature"];
		unset($data["signature"]);
		
		if (1==1 || $signature == GetSignature($data)) {	
			if(count($parts)>1) {
				$classname = $parts[1]."Controller";	
				if (class_exists($classname)) {
					$object = new $classname();
					$object->ID = isset($parts[2])?$parts[2]:NULL;
					$object->processRequest($request_method, $content_type, $data);
				} else {
					RESTcontroller::sendResponse(404, '', $content_type);
				}
			}
		} else {
			RESTcontroller::sendResponse(401, '', $content_type);
		}
	} else {
		RESTcontroller::sendResponse(401, GetSignature($data), $content_type);
	}
	
	function GetSignature($data) {
		$signature = "";
		$secret = "Put here your secret phrase";
		
		// Short by name
		ksort($data);
		
		// Convert to JSON
		$json = json_encode($data);
		
		// Get message signature
		$signature = hash_hmac("sha256", $json, $secret);
		
		return $signature;
	}
?>
