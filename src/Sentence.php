<?php

namespace scy\NMEA;

use scy\NMEA\Type\Unknown;

abstract class Sentence {

    /** @var string Talker ID for BeiDou (北斗, Chinese satellite navigation system). */
    const TALKER_BEIDOU = 'BD';
    /** @var string Alternative talker ID for BeiDou (北斗, Chinese satellite navigation system) with a "G" prefix. */
    const TALKER_BEIDOU_G = 'GB';
    /** @var string Talker ID for Galileo (European satellite navigation system). */
    const TALKER_GALILEO = 'GA';
    /** @var string Talker ID for GLONASS (ГЛОНАСС, Russian satellite navigation system). */
    const TALKER_GLONASS = 'GL';
    /** @var string Talker ID for generic GNSS or when a combination of navigation systems is used. */
    const TALKER_GNSS = 'GN';
    /** @var string Talker ID for GPS (US satellite navigation system). */
    const TALKER_GPS = 'GP';

    /** @var string[] Maps talker IDs to human-readable names. */
    const TALKERS = [
        self::TALKER_BEIDOU   => 'BeiDou',
        self::TALKER_GALILEO  => 'Galileo',
        self::TALKER_BEIDOU_G => 'BeiDou',
        self::TALKER_GLONASS  => 'GLONASS',
        self::TALKER_GNSS     => 'GNSS',
        self::TALKER_GPS      => 'GPS',
    ];

    /** @var string[] Maps three-letter sentence type codes to the classes that represent them. */
    const TYPES = [
        'GGA' => Type\Fix::class,
        'RMC' => Type\RecommendedMinimumC::class,
        /* Other types that my GPS receiver seems to support, to be implemented:
        'GLL' => 'Latitude/Longitude',
        'GSA' => 'Satellites Used and DOP',
        'GSV' => 'Satellites in View',
        'VTG' => 'Track and Ground Speed',
        */
    ];

    /** @var string A regex to split an NMEA sentence into its respective high-level parts. */
    const REGEX_NMEA = '/\$(?<checksummed>(?<talker>[A-Z]{2})(?<type>[A-Z]{3}),(?<fields>.*?))(?:\*(?<checksum>[0-9A-F]{2}))?$/';

    /** @var string Default format for combined date/time: `2018-09-20T11:20:53.421` */
    const FORMAT_DATETIME = 'Y-m-d\TH:i:s.v';

    /** @var string The type code that this class represents. */
    const TYPE = '???'; // to be overwritten by subclasses
    /** @var string The human-readable type name that this class represents. */
    const TYPE_NAME = '[undefined type]'; // to be overwritten by subclasses

    /** @var string|null Original NMEA string that this object was constructed from, or null if it was constructed from scratch. */
    protected $nmea;
    /** @var string|null Talker ID in the original NMEA string. Null if this object was constructed from scratch. */
    protected $talker;
    /** @var string|null Type ID in the original NMEA string. Null if this object was constructed from scratch. */
    protected $type;
    /** @var string[] Fields of the NMEA representation, not including the first one (talker/type) and the checksum. */
    protected $fields;

    /**
     * Create and return a `Sentence` subclass object from a NMEA sentence string.
     *
     * @todo Support sentences without a checksum.
     *
     * @param string $nmea The NMEA string that should be used for initialization. Leading and trailing whitespace will
     *                     be stripped.
     * @return Sentence
     *
     * @throws ParseException if the NMEA string's structure seems invalid (i.e. does not conform to REGEX_NMEA).
     * @throws ChecksumException if the checksum is incorrect.
     */
    public static function fromNMEA(string $nmea): Sentence
    {
        $nmea = trim($nmea);
        if (!preg_match(static::REGEX_NMEA, $nmea, $data)) {
            throw new ParseException("could not recognize NMEA structure in: $nmea");
        }
        // Keep the original data around so that the object can return it when asked.
        $data['nmea'] = $nmea;

        if (($checksum = static::getChecksumFor($data['checksummed'])) !== $data['checksum']) {
            throw new ChecksumException("checksum is {$data['checksum']}, expected $checksum: $nmea");
        }

        // If we don't have a specific subclass, use `Unknown`.
        $class = static::TYPES[$data['type']] ?? Unknown::class;

        return new $class($data);
    }

