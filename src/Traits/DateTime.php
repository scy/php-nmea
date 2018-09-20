<?php

namespace scy\NMEA\Traits;

trait DateTime
{
    abstract public function getRawDate(): string;
    abstract public function getRawTime(): string;

    public function getDateTime(): \DateTimeImmutable
    {
        list($time, $frac) = explode('.', $this->getRawTime(), 2);
        $frac = str_pad($frac, 6, '0');

        return \DateTimeImmutable::createFromFormat(
            'dmyHis.u e', $this->getRawDate() . "$time.$frac UTC"
        );
    }
}