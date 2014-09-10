<?php
function currentBaseUrl()
{
	$ssl = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on";

	$baseURL = 'http';
	if ($ssl) {$baseURL .= "s";}
	$baseURL .= "://";
	if ((!$ssl && $_SERVER["SERVER_PORT"] != "80") || ($ssl && $_SERVER["SERVER_PORT"] != "443"))  {
		$baseURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	} else {
		$baseURL .= $_SERVER["SERVER_NAME"];
	}
	return $baseURL;
}
?>