<?php

class ConstructionStagesCreate
{
    public $name;
    public $startDate;
    public $endDate;
    public $duration;
    public $durationUnit;
    public $color;
    public $externalId;
    public $status;

    /**
     * ConstructionStagesCreate constructor.
     *
     * @param mixed $data
     */
    public function __construct($data) {

        if(is_object($data)) {

            $vars = get_object_vars($this);

            foreach ($vars as $name => $value) {

                if (isset($data->$name)) {

                    $this->$name = $data->$name;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @throws InvalidArgumentException
     */
    public function setName(string $name): void
    {
        if(strlen($name) > 255) {
            throw new InvalidArgumentException("Name must be maximum of 255 characters");
        }
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     * @throws InvalidArgumentException
     */
    public function setStartDate($startDate): void
    {
        $regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/';
        if (preg_match($regex, $startDate)) {
            $this->startDate = $startDate;
        } else {
            throw new InvalidArgumentException('Invalid start date format. The format must be in ISO8601 format, e.g. 2022-12-31T14:59:00Z');
        }
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     * @throws InvalidArgumentException
     */
    public function setEndDate($endDate): void
    {
        if ($endDate !== null && !strtotime($endDate)) {
            throw new InvalidArgumentException('End date must be a valid datetime string');
        }
        if ($endDate !== null && strtotime($endDate) <= strtotime($this->startDate)) {
            throw new InvalidArgumentException('End date must be later than start date');
        }
        $this->endDate = $endDate;
    }
    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * @param mixed $durationUnit
     * @throws InvalidArgumentException
     */
    public function setDurationUnit($durationUnit = 'DAYS'): void
    {
        $validDurationUnits = array('HOURS', 'DAYS', 'WEEKS');
        if ($durationUnit === null) {
            $durationUnit = 'DAYS';
        }
        if (in_array(strtoupper($durationUnit), $validDurationUnits)) {
            $this->durationUnit = strtoupper($durationUnit);
        } else {
            throw new InvalidArgumentException('Invalid duration unit');
        }
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     * @throws InvalidArgumentException
     */
    public function setColor($color): void
    {
        $regex = "/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/";
        if ($color === null || preg_match($regex, $color)) {
            $this->color = $color;
        } else {
            throw new InvalidArgumentException('Invalid color format');
        }
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     * @throws InvalidArgumentException
     */
    public function setExternalId(?string $externalId): void
    {
        if ($externalId !== null && strlen($externalId) > 255) {
            throw new InvalidArgumentException("External ID must be maximum of 255 characters or null");
        }
        $this->externalId = $externalId;
    }
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @throws InvalidArgumentException
     */
    public function setStatus(?string $status): void
    {
        $validStatuses = array('NEW', 'PLANNED', 'DELETED');
        if ($status === null) {
            $this->status = 'NEW';
        } else if (in_array(strtoupper($status), $validStatuses)) {
            $this->status = strtoupper($status);
        } else {
            throw new InvalidArgumentException('Invalid status');
        }
    }

    /**
     *@throws InvalidArgumentException If the start date is invalid or the end date is invalid or earlier than the start date.
     *@throws InvalidArgumentException If the duration unit is invalid.
     *@return void
     */
    public function calculateDuration() {

        if (!$this->startDate || !is_string($this->startDate)) {
            throw new InvalidArgumentException('Invalid start date');
        }

        if ($this->endDate && !is_string($this->endDate)) {
            throw new InvalidArgumentException('Invalid end date');
        }

        $startTimestamp = strtotime($this->startDate);
        $endTimestamp = $this->endDate ? strtotime($this->endDate) : null;

        if ($endTimestamp && $endTimestamp <= $startTimestamp) {
            throw new InvalidArgumentException('End date must be later than start date');
        }

        switch ($this->durationUnit) {
            case 'HOURS':
                $duration = $endTimestamp ? ceil(($endTimestamp - $startTimestamp) / 3600) : null;
                break;
            case 'DAYS':
                $duration = $endTimestamp ? ceil(($endTimestamp - $startTimestamp) / 86400) : null;
                break;
            case 'WEEKS':
                $duration = $endTimestamp ? ceil(($endTimestamp - $startTimestamp) / 604800) : null;
                break;
            default:
                throw new InvalidArgumentException('Invalid duration unit');
        }

        $this->duration = $duration !== null ? (float) $duration : null;
    }
}