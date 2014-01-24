<?php namespace QueueIT\Security;
require_once('ValidateResultBase.php');

class EnqueueResult extends ValidateResultBase
{
	private $redirectUrl;
	
	function getRedirectUrl()
	{
		return $this->redirectUrl;
	}
	
	public function __construct($queue, $redirectUrl)
	{
		parent::__construct($queue);
		
		$this->redirectUrl = $redirectUrl;
	}
}
?>