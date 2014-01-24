<?php namespace QueueIT\Security;
require_once('IKnownUserUrlProvider.php');
require_once('CurrentUrl.php');

class DefaultKnownUserUrlProvider implements IKnownUserUrlProvider
{
	public function getUrl()
	{
		return currentUrl();
	}
	
	public function getQueueId($queryStringPrefix)
	{
		$key = $queryStringPrefix . "q";
		
		return $this->getVar($key);
	}
	
	public function getPlaceInQueue($queryStringPrefix)
	{
		$key = $queryStringPrefix . "p";
		
		return $this->getVar($key);
	}
	
	public function getTimeStamp($queryStringPrefix)
	{
		$key = $queryStringPrefix . "ts";
		
		return $this->getVar($key);
	}
	
	public function getEventId($queryStringPrefix)
	{
		$key = $queryStringPrefix . "e";
		
		return $this->getVar($key);
	}
	
	public function getCustomerId($queryStringPrefix)
	{
		$key = $queryStringPrefix . "c";
		
		return $this->getVar($key);
	}

	public function getRedirectType($queryStringPrefix)
	{
		$key = $queryStringPrefix . "rt";
	
		return $this->getVar($key);
	}
	public function getOriginalUrl($queryStringPrefix)
	{
		$url = $this->getUrl();
		
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "q=[^&]*&?)/i", "$1", $url);
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "p=[^&]*&?)/i", "$1", $url);
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "ts=[^&]*&?)/i", "$1", $url);
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "c=[^&]*&?)/i", "$1", $url);
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "e=[^&]*&?)/i", "$1", $url);
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "rt=[^&]*&?)/i", "$1", $url);
		$url = preg_replace("/([\?&])(" . $queryStringPrefix . "h=[^&]*&?)/i", "$1", $url);
				$url = preg_replace("/[\?&]$/", "", $url);		
		return $url;
	}
	
	private function getVar($key)
	{
		if (!isset($_GET[$key]))
			return null;
	
		return $_GET[$key];
	}
}
?>