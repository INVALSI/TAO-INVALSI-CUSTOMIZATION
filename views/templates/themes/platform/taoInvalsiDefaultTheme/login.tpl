<?php
use oat\tao\helpers\Layout;
use oat\tao\model\theme\Theme;
?>

<style>
    #campaign-notice{width:40%;margin:30px auto;padding:10px 10px 0;left:10%;z-index:-1;font-weight:bold;}#campaign-notice p{text-align:center;font-size:1.2rem;line-height:1.5em;color:#767676}@media screen and (max-width: 1920px) {#campaign-notice{width:50%}}@media screen and ((max-width: 1600px) or (max-width: 440px)) {#campaign-notice{width:60%}}@media screen and ((max-width: 1366px) or (max-width: 1280px)) {#campaign-notice{width:70%}}@media screen and (max-width: 1024px) {#campaign-notice{width:90%}}
</style>

<h2 id="campaign-notice">Questa pagina è riservata al personale INVALSI.
    I link alle piattaforme di somministrazione delle prove per studenti sono, rispettivamente: <br>
    - Studenti del terzo anno della Scuola secondaria di I grado:  <a href="https://ms8.invalsi.taocloud.org">https://ms8.invalsi.taocloud.org</a> <br>
    - Studenti del secondo anno scuola secondaria di II grado: <a href="https://ms10.invalsi.taocloud.org">https://ms10.invalsi.taocloud.org</a> <br>
    - Studenti dell'ultimo anno scuola secondaria di II grado: <a href="https://ms13.invalsi.taocloud.org">https://ms13.invalsi.taocloud.org</a>
</h2>
<main id="login-box" class="entry-point entry-point-container">
    <?=Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'login-message')?>
    <div id="login-box-inner-container"></div>

    <?php foreach(get_data('entryPoints') as $entrypoint): ?>
    <div>
        <a class="entry-point-link" href="<?= $entrypoint->getUrl() ?>" role="button"><?= $entrypoint->getTitle() ?></a>
    </div>
    <?php endforeach;?>
</main>
<?php if(get_data('show_gdpr')): ?>
<?=Layout::renderThemeTemplate(Theme::CONTEXT_BACKOFFICE, 'gdpr')?>
<?php endif; ?>
<script>
    requirejs.config({
        config: {
            'controller/login': {
                'message' : {
                    'info': <?=json_encode(get_data('msg'))?>,
                    'error': <?=json_encode(urldecode(get_data('errorMessage')))?>
                },
                'disableAutocomplete' : <?=get_data('autocompleteDisabled')?>,
                'enablePasswordReveal' : <?=get_data('passwordRevealEnabled')?>,
                'disableAutofocus': <?=get_data('autofocusDisabled')?>,
                'fieldMessages': {
                    'login': <?=json_encode(get_data('fieldMessages_password'))?>,
                    'password': <?=json_encode(get_data('fieldMessages_password'))?>
                }
            }
        }
    });
</script>
