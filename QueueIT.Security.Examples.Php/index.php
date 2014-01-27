<?php
  //Buffer larger content areas like the main page content
  ob_start();
?>

    <h3>Setting up the queue:</h3>
    <ol class="round">
        <li class="one">
            <h5>Add settings ini file</h5>
        </li>
        <li class="two">
            <h5>Write code</h5>
        </li>
    </ol>
<?php
  //Assign all Page Specific variables
  $body = ob_get_contents();
  ob_end_clean();
  $title = "Queue-it";
  //Apply the template
  include("master.php");
?>