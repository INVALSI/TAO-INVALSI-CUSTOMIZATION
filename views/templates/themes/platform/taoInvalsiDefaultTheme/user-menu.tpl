<div id="user-menu" class="hidden"></div>
<script>
    require(['/tao/ClientConfig/config'], function () {
        require(['jquery','/taoInvalsi/views/js/dropdown/populateDropdown.js'], function ($, populateDropdown) {
            if ($(document.body).hasClass('delivery-scope')) {
                populateDropdown.init();
            }
        });
    });
</script>
