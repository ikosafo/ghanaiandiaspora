<?php

/**
 * Incremental backup ajax services
 *
 * @package   Duplicator\Addons\IncrementalAddon
 * @copyright (c) 2024, Snap Creek LLC
 */

declare(strict_types=1);

namespace Duplicator\Addons\IncrementalAddon\Ajax;

use Duplicator\Addons\IncrementalAddon\IncBackupMng;
use Duplicator\Ajax\AbstractAjaxService;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Core\CapMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;

/**
 * Handles AJAX requests for Incremental backup functionality
 *
 * This class manages the activation and deactivation of incremental backups
 * through AJAX endpoints. It ensures proper security through nonce verification
 * and capability checks, and provides appropriate feedback for all operations.
 */
class ServicesIncrementalBackup extends AbstractAjaxService
{
    const AJAX_ACTION_ACTIVATE   = 'duplicator_incremental_activate';
    const AJAX_ACTION_DEACTIVATE = 'duplicator_incremental_deactivate';
    const AJAX_ACTION_RUN_NOW    = 'duplicator_incremental_run_now';

    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init(): void
    {
        $this->addAjaxCall('wp_ajax_' . self::AJAX_ACTION_ACTIVATE, 'activateIncrementalBackup');
        $this->addAjaxCall('wp_ajax_' . self::AJAX_ACTION_DEACTIVATE, 'deactivateIncrementalBackup');
        $this->addAjaxCall('wp_ajax_' . self::AJAX_ACTION_RUN_NOW, 'runNowIncrementalBackup');
    }

    /**
     * Activate incremental backup
     *
     * @return void
     */
    public function activateIncrementalBackup(): void
    {
        AjaxWrapper::json(
            [
                $this,
                'activateIncrementalBackupCallback',
            ],
            self::AJAX_ACTION_ACTIVATE,
            SnapUtil::sanitizeStrictInput(INPUT_POST, 'nonce'),
            CapMng::CAP_CREATE
        );
    }

    /**
     * Deactivate incremental backup
     *
     * @return void
     */
    public function deactivateIncrementalBackup(): void
    {
        AjaxWrapper::json(
            [
                $this,
                'deactivateIncrementalBackupCallback',
            ],
            self::AJAX_ACTION_DEACTIVATE,
            SnapUtil::sanitizeStrictInput(INPUT_POST, 'nonce'),
            CapMng::CAP_CREATE
        );
    }

    /**
     * Activate incremental backup callback
     *
     * @return array{success:bool,message:string}
     */
    public function activateIncrementalBackupCallback(): array
    {
        $incBackupMng = IncBackupMng::getInstance();

        if ($incBackupMng->isActive()) {
            return [
                'success' => true,
                'message' => __('Incremental backup is already active', 'duplicator-pro'),
            ];
        }

        if (!$incBackupMng->activate()) {
            throw new Exception(__('Failed to activate incremental backup', 'duplicator-pro'));
        }

        return [
            'success' => true,
            'message' => __('Incremental backup activated successfully', 'duplicator-pro'),
        ];
    }

    /**
     * Deactivate incremental backup callback
     *
     * @return array{success:bool,message:string}
     */
    public function deactivateIncrementalBackupCallback(): array
    {
        $incBackupMng = IncBackupMng::getInstance();

        if (!$incBackupMng->isActive()) {
            return [
                'success' => true,
                'message' => __('Incremental backup is already inactive', 'duplicator-pro'),
            ];
        }

        if (!$incBackupMng->deactivate()) {
            throw new Exception(__('Failed to deactivate incremental backup', 'duplicator-pro'));
        }

        return [
            'success' => true,
            'message' => __('Incremental backup deactivated successfully', 'duplicator-pro'),
        ];
    }

    /**
     * Run now incremental backup
     *
     * @return void
     */
    public function runNowIncrementalBackup(): void
    {
        AjaxWrapper::json(
            [
                $this,
                'runNowIncrementalBackupCallback',
            ],
            self::AJAX_ACTION_RUN_NOW,
            SnapUtil::sanitizeStrictInput(INPUT_POST, 'nonce'),
            CapMng::CAP_CREATE
        );
    }

    /**
     * Run now incremental backup callback
     *
     * @return array{success:bool,message:string}
     */
    public function runNowIncrementalBackupCallback(): array
    {
        try {
            $incBackupMng = IncBackupMng::getInstance();
            $incBackupMng->runNow();

            return [
                'success' => true,
                'message' => __('Incremental backup started successfully', 'duplicator-pro'),
            ];
        } catch (Exception $e) {
            throw new Exception(__('Failed to start incremental backup', 'duplicator-pro'));
        }
    }
}
