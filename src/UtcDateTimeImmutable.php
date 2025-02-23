<?php
namespace MawebDK\UtcDateTime;

/**
 * Representation of a UTC date and time.
 * This class behaves the same as UtcDateTime, except new objects are returned when modification methods are called.
 */
class UtcDateTimeImmutable extends UtcDateTimeAbstract implements UtcDateTimeInterface
{
    /**
     * Returns a new UtcDateTimeImmutable object representing the current UTC date and time.
     * @return self                   UtcDateTimeImmutable object representing the current UTC date and time.
     * @throws UtcDateTimeException   Failed to create a new UtcDateTimeImmutable object representing the current UTC date and time.
     */
    public static function now(): self
    {
        return new self(dateTimeImmutable: self::createDateTimeImmutableWithCurrentUtcDateTime());
    }

    /**
     * Returns a new UtcDateTimeImmutable object representing the supplied Unix timestamp.
     * @param int $unixTimestamp      Unix timestamp.
     * @return self                   UtcDateTimeImmutable object representing the supplied Unix timestamp.
     * @throws UtcDateTimeException   Failed to create a new UtcDateTimeImmutable object representing the supplied Unix timestamp.
     */
    public static function createFromUnixTimestamp(int $unixTimestamp): self
    {
        return new self(dateTimeImmutable: self::createDateTimeImmutableFromUnixTimestamp(unixTimestamp: $unixTimestamp));
    }

    /**
     * Returns a new UtcDateTimeImmutable object representing the supplied Unix milli timestamp.
     * @param int $unixMilliTimestamp   Unix milli timestamp.
     * @return self                     UtcDateTimeImmutable object representing the supplied Unix milli timestamp.
     * @throws UtcDateTimeException     Failed to create a new UtcDateTimeImmutable object representing the supplied Unix milli timestamp.
     */
    public static function createFromUnixMilliTimestamp(int $unixMilliTimestamp): self
    {
        return new self(dateTimeImmutable: self::createDateTimeImmutableFromUnixMilliTimestamp(unixMilliTimestamp: $unixMilliTimestamp));
    }

    /**
     * Returns a new UtcDateTimeImmutable object representing the supplied Unix micro timestamp.
     * @param int $unixMicroTimestamp   Unix micro timestamp.
     * @return self                     UtcDateTimeImmutable object representing the supplied Unix micro timestamp.
     * @throws UtcDateTimeException     Failed to create a new UtcDateTimeImmutable object representing the supplied Unix micro timestamp.
     */
    public static function createFromUnixMicroTimestamp(int $unixMicroTimestamp): self
    {
        return new self(dateTimeImmutable: self::createDateTimeImmutableFromUnixMicroTimestamp(unixMicroTimestamp: $unixMicroTimestamp));
    }

    /**
     * Returns a new UtcDateTimeImmutable object representing the supplied MySQL date and time with microseconds.
     * @param string $mysqlDateTime6   MySQL UTC date and time with microseconds.
     * @return self                    UtcDateTimeImmutable object representing the supplied MySQL date and time with microseconds..
     * @throws UtcDateTimeException    Failed to create a new UtcDateTimeImmutable object representing the supplied MySQL date and time with microseconds.
     */
    public static function createFromMysqlDateTime6(string $mysqlDateTime6): self
    {
        return new self(dateTimeImmutable: self::createDateTimeImmutableFromMysqlDateTime6(mysqlDateTime6: $mysqlDateTime6));
    }
}