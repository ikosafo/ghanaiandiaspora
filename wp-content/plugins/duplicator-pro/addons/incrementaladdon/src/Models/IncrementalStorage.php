<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\IncrementalAddon\Models;

use Duplicator\Addons\DupCloudAddon\Models\DupCloudStorage;
use Duplicator\Addons\DupCloudAddon\Utils\DupCloudClient;
use Duplicator\Addons\DupCloudAddon\Utils\DupCloudStorageAdapter;

class IncrementalStorage extends DupCloudStorage
{
    /**
     * Get new storage object by type
     *
     * @return self
     */
    protected static function getNewStorageInstance(): self
    {
        return new self();
    }

    /**
     * Entity type
     *
     * @return string
     */
    public static function getType(): string
    {
        // Overwrite the storage type beacuse this is a special storage type
        return 'IncrementalStorage';
    }

    /**
     * Return the storage type
     *
     * @return int
     */
    public static function getSType(): int
    {
        return 18; // Unique ID for Incremental Storage
    }

    /**
     * If true the storage type will not be shown in the storage list, and is not selectable
     *
     * @return bool
     */
    public static function isHidden(): bool
    {
        return true;
    }

    /**
     * Get the storage adapter
     *
     * @return DupCloudStorageAdapter
     */
    protected function getAdapter(): DupCloudStorageAdapter
    {
        if ($this->adapter === null) {
            $this->adapter = new DupCloudStorageAdapter($this->config['accessToken'], DupCloudClient::BACKUP_TYPE_INCREMENTAL);
        }

        return $this->adapter;
    }
}
