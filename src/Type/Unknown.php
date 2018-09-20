<?php

namespace scy\NMEA\Type;

use scy\NMEA\Sentence;

class Unknown extends Sentence
{

    const TYPE_NAME = '[unknown type]';

    public function getTypeName(): string
    {
        return "[Type {$this->type}]";
    }

}