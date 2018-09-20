<?php

namespace scy\NMEA;

class Coordinate
{
    const REGEX_NMEA = '/^(?<deg>[+-]?\d{1,3})(?<min>\d{2}\.\d+)$/';

    protected $value;

    public function __construct(string $nmea)
    {
        if (preg_match(static::REGEX_NMEA, $nmea, $matches)) {
            $this->value = (int)$matches['deg'] + ((float)$matches['min'] / 60);
        } else {
            throw new ParseException("could not parse coordinate value: $nmea");
        }
    }

    public function __toString(): string
    {
        return sprintf('%.7f', $this->value);
    }

    public function getValue(): float
    {
        return $this->value;
    }
}