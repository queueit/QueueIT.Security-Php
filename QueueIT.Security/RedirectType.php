<?php namespace QueueIT\Security;

class RedirectType
{
	const Unknown = 0;
	const Queue = 1;
	const Safetynet = 2;
	const AfterEvent = 3;
	const Disabled = 4;
	const DirectLink = 5;
	const Idle = 6;
	  
	static function FromString($value)
	{
		if ($value == null)
			return RedirectType::Unknown;
		if (strtolower($value) == 'queue')
			return RedirectType::Queue;
		if (strtolower($value) == 'safetynet')
			return RedirectType::Safetynet;
		if (strtolower($value) == 'afterevent')
			return RedirectType::AfterEvent;
		if (strtolower($value) == 'disabled')
			return RedirectType::Disabled;
		if (strtolower($value) == 'directlink')
			return RedirectType::DirectLink;
		if (strtolower($value) == 'idle')
			return RedirectType::Idle;	
		
		return RedirectType::Unknown;
	}
}
?>