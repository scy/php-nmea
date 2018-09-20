<?php

namespace scy\NMEA\Interfaces;

interface DateTime {
    public function getDateTime(): \DateTimeImmutable;
    public function getRawDate(): string;
    public function getRawTime(): string;
}