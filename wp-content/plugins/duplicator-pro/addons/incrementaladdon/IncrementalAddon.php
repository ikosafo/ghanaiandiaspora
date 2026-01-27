<?php

/**
 * INCREMENTAL BACKUP ADDON
 *
 * Name: Duplicator PRO Incremental Backup
 * Version: 1
 * Author: Duplicator
 * Author URI: https://duplicator.com/
 * Requires addons: DupCloudAddon
 *
 * PHP version 5.6
 *
 * @category  Duplicator
 * @package   Plugin
 * @author    Duplicator
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @version   GIT: $Id$
 * @link      https://duplicator.com/
 */

namespace Duplicator\Addons\IncrementalAddon;

use Duplicator\Addons\IncrementalAddon\Controllers\IncrementalBackupsPageController;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalBackup;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalStorage;
use Duplicator\Addons\IncrementalAddon\Ajax\ServicesIncrementalBackup;
use Duplicator\Addons\IncrementalAddon\IncBackupMng;
use Duplicator\Core\Addons\AbstractAddonCore;
use Duplicator\Core\Bootstrap;
use Duplicator\Models\Storages\AbstractStorageEntity;

/**
 * Incremental Backup addon class
 */
class IncrementalAddon extends AbstractAddonCore
{
    const ADDON_PATH = __DIR__;

    /**
     * On Costruct function called on child class constructor
     * Use to exect function before init hookw, at this point wordpress is not fully loaded
     *
     * @return void
     */
    protected function onConstruct(): void
    {
        IncrementalBackup::registerType();
    }

    /**
     * @return void
     */
    public function init(): void
    {
        add_action('duplicator_pro_register_storage_types', [$this, 'registerStorages']);
        add_filter('duplicator_template_file', [self::class, 'getTemplateFile'], 10, 2);
        add_filter('duplicator_menu_pages', function (array $pages) {
            $pages[]  = IncrementalBackupsPageController::getInstance();
            $pageSlug = IncrementalBackupsPageController::getInstance()->getMenuHookSuffix();
            add_action('admin_print_scripts-' . $pageSlug, [Bootstrap::class, 'enqueueScripts']);
            add_action('admin_print_styles-' . $pageSlug, [Bootstrap::class, 'enqueueStyles']);
            add_action('admin_print_styles-' . $pageSlug, [self::class, 'enqueueStyles']);
            return $pages;
        });

        add_filter('duplicator_get_active_schedules', fn(array $schedule): array =>
            // Can't init IncbackupMng on add init because the sorage isn't registered yet, so use a closure
            IncBackupMng::getInstance()->getProcessActiveSchedules($schedule));

        add_action('admin_init', [self::class, 'registerJsCss']);

        // Register incremental backup ajax service
        (new ServicesIncrementalBackup())->init();
    }

    /**
     * Register storages
     *
     * @return void
     */
    public function registerStorages(): void
    {
        IncrementalStorage::registerType();
    }

    /**
     * Return template file path
     *
     * @param string $path    path to the template file
     * @param string $slugTpl slug of the template
     *
     * @return string
     */
    public static function getTemplateFile($path, $slugTpl)
    {
        if (strpos($slugTpl, 'incrementaladdon/') === 0) {
            return self::getAddonPath() . '/template/' . $slugTpl . '.php';
        }
        return $path;
    }

    /**
     * Get storage usage stats
     *
     * @param array<string,int> $storageNums Storages num
     *
     * @return array<string,int>
     */
    public static function getStorageUsageStats($storageNums)
    {
        if (($storages = AbstractStorageEntity::getAll()) === false) {
            $storages = [];
        }

        $storageNums['storages_incremental_count'] = 0;

        foreach ($storages as $storage) {
            if ($storage->getStype() === IncrementalStorage::getSType()) {
                $storageNums['storages_incremental_count']++;
            }
        }

        return $storageNums;
    }

    /**
     * Register styles and scripts
     *
     * @return void
     */
    public static function registerJsCss(): void
    {
        if (wp_doing_ajax()) {
            return;
        }

        $min = Bootstrap::getMinPrefix();

        wp_register_style(
            'dup-addon-incremental-addon',
            self::getAddonUrl() . "/assets/css/incrementaladdon{$min}.css",
            ['dup-plugin-global-style'],
            DUPLICATOR_PRO_VERSION
        );
    }

    /**
     * Enqueue CSS Styles:
     * Loads all CSS style libs/source for DupPro
     *
     * @return void
     */
    public static function enqueueStyles(): void
    {
        wp_enqueue_style('dup-addon-incremental-addon');
    }

    /**
     *
     * @return string
     */
    public static function getAddonPath(): string
    {
        return __DIR__;
    }

    /**
     *
     * @return string
     */
    public static function getAddonFile(): string
    {
        return __FILE__;
    }
}
