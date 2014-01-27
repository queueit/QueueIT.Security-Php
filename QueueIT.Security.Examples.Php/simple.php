<?php
	require_once('../QueueIT.Security/SessionValidationController.php');
		
	use QueueIT\Security\SessionValidationController, 
		QueueIT\Security\ExpiredValidationException, 
		QueueIT\Security\KnownUserValidationException,
		QueueIT\Security\EnqueueResult;

	session_start();

	try
	{
		$result = SessionValidationController::validateRequestFromConfiguration();
		
		// Check if user must be enqueued
		if ($result instanceof EnqueueResult)
		{
			header('Location: ' . $result->getRedirectUrl());
		}
	}
	catch (ExpiredValidationException $ex)
	{
		// Known user has has expired - Show error page and use GetCancelUrl to get user back in the queue
		header('Location: error.php?queuename=default&t=' . urlencode($ex->getKnownUser()->getOriginalUrl()));
	}
	catch (KnownUserValidationException $ex)
	{
		// Known user is invalid - Show error page and use GetCancelUrl to get user back in the queue
		header('Location: error.php?queuename=default&t=' + urlencode($ex->previous->getOriginalUrl()));
	}

  	//Buffer larger content areas like the main page content
  	ob_start();
?>
    <h3>Setting up the queue:</h3>
    <ol class="round">
        <li class="one">
            <h5>Add configuration section to queueit.ini config file</h5>
            This example uses the queue with name &#39;default&#39; from the web config file. The 
            entry contains the minimum required attributes.</li>
        <li class="two">
            <h5>Write controller code</h5>
            Add controller code to the php files. The simple.php file 
            contains the minimum code required to set up the queue.</li>
    </ol>
	
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Simple";
  //Apply the template
  include("master.php");
?>