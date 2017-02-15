<?php
	require_once('../QueueIT.Security/SessionValidationController.php');
	require_once('CurrentBaseUrl.php');
	
	use QueueIT\Security\SessionValidationController, 
		QueueIT\Security\KnownUserFactory,
		QueueIT\Security\ExpiredValidationException, 
		QueueIT\Security\KnownUserValidationException,
		QueueIT\Security\AcceptedConfirmedResult,
		QueueIT\Security\EnqueueResult;

	KnownUserFactory::configure('a774b1e2-8da7-4d51-b1a9-7647147bb13bace77210-a488-4b6f-afc9-8ba94551a7d7', null, 'qit');
	
	try
	{
		$result = SessionValidationController::validateRequest('ticketania', 'codeonly', true);
		
		// Check if user must be enqueued
		if ($result instanceof EnqueueResult)
		{
			header('Location: ' . $result->getRedirectUrl());
		}
		if ($result instanceof AcceptedConfirmedResult)
		{
			$cancelLink = $result->getQueue()->getCancelUrl(currentBaseUrl() . '/cancel.php?eventid=' . $result->getQueue()->getEventId());
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
		header('Location: error.php?queuename=default&t=' . urlencode($ex->getPrevious()->getOriginalUrl()));
	}

  	//Buffer larger content areas like the main page content
  	ob_start();
?>
    <h3>Setting up the queue:</h3>
    <ol class="round">
        <li class="one">
            <h5>Add configuration using code</h5>
            All configuration that is supported using the configuration section is also 
            supported in code. In this example it is configured in the
            &#39;codeonly.php&#39; file.</li>
        <li class="two">
            <h5>Write controller code</h5>
            Add controller code to the php files. The codeonly.php file 
            configures the queue with Customer ID and Event ID and thereby bypasses the 
            configuration section.</li>
    </ol>
	<div><a href="<?php echo $cancelLink; ?>">Cancel queue validation token</a></div>
	<div><a href="expire.php?eventid=<?php echo $result->getQueue()->getEventId(); ?>">Change expiration</a></div>
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Code Only Queue Configuration";
  //Apply the template
  include("master.php");
?>
