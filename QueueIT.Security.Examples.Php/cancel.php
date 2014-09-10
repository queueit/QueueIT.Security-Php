<?php
	require_once('../QueueIT.Security/SessionValidationController.php');
			
	use QueueIT\Security\SessionValidationController, 
		QueueIT\Security\ExpiredValidationException, 
		QueueIT\Security\SessionValidateResultRepository,
		QueueIT\Security\KnownUserValidationException,
		QueueIT\Security\AcceptedConfirmedResult;

	//session_start();
	//SessionValidationController::configure(null, function () { return new SessionValidateResultRepository();});
	
	try
	{
		$result = SessionValidationController::validateRequest('ticketania', $_GET['eventid']);
		
		if ($result instanceof AcceptedConfirmedResult)
		{
			$result->cancel();
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
    <h3>Cancel validation result</h3>
    <p>Your validation result has been canceled</p>
	
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Cancel";
  //Apply the template
  include("master.php");
?>