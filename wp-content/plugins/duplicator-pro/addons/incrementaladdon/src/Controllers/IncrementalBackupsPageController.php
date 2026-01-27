<?php

/**
 * Incremental Backups page controller
 *
 * @package   Duplicator\Addons\IncrementalAddon
 * @copyright (c) 2024, Snap Creek LLC
 */

namespace Duplicator\Addons\IncrementalAddon\Controllers;

use Duplicator\Addons\IncrementalAddon\IncBackupMng;
use Duplicator\Core\CapMng;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;
use Duplicator\Core\Controllers\PageAction;
use Duplicator\Core\Views\TplMng;

class IncrementalBackupsPageController extends AbstractMenuPageController
{
    const INCREMENTAL_BACKUPS_SLUG = ControllersManager::MAIN_MENU_SLUG . '-incremental-backups';
    const ACTION_UPDATE_OPTIONS    = 'update_options';
    const ACTION_REVOKE            = 'revoke';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = self::INCREMENTAL_BACKUPS_SLUG;
        $this->pageTitle    = __('Incremental Backups', 'duplicator-pro');
        $this->menuLabel    = __('Incremental Backups', 'duplicator-pro');
        $this->capatibility = CapMng::CAP_BASIC;
        $this->menuPos      = 25;

        add_action('duplicator_render_page_content_' . $this->pageSlug, [$this, 'renderContent'], 10, 2);
    }

    /**
     * Return actions for current page
     *
     * @return PageAction[]
     */
    public function getActions()
    {
        $actions = parent::getActions();

        $actions[] = new PageAction(
            self::ACTION_UPDATE_OPTIONS,
            [
                $this,
                'actionUpdateOptions',
            ],
            [$this->pageSlug]
        );

        $actions[] = new PageAction(
            self::ACTION_REVOKE,
            [
                $this,
                'actionRevoke',
            ],
            [$this->pageSlug]
        );

        return $actions;
    }

    /**
     * Update incremental backup options
     *
     * @return array<string, mixed>
     */
    public function actionUpdateOptions(): array
    {
        $result = [
            'updateSuccess'  => false,
            'errorMessage'   => '',
            'successMessage' => '',
        ];

        try {
            $message      = '';
            $incBackupMng = IncBackupMng::getInstance();
            $incBackupMng->updateFromHttpRequest($message);
            $result['updateSuccess']  = true;
            $result['successMessage'] = __('Incremental Backup options have been updated successfully.', 'duplicator-pro');
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Revoke incremental backup storage access
     *
     * @return array<string, mixed>
     */
    public function actionRevoke(): array
    {
        $result = [
            'revokeSuccess'  => false,
            'errorMessage'   => '',
            'successMessage' => '',
        ];

        try {
            IncBackupMng::getInstance()->disconnectStorage();
            $result['revokeSuccess']  = true;
            $result['successMessage'] = __('Storage access has been revoked successfully.', 'duplicator-pro');
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test storage and update status
     *
     * @return void
     */
    protected function testStorage(): void
    {
        $incBackupMng = IncBackupMng::getInstance();
        $message      = '';

        if (!$incBackupMng->testStorage($message)) {
            $incBackupMng->deactivate();
            TplMng::getInstance()->setGlobalValue('errorMessage', $message);
        }
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs Current menu slugs
     * @param string   $innerPage         Current inner page, empty if not set
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs, $innerPage): void
    {
        $this->testStorage();
        TplMng::getInstance()->render('incrementaladdon/incremental_backups_page');
    }
}