    /**
     * Generate the NMEA checksum for a given payload.
     *
     * The checksum is computed by simply XORing all the characters in the payload.
     *
     * @param string $payload The "payload" to compute the checksum for. It is your responsibility to supply only the
     *                        data that really is subject to checksumming in the NMEA standard: Talker/type ID (but
     *                        without the leading `$`), comma, comma-separated list of field values. Don't supply the
     *                        asterisk (`*`) that precedes the checksum in the resulting complete sentence either.
     * @return string The two-character, upper-case hexadecimal checksum.
     */
    public static function getChecksumFor(string $payload): string
    {
        $checksum = 0;
        $len = \strlen($payload);
        for ($pos = 0; $pos < $len; $pos++) {
            $char = \ord($payload[$pos]);
            $checksum = $pos ? ($checksum ^ $char) : $char;
        }
        return sprintf('%02X', $checksum);
    }

    /**
     * Create a new sentence, probably based on NMEA data.
     *
     * @param array $data The following fields will be used:
     *                    `nmea` for supplying the complete original NMEA string. It is only used for later referencing
     *                    and will not be parsed.
     *                    `talker` is the two-character talker ID, e.g. "GP" or "GN".
     *                    `type` is the three-character sentence type ID, e.g. "GGA" or "RMC".
     *                    The concatenation of talker and type is contained in the first field of a NMEA sentence.
     *                    `fields` are the comma-separated, type-specific data fields from the NMEA sentence. You may
     *                    also supply this value already split into an array.
     */
    public function __construct(array $data)
    {
        $this->nmea   = isset($data['nmea'])   ? (string)$data['nmea']   : null;
        $this->setTalker($data['talker']);
        $this->type   = isset($data['type'])   ? (string)$data['type']   : static::TYPE;
        if (isset($data['fields'])) {
            $this->fields = \is_array($data['fields']) ? $data['fields'] : explode(',', $data['fields']);
        }
    }

    /**
     * @return string A short but human-understandable representation of the sentence. The default implementation will
     *                return human-readable talker/type names, followed by a colon and then the fields in either comma-
     *                separated or, if available, human-understandable form.
     */
    public function __toString(): string
    {
        $fields = $this->getFieldsToString();
        return $this->getTalkerAndTypeNames() . (
            $fields === '' ? '' : ": $fields"
        );
    }

    /**
     * @return string The correct NMEA checksum for this sentence.
     */
    public function getChecksum(): string
    {
        return static::getChecksumFor(ltrim($this->toNMEAWithoutChecksum(), '$'));
    }

    /**
     * @return string This default implementation will simply return all the fields as a comma-separated string.
     */
    public function getFieldsToString(): string
    {
        return \count($this->fields) ? implode(',', $this->fields) : '';
    }

    /**
     * @return string The two-character talker ID of this object.
     */
    public function getTalker(): string
    {
        return $this->talker;
    }

    /**
     * @param string $talker The new talker ID. It will be upper-cased and limited to two characters.
     * @return $this
     */
    public function setTalker(string $talker): self
    {
        $this->talker = strtoupper(str_pad(substr($talker, 0, 2), 2, '?'));
        return $this;
    }

    /**
     * @return string The human-understandable name for this object's talker ID, or `[Talker XX]` (with XX being the ID)
     *                for unknown talker IDs.
     */
    public function getTalkerName(): string
    {
        return static::TALKERS[$this->talker] ?? "[Talker {$this->talker}]";
    }

    /**
     * @return string The human-understandable name for this object's type ID.
     */
    public function getTypeName(): string
    {
        return static::TYPE_NAME;
    }

    /**
     * @return string A concatenation of talker and type names, e.g. `GPS Fix`.
     */
    public function getTalkerAndTypeNames(): string
    {
        return $this->getTalkerName() . ' ' . $this->getTypeName();
    }

    /**
     * @return string The complete, `$`-prefixed and checksummed NMEA string representation of this object.
     */
    public function toNMEA(): string
    {
        $payload = $this->toNMEAWithoutChecksum();
        return "$payload*" . static::getChecksumFor(ltrim($payload, '$'));
    }

    /**
     * @return string The `$`-prefixed but not checksummed NMEA string representation of this object.
     */
    public function toNMEAWithoutChecksum(): string
    {
        return sprintf('$%s%s,%s',
            $this->getTalker(),
            $this::TYPE,
            implode(',', $this->fields)
        );
    }
}