<h1><?= constant('website_title') ?></h1>
<?php
    if(isset($ex)) {
        echo "<p>" . $ex->getMessage() ."</p>";
        echo "<pre>" . $ex->getTraceAsString() . "</pre>";
    } else {
        echo '<p>There was an error. See logs for more details.</p>';
    }
?>
