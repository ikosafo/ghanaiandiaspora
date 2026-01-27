<?php

/**
 * Incremental Template for incremental backups
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\IncrementalAddon\Models;

use Duplicator\Models\TemplateEntity;

/**
 * Incremental Template for incremental backups
 * This class extends the TemplateEntity class and is used for incremental backup templates
 */
class IncrementalTemplate extends TemplateEntity
{
    /**
     * Entity type
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'IncrementalTemplate';
    }

    /**
     * Constructor for IncrementalTemplate
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = __('Incremental Backup Template', 'duplicator-pro');
    }
}
