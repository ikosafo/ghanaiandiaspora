<?php

/**
 * @package Duplicator
 */

defined("ABSPATH") or die("");

use Duplicator\Controllers\SettingsPageController;
use Duplicator\Core\Controllers\ControllersManager;

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 */

$fullEditUrl = ControllersManager::getMenuLink(
    ControllersManager::STORAGE_SUBMENU_SLUG,
    SettingsPageController::L2_SLUG_STORAGE,
    null,
    [ControllersManager::QUERY_STRING_INNER_PAGE => 'edit'],
    false
);

?>
<script>
    jQuery(document).ready(function ($) {
        let storageEditBaseUrl = <?php echo json_encode($fullEditUrl); ?>;

        // Quick fix for submint/enter error
        $(window).on('keyup keydown', function (e) {
            if (!$(e.target).is('textarea'))
            {
                var keycode = (typeof e.keyCode != 'undefined' && e.keyCode > -1 ? e.keyCode : e.which);
                if ((keycode === 13)) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Removes the values of hidden input fields marked with class dup-empty-field-on-submit
        DupPro.Storage.EmptyValues = function () {
            $(':hidden .dup-empty-field-on-submit').val('');
        }

        // Removes tags marked with class dup-remove-on-submit-if-hidden, if they are hidden
        DupPro.Storage.RemoveMarkedHiddenTags = function () {
            $('.dup-remove-on-submit-if-hidden:hidden').each(function() {
                $(this).remove();
            });
        }

        DupPro.Storage.PrepareForSubmit = function () {
            DupPro.Storage.EmptyValues();
            if ($('#dup-storage-form').parsley().isValid()) {
                // The form is about to be submitted.                
                DupPro.Storage.RemoveMarkedHiddenTags();
            }
        }

        $('#dup-storage-form').submit(DupPro.Storage.PrepareForSubmit);

        DupPro.Storage.AuthMessages = function () {
            let reloadUrl = new URL(window.location.href);
            let authMessage = reloadUrl.searchParams.get('dup-auth-message');
            if (authMessage) {
                DupPro.addAdminMessage(authMessage, 'notice');
            }
            let revokeMessage = reloadUrl.searchParams.get('dup-revoke-message');
            if (revokeMessage) {
                DupPro.addAdminMessage(revokeMessage, 'notice');
            }
        }
        
        DupPro.Storage.RevokeAuth = function (storageId)
        {
            Duplicator.Util.ajaxWrapper(
                {
                    action: 'duplicator_pro_revoke_storage',
                    storage_id: storageId,
                    nonce: '<?php echo esc_js(wp_create_nonce('duplicator_pro_revoke_storage')); ?>'
                },
                function (result, data, funcData, textStatus, jqXHR) {
                    if (funcData.success) {
                        let reloadUrl = new URL(storageEditBaseUrl);
                        reloadUrl.searchParams.set('storage_id', storageId);
                        reloadUrl.searchParams.set('dup-revoke-message', funcData.message);
                        window.location.href = reloadUrl.href;
                        //location.reload();
                    } else {
                        DupPro.addAdminMessage(funcData.message, 'error');
                    }
                    return '';
                }
            );
        }

        DupPro.Storage.Authorize = function (storageId, storageType, extraData)
        {
            extraData.action       = 'duplicator_pro_auth_storage';
            extraData.storage_id   = storageId;
            extraData.storage_type = storageType;
            extraData.nonce = '<?php echo esc_js(wp_create_nonce('duplicator_pro_auth_storage')); ?>';

            Duplicator.Util.ajaxWrapper(
                extraData,
                function (result, data, funcData, textStatus, jqXHR) {
                    if (funcData.success) {
                        let reloadUrl = new URL(storageEditBaseUrl);
                        reloadUrl.searchParams.set('storage_id', funcData.storage_id);
                        reloadUrl.searchParams.set('dup-auth-message', funcData.message);

                        // set unsaved changes to false, not to trigger alert during finalization
                        DupPro.UI.hasUnsavedChanges = false;

                        window.location.href = reloadUrl.href;
                    } else {
                        DupPro.addAdminMessage(funcData.message, 'error');
                    }
                    return '';
                }
            );

            return false;
        }

        // Toggles Save Provider button for existing Storages only
        DupPro.UI.formOnChangeValues($('#dup-storage-form'), function() {
            $('#button_file_test').prop('disabled', true);
        });

        //Init
        DupPro.Storage.AuthMessages();
        jQuery('#name').focus().select();
    });
    
</script>