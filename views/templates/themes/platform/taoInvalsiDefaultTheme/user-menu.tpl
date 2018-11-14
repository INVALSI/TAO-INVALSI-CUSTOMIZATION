<!-- hidden element hack to get the info I need into dialog: -->
<div id="info-dialog-content" class="hidden">
    <h3 class="tao-version">TAO <?= oat\tao\helpers\ApplicationHelper::getVersionName() ?></h3>
    <img src="<?=oat\tao\helpers\Template::img('themes/platform/taoInvalsiDefaultTheme/logo-invalsi.jpg', 'taoInvalsi')?>" alt="Invalsi Logo">
    <img src="<?=oat\tao\helpers\Template::img('OAT-logo.png', 'taoInvalsi')?>" alt="OAT Logo">
    <p>© Contenuto: <?= date('Y') ?> INVALSI - Tutti i diritti riservati </p>
    <p>© TAO Platform: 2013 - <?= date('Y') ?> · Open Assessment Technologies S.A. · Tutti i diritti riservati</p>
</div>
<script>
    require(['/tao/ClientConfig/config'], function () {
        require(['/taoInvalsi/views/js/dropdown/populateDropdown.js'], function (populateDropdown) {
            populateDropdown.init();
        });
    });
</script>
