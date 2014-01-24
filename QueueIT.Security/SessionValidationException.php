<?php namespace QueueIT\Security;
class SessionValidationException extends \Exception
{
	private $queue;
	
	function getQueue()
	{
		return $this->queue;
	}
	
	function __construct($message, $previous, $queue)
	{
		parent::__construct($message, null, $previous);
		
		$this->queue = $queue;
	}		
}
	
?>