<?php namespace QueueIT\Security;
require_once('KnownUserFactory.php');
require_once('QueueFactory.php');
require_once('AcceptedConfirmedResult.php');
require_once('EnqueueResult.php');
require_once('KnownUserValidationException.php');
require_once('ExpiredValidationException.php');
require_once('InvalidKnownUserUrlException.php');
require_once('InvalidKnownUserHashException.php');
require_once('CookieValidateResultRepository.php');


class SessionValidationController
{
	private static $defaultTicketExpiration;
	private static $resultProviderFactory;
	
	static function reset($loadConfiguration = false)
	{
		global $resultProviderFactory, $defaultTicketExpiration;
		
		$defaultTicketExpiration = 180;
		$resultProviderFactory = function () { return new CookieValidateResultRepository(); };
		
		if (!$loadConfiguration)
			return;
		
		$iniFileName = $_SERVER['DOCUMENT_ROOT'] . "\queueit.ini";
		
		if (!file_exists($iniFileName))
			return;
		
		$settings_array = parse_ini_file($iniFileName, true);
		
		if (!$settings_array)
			return;
		
		$settings = $settings_array['settings'];
		
		if ($settings == null)
			return;
		
		if (isset($settings['ticketExpiration']) && $settings['ticketExpiration'] != null && is_numeric($settings['ticketExpiration']))
			$defaultTicketExpiration = intval($settings['ticketExpiration']);
	}
	
	static function configure($ticketExpiration = null, $validationResultProviderFactory = null)
	{
		global $resultProviderFactory, $defaultTicketExpiration;
		
		if ($ticketExpiration != null)
			$defaultTicketExpiration = $ticketExpiration;
		if ($validationResultProviderFactory != null)
			$resultProviderFactory = $validationResultProviderFactory;
	}
	
	// Never call request validation from error handling pages (e.g. error.php) which will cause users to get looped arround.
	static function validateRequestFromConfiguration($queueName = 'default', $includeTargetUrl = null, $sslEnabled = null, $domainAlias = null, $language = null, $layoutName = null)
	{
		if ($queueName == null)
			throw new InvalidArgumentException("Queue name is required");	
		
		$queue = QueueFactory::createQueueFromConfiguration($queueName);
		
		return SessionValidationController::validateRequestFromQueue($queue, $includeTargetUrl, $sslEnabled, $domainAlias, $language, $layoutName);
	}

	// Never call request validation from error handling pages (e.g. error.php) which will cause users to get looped arround.
	static function validateRequest($customerId, $eventId, $includeTargetUrl = null, $sslEnabled = null, $domainAlias = null, $language = null, $layoutName = null)
	{
		if ($customerId == null)
			throw new InvalidArgumentException("Customer ID is required");
		if ($eventId == null)
			throw new InvalidArgumentException("Event ID is required");
		
		$queue = QueueFactory::createQueue($customerId, $eventId);
		
		return SessionValidationController::validateRequestFromQueue($queue, $includeTargetUrl, $sslEnabled, $domainAlias, $language, $layoutName);
	}
	
	private static function validateRequestFromQueue($queue, $includeTargetUrl = null, $sslEnabled = null, $domainAlias = null, $language = null, $layoutName = null)
	{
		global $resultProviderFactory;
	
		$sessionObject = $resultProviderFactory()->getValidationResult($queue);
			
		if ($sessionObject != null)
		{			
			if ($sessionObject instanceof AcceptedConfirmedResult)
			{
				return new AcceptedConfirmedResult($queue, $sessionObject->getKnownUser(), false);
			}			
			
			return $sessionObject;
		}
			
		try {
			$knownUser = KnownUserFactory::verifyMd5Hash();
			
			if ($knownUser == null)
			{
				$landingPage = $queue->getLandingPageUrl($includeTargetUrl);
				
				if ($landingPage != null)
					return new EnqueueResult($queue, $landingPage);
				
				return new EnqueueResult($queue, $queue->GetQueueUrl($includeTargetUrl, $sslEnabled, $domainAlias, $language, $layoutName));
			}
						
			if ($knownUser->getTimeStamp()->getTimestamp() < (time() - 180))
				throw new ExpiredValidationException($queue, $knownUser);
				
			$result = new AcceptedConfirmedResult($queue, $knownUser, true);
			$resultProviderFactory()->setValidationResult($queue, $result);
			
			return $result;
		}
		catch (InvalidKnownUserUrlException $ex)
		{
			throw new KnownUserValidationException($ex, $queue);
		}
		catch (InvalidKnownUserHashException $ex)
		{
			throw new KnownUserValidationException($ex, $queue);
		}		
	}
	
	static function setExpiration($validationResult, $expirationTime)
	{
		global $resultProviderFactory;
	
		$sessionObject = $resultProviderFactory()->setValidationResult($validationResult->getQueue(), $validationResult, $expirationTime);
	}
	
	static function cancel($validationResult)
	{
		global $resultProviderFactory;
		
		$sessionObject = $resultProviderFactory()->cancel($validationResult->getQueue(), $validationResult);
	}
}

SessionValidationController::reset(true);
