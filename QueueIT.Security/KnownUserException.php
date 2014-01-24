<?php namespace QueueIT\Security;
use QueueIT\Security\DefaultKnownUserUrlProvider;

require_once('DefaultKnownUserUrlProvider.php');

class KnownUserException extends \Exception
{
	public function getOriginalUrl()
	{
		$urlProvider = new DefaultKnownUserUrlProvider();
		
		return $urlProvider->getOriginalUrl();
	}
}