<?php namespace QueueIT\Security;
use QueueIT\Security\DefaultKnownUserUrlProvider;

require_once('DefaultKnownUserUrlProvider.php');

class KnownUserException extends \Exception
{
	private $originalUrl;
	private $validationUrl;
	
	public function setOriginalUrl($value)
	{
		$this->originalUrl = $value;
	}

	public function setValidationUrl($value)
	{
		$this->validationUrl = $value;
	}
	
	public function getOriginalUrl()
	{		
		return $this->originalUrl;
	}
	
	public function getValidationUrl()
	{

		return $this->validationUrl;
	}
}