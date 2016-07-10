<!DOCTYPE html>
<?php
    require_once ROOTPATH.'/Components/componentsmanager.php';
?>
<html>
<head>
    <title><?= constant('website_title') ?></title>
    <?php $this->RenderRequiredScripts(); ?>
    <link rel="stylesheet" href="/CurrentTheme/main.css" />
    <?php $this->RenderRequiredStyles(); ?>
    <?php $this->RenderSection('ExtraHeadElements'); ?>
</head>
<body>
    <?php
        include __DIR__.'/siteheader.php';
        $accountmanager = ComponentsManager::Instance()->GetComponent('IAccountManager');
        $user = $accountmanager->GetCurrentUser();
        if ($user != null){
            include __DIR__.'/userheader.php';
        }
        else {
            include __DIR__.'/nouserheader.php';
        }
        $this->RenderSection('MainContent');
        include __DIR__.'/footer.php'
    ?>
</body>
</html>
