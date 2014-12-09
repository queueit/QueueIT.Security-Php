<?php 
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $title; ?> - Reference Implementation</title>
        <meta name="viewport" content="width=device-width" />
        <link rel="stylesheet" href="/Content/Site.css" />
    </head>
    <body id="<?php echo $title; ?>">
        <header>
            <div class="content-wrapper">
                <div class="float-left">
                    <p class="site-title"><img src="/Images/logo.jpg" alt=""/></p>
                </div>
                <div class="float-right">
                    <nav>
                        <ul id="menu">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="simple.php">Simple</a></li>
                            <li><a href="advanced.php">Advanced</a></li>
                            <li><a href="codeonly.php">Code Only</a></li>
                            <li><a href="link.php">Link</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
        <div id="body">
            <section class="featured">
		        <div class="content-wrapper">
		            <hgroup class="title">
		                <h1><?php echo $title; ?>.</h1>
		                <h2>Configuration of queues.</h2>
		            </hgroup>
		            <p>
		                To learn more about configuring queues, please contact Queue-it.
		            </p>
		        </div>
		    </section>
            <section class="content-wrapper main-content clear-fix">
                <?php echo $body; ?>
            </section>
        </div>
        <footer>
            <div class="content-wrapper">
                <div class="float-left">
                    <p>&copy; 2013 - Queue-it</p>
                </div>
            </div>
        </footer>
    </body>
</html>