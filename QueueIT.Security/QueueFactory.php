<?php namespace QueueIT\Security;
require_once('Queue.php');
require_once('ConfigurationErrorsException.php');
require_once('CurrentUrl.php');

use \InvalidArgumentException;
class QueueFactory
{
	private static $domain;

	static function reset()
	{
		global $domain;

		$domain = "queue-it.net";
	}

	static function configure($hostDomain = null)
	{
		global $domain;

		if ($hostDomain != null)
			$domain = $hostDomain;
	}

	static function createQueueFromConfiguration($queueName = 'default')
	{
		if ($queueName == null)
			throw new InvalidArgumentException('Queue Name cannot be null or empty');

		$iniFileName = $_SERVER['DOCUMENT_ROOT'] . "\queueit.ini";

		if (!file_exists($iniFileName))
			throw new ConfigurationErrorsException('Configuration file "' . $iniFileName . '" is missing');

		$settings_array = parse_ini_file($iniFileName, true);

		if (!$settings_array)
			throw new ConfigurationErrorsException('Configuration file "' . $iniFileName . '" is invalid');

		$queue = $settings_array[$queueName];

		if ($queue == null)
			throw new ConfigurationErrorsException('Configuration for Queue Name "' . $queueName . '" in file "' . $iniFileName . '" is missing from configuration file');

		return QueueFactory::instantiateQueue(
				$queue['customerId'],
				$queue['eventId'],
				isset($queue['domainAlias']) ? $queue['domainAlias'] : null,
				isset($queue['landingPage']) ? $queue['landingPage'] : null,
				isset($queue['useSsl']) && $queue['useSsl'] == 1 ? true : false,
				isset($queue['includeTargetUrl']) && $queue['includeTargetUrl'] == 1 ? true : false,
				isset($queue['language']) ? $queue['language'] : null,
				isset($queue['layoutName']) ? $queue['layoutName'] : null);
	}

	static function createQueue($customerId, $eventId)
	{
		return QueueFactory::instantiateQueue($customerId, $eventId, null, null, false, false, null, null);
	}

	private static function instantiateQueue($customerId, $eventId, $domainAlias, $landingPage, $sslEnabled, $includeTargetUrl, $language, $layoutName)
	{
		global $domain;

		$customerId = trim(strtolower($customerId));
		$eventId = trim(strtolower($eventId));

		if ($domainAlias == null)
			$domainAlias = $customerId . '.' . $domain;

		return new Queue(
				$customerId,
				$eventId,
				$domainAlias,
				$landingPage,
				$sslEnabled,
				$includeTargetUrl,
				$language,
				$layoutName);
	}
}

QueueFactory::reset(true);

?>
