<?php
use oat\tao\helpers\Layout;
use oat\tao\helpers\Template;
use oat\tao\model\theme\Theme;
use oat\taoDelivery\controller\DeliveryServer;
use oat\taoDelivery\helper\Delivery;

$delivery = get_data('delivery');
var_dump($delivery); // NULL
?>
<div class="settings-menu">
    <ul class="clearfix plain">
        <li class="infoControl sep-before" data-control="home">
            <a id="home" href="<?= get_data('returnUrl') ?>">
                <span class="icon-home"></span>
                <span class="text"><?= __("Tests Home"); ?></span>
            </a>
        </li>
        <li class="infoControl sep-before">
            <span class="a">
                <span class="icon-test-taker"></span>
                <span><?= get_data('userLabel'); ?></span>
            </span>
        </li>
        <li class="infoControl" data-control="logout">
            <a id="logout" class="" href="<?= get_data('logout') ?>">
                <span class="icon-logout"></span>
                <span class="text"><?= __("Logout"); ?></span>
            </a>
        </li>
        <li class="infoControl hidden" data-control="exit">
            <a href="#">
                <span class="icon-logout"></span>
                <span class="text"><?= __("Exit"); ?></span>
            </a>
        </li>
    </ul>
</div>
<div id="user-menu-placeholder"></div>
<script>
    require(['/tao/ClientConfig/config'], function () {
        require(['/taoInvalsi/views/js/dropdown/populateDropdown.js'], function (populateDropdown) {
            populateDropdown.init();
        });
    });
</script>
<!-- hidden element hack to get the info I need into dialog: -->
<div id="info-dialog-content" class="hidden">
    <h3 class="tao-version">TAO <?= oat\tao\helpers\ApplicationHelper::getVersionName() ?></h3>
    <img src="<?=oat\tao\helpers\Template::img('themes/platform/taoInvalsiDefaultTheme/logo-invalsi.jpg', 'taoInvalsi')?>" alt="Invalsi Logo">
    <img src="<?=oat\tao\helpers\Template::img('OAT-logo.png', 'taoInvalsi')?>" alt="OAT Logo">
    <p>© Contenuto: <?= date('Y') ?> INVALSI - Tutti i diritti riservati </p>
    <p>© TAO Platform: 2013 - <?= date('Y') ?> · Open Assessment Technologies S.A. · Tutti i diritti riservati</p>
</div>