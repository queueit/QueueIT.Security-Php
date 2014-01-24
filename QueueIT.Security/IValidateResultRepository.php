<?php namespace QueueIT\Security;

interface IValidateResultRepository
{
	public function getValidationResult($queue);
	public function setValidationResult($queue, $validationResult);
}

?>