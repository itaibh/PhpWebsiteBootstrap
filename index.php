<?php
    include_once('./settings.php');
    include_once('./components.php')
?>
<h1><?= constant('website_title') ?></h1>
<?php
    $success = InitWebsite();
    if (!$success) :
?>
    <h2>Website not initialized</h2>
    <a href="#">Initialize website</a>
<?php
    endif;
?>
