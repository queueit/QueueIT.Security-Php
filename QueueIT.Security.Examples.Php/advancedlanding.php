<?php
	require_once('../QueueIT.Security/QueueFactory.php');
	
	use QueueIT\Security\QueueFactory;
	
	$queue = QueueFactory::CreateQueueFromConfiguration('advanced');
	
  	//Buffer larger content areas like the main page content
  	ob_start();
?>

<a href="index.php">Back To Home</a> <a href="<?php echo $queue->getQueueUrl($_GET['t']); ?>">Go to queue</a>

	
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Advanced Queue Landing Page";
  //Apply the template
  include("master.php");
?>