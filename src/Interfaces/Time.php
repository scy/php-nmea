<?php

namespace scy\NMEA\Interfaces;

interface Time {
    public function getRawTime(): string;
}