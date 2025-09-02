<?php
namespace MawebDK\UtcDateTime;

use DateTimeImmutable;
use DateTimeZone;
use MawebDK\Clock\Clock;
use MawebDK\PregMatch\PregMatch;
use MawebDK\ToStringBuilder\ToStringBuilder;
use Stringable;
use Throwable;

/**
 * Common properties and methods for UtcDateTime and UtcDateTimeImmutable.
 * Valid date and time range is from 1000-01-01 00:00:00.000000 to 9999-12-31 23:59:59.999999.
 */
abstract class UtcDateTimeAbstract implements UtcDateTimeInterface, Stringable
{
    /**
     * @var DateTimeImmutable   Internal representation of the UTC date and time.
     */
    private DateTimeImmutable $dateTimeImmutable;

    /**
     * @inheritDoc
     */
    public function getUnixTimestamp(): int
    {
        return $this->dateTimeImmutable->getTimestamp();
    }

    /**
     * @inheritDoc
     */
    public function getUnixMilliTimestamp(): int
    {
        return 1000 * $this->dateTimeImmutable->getTimestamp() + (int)$this->dateTimeImmutable->format(format: 'v');
    }

    /**
     * @inheritDoc
     */
    public function getUnixMicroTimestamp(): int
    {
        return 1000000 * $this->dateTimeImmutable->getTimestamp() + (int)$this->dateTimeImmutable->format(format: 'u');
    }

    /**
     * @inheritDoc
     */
    public function formatMysqlDateTime6(): string
    {
        return $this->dateTimeImmutable->format(format: 'Y-m-d H:i:s.u');
    }

    /**
     * @inheritDoc
     */
    public function getDateTimeImmutable(): DateTimeImmutable
    {
        return $this->dateTimeImmutable;
    }

    /**
     * Returns a string representation of the object.
     * @return string   String representation of the object.
     */
    public function __toString(): string
    {
        $toStringBuilder = new ToStringBuilder(object: $this);

        return $toStringBuilder
            ->add(name: 'mysqlDateTime6', value: $this->formatMysqlDateTime6())
            ->build();
    }

    /**
     * Returns a new DateTimeImmutable object with the current UTC date and time.
     * @return DateTimeImmutable      New DateTimeImmutable object with the current UTC date and time.
     * @throws UtcDateTimeException   Failed to create a new DateTimeImmutable object with the current UTC date and time.
     */
    protected static function createDateTimeImmutableWithCurrentUtcDateTime(): DateTimeImmutable
    {
        try {
            $dateTimeImmutable = Clock::getSingleton()->now();
        } catch (Throwable $e) {
            throw new UtcDateTimeException(
                message: 'Failed to create an instance of DateTimeImmutable with the current UTC date and time.',
                previous: $e
            );
        }

        if ($dateTimeImmutable->getTimestamp() < UtcDateTimeInterface::UNIX_TIMESTAMP_MIN):
            throw new UtcDateTimeException(message: sprintf(
                'Current Unix timestamp %d is less than minimum supported Unix timestamp %d.',
                $dateTimeImmutable->getTimestamp(), UtcDateTimeInterface::UNIX_TIMESTAMP_MIN
            ));
        elseif ($dateTimeImmutable->getTimestamp() > UtcDateTimeInterface::UNIX_TIMESTAMP_MAX):
            throw new UtcDateTimeException(message: sprintf(
                'Current Unix timestamp %d is greater than maximum supported Unix timestamp %d.',
                $dateTimeImmutable->getTimestamp(), UtcDateTimeInterface::UNIX_TIMESTAMP_MAX
            ));
        endif;

        return $dateTimeImmutable;
    }

    /**
     * Returns a new DateTimeImmutable object with the given Unix timestamp.
     * @param int $unixTimestamp      Unix timestamp.
     * @return DateTimeImmutable      New DateTimeImmutable object with the given Unix timestamp.
     * @throws UtcDateTimeException   Failed to create a new DateTimeImmutable object with the given Unix timestamp.
     */
    protected static function createDateTimeImmutableFromUnixTimestamp(int $unixTimestamp): DateTimeImmutable
    {
        if ($unixTimestamp < UtcDateTimeInterface::UNIX_TIMESTAMP_MIN):
            throw new UtcDateTimeException(message: sprintf(
                'Unix timestamp %d is less than minimum supported Unix timestamp %d.',
                $unixTimestamp, UtcDateTimeInterface::UNIX_TIMESTAMP_MIN
            ));
        elseif ($unixTimestamp > UtcDateTimeInterface::UNIX_TIMESTAMP_MAX):
            throw new UtcDateTimeException(message: sprintf(
                'Unix timestamp %d is greater than maximum supported Unix timestamp %d.',
                $unixTimestamp, UtcDateTimeInterface::UNIX_TIMESTAMP_MAX
            ));
        endif;

        try {
            $dateTimeImmutable = DateTimeImmutable::createFromFormat(
                format: 'U',
                datetime: $unixTimestamp,
                timezone: new DateTimeZone(timezone: 'UTC')
            );
        } catch (Throwable $e) {
            throw new UtcDateTimeException(
                message: sprintf('Failed to create an instance of DateTimeImmutable with the given Unix timestamp %s.', $unixTimestamp),
                previous: $e
            );
        }

        return $dateTimeImmutable;
    }

    /**
     * Returns a new DateTimeImmutable object with the given Unix milli timestamp.
     * @param int $unixMilliTimestamp   Unix milli timestamp.
     * @return DateTimeImmutable        New DateTimeImmutable object with the given Unix milli timestamp.
     * @throws UtcDateTimeException     Failed to create a new DateTimeImmutable object with the given Unix milli timestamp.
     */
    protected static function createDateTimeImmutableFromUnixMilliTimestamp(int $unixMilliTimestamp): DateTimeImmutable
    {
        if ($unixMilliTimestamp < UtcDateTimeInterface::UNIX_MILLI_TIMESTAMP_MIN):
            throw new UtcDateTimeException(message: sprintf(
                'Unix milli timestamp %d is less than minimum supported Unix milli timestamp %d.',
                $unixMilliTimestamp, UtcDateTimeInterface::UNIX_MILLI_TIMESTAMP_MIN
            ));
        elseif ($unixMilliTimestamp > UtcDateTimeInterface::UNIX_MILLI_TIMESTAMP_MAX):
            throw new UtcDateTimeException(message: sprintf(
                'Unix milli timestamp %d is greater than maximum supported Unix milli timestamp %d.',
                $unixMilliTimestamp, UtcDateTimeInterface::UNIX_MILLI_TIMESTAMP_MAX
            ));
        endif;

        $unixTimestamp = intdiv(num1: $unixMilliTimestamp, num2: 1000);
        $milliseconds  = $unixMilliTimestamp % 1000;

        try {
            $dateTimeImmutable = DateTimeImmutable::createFromFormat(
                format: 'U.v',
                datetime: sprintf('%d.%d', $unixTimestamp, $milliseconds),
                timezone: new DateTimeZone(timezone: 'UTC')
            );

            if (!($dateTimeImmutable instanceof DateTimeImmutable)):
                throw new UtcDateTimeException(
                    message: sprintf('Failed to create an instance of DateTimeImmutable with the given Unix milli timestamp %s.', $unixMilliTimestamp)
                );
            endif;
        } catch (Throwable $e) {
            throw new UtcDateTimeException(
                message: sprintf('Failed to create an instance of DateTimeImmutable with the given Unix milli timestamp %s.', $unixMilliTimestamp),
                previous: $e
            );
        }

        return $dateTimeImmutable;
    }

