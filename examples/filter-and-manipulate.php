<?php

/**
 * This example can be used to read NMEA from stdin and convert the two types of sentences that are compatible with the
 * MockGeoFix Android app <https://play.google.com/store/apps/details?id=github.luv.mockgeofix> in a format that it
 * understands.
 *
 * Since the receiver I've developed this for supports not only GPS, but also GLONASS and Galileo, but MockGeoFix
 * requires the talker ID (i.e. the first two characters) to be GP(S), I'm faking the talker to be of that type.
 *
 * Also, the receiver has a high refresh rate that I don't need, which is why the example includes basic rate limiting
 * functionality.
 */



use scy\NMEA\Exception;
use scy\NMEA\Sentence;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Block sentences of the same class if they appeared less than 0.5 seconds ago.
 *
 * @param Sentence $sentence The sentence to check for.
 * @return bool False if this function has been called with the same (sub)class of sentence less than 0.5 seconds ago,
 *              else true.
 */
function ratelimit_pass(Sentence $sentence)
{
    /** @var float[] $lastPass Keeps track of when each class of sentence has most recently resulted in `true` being returned. */
    static $lastPass = [];

    $now = microtime(true);
    $class = get_class($sentence);

    // If there's already an entry and it's not longer than 0.5 seconds ago, return false.
    if (isset($lastPass[$class]) && $lastPass[$class] >= $now - 0.5) {
        return false;
    }

    // If there's no entry or it's old enough, update (or create) an entry and track the time.
    $lastPass[$class] = $now;
    return true;
}

// Read from stdin until it's no longer available.
while (($line = fgets(STDIN)) !== false) {
    try {
        // Create a Sentence subclass instance from the NMEA string.
        $sentence = Sentence::fromNMEA(trim($line));

        // If the type is GGA or RMC and rate limiting is fine with it, change the talker to GPS and output in Android-compatible format.
        switch (get_class($sentence)) {
            case \scy\NMEA\Type\Fix::class:
            case \scy\NMEA\Type\RecommendedMinimumC::class:
                if (ratelimit_pass($sentence)) {
                    $sentence->setTalker($sentence::TALKER_GPS);
                    echo 'geo nmea ' . $sentence->toNMEA() . "\n";
                }
        }
    } catch (Exception $e) {
        // Parser or checksum exceptions shouldn't lead to termination, but send them to stderr.
        fwrite(STDERR, $e->getMessage() . "\n");
    }
}