<?php
	require_once('../QueueIT.Security/QueueFactory.php');

	use QueueIT\Security\QueueFactory;
  	
	$queue = QueueFactory::CreateQueueFromConfiguration('link');
  
	function getTargetUrl()
	{
		$ssl = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on";
	
		$pageURL = 'http';
		if ($ssl) {$pageURL .= "s";}
		$pageURL .= "://";
		if ((!$ssl && $_SERVER["SERVER_PORT"] != "80") || ($ssl && $_SERVER["SERVER_PORT"] != "443"))  {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]. '/linktarget.php';
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"]. '/linktarget.php';
		}
		return $pageURL;
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
    
	<div><a href="<?php echo $queue->getQueueUrl(getTargetUrl()); ?>">Goto Queue</a></div>
	
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Link";
  //Apply the template
  include("master.php");
?>