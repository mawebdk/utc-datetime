<?php
namespace MawebDK\UtcDateTime;

use DateTimeImmutable;

/**
 * UtcDateTimeInterface was created so that parameter, return and property type declarations may accept either UtcDateTime or
 * UtcDateTimeImmutable as a value.
 */
interface UtcDateTimeInterface
{
    /**
     * Minimum supported Unix timestamp representing 1000-01-01 00:00:00 UTC.
     */
    public const int UNIX_TIMESTAMP_MIN = -30610224000;

    /**
     * Maximum supported Unix timestamp representing 9999-12-31 23:59:59 UTC.
     */
    public const int UNIX_TIMESTAMP_MAX = 253402300799;

    /**
     * Minimum supported Unix milli timestamp representing 1000-01-01 00:00:00.000 UTC.
     */
    public const int UNIX_MILLI_TIMESTAMP_MIN = -30610224000000;

    /**
     * Maximum supported Unix milli timestamp representing 9999-12-31 23:59:59.999 UTC.
     */
    public const int UNIX_MILLI_TIMESTAMP_MAX = 253402300799999;

    /**
     * Minimum supported Unix micro timestamp representing 1000-01-01 00:00:00.000000 UTC.
     */
    public const int UNIX_MICRO_TIMESTAMP_MIN = -30610224000000000;

    /**
     * Maximum supported Unix micro timestamp representing 9999-12-31 23:59:59.999999 UTC.
     */
    public const int UNIX_MICRO_TIMESTAMP_MAX = 253402300799999999;

    /**
     * Minimum supported MySQL date and time with microseconds.
     */
    public const string MYSQL_DATETIME6_MIN = '1000-01-01 00:00:00.000000';

    /**
     * Maximum supported MySQL date and time with microseconds.
     */
    public const string MYSQL_DATETIME6_MAX = '9999-12-31 23:59:59.999999';

    /**
     * Gets the Unix timestamp.
     * @return int   Unix timestamp representing the UTC date and time.
     */
    public function getUnixTimestamp(): int;

    /**
     * Gets the Unix timestamp with milliseconds.
     * @return int   Unix timestamp with milliseconds representing the UTC date and time.
     */
    public function getUnixMilliTimestamp(): int;

    /**
     * Gets the Unix timestamp with microseconds.
     * @return int   Unix timestamp with microseconds representing the UTC date and time.
     */
    public function getUnixMicroTimestamp(): int;

    /**
     * Returns date and time formatted as a MySQL date and time with microseconds.
     * @return string   Formatted date and time as a MySQL date and time with microseconds, e.g. 1999-12-31 23:59:59.999999.
     */
    public function formatMysqlDateTime6(): string;

    /**
     * Returns a DateTimeImmutable object representing the date and time.
     * @return DateTimeImmutable   DateTimeImmutable object representation the date and time.
     */
    public function getDateTimeImmutable(): DateTimeImmutable;
}