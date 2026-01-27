<?php

namespace Duplicator\Addons\IncrementalAddon;

use Duplicator\Utils\Logging\DupLog;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalSchedule;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalStorage;
use Duplicator\Addons\IncrementalAddon\Models\IncrementalTemplate;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Models\ScheduleEntity;
use Duplicator\Package\Runner;
use Error;
use Exception;

/**
 * Incremental backup manager
 * This class is responsible for managing the incremental backup process, manage storage schedule and template
 */
final class IncBackupMng
{
    private static ?self $instance = null;
    private IncrementalStorage $storage;
    private IncrementalSchedule $schedule;
    private IncrementalTemplate $template;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->initStorage();
        $this->initIncrementalTemplate();
        // This must be called after template and storage are initialized
        $this->initIncrementalSchedule();
    }

    /**
     * Get singleton instance
     *
     * @return IncBackupMng
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Check if incremental backup is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->schedule->isActive();
    }

    /**
     * Disconnect storage
     *
     * @return bool True if success, false otherwise
     */
    public function disconnectStorage(): bool
    {
        return $this->storage->revokeAuthorization();
    }

    /**
     * Activate Schedule
     *
     * @return bool True if success, false otherwise
     */
    public function activate(): bool
    {
        if (!$this->storage->isValid()) {
            return false;
        }

        $this->schedule->setActive(true);
        return $this->schedule->save();
    }

    /**
     * Deactivate Schedule
     *
     * @return bool True if success, false otherwise
     */
    public function deactivate(): bool
    {
        $this->schedule->setActive(false);
        return $this->schedule->save();
    }

    /**
     * Get schedule repeat type
     *
     * @return int
     */
    public function getScheduleRepeatType(): int
    {
        return $this->schedule->repeat_type;
    }

    /**
     * Get schedule run every value
     *
     * @return int
     */
    public function getScheduleRunEvery(): int
    {
        return $this->schedule->run_every;
    }

    /**
     * Get schedule day of month
     *
     * @return int
     */
    public function getScheduleDayOfMonth(): int
    {
        return $this->schedule->day_of_month;
    }

    /**
     * Check if the given day is set in the schedule
     *
     * @param string $day Day to check (mon, tue, wed, thu, fri, sat, sun)
     *
     * @return bool
     */
    public function isScheduleDaySet(string $day): bool
    {
        return $this->schedule->isDaySet($day);
    }

    /**
     * Hook function to add at active schedules at duplicator_get_active_schedules
     *
     * @param ScheduleEntity[] $schedules Active schedules
     *
     * @return ScheduleEntity[]
     */
    public function getProcessActiveSchedules(array $schedules): array
    {
        if ($this->schedule->isActive()) {
            $schedules[] = $this->schedule;
        }

        return $schedules;
    }

    /**
     * Get schedule start time piece
     *
     * @param int $piece Piece index (0 for hour, 1 for minute)
     *
     * @return int
     */
    public function getScheduleStartTimePiece(int $piece): int
    {
        return $this->schedule->getStartTimePiece($piece);
    }

    /**
     * Update data from http request, this method don't save data, just update object properties
     *
     * @param string $message Message
     *
     * @return bool True if success and all data is valid, false otherwise
     */
    public function updateFromHttpRequest(&$message = ''): bool
    {
        try {
            $this->storage->updateFromHttpRequest($message);
            if ($this->storage->save() == false) {
                throw new Exception(__('Is not possible to update the storage settings', 'duplicator-pro'));
            }

            $this->schedule->setFromInput(SnapUtil::INPUT_REQUEST);
            if ($this->schedule->save() == false) {
                throw new Exception(__('Is not possible to update the schedule settings', 'duplicator-pro'));
            }
        } catch (Exception | Error $e) {
            $message = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Get DupCloud storage, if don't exists create it
     *
     * @return void
     */
    private function initStorage(): void
    {
        if (
            ($storages = IncrementalStorage::getAllBySType(IncrementalStorage::getSType())) !== false &&
            count($storages) > 0
        ) {
            $this->storage = $storages[0];
        } else {
            $this->storage = new IncrementalStorage();
            $this->storage->save();
        }
    }

    /**
     * Get Incremental schedule, if don't exists create it
     *
     * @return void
     */
    private function initIncrementalSchedule(): void
    {
        if (($schedules = IncrementalSchedule::getAll()) === false) {
            throw new Exception('Error reading incremental schedule');
        }

        switch (count($schedules)) {
            case 0:
                $this->schedule = new IncrementalSchedule();
                $this->schedule->setTemplateAndStorage(
                    $this->template->getId(),
                    $this->storage->getId()
                );
                $this->schedule->save();
                break;
            case 1:
                $this->schedule = $schedules[0];
                break;
            default:
                throw new Exception('More than one incremental schedule found');
        }
    }

    /**
     * Get Incremental template, if don't exists create it
     *
     * @return void
     */
    private function initIncrementalTemplate(): void
    {
        if (($templates = IncrementalTemplate::getAll()) === false) {
            throw new Exception('Error reading incremental template');
        }

        switch (count($templates)) {
            case 0:
                $this->template = new IncrementalTemplate();
                $this->template->save();
                break;
            case 1:
                $this->template = $templates[0];
                break;
            default:
                throw new Exception('More than one incremental template found');
        }
    }

    /**
     * Prevent cloning of the instance
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Test storage connection and validity
     *
     * @param ?string $message Reference to store test result message
     *
     * @return bool True if storage is valid and test successful, false otherwise
     */
    public function testStorage(?string &$message = ''): bool
    {
        return $this->storage->isValid($message);
    }

    /**
     * Get storage validity status
     *
     * @return bool
     */
    public function isStorageValid(): bool
    {
        return $this->storage->isValid();
    }

    /**
     * Get next scheduled execution time
     *
     * @return string Return formatted next execution time or false if not scheduled
     */
    public function getNextRunTime(): string
    {
        if ($this->schedule->next_run_time === -1) {
            return __('Not scheduled', 'duplicator-pro');
        } else {
            return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $this->schedule->next_run_time);
        }
    }

    /**
     * Get storage id
     *
     * @return int Return storage id
     */
    public function getStorageId(): int
    {
        return $this->storage->getId();
    }

    /**
     * Get storage user name
     *
     * @return string Return storage user name or empty string if not available
     */
    public function getStorageUserName(): string
    {
        return $this->storage->getUserName();
    }

    /**
     * Get storage user email
     *
     * @return string Return storage user email or empty string if not available
     */
    public function getStorageUserEmail(): string
    {
        return $this->storage->getUserEmail();
    }

    /**
     * Get storage total space
     *
     * @return int Return total space in bytes or 0 if not available
     */
    public function getStorageTotalSpace(): int
    {
        return $this->storage->getTotalSpace();
    }

    /**
     * Get storage free space
     *
     * @return int Return free space in bytes or 0 if not available
     */
    public function getStorageFreeSpace(): int
    {
        return $this->storage->getFreeSpace();
    }

    /**
     * Run now incremental backup
     *
     * @return void
     */
    public function runNow(): void
    {
        DupLog::trace("Running now incremental backup");
        $this->schedule->insertNewPackage(true);
        Runner::kickOffWorker();
    }
}
