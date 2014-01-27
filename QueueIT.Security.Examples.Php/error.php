<?php
	require_once('../QueueIT.Security/QueueFactory.php');

	use QueueIT\Security\QueueFactory;
  	
	$queueName = $_GET['queuename'];
	$queue = QueueFactory::CreateQueueFromConfiguration($queueName);
  

  //Buffer larger content areas like the main page content
  ob_start();
?>
    <div>An error occured.</div>
    <div>
        <a href="index.php">Back To Home</a> <a href="<?php echo $queue->getCancelUrl($_GET['t']); ?>">Go to queue</a>
    </div>
	
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Error Page";
  //Apply the template
  include("master.php");
?>