<?php namespace QueueIT\Security;
require_once('IValidateResultRepository.php');
require_once('AcceptedConfirmedResult.php');
require_once('Md5KnownUser.php');
require_once('Queue.php');
require_once('IQueue.php');

class SessionValidateResultRepository implements IValidateResultRepository
{
	private $sessionQueueId = "QueueITAccepted-SDFrts345E-";
	
	public function getValidationResult($queue)
	{		
		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());

		if (!isset($_SESSION[$key]))
			return null;
		
		$result = $_SESSION[$key];
		
		return $result;
		
	}
	
	public function setValidationResult($queue, $validationResult)
	{
		$key = $this->generateKey($queue->getCustomerId(), $queue->getEventId());

		$_SESSION[$key] = $validationResult;
	}
	
	private function generateKey($customerId, $eventId)
	{
		return $this->sessionQueueId . $customerId . "-" . $eventId;
	}
}

?>
