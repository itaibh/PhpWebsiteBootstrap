<!DOCTYPE html>
<?php
    require_once ROOTPATH.'/Components/componentsmanager.php';
?>
<html>
<head>
    <title><?= constant('website_title') ?></title>
    <link rel="stylesheet" href="CurrentTheme/main.css" />
</head>
<body>
    <?php
        include __DIR__.'/siteheader.php';
        include __DIR__.'/login.php';
        include __DIR__.'/footer.php'
    ?>
</body>
</html>
