<?php

namespace scy\NMEA\Traits;

use scy\NMEA\Coordinate;

trait LatLon
{
    abstract public function getRawLatitude(): string;
    abstract public function getRawLongitude(): string;
    abstract public function getEW(): string;
    abstract public function getNS(): string;

    public function getLatitude(): Coordinate
    {
        return new Coordinate(
            ($this->getNS() === 'N' ? '' : '-')
            . $this->getRawLatitude()
        );
    }

    public function getLongitude(): Coordinate
    {
        return new Coordinate(
            ($this->getEW() === 'E' ? '' : '-')
            . $this->getRawLongitude()
        );
    }
}