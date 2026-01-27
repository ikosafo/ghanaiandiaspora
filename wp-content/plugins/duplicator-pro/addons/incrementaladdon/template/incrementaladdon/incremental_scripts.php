<?php

/**
 * Template for Incremental Backup Scripts
 *
 * @package   Duplicator\Addons\IncrementalAddon
 * @copyright (c) 2024, Snap Creek LLC
 */

use Duplicator\Addons\IncrementalAddon\Ajax\ServicesIncrementalBackup;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Views\UI\UiDialog;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 */

$runNowConfirm               = new UiDialog();
$runNowConfirm->title        = __('RUN INCREMENTAL BACKUP?', 'duplicator-pro');
$runNowConfirm->message      = __('Are you sure you want to run incremental backup now?', 'duplicator-pro');
$runNowConfirm->progressText = __('Running Incremental Backup, Please Wait...', 'duplicator-pro');
$runNowConfirm->jsCallback   = 'DupPro.Incremental.runSchedule()';
$runNowConfirm->initConfirm();
?>

<script>
    jQuery(document).ready(function($) {
        DupPro.Incremental = {
            modalBox: null,
            init: function() {
                this.modalBox = new DuplicatorModalBox();
                this.initEvents();
                this.reorderTableColumns();
            },

            initEvents: function() {
                $('#run-now-button').on('click', function() {
                    DupPro.Incremental.runNow();
                });

                $('#dup-inc-activate-btn').on('click', function() {
                    DupPro.Incremental.changeScheduleStatus(true);
                });

                $('#dup-inc-deactivate-btn').on('click', function() {
                    DupPro.Incremental.changeScheduleStatus(false);
                });
            },

            changeScheduleStatus: function(activate) {
                const action = (
                    !activate ?
                    '<?php echo esc_js(ServicesIncrementalBackup::AJAX_ACTION_DEACTIVATE); ?>' :
                    '<?php echo esc_js(ServicesIncrementalBackup::AJAX_ACTION_ACTIVATE); ?>'
                );
                const nonce = (
                    !activate ?
                    '<?php echo esc_js(wp_create_nonce(ServicesIncrementalBackup::AJAX_ACTION_DEACTIVATE)); ?>' :
                    '<?php echo esc_js(wp_create_nonce(ServicesIncrementalBackup::AJAX_ACTION_ACTIVATE)); ?>'
                );

                Duplicator.Util.ajaxWrapper({
                        'action': action,
                        'nonce': nonce
                    },
                    function(result, data, funcData, textStatus, jqXHR) {
                        if (funcData.success) {
                            /*DupPro.addAdminMessage(funcData.message, 'success', {
                                'hideDelay': 5000
                            });*/
                            window.location.reload();
                        } else {
                            DupPro.addAdminMessage(funcData.message, 'error', {
                                'hideDelay': 5000
                            });
                        }
                        return '';
                    },
                    function(result, data, funcData, textStatus, jqXHR) {
                        DupPro.addAdminMessage(data.message, 'error', {
                            'hideDelay': 5000
                        });
                        return '';
                    }
                );
            },

            runNow: function() {
                <?php $runNowConfirm->showConfirm(); ?>
            },

            runSchedule: function() {
                $('#run-now-button').prop('disabled', true);
                $('#run-now-button').html("<?php esc_html_e('Running - Please Wait...', 'duplicator-pro') ?>");

                Duplicator.Util.ajaxWrapper({
                        'action': '<?php echo esc_js(ServicesIncrementalBackup::AJAX_ACTION_RUN_NOW); ?>',
                        'nonce': '<?php echo esc_js(wp_create_nonce(ServicesIncrementalBackup::AJAX_ACTION_RUN_NOW)); ?>'
                    },
                    function(result, data, funcData, textStatus, jqXHR) {
                        if (funcData.success) {
                            window.location.reload();
                        } else {
                            $('#run-now-button').prop('disabled', false);
                            $('#run-now-button').html("<?php esc_html_e('Run Now', 'duplicator-pro') ?>");
                            DupPro.addAdminMessage(funcData.message, 'error', {
                                'hideDelay': 5000
                            });
                        }
                        return '';
                    },
                    function(result, data, funcData, textStatus, jqXHR) {
                        $('#run-now-button').prop('disabled', false);
                        $('#run-now-button').html("<?php esc_html_e('Run Now', 'duplicator-pro') ?>");
                        DupPro.addAdminMessage(data.message, 'error', {
                            'hideDelay': 5000
                        });
                        return '';
                    }
                );
            },
            reorderTableColumns: function() {
                /** @todo Temporaty function for mochup (TODO: remove this and rebuild the table) */
                const rows = document.querySelectorAll('table tr');

                rows.forEach(row => {
                    const sizeCell = row.querySelector('.dup-size-column');
                    const ageCell = row.querySelector('.dup-age-column');

                    if (sizeCell && ageCell) {
                        const nextAfterAge = ageCell.nextElementSibling;
                        row.removeChild(sizeCell);
                        if (nextAfterAge) {
                            row.insertBefore(sizeCell, nextAfterAge);
                        } else {
                            row.appendChild(sizeCell);
                        }
                    }
                });
            }
        };

        // Initialize Incremental object
        DupPro.Incremental.init();
    });
</script>
