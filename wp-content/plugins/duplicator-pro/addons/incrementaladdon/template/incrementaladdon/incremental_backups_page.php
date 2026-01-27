<?php

/**
 * Template for Incremental Backups page
 *
 * @package   Duplicator\Addons\IncrementalAddon
 * @copyright (c) 2024, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Addons\DupCloudAddon\Utils\DupCloudClient;
use Duplicator\Addons\IncrementalAddon\IncBackupMng;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalBackup;
use Duplicator\Views\PackageListTable;

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 */

$incBackupMng = IncBackupMng::getInstance();
$isActive     = $incBackupMng->isActive();
$pager        = new PackageListTable();
$perPage      = $pager->get_per_page();
$currentPage  = $pager->get_pagenum();
$offset       = ($currentPage - 1) * $perPage;


// Render status information section
$tplMng->render('incrementaladdon/parts/inc_page_info');

// Render options section
$tplMng->render('incrementaladdon/parts/inc_page_options');

// Render backups table
$tplMng->render(
    'incrementaladdon/parts/inc_page_backups',
    [
        'pager'       => $pager,
        'perPage'     => $perPage,
        'offset'      => $offset,
        'currentPage' => $currentPage,
    ]
);

// Render scripts
$tplMng->render('incrementaladdon/incremental_scripts');

$tplMng->render(
    'admin_pages/packages/packages_scripts',
    [
        'perPage'          => $perPage,
        'offset'           => $offset,
        'currentPage'      => $currentPage,
        'stattiBackupType' => IncrementalBackup::getBackupType(),
    ]
);
