<?php

/**
 * Duplicator Cloud Storage Provider Configs
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Addons\DupCloudAddon\Models\DupCloudStorage;
use Duplicator\Views\UI\UiDialog;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var DupCloudStorage $storage
 */
$storage   = $tplData["storage"];
$userName  = $tplData["userName"];
$userEmail = $tplData["userEmail"];

$tplMng->render('admin_pages/storages/parts/provider_head');
?>
<tr>
    <th scope="row"><label for=""><?php esc_html_e("Authorization", 'duplicator-pro'); ?></label></th>
    <td class="dupcloud-authorize">
        <?php if (!$storage->isAuthorized()) : ?>
            <div class='dupcloud-authorization-state' id="dupcloud-state-unauthorized">
                <!-- CONNECT -->
                <div id="dupcloud-connect-btn-area">
                    <button
                        id="dupcloud-connect-btn"
                        type="button"
                        class="button button-large"
                        onclick="DupPro.Storage.DupCloud.GetAuthUrl();">
                        <i class="fa fa-plug"></i> <?php esc_html_e('Connect to Duplicator Cloud', 'duplicator-pro'); ?>
                    </button>
                </div>
                <!-- STEPS -->
                <div id="dupcloud-steps">
                    <div>
                        <b><?php esc_html_e('Step 1:', 'duplicator-pro'); ?></b>&nbsp;
                        <?php
                        esc_html_e(
                            "Duplicator needs to authorize Duplicator Cloud. Make sure to allow all required permissions.",
                            'duplicator-pro'
                        ); ?>
                        <div class="auth-code-popup-note">
                            <?php
                            echo esc_html__(
                                'Note: Clicking the button below will open a new tab/window. Please be sure your browser does not block popups.
                                If a new tab/window does not open check your browsers address bar to allow popups from this URL.',
                                'duplicator-pro'
                            );
                            ?>
                        </div>
                        <button id="dupcloud-auth-window-button" class="button" onclick="DupPro.Storage.DupCloud.OpenAuthPage(); return false;">
                            <i class="fa fa-user"></i> <?php esc_html_e("Authorize Duplicator Cloud", 'duplicator-pro'); ?>
                        </button>
                    </div>

                    <div id="dupcloud-auth-code-area">
                        <b><?php esc_html_e('Step 2:', 'duplicator-pro'); ?></b>
                        <?php esc_html_e("Paste code from Google authorization page.", 'duplicator-pro'); ?> <br />
                        <input style="width:400px" id="dupcloud-access-token" name="dupcloud_access_token" />
                    </div>

                    <b><?php esc_html_e('Step 3:', 'duplicator-pro'); ?></b>
                    <?php esc_html_e('Finalize Duplicator Cloud setup by clicking the "Finalize Setup" button.', 'duplicator-pro') ?>
                    <br />
                    <button
                        id="dupcloud-finalize-setup"
                        type="button"
                        class="button">
                        <i class="fa fa-check-square"></i> <?php esc_html_e('Finalize Setup', 'duplicator-pro'); ?>
                    </button>
                </div>
            </div>
        <?php else : ?>
            <div class='dupcloud-authorization-state' id="dupcloud-state-authorized" style="margin-top:-10px">
                <?php if (strlen($userName) > 0) : ?>
                    <h3>
                        <?php esc_html_e('Duplicator Cloud Account', 'duplicator-pro'); ?><br />
                        <i class="dpro-edit-info">
                            <?php esc_html_e('Duplicator has been authorized to access this user\'s Duplicator Cloud account', 'duplicator-pro'); ?>
                        </i>
                    </h3>
                    <div id="dupcloud-account-info">
                        <label><?php esc_html_e('Name', 'duplicator-pro'); ?>:</label>
                        <?php echo esc_html($userName); ?><br />

                        <label><?php esc_html_e('Email', 'duplicator-pro'); ?>:</label> <?php echo esc_html($userEmail); ?>
                    </div><br />
                <?php else : ?>
                    <div><?php esc_html_e('Error retrieving user information.', 'duplicator-pro'); ?></div>
                <?php endif ?>

                <button
                    id="dup-dupcloud-cancel-authorization"
                    type="button"
                    class="button">
                    <?php esc_html_e('Cancel Authorization', 'duplicator-pro'); ?>
                </button><br />
                <i class="dpro-edit-info">
                    <?php
                    esc_html_e(
                        'Disassociates storage provider with the Duplicator Cloud account. Will require re-authorization.',
                        'duplicator-pro'
                    ); ?>
                </i>
            </div>
        <?php endif ?>
    </td>
</tr>
<?php
$tplMng->render('admin_pages/storages/parts/provider_foot');

$alertConnStatus          = new UiDialog();
$alertConnStatus->title   = __('Duplicator Cloud Authorization Error', 'duplicator-pro');
$alertConnStatus->message = ''; // javascript inserted message
$alertConnStatus->initAlert();
?>
<script>
    jQuery(document).ready(function($) {
        DupPro.Storage.DupCloud = DupPro.Storage.DupCloud || {};

        DupPro.Storage.DupCloud.GetAuthUrl = function() {
            $('#dupcloud-connect-btn-area').hide();
            $('#dupcloud-steps').show();
        }

        DupPro.Storage.DupCloud.OpenAuthPage = function() {
            var authUrl = '<?php echo esc_js(DupCloudStorage::getAuthUrl()); ?>';
            window.open(authUrl, '_blank');
        }

        $('#dup-dupcloud-cancel-authorization').click(function(e) {
            e.stopPropagation();

            DupPro.Storage.RevokeAuth(<?php echo (int) $storage->getId(); ?>);

            return false;
        });

        $('#dupcloud-finalize-setup').click(function(event) {
            event.stopPropagation();

            if ($('#dupcloud-access-token').val().length > 20) {
                DupPro.Storage.PrepareForSubmit();

                DupPro.Storage.Authorize(
                    <?php echo (int) $storage->getId(); ?>,
                    <?php echo (int) $storage->getSType(); ?>, {
                        'name': $('#name').val(),
                        'notes': $('#notes').val(),
                        'access_token': $('#dupcloud-access-token').val()
                    }
                );
            } else {
                <?php $alertConnStatus->showAlert(); ?>
                let alertMsg = "<i class='fas fa-exclamation-triangle'></i> " +
                    "<?php esc_html_e('Please enter your Duplicator Cloud access token!', 'duplicator-pro'); ?>";
                <?php $alertConnStatus->updateMessage("alertMsg"); ?>
            }

            return false;
        });
    });
</script>