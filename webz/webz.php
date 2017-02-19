<?php
date_default_timezone_set("Europe/Berlin");

require_once 'httpserver.php';

echo "WebZHTTP/1.0\n";
if(!file_exists("webz.ini")){
	echo "WebZHTTP error: unable to load config file! Aborting...\n";
	die();
}
$ini = parse_ini_file("webz.ini");
if(!isset($ini["webz_port"]) or !isset($ini["webz_addr"]) or !isset($ini["allow_dir_listening"]) or !isset($ini["webz_www"])){
	echo "WebZHTTP error: bad config file! Aborting...\n";
	die();
}
if(!is_dir(trim($ini["webz_www"])) or !is_writable(trim($ini["webz_www"])) or !is_readable(trim($ini["webz_www"]))){
	echo "WebZHTTP error: www folder is unaccessable! Aborting...\n";
}
class WebzServer extends HTTPServer
{
    function __construct()
    {
		$ini = parse_ini_file("webz.ini");
		$webz_port = trim($ini["webz_port"]);
		$webz_addr = trim($ini["webz_addr"]);
        parent::__construct(array(
            'port' => $webz_port,
			'addr' => $webz_addr,
        ));
    }

    function route_request($request)
    {
        $uri = $request->uri;	
        
	$ini = parse_ini_file("webz.ini");
	$webz_www = trim($ini["webz_www"]);
        $doc_root = (strtolower($webz_www)=='[default]')?realpath('www'):$webz_www;
        
        if (preg_match('#/$#', $uri))
        {          
	               if(file_exists($doc_root.$uri."/index.php")){
					   				   $uri .= "index.php";
				   }
        }
        
        if (preg_match('#\.php$#', $uri))
        {
            return $this->get_php_response($request, "$doc_root$uri");
        }
        else
        {
            return $this->get_static_response($request, "$doc_root$uri");
        }                
    }        
}


$server = new WebzServer();
$server->run_forever();
