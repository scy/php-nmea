<?php

namespace scy\NMEA\Interfaces;

use scy\NMEA\Coordinate;

interface LatLon {
    public function getLatitude(): Coordinate;
    public function getLongitude(): Coordinate;
}