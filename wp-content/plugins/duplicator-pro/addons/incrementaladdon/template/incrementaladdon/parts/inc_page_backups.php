<?php

/**
 * Template for Incremental Backups Info Section
 *
 * @package   Duplicator\Addons\IncrementalAddon
 * @copyright (c) 2024, Snap Creek LLC
 */

use Duplicator\Addons\IncrementalAddon\Models\IncrementalBackup;
use Duplicator\Core\Views\TplMng;
use Duplicator\Package\DupPackage;
use Duplicator\Package\PackageUtils;
use Duplicator\Views\PackageListTable;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 */

$totalElements = PackageUtils::getNumPackages([IncrementalBackup::getBackupType()]);
$pager         = $tplMng->getDataValueObjRequired('pager', PackageListTable::class);
$perPage       = $tplMng->getDataValueInt('perPage', 10);
$offset        = $tplMng->getDataValueInt('offset', 0);
$currentPage   = $tplMng->getDataValueInt('currentPage', 1);
$statusActive  = DupPackage::isPackageRunning();

?>
<table class="widefat dup-table-list dup-packtbl striped margin-top-1" aria-label="Backup List">
    <?php
    $tplMng->render(
        'admin_pages/packages/packages_table_head',
        ['totalElements' => $totalElements]
    );

    if ($totalElements == 0) {
        $tplMng->render('admin_pages/packages/no_elements_row');
    } else {
        IncrementalBackup::dbSelectByStatusCallback(
            function (IncrementalBackup $package): void {
                TplMng::getInstance()->render(
                    'admin_pages/packages/package_row',
                    ['package' => $package]
                );
            },
            [],
            $perPage,
            $offset,
            '`id` DESC',
            [
                IncrementalBackup::getBackupType(),
            ]
        );
    }
    $tplMng->render(
        'admin_pages/packages/packages_table_foot',
        ['totalElements' => $totalElements]
    ); ?>
</table>

<?php if ($totalElements > $perPage) { ?>
    <form id="form-duplicator-nav" method="post">
        <?php wp_nonce_field('dpro_package_form_nonce'); ?>
        <div class="dup-paged-nav tablenav">
            <?php if ($statusActive > 0) : ?>
                <div id="dpro-paged-progress" style="padding-right: 10px">
                    <i class="fas fa-circle-notch fa-spin fa-lg fa-fw"></i>
                    <i><?php esc_html_e('Paging disabled during build...', 'duplicator-pro'); ?></i>
                </div>
            <?php else : ?>
                <div id="dpro-paged-buttons">
                    <?php $pager->display_pagination($totalElements, $perPage); ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
<?php } else { ?>
    <div style="float:right; padding:10px 5px">
        <?php echo esc_html(sprintf(_n('%s item', '%s items', $totalElements, 'duplicator-pro'), $totalElements)); ?>
    </div>
<?php }
