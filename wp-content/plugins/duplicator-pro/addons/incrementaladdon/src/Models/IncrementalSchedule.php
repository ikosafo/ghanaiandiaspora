<?php

namespace Duplicator\Addons\IncrementalAddon\Models;

use Duplicator\Utils\Logging\DupLog;
use Duplicator\Models\ScheduleEntity;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalTemplate;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalBackup;

/**
 * Incremental Schedule for incremental backups
 * This class extends the ScheduleEntity class and is used to schedule incremental backups
 */
class IncrementalSchedule extends ScheduleEntity
{
    /**
     * Entity type
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'IncrementalSchedule';
    }

    /**
     * Constructor for IncrementalSchedule
     */
    public function __construct()
    {
        $this->active = false;
        $this->name   = __('Incremental Backup Schedule', 'duplicator-pro');
        parent::__construct();
    }

    /**
     * Set template and storage
     *
     * @param int $templateId Template ID
     * @param int $storageId  Storage ID
     *
     * @return void
     */
    public function setTemplateAndStorage(int $templateId, int $storageId): void
    {
        $this->template_id = $templateId;
        $this->storage_ids = [$storageId];
    }

    /**
     * Set data from query input
     *
     * @param int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV, SnapUtil::INPUT_REQUEST
     *
     * @return bool true on success or false on failure
     */
    public function setFromInput($type): bool
    {
        /**
         * Update only repeat type and run every properties
         */
        $input = SnapUtil::getInputFromType($type);

        $this->setFromArrayKey(
            $input,
            function ($key, $val) {
                if (is_string($val)) {
                    $val = stripslashes($val);
                }
                return (is_scalar($val) ? SnapUtil::sanitizeNSChars($val) : $val);
            }
        );

        $this->repeat_type  = intval($this->repeat_type);
        $this->day_of_month = intval($this->day_of_month);

        switch ($this->repeat_type) {
            case ScheduleEntity::REPEAT_HOURLY:
                $this->run_every = intval($input['_run_every_hours']);
                DupLog::trace("run every hours: " . $input['_run_every_hours']);
                break;
            case ScheduleEntity::REPEAT_DAILY:
                $this->run_every = intval($input['_run_every_days']);
                DupLog::trace("run every days: " . $input['_run_every_days']);
                break;
            case ScheduleEntity::REPEAT_MONTHLY:
                $this->run_every = intval($input['_run_every_months']);
                DupLog::trace("run every months: " . $input['_run_every_months']);
                break;
            case ScheduleEntity::REPEAT_WEEKLY:
                $this->setWeekdaysFromRequest($input);
                break;
        }

        $this->setStartDateTime($input['_start_time']);

        // Update cron schedule after all input changes
        $this->updateCronSchedule();

        return true;
    }

    /**
     * Get schedule template object or false if don't exists
     *
     * @return false|IncrementalTemplate
     */
    public function getTemplate()
    {
        if ($this->template_id > 0) {
            $template = IncrementalTemplate::getById($this->template_id);
        } else {
            $template = null;
        }

        if (!$template instanceof IncrementalTemplate) {
            return false;
        }

        return $template;
    }

    /**
     * Get new package
     *
     * @param bool $run_now If true the Backup creation is started immediately, otherwise it is scheduled
     *
     * @return IncrementalBackup
     */
    protected function getNewPackage(bool $run_now = false): IncrementalBackup
    {
        $type = ($run_now ? IncrementalBackup::EXEC_TYPE_RUN_NOW : IncrementalBackup::EXEC_TYPE_SCHEDULED);
        return new IncrementalBackup(
            $type,
            $this->storage_ids,
            $this->getTemplate(),
            $this
        );
    }
}