    /**
     * Returns a new DateTimeImmutable object with the given Unix micro timestamp.
     * @param int $unixMicroTimestamp   Unix micro timestamp.
     * @return DateTimeImmutable        New DateTimeImmutable object with the given Unix micro timestamp.
     * @throws UtcDateTimeException     Failed to create a new DateTimeImmutable object with the given Unix micro timestamp.
     */
    protected static function createDateTimeImmutableFromUnixMicroTimestamp(int $unixMicroTimestamp): DateTimeImmutable
    {
        if ($unixMicroTimestamp < UtcDateTimeInterface::UNIX_MICRO_TIMESTAMP_MIN):
            throw new UtcDateTimeException(message: sprintf(
                'Unix micro timestamp %d is less than minimum supported Unix micro timestamp %d.',
                $unixMicroTimestamp, UtcDateTimeInterface::UNIX_MICRO_TIMESTAMP_MIN
            ));
        elseif ($unixMicroTimestamp > UtcDateTimeInterface::UNIX_MICRO_TIMESTAMP_MAX):
            throw new UtcDateTimeException(message: sprintf(
                'Unix micro timestamp %d is greater than maximum supported Unix micro timestamp %d.',
                $unixMicroTimestamp, UtcDateTimeInterface::UNIX_MICRO_TIMESTAMP_MAX
            ));
        endif;

        $unixTimestamp = intdiv(num1: $unixMicroTimestamp, num2: 1000000);
        $microseconds  = $unixMicroTimestamp % 1000000;

        try {
            $dateTimeImmutable = DateTimeImmutable::createFromFormat(
                format: 'U.u',
                datetime: sprintf('%d.%d', $unixTimestamp, $microseconds),
                timezone: new DateTimeZone(timezone: 'UTC')
            );

            if (!($dateTimeImmutable instanceof DateTimeImmutable)):
                throw new UtcDateTimeException(
                    message: sprintf('Failed to create an instance of DateTimeImmutable with the given Unix micro timestamp %s.', $unixMicroTimestamp)
                );
            endif;
        } catch (Throwable $e) {
            throw new UtcDateTimeException(
                message: sprintf('Failed to create an instance of DateTimeImmutable with the given Unix micro timestamp %s.', $unixMicroTimestamp),
                previous: $e
            );
        }

        return $dateTimeImmutable;
    }

    /**
     * Returns a new DateTimeImmutable object with the given MySQL and time with microseconds.
     * @param string $mysqlDateTime6   MySQL date and time with microseconds.
     * @return DateTimeImmutable       New DateTimeImmutable object with the given MySQL date and time with microseconds.
     * @throws UtcDateTimeException    Failed to create a new DateTimeImmutable object with the given MySQL date and time with microseconds.
     */
    protected static function createDateTimeImmutableFromMysqlDateTime6(string $mysqlDateTime6): DateTimeImmutable
    {
        try {
            if (!PregMatch::pregMatch(pattern: '#\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{6}\Z#', subject: $mysqlDateTime6)):
                throw new UtcDateTimeException(message: sprintf('MySQL date and time "%s" is not valid.', $mysqlDateTime6));
            endif;
        } catch (UtcDateTimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new UtcDateTimeException(
                message: sprintf('Failed to verify MySQL date and time "%s".', $mysqlDateTime6),
                previous: $e
            );
        }

        $dateTimeImmutable = DateTimeImmutable::createFromFormat(
            format: 'Y-m-d H:i:s.u',
            datetime: $mysqlDateTime6,
            timezone: new DateTimeZone(timezone: 'UTC')
        );

        if (!($dateTimeImmutable instanceof DateTimeImmutable)):
            throw new UtcDateTimeException(message: sprintf('MySQL date and time "%s" is not valid.', $mysqlDateTime6));
        endif;

        if ($mysqlDateTime6 < UtcDateTimeInterface::MYSQL_DATETIME6_MIN):
            throw new UtcDateTimeException(message: sprintf(
                'MySQL date and time "%s" is less than minimum supported MySQL date and time "%s".',
                $mysqlDateTime6, UtcDateTimeInterface::MYSQL_DATETIME6_MIN
            ));
        elseif ($mysqlDateTime6 > UtcDateTimeInterface::MYSQL_DATETIME6_MAX):
            throw new UtcDateTimeException(message: sprintf(
                'MySQL date and time "%s" is greater than maximum supported MySQL date and time "%s".',
                $mysqlDateTime6, UtcDateTimeInterface::MYSQL_DATETIME6_MAX
            ));
        endif;

        if ($dateTimeImmutable->format(format: 'Y-m-d H:i:s.u') !== $mysqlDateTime6):
            throw new UtcDateTimeException(message: sprintf('MySQL date and time "%s" is not valid.', $mysqlDateTime6));
        endif;

        return $dateTimeImmutable;
    }

    /**
     * Returns a new object with the supplied DateTimeImmutable object.
     * Note: There is no validation of the supplied object.
     * @param DateTimeImmutable $dateTimeImmutable   Instance of DateTimeImmutable with the UTC date and time.
     */
    protected function __construct(DateTimeImmutable $dateTimeImmutable)
    {
        $this->dateTimeImmutable = $dateTimeImmutable;
    }
}