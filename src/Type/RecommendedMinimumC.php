<?php

namespace scy\NMEA\Type;

use scy\NMEA\Interfaces\DateTime;
use scy\NMEA\Interfaces\LatLon;
use scy\NMEA\Interfaces\Time;
use scy\NMEA\Sentence;

class RecommendedMinimumC extends Sentence implements LatLon, DateTime, Time
{
    use \scy\NMEA\Traits\DateTime;
    use \scy\NMEA\Traits\LatLon;

    const TYPE = 'RMC';
    const TYPE_NAME = 'Rec. Minimum C';

    const STATUS = [
        'A' => 'OK',
        'V' => 'Warning',
    ];

    public function getFieldsToString(): string
    {
        return sprintf(
            '%s,%s, %.1fkps @%s (%s)',
            $this->getLatitude(),
            $this->getLongitude(),
            $this->getRawGroundSpeed(),
            $this->getDateTime()->format(static::FORMAT_DATETIME),
            $this->getStatus()
        );
    }

    public function getRawLatitude(): string
    {
        return $this->fields[2];
    }

    public function getRawLongitude(): string
    {
        return $this->fields[4];
    }

    public function getEW(): string
    {
        return $this->fields[5];
    }

    public function getNS(): string
    {
        return $this->fields[3];
    }

    public function getRawDate(): string
    {
        return $this->fields[8];
    }

    public function getRawTime(): string
    {
        return $this->fields[0];
    }

    public function getRawGroundSpeed(): string
    {
        return $this->fields[6];
    }

    public function getRawStatus(): string
    {
        return $this->fields[1];
    }

    public function getStatus(): string
    {
        $status = $this->fields[1];
        return static::STATUS[$status] ?? "status $status";
    }
}