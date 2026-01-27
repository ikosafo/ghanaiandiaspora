<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\IncrementalAddon\Controllers\IncrementalBackupsPageController;
use Duplicator\Addons\IncrementalAddon\IncBackupMng;
use Duplicator\Core\CapMng;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 */

$incBackupMng   = IncBackupMng::getInstance();
$isStorageValid = $incBackupMng->isStorageValid();
$isActive       = ($isStorageValid && $incBackupMng->isActive());
?>
<div class="dup-settings-wrapper">
    <div class="dup-accordion-wrapper display-separators close">
        <div class="accordion-header">
            <h3 class="title">
                <?php esc_html_e("Options", 'duplicator-pro') ?>
            </h3>
        </div>
        <div class="accordion-content">
            <form method="post" id="dup-incremental-form">
                <?php $tplMng->getAction(IncrementalBackupsPageController::ACTION_UPDATE_OPTIONS)->getActionNonceFileds(); ?>

                <table class="form-table">
                    <?php
                    $tplMng->render(
                        'admin_pages/schedules/parts/repeats_options',
                        [
                            'repeat_type'  => $incBackupMng->getScheduleRepeatType(),
                            'run_every'    => $incBackupMng->getScheduleRunEvery(),
                            'day_of_month' => $incBackupMng->getScheduleDayOfMonth(),
                            'weekdays'     => [
                                'mon' => $incBackupMng->isScheduleDaySet('mon'),
                                'tue' => $incBackupMng->isScheduleDaySet('tue'),
                                'wed' => $incBackupMng->isScheduleDaySet('wed'),
                                'thu' => $incBackupMng->isScheduleDaySet('thu'),
                                'fri' => $incBackupMng->isScheduleDaySet('fri'),
                                'sat' => $incBackupMng->isScheduleDaySet('sat'),
                                'sun' => $incBackupMng->isScheduleDaySet('sun'),
                            ],
                            'start_hour'   => $incBackupMng->getScheduleStartTimePiece(0),
                            'start_minute' => $incBackupMng->getScheduleStartTimePiece(1),
                        ]
                    );
                    ?>
                </table>

                <label class="lbl-larger">
                    &nbsp;
                </label>
                <div class="margin-bottom-1">
                    <button
                        class="button secondary hollow small margin-0"
                        type="submit"
                        id="update-options-button">
                        <?php esc_html_e('Update Options', 'duplicator-pro'); ?>
                    </button>
                    <?php if (CapMng::can(CapMng::CAP_CREATE, false)) : ?>
                        <button
                            class="button secondary hollow small margin-0 margin-left-1"
                            type="button"
                            id="run-now-button"
                            <?php disabled(!$isStorageValid); ?>>
                            <?php esc_html_e('Run Now', 'duplicator-pro'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
