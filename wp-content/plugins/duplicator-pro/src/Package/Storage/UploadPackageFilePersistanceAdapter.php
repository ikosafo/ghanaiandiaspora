<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Storage;

use Duplicator\Package\DupPackage;
use Duplicator\Package\Storage\UploadInfo;
use Duplicator\Libs\Chunking\Persistance\PersistanceAdapterInterface;
use Duplicator\Libs\Chunking\Iterators\GenericSeekableIteratorInterface;

class UploadPackageFilePersistanceAdapter implements PersistanceAdapterInterface
{
    protected UploadInfo $uploadInfo;
    protected DupPackage $package;

    /**
     * @param UploadInfo $uploadInfo upload info object
     * @param DupPackage $package    package object
     */
    public function __construct(UploadInfo $uploadInfo, DupPackage $package)
    {
        $this->uploadInfo = $uploadInfo;
        $this->package    = $package;
    }

    /**
     * Load data from previous iteration if exists
     *
     * @return mixed
     */
    public function getPersistanceData()
    {
        return empty($this->uploadInfo->chunkPosition) ? null : $this->uploadInfo->chunkPosition;
    }

    /**
     * Save data for next step
     *
     * @param mixed                            $data data to save
     * @param GenericSeekableIteratorInterface $it   current iterator
     *
     * @return bool This function returns true on success, or FALSE on failure.
     */
    public function savePersistanceData($data, GenericSeekableIteratorInterface $it): bool
    {
        $this->uploadInfo->progress      = $it->getProgressPerc();
        $this->uploadInfo->chunkPosition = $data;

        return $this->package->update();
    }

    /**
     * delete data
     *
     * @return bool
     */
    public function deletePersistanceData(): bool
    {
        $this->uploadInfo->chunkPosition = [];
        return $this->package->update();
    }
}
