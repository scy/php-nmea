<?php

namespace scy\NMEA\Type;

use scy\NMEA\Interfaces\Elevation;
use scy\NMEA\Interfaces\HDOP;
use scy\NMEA\Interfaces\LatLon;
use scy\NMEA\Interfaces\Time;
use scy\NMEA\Sentence;

class Fix extends Sentence implements Elevation, HDOP, LatLon, Time
{
    use \scy\NMEA\Traits\LatLon;

    const TYPE = 'GGA';
    const TYPE_NAME = 'Fix';

    const QUALITIES = [
        0 => 'no fix',
        1 => 'GNSS',
        2 => 'differential',
        3 => 'PPS',
        4 => 'RTK',
        5 => 'float RTK',
        6 => 'estimated',
        7 => 'manual input',
        8 => 'simulation',
    ];

    public function getFieldsToString(): string
    {
        return sprintf(
            '%s,%s ±%.2f, %.1fm @%s (%s)',
            $this->getLatitude(),
            $this->getLongitude(),
            $this->getHorizontalDOP(),
            $this->getElevation(),
            $this->getRawTime(),
            $this->getQuality()
        );
    }

    public function getElevation(): float
    {
        return (float)$this->fields[8];
    }

    public function getHorizontalDOP(): float
    {
        return (float)$this->fields[7];
    }

    public function getRawLatitude(): string
    {
        return $this->fields[1];
    }

    public function getRawLongitude(): string
    {
        return $this->fields[3];
    }

    public function getEW(): string
    {
        return $this->fields[4];
    }

    public function getNS(): string
    {
        return $this->fields[2];
    }

    public function getRawTime(): string
    {
        return $this->fields[0];
    }

    public function getRawQuality(): int
    {
        return (int)$this->fields[5];
    }

    public function getQuality(): string
    {
        $quality = (int)$this->fields[5];
        return static::QUALITIES[$quality] ?? "quality $quality";
    }
}