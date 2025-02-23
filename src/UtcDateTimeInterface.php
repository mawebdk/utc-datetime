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