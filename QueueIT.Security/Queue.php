<?php namespace QueueIT\Security;
require_once('IQueue.php');
require_once('Version.php');

class Queue implements IQueue
{
	private $customerId;
	private $eventId;
	private $safetynetImageUrl;
	private $defaultDomainAlias;
	private $defaultQueueUrl;
	private $defaultCancelUrl;
	private $defaultLandingPageUrl;
	private $defaultIncludeTargetUrl;
	private $defaultSslEnabled;
	private $defaultLanguage;
	private $defaultLayoutName;

	public function __construct($customerId, $eventId, $domainAlias, $landingPage, $sslEnabled, $includeTargetUrl, $language, $layoutName)
	{
		$this->customerId = $customerId;
		$this->eventId = $eventId;
		$this->defaultQueueUrl = $this->generateQueueUrl($sslEnabled, $domainAlias, $language, $layoutName);
		$this->defaultCancelUrl = $this->generateCancelUrl($sslEnabled, $domainAlias, $language, $layoutName);
		$this->safetynetImageUrl = '//' . $domainAlias . '/queue/' . $customerId . '/' . $eventId . '/safetynetimage';
		$this->defaultDomainAlias = $domainAlias;
		$this->defaultLandingPageUrl = $landingPage;
		$this->defaultSslEnabled = $sslEnabled;
		$this->defaultIncludeTargetUrl = $includeTargetUrl;
		$this->defaultLanguage = $language;
		$this->defaultLayoutName = $layoutName;
	}

	public function getEventId()
	{
		return $this->eventId;
	}
	public function getCustomerId()
	{
		return $this->customerId;
	}

	public function getQueueUrl($targetUrl = null, $sslEnabled = null, $domainAlias = null, $language = null, $layoutName = null)
	{
		$queueUrl = $this->getQueueUrlWithoutTarget($sslEnabled, $domainAlias, $language, $layoutName);

		$queueUrl = $this->includeTargetUrl($targetUrl, $queueUrl);

		return $queueUrl;
	}
	public function getCancelUrl($landingPage = null, $queueId = null, $sslEnabled = null, $domainAlias = null)
	{
		$url = $domainAlias != null
			? $this->generateCancelUrl($sslEnabled, $domainAlias)
			: $this->defaultCancelUrl;

		if ($sslEnabled)
		{
			$url = str_replace('http://', 'https://', $url);
		}
		else if ($sslEnabled != null)
		{
			$url = str_replace('https://', 'http://', $url);
		}

		if ($queueId != null)
			$url = $url . '&q=' . $queueId;

		if ($landingPage != null)
			$url = $url . '&r=' . urlencode($landingPage);

		if ($landingPage == null && $this->defaultLandingPageUrl != null)
			$url = $url . '&r=' . urlencode($this->defaultLandingPageUrl);

		return $url;
	}

	public function getLandingPageUrl($targetUrl = null)
	{
		if ($this->defaultLandingPageUrl == null)
			return null;

		if (!$targetUrl && !$this->defaultIncludeTargetUrl)
			return $this->defaultLandingPageUrl;

		return $this->includeTargetUrl($targetUrl, $this->defaultLandingPageUrl);
	}

	private function getQueueUrlWithoutTarget($sslEnabled, $domainAlias, $language, $layoutName)
	{
		$url = $domainAlias != null || $language != null || $layoutName != null
			? $this->generateQueueUrl($sslEnabled, $domainAlias, $language, $layoutName)
			: $this->defaultQueueUrl;

		if ($sslEnabled)
		{
			$url = str_replace('http://', 'https://', $url);
		}
		else if ($sslEnabled != null)
		{
			$url = str_replace('https://', 'http://', $url);
		}

		return $url;
	}

	private function includeTargetUrl($targetUrl, $queueUrl)
	{
		$queueUrl = preg_replace("/(&?t=[^&]*&?)/i", "", $queueUrl);

		if ($targetUrl == null)
			$targetUrl = $this->defaultIncludeTargetUrl;

		if (is_bool($targetUrl) && $targetUrl == true)
			$targetUrl = KnownUserFactory::getKnownUserUrlProvider()->getUrl();

		if (is_bool($targetUrl) && $targetUrl == false)
			return $queueUrl;

		if (!strpos($queueUrl, '?'))
			return $queueUrl . '?t=' . urlencode($targetUrl);

		return $queueUrl . '&t=' . urlencode($targetUrl);
	}

	private function generateQueueUrl($sslEnabled, $domainAlias, $language, $layoutName)
	{
		if ($domainAlias == null)
			$domainAlias = $this->defaultDomainAlias;

		$protocol = $sslEnabled ? 'https://' : 'http://';

		$url = $protocol . $domainAlias . '/?c=' . $this->customerId . '&e=' . $this->eventId;

		if ($language != null)
			$url = $url . '&cid=' . $language;

		if ($layoutName != null)
			$url = $url . '&l=' . urlencode($layoutName);

		$url = $url . '&ver=p' . constant('Version');

		return $url;
	}

	private function generateCancelUrl($sslEnabled, $domainAlias)
	{
		if ($domainAlias == null)
			$domainAlias = $this->defaultDomainAlias;

		$protocol = $sslEnabled ? 'https://' : 'http://';

		return $protocol . $domainAlias . '/cancel.aspx?c=' . $this->customerId . '&e=' . $this->eventId;
	}
}

?>
