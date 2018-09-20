# PHP NMEA parser and utility library

Support reading, understanding and creating [NMEA 0183](https://en.wikipedia.org/wiki/NMEA_0183) data that is the de-facto standard for GNSS (GPS) receivers and software handling GNSS data.

## Status

_(last update of this section: 2018-09-20)_

This is a personal side project and currently a work in progress. 
I am using it to understand the data returned by my GNSS receiver and manipulate it before forwarding it to my Android tablet (see the [example](examples/filter-and-manipulate.php)).

It's currently in the 0.x phase of things. 
The API may change, but right now I'm quite satisfied with it.

It currently only supports the types that I need, namely `GGA` and `RMC`. 
More will be added once I need them or if somebody contributes them.

Speaking of contributions, I'm basically open for them, but please create an issue _before_ doing larger changes/additions if you want them to be accepted.

## To Do

* Improve API docs. Currently, only `Sentence` and the example have proper documentation.
* Check format of fields when creating subtype objects.
* Real support for creating objects from scratch instead of from an NMEA string.
  * Properly initialize `fields` property (e.g. ensure correct number of fields).
* Add tests.

## Meta

This library was written by Tim Weber ([@scy](https://github.com/scy)). 
Its official home is [scy/php-nmea on GitHub](https://github.com/scy/php-nmea).