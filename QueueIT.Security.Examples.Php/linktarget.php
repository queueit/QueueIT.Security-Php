<?php
	require_once('../QueueIT.Security/KnownUserFactory.php');
	
	use QueueIT\Security\KnownUserFactory, QueueIT\Security\KnownUserException;
	
	
	$urlProvider = new QueueIT\Security\DefaultKnownUserUrlProvider();
	
	function getLinkUrl()
	{
		$ssl = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on";
	
		$pageURL = 'http';
		if ($ssl) {$pageURL .= "s";}
		$pageURL .= "://";
		if ((!$ssl && $_SERVER["SERVER_PORT"] != "80") || ($ssl && $_SERVER["SERVER_PORT"] != "443"))  {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]. '/link.php';
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"]. '/link.php';
		}
		return $pageURL;
	}
	
	try
	{
		$knownUser = KnownUserFactory::verifyMd5Hash();
	
		if ($knownUser == null)
			header('Location: link.php');
				
		if ($knownUser->getTimeStamp()->getTimestamp() < (time() - 180))
			header('Location: link.php');
	}
	catch (KnownUserException $ex)
	{
		header('Location: error.php?queuename=link&t=' . urlencode(getLinkUrl()));
	}
	
		
  //Buffer larger content areas like the main page content
  ob_start();
?>
	<h3>Setting up the queue:</h3>
    <ol class="round">
        <li class="one">
            <h5>Write Known User code</h5>
            Add Known User code to the php page. The target php 
            page contains code to extract and persist information about a queue number. </li>
    </ol>

<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Link";
  //Apply the template
  include("master.php");
?>