<?php namespace QueueIT\Security;
require_once('SessionValidationException.php');

class KnownUserValidationException extends SessionValidationException
{
	public function __construct($previous, $queue)
	{
		parent::__construct($previous->message, $previous, $queue);
	}
}

?>