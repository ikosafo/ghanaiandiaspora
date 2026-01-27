<?php

/**
 * Template for Incremental Backups Info Section
 *
 * @package   Duplicator\Addons\IncrementalAddon
 * @copyright (c) 2024, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Addons\IncrementalAddon\IncBackupMng;
use Duplicator\Addons\IncrementalAddon\Controllers\IncrementalBackupsPageController;

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 */

$incBackupMng   = IncBackupMng::getInstance();
$isActive       = $incBackupMng->isActive();
$isStorageValid = $incBackupMng->isStorageValid();
$nextRunTime    = $incBackupMng->getNextRunTime();
$userName       = $incBackupMng->getStorageUserName();
$userEmail      = $incBackupMng->getStorageUserEmail();
$totalSpace     = $incBackupMng->getStorageTotalSpace();
$freeSpace      = $incBackupMng->getStorageFreeSpace();
$usedSpace      = $totalSpace - $freeSpace;
$usedPercentage = ($totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 1) : 0);

$revokeUrl = $tplMng->getAction(IncrementalBackupsPageController::ACTION_REVOKE)->getUrl();
?>
<div class="dup-settings-wrapper margin-bottom-2">
    <h3 class="title">
        <?php esc_html_e("Status Information", 'duplicator-pro') ?>
    </h3>

    <label class="lbl-larger">
        <?php esc_html_e('Storage Status', 'duplicator-pro'); ?>
    </label>
    <div class="margin-bottom-1">
        <?php if ($isStorageValid) { ?>
            <b class="success-color">
                <?php esc_html_e('Connected', 'duplicator-pro'); ?>
            </b>
            <a
                href="<?php echo esc_url($revokeUrl); ?>"
                class="button small margin-left-1 margin-bottom-0">
                <?php esc_html_e('Disconnect', 'duplicator-pro'); ?>
            </a>
        <?php } else { ?>
            <b class="alert-color">
                <?php esc_html_e('Disconnected', 'duplicator-pro'); ?>
            </b>
            <button
                type="button"
                id="dup-cloud-connect-btn"
                class="button small margin-left-1 margin-bottom-0">
                <?php esc_html_e('Connect', 'duplicator-pro'); ?>
            </button>
        <?php } ?>
    </div>

    <?php if ($isStorageValid) { ?>
        <label class="lbl-larger">
            <?php esc_html_e('User', 'duplicator-pro'); ?>
        </label>
        <div class="margin-bottom-1">
            <?php echo esc_html($userName . ' (' . $userEmail . ')'); ?>
        </div>

        <label class="lbl-larger">
            <?php esc_html_e('Storage Usage', 'duplicator-pro'); ?>
        </label>
        <div class="margin-bottom-1">
            <?php
            printf(
                esc_html_x(
                    '%1$s used of %2$s (%3$s%%)',
                    'Storage space usage info',
                    'duplicator-pro'
                ),
                esc_html(size_format($usedSpace)),
                esc_html(size_format($totalSpace)),
                (float) $usedPercentage
            );
            ?>
        </div>

        <label class="lbl-larger">
            <?php esc_html_e('Schedule Status', 'duplicator-pro'); ?>
        </label>
        <div class="margin-bottom-1">
            <?php if ($isActive) : ?>
                <b class="success-color">
                    <?php esc_html_e('Active', 'duplicator-pro'); ?>
                </b>
                <button
                    type="button"
                    id="dup-inc-deactivate-btn"
                    class="button small margin-left-1 margin-bottom-0">
                    <?php esc_html_e('Deactivate', 'duplicator-pro'); ?>
                </button>
            <?php else : ?>
                <b class="alert-color">
                    <?php esc_html_e('Inactive', 'duplicator-pro'); ?>
                </b>
                <button
                    type="button"
                    id="dup-inc-activate-btn"
                    class="button small margin-left-1 margin-bottom-0">
                    <?php esc_html_e('Activate', 'duplicator-pro'); ?>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($isActive) { ?>
            <label class="lbl-larger">
                <?php esc_html_e('Next Scheduled Run', 'duplicator-pro'); ?>
            </label>
            <div class="margin-bottom-1">
                <?php echo esc_html($nextRunTime); ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>
