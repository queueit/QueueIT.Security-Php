<?php namespace QueueIT\Security;
require_once('IValidateResult.php');

class ValidateResultBase implements IValidateResult
{
	private $queue;
	
	function getQueue()
	{
		return $this->queue;
	}
	
	public function __construct($queue)
	{
		$this->queue = $queue;		
	}
}
?>