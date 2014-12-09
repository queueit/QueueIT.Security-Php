<?php
	require_once('../QueueIT.Security/SessionValidateResultRepository.php');
	require_once('../QueueIT.Security/SessionValidationController.php');
	require_once('../QueueIT.Security/AcceptedConfirmedResult.php');
	require_once('CurrentBaseUrl.php');
	
	use QueueIT\Security\SessionValidationController, 
		QueueIT\Security\ExpiredValidationException, 
		QueueIT\Security\KnownUserValidationException,
		QueueIT\Security\SessionValidateResultRepository,
		QueueIT\Security\AcceptedConfirmedResult,
		QueueIT\Security\EnqueueResult;

	//session_start();
	//SessionValidationController::configure(null, function () { return new SessionValidateResultRepository();});
	
	try
	{
		$result = SessionValidationController::validateRequestFromConfiguration('advanced');
		
		// Check if user must be enqueued
		if ($result instanceof EnqueueResult)
		{
			header('Location: ' . $result->getRedirectUrl());
		}
		
		// Check if user has been through the queue (will be invoked for every page request after the user has been validated)
		if ($result instanceof AcceptedConfirmedResult)
		{		
			if ($result->isInitialValidationRequest())
			{
				$model = array(
					'CustomerId' => $result->getQueue()->getCustomerId(),
					'EventId' => $result->getQueue()->getEventId(),
					'QueueId' => $result->getKnownUser()->getQueueId(),
					'PlaceInQueue' => $result->getKnownUser()->getPlaceInQueue(),
					'TimeStamp' => $result->getKnownUser()->getTimeStamp());
			}

			$cancelLink = $result->getQueue()->getCancelUrl(currentBaseUrl() . '/cancel.php?eventid=' . $result->getQueue()->getEventId());
		}
	}
	catch (ExpiredValidationException $ex)
	{
		// Known user has has expired - Show error page and use GetCancelUrl to get user back in the queue
		header('Location: error.php?queuename=advanced&t=' . urlencode($ex->getKnownUser()->getOriginalUrl()));
	}
	catch (KnownUserValidationException $ex)
	{
		// Known user is invalid - Show error page and use GetCancelUrl to get user back in the queue
		header('Location: error.php?queuename=advanced&t=' . urlencode($ex->getPrevious()->getOriginalUrl()));
	}

  	//Buffer larger content areas like the main page content
  	ob_start();
?>
    <h3>Setting up the queue:</h3>
    <ol class="round">
        <li class="one">
            <h5>Add configuration section to web config</h5>
            This example uses the queue with name &#39;advanced&#39; from the queueit.ini config file. The 
            entry contains a domain alias which is used when users are redirected to the 
            queue as well as a landing page (split page) allowing users to choose if the 
            want to be redirected to the queue.</li>
        <li class="two">
            <h5>Write controller code</h5>
            Add controller code to the php pages. The advanced.php file
            contains code to extract and persist information about a queue number. The 
            advancedlanding.php file contains code to route the user to the queue and back to 
            the advanced.php page once the user has been through the queue. </li>
    </ol>
	<div><a href="<?php echo $cancelLink; ?>">Cancel queue validation token</a></div>
	<div><a href="expire.php?eventid=<?php echo $result->getQueue()->getEventId(); ?>">Change expiration</a></div>
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Advanced Queue Configuration";
  //Apply the template
  include("master.php");
?>