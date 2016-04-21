<?php
abstract class RESTcontroller
{
	protected $classname;
	protected $data;
	public $ID;
	
	abstract function post(&$error);
	abstract function put(&$error);
	abstract function get(&$error);
	abstract function delete(&$error);
	
	public function processRequest($request_method, $content_type, $data)
	{	
		$this->data = $data;
		$classname = isset($this->classname)?$this->classname:get_class($this);
		
		switch($request_method) {		
		case 'get':
			$error = "";
			$result = $this->get($error);
	
			if ($result) {
				if ($content_type == 'json')
				{
					 // Conjunto de conjunto de resultados		
					if (!array_key_exists("ID", $result)) {
						$result = array("ArrayOf$classname" => $result);
					}
					RESTcontroller::sendResponse(200, json_encode($result), 'application/json');
				}
				else
				{	
					// Consulta de un sólo elemento			
					if (array_key_exists("ID", $result)) {
						$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><$classname/>");
						RESTcontroller::array_to_xml($result, $xml);
					} else { // Conjunto de conjunto de resultados
						$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><ArrayOf$classname/>");
						foreach($result as $object) {
	        				$subnode = $xml->addChild("$classname");
	        				RESTcontroller::array_to_xml($object, $subnode);
						}					
					}
					
					RESTcontroller::sendResponse(200, $xml->asXML(), 'application/xml');
				}	
			} else {
				RESTcontroller::sendError(500, $error, $content_type);
			}
			break;	
		case 'post':
		case 'put':	
			$error = "";
			if ($request_method == 'post') {
				$result = $this->post($error);
			} else {
				$result = $this->put($error);
			}
				
			if($result) {
				if ($content_type == 'json')
				{
					RESTcontroller::sendResponse(200, json_encode($result), 'application/json');
				}
				else 
				{
					$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><$classname/>");
					RESTcontroller::array_to_xml($result, $xml);					
					RESTcontroller::sendResponse(200, $xml->asXML(), 'application/xml');
				}
			} else {
				RESTcontroller::sendError(500, $error, $content_type);
			}
			break;
		case 'delete':
			if($this->delete($error)) {
				if ($content_type == 'json')
				{
					RESTcontroller::sendResponse(200, json_encode(array("message" => "OK")), 'application/json');
				}
				else 
				{						
					RESTcontroller::sendResponse(200, "<?xml version=\"1.0\"?><message>OK</message>", 'application/xml');
				}
				RESTcontroller::sendResponse(200);
			} else {
				RESTcontroller::sendError(500, $error, $content_type);
			}
			break;
		default:
			RESTcontroller::sendError(500, "Unkwon request method", $content_type);
		}

		return $return_obj;
	}

	public static function sendResponse($status = 200, $body = '', $content_type = 'xml')
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . RESTcontroller::getStatusCodeMessage($status);
		// set the status
		header($status_header);
		// set the content type
		header('Content-type: application/' . $content_type);

		// pages with body are easy
		if($body != '')
		{
			// send the body
			echo $body;
			exit;
		}
		// we need to create the body if none is passed
		else
		{
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
				default:
					$message = '';
			}
			
			if ($content_type == 'json') {			
				$body = "{message:'$message'}";
			}
			else {
				$body = "<message>$message</message>";
			}

			echo $body;
			exit;
		}
	}


	protected static function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}
	
	protected static function sendError($status_code, $error_message, $content_type) {
		$message = array("error" => $error_message);
				
		if ($content_type == 'json')
		{
			RESTcontroller::sendResponse($status_code, json_encode($message), 'application/json');
		}
		else
		{		
			$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><message/>");
			RESTcontroller::array_to_xml($message, $xml);		
			RESTcontroller::sendResponse($status_code, $xml->asXML(), 'application/xml');
		}
	}
	
	// function definition to convert array to xml
    protected static function array_to_xml($data, &$xml) {
	    foreach($data as $key => $value) {
	        if(is_array($value)) {
	            if(!is_numeric($key)){
	                $subnode = $xml->addChild("$key");
	                RESTcontroller::array_to_xml($value, $subnode);
	            }
	            else{
	                RESTcontroller::array_to_xml($value, $xml);
	            }
	        }
	        else {
	            $xml->addChild("$key","$value");
	        }
	    }
	}

}
?>