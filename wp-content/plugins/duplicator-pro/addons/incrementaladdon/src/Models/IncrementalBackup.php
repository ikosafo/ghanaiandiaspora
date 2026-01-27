<?php

namespace Duplicator\Addons\IncrementalAddon\Models;

use Duplicator\Utils\Logging\DupLog;
use Duplicator\Package\DupPackage;
use Duplicator\Package\Storage\UploadInfo;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalSchedule;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalStorage;

class IncrementalBackup extends DupPackage
{
    /**
     * Get backup type
     *
     * @return string
     */
    public static function getBackupType(): string
    {
        return 'IncrementalBackup';
    }

    /**
     * Add upload info
     *
     * @param int[] $storage_ids storage ids
     *
     * @return void
     */
    protected function addUploadInfos($storage_ids)
    {
        DupLog::traceObject('ADDING UPLOAD INFOS', $storage_ids);
        $this->upload_infos = [];
        foreach ($storage_ids as $storage_id) {
            if (IncrementalStorage::exists($storage_id) == false) {
                DupLog::trace("Storage id {$storage_id} not found");
                continue;
            }
            $this->upload_infos[] = new UploadInfo($storage_id, IncrementalStorage::class);
        }
        DupLog::trace('NUMBER UPLOAD INFOS ADDED: ' . count($this->upload_infos));
    }

    /**
     * Get schedule if is set
     *
     * @return ?IncrementalSchedule
     */
    public function getSchedule(): ?IncrementalSchedule
    {
        if ($this->schedule_id === -1) {
            return null;
        }

        if (($schedule = IncrementalSchedule::getById($this->schedule_id)) === false) {
            DupLog::traceBacktrace("No IncrementalSchedule found: id {$this->schedule_id}");
            return null;
        }

        return $schedule;
    }
}
