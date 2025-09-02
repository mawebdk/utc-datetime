<?php
namespace MawebDK\UtcDateTime\Test;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use MawebDK\Clock\Clock;
use MawebDK\UtcDateTime\UtcDateTimeException;
use MawebDK\UtcDateTime\UtcDateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use ReflectionClass;

class UtcDateTimeImmutableTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflectionClass = new ReflectionClass(objectOrClass: Clock::class);
        $reflectionClass->setStaticPropertyValue(name: 'singleton', value: null);
    }

    /**
     * @throws UtcDateTimeException
     */
    public function testNow_UnixTimestamp()
    {
        $dateTimeBefore = new DateTime(datetime: 'now');
        $utcDateTime    = UtcDateTimeImmutable::now();
        $dateTimeAfter  = new DateTime(datetime: 'now');

        $this->assertGreaterThanOrEqual(
            minimum: $dateTimeBefore->getTimestamp(),
            actual: $utcDateTime->getUnixTimestamp()
        );

        $this->assertLessThanOrEqual(
            maximum: $dateTimeAfter->getTimestamp(),
            actual: $utcDateTime->getUnixTimestamp()
        );
    }

    /**
     * @throws UtcDateTimeException
     */
    public function testNow_UnixMilliTimestamp()
    {
        $dateTimeBefore = new DateTime(datetime: 'now');
        $utcDateTime    = UtcDateTimeImmutable::now();
        $dateTimeAfter  = new DateTime(datetime: 'now');

        $this->assertGreaterThanOrEqual(
            minimum: 1000 * $dateTimeBefore->getTimestamp() + (int)$dateTimeBefore->format(format: 'v'),
            actual: $utcDateTime->getUnixMilliTimestamp()
        );

        $this->assertLessThanOrEqual(
            maximum: 1000 * $dateTimeAfter->getTimestamp() + (int)$dateTimeAfter->format(format: 'v'),
            actual: $utcDateTime->getUnixMilliTimestamp()
        );
    }

    /**
     * @throws UtcDateTimeException
     */
    public function testNow_UnixMicroTimestamp()
    {
        $dateTimeBefore = new DateTime(datetime: 'now');
        $utcDateTime    = UtcDateTimeImmutable::now();
        $dateTimeAfter  = new DateTime(datetime: 'now');

        $this->assertGreaterThan(
            minimum: 1000000 * $dateTimeBefore->getTimestamp() + (int)$dateTimeBefore->format(format: 'u'),
            actual: $utcDateTime->getUnixMicroTimestamp()
        );

        $this->assertLessThan(
            maximum: 1000000 * $dateTimeAfter->getTimestamp() + (int)$dateTimeAfter->format(format: 'u'),
            actual: $utcDateTime->getUnixMicroTimestamp()
        );
    }

    /**
     * @throws UtcDateTimeException
     */
    public function testNow_MysqlDateTime6()
    {
        $dateTimeBefore = new DateTime(datetime: 'now');
        $utcDateTime    = UtcDateTimeImmutable::now();
        $dateTimeAfter  = new DateTime(datetime: 'now');

        $this->assertGreaterThan(
            minimum: $dateTimeBefore->format(format: 'Y-m-d H:i:s.u'),
            actual: $utcDateTime->formatMysqlDateTime6()
        );

        $this->assertLessThan(
            maximum: $dateTimeAfter->format(format: 'Y-m-d H:i:s.u'),
            actual: $utcDateTime->formatMysqlDateTime6()
        );
    }

    /**
     * @throws Exception
     */
    public function testNow_UtcDateTimeException_BeforeMinimumSupportDateAndTime()
    {
        $mockDateTimeImmutable = (new DateTimeImmutable())
            ->setTimezone(new DateTimeZone(timezone: 'UTC'))
            ->setDate(year: 999, month: 12, day: 31)
            ->setTime(hour: 23, minute: 59, second: 59, microsecond: 999999);

        $mockClock = $this->createMock(type: ClockInterface::class);
        $mockClock
            ->method(constraint: 'now')
            ->willReturn($mockDateTimeImmutable);

        $reflectionClass = new ReflectionClass(objectOrClass: Clock::class);
        $reflectionClass->setStaticPropertyValue(name: 'singleton', value: $mockClock);

        $this->expectException(exception: UtcDateTimeException::class);
        $this->expectExceptionMessage(message: 'Current Unix timestamp -30610224001 is less than minimum supported Unix timestamp -30610224000.');

        UtcDateTimeImmutable::now();
    }

    /**
     * @throws Exception
     */
    public function testNow_UtcDateTimeException_AfterMaximumSupportDateAndTime()
    {
        $mockDateTimeImmutable = (new DateTimeImmutable())
            ->setTimezone(new DateTimeZone(timezone: 'UTC'))
            ->setDate(year: 10000, month: 1, day: 1)
            ->setTime(hour: 0, minute: 0, second: 0, microsecond: 0);

        $mockClock = $this->createMock(type: ClockInterface::class);
        $mockClock
            ->method(constraint: 'now')
            ->willReturn($mockDateTimeImmutable);

        $reflectionClass = new ReflectionClass(objectOrClass: Clock::class);
        $reflectionClass->setStaticPropertyValue(name: 'singleton', value: $mockClock);

        $this->expectException(exception: UtcDateTimeException::class);
        $this->expectExceptionMessage(message: 'Current Unix timestamp 253402300800 is greater than maximum supported Unix timestamp 253402300799.');

        UtcDateTimeImmutable::now();
    }

    /**
     * @throws Exception
     * @throws UtcDateTimeException
     */
    public function testNow_Mocking()
    {
        $mockedDateTimeImmutable = DateTimeImmutable::createFromFormat(
            format: 'Y-m-d H:i:s.u',
            datetime: '1999-12-31 23:59:59.999999',
            timezone: new DateTimeZone(timezone: 'UTC')
        );

        $mockedClock = $this->createMock(type: ClockInterface::class);
        $mockedClock
            ->method(constraint: 'now')
            ->willReturn($mockedDateTimeImmutable);

        $reflectionClass = new ReflectionClass(objectOrClass: Clock::class);
        $reflectionClass->setStaticPropertyValue(name: 'singleton', value: $mockedClock);

        $this->assertSame(
            expected: $mockedDateTimeImmutable->format(format: 'Y-m-d H:i:s.u'),
            actual: UtcDateTimeImmutable::now()->formatMysqlDateTime6()
        );
    }

    /**
     * @throws UtcDateTimeException
     */
    #[DataProvider('dataProviderCreateFromUnixTimestamp')]
    public function testCreateFromUnixTimestamp(int $unixTimestamp, string $expectedMysqlDateTime6)
    {
        $utcDateTime = UtcDateTimeImmutable::createFromUnixTimestamp(unixTimestamp: $unixTimestamp);

        $this->assertSame(expected: $expectedMysqlDateTime6, actual: $utcDateTime->formatMysqlDateTime6());
    }

    public static function dataProviderCreateFromUnixTimestamp(): array
    {
        return [
            '-30610224000 (1000-01-01 00:00:00.000000)' => [
                'unixTimestamp'          => -30610224000,
                'expectedMysqlDateTime6' => '1000-01-01 00:00:00.000000',
            ],
            '946684799 (1999-12-31 23:59:59.000000)' => [
                'unixTimestamp'          => 946684799,
                'expectedMysqlDateTime6' => '1999-12-31 23:59:59.000000',
            ],
            '946684800 (2000-01-01 00:00:00.000000)' => [
                'unixTimestamp'          => 946684800,
                'expectedMysqlDateTime6' => '2000-01-01 00:00:00.000000',
            ],
            '253402300799 (9999-12-31 23:59:59.000000)' => [
                'unixTimestamp'          => 253402300799,
                'expectedMysqlDateTime6' => '9999-12-31 23:59:59.000000',
            ],
        ];
    }

    #[DataProvider('dataProviderCreateFromUnixTimestamp_UtcDateTimeException')]
    public function testCreateFromUnixTimestamp_UtcDateTimeException(int $unixTimestamp, string $expectedExceptionMessage)
    {
        $this->expectException(exception: UtcDateTimeException::class);
        $this->expectExceptionMessage(message: $expectedExceptionMessage);

        UtcDateTimeImmutable::createFromUnixTimestamp(unixTimestamp: $unixTimestamp);
    }

    public static function dataProviderCreateFromUnixTimestamp_UtcDateTimeException(): array
    {
        return [
            'PHP_INT_MIN' => [
                'unixTimestamp'            => PHP_INT_MIN,
                'expectedExceptionMessage' => 'Unix timestamp -9223372036854775808 is less than minimum supported Unix timestamp -30610224000.',
            ],
            '-30610224001 (0999-12-31 23:59:59.000000)' => [
                'unixTimestamp'            => -30610224001,
                'expectedExceptionMessage' => 'Unix timestamp -30610224001 is less than minimum supported Unix timestamp -30610224000.',
            ],
            '253402300800 (10000-01-01 00:00:00.000000)' => [
                'unixTimestamp'            => 253402300800,
                'expectedExceptionMessage' => 'Unix timestamp 253402300800 is greater than maximum supported Unix timestamp 253402300799.',
            ],
            'PHP_INT_MAX' => [
                'unixTimestamp'            => PHP_INT_MAX,
                'expectedExceptionMessage' => 'Unix timestamp 9223372036854775807 is greater than maximum supported Unix timestamp 253402300799.',
            ],
        ];
    }

    /**
     * @throws UtcDateTimeException
     */
    #[DataProvider('dataProviderCreateFromUnixMilliTimestamp')]
    public function testCreateFromUnixMilliTimestamp(int $unixMilliTimestamp, string $expectedMysqlDateTime6)
    {
        $utcDateTime = UtcDateTimeImmutable::createFromUnixMilliTimestamp(unixMilliTimestamp: $unixMilliTimestamp);

        $this->assertSame(expected: $expectedMysqlDateTime6, actual: $utcDateTime->formatMysqlDateTime6());
    }

    public static function dataProviderCreateFromUnixMilliTimestamp(): array
    {
        return [
            '-30610224000000 (1000-01-01 00:00:00.000000)' => [
                'unixMilliTimestamp'     => -30610224000000,
                'expectedMysqlDateTime6' => '1000-01-01 00:00:00.000000',
            ],
            '946684799999 (1999-12-31 23:59:59.999000)' => [
                'unixMilliTimestamp'     => 946684799999,
                'expectedMysqlDateTime6' => '1999-12-31 23:59:59.999000',
            ],
            '946684800000 (2000-01-01 00:00:00.000000)' => [
                'unixMilliTimestamp'     => 946684800000,
                'expectedMysqlDateTime6' => '2000-01-01 00:00:00.000000',
            ],
            '253402300799999 (9999-12-31 23:59:59.999000)' => [
                'unixMilliTimestamp'     => 253402300799999,
                'expectedMysqlDateTime6' => '9999-12-31 23:59:59.999000',
            ],
        ];
    }

    #[DataProvider('dataProviderCreateFromUnixMilliTimestamp_UtcDateTimeException')]
    public function testCreateFromUnixMilliTimestamp_UtcDateTimeException(int $unixMilliTimestamp, string $expectedExceptionMessage)
    {
        $this->expectException(exception: UtcDateTimeException::class);
        $this->expectExceptionMessage(message: $expectedExceptionMessage);

        UtcDateTimeImmutable::createFromUnixMilliTimestamp(unixMilliTimestamp: $unixMilliTimestamp);
    }

    public static function dataProviderCreateFromUnixMilliTimestamp_UtcDateTimeException(): array
    {
        return [
            'PHP_INT_MIN' => [
                'unixMilliTimestamp'       => PHP_INT_MIN,
                'expectedExceptionMessage' => 'Unix milli timestamp -9223372036854775808 is less than minimum supported Unix milli timestamp -30610224000000.',
            ],
            '-30610224000001 (0999-12-31 23:59:59.999000)' => [
                'unixMilliTimestamp'       => -30610224000001,
                'expectedExceptionMessage' => 'Unix milli timestamp -30610224000001 is less than minimum supported Unix milli timestamp -30610224000000.',
            ],
            '253402300800000 (10000-01-01 00:00:00.000000)' => [
                'unixMilliTimestamp'       => 253402300800000,
                'expectedExceptionMessage' => 'Unix milli timestamp 253402300800000 is greater than maximum supported Unix milli timestamp 253402300799999.',
            ],
            'PHP_INT_MAX' => [
                'unixMilliTimestamp'       => PHP_INT_MAX,
                'expectedExceptionMessage' => 'Unix milli timestamp 9223372036854775807 is greater than maximum supported Unix milli timestamp 253402300799999.',
            ],
        ];
    }

    /**
     * @throws UtcDateTimeException
     */
    #[DataProvider('dataProviderCreateFromUnixMicroTimestamp')]
    public function testCreateFromUnixMicroTimestamp(int $unixMicroTimestamp, string $expectedMysqlDateTime6)
    {
        $utcDateTime = UtcDateTimeImmutable::createFromUnixMicroTimestamp(unixMicroTimestamp: $unixMicroTimestamp);

        $this->assertSame(expected: $expectedMysqlDateTime6, actual: $utcDateTime->formatMysqlDateTime6());
    }

    public static function dataProviderCreateFromUnixMicroTimestamp(): array
    {
        return [
            '-30610224000000000 (1000-01-01 00:00:00.000000)' => [
                'unixMicroTimestamp'     => -30610224000000000,
                'expectedMysqlDateTime6' => '1000-01-01 00:00:00.000000',
            ],
            '946684799999999 (1999-12-31 23:59:59.999999)' => [
                'unixMicroTimestamp'     => 946684799999999,
                'expectedMysqlDateTime6' => '1999-12-31 23:59:59.999999',
            ],
            '946684800000000 (2000-01-01 00:00:00.000000)' => [
                'unixMicroTimestamp'     => 946684800000000,
                'expectedMysqlDateTime6' => '2000-01-01 00:00:00.000000',
            ],
            '253402300799999999 (9999-12-31 23:59:59.999999)' => [
                'unixMicroTimestamp'     => 253402300799999999,
                'expectedMysqlDateTime6' => '9999-12-31 23:59:59.999999',
            ],
        ];
    }

    #[DataProvider('dataProviderCreateFromUnixMicroTimestamp_UtcDateTimeException')]
    public function testCreateFromUnixMicroTimestamp_UtcDateTimeException(int $unixMicroTimestamp, string $expectedExceptionMessage)
    {
        $this->expectException(exception: UtcDateTimeException::class);
        $this->expectExceptionMessage(message: $expectedExceptionMessage);

        UtcDateTimeImmutable::createFromUnixMicroTimestamp(unixMicroTimestamp: $unixMicroTimestamp);
    }

    public static function dataProviderCreateFromUnixMicroTimestamp_UtcDateTimeException(): array
    {
        return [
            'PHP_INT_MIN' => [
                'unixMicroTimestamp'       => PHP_INT_MIN,
                'expectedExceptionMessage' => 'Unix micro timestamp -9223372036854775808 is less than minimum supported Unix micro timestamp -30610224000000000.',
            ],
            '-30610224000000001 (0999-12-31 23:59:59.999999)' => [
                'unixMicroTimestamp'       => -30610224000000001,
                'expectedExceptionMessage' => 'Unix micro timestamp -30610224000000001 is less than minimum supported Unix micro timestamp -30610224000000000.',
            ],
            '253402300800000 (10000-01-01 00:00:00.000000)' => [
                'unixMicroTimestamp'       => 253402300800000000,
                'expectedExceptionMessage' => 'Unix micro timestamp 253402300800000000 is greater than maximum supported Unix micro timestamp 253402300799999999.',
            ],
            'PHP_INT_MAX' => [
                'unixMicroTimestamp'       => PHP_INT_MAX,
                'expectedExceptionMessage' => 'Unix micro timestamp 9223372036854775807 is greater than maximum supported Unix micro timestamp 253402300799999999.',
            ],
        ];
    }


    /**
     * @throws UtcDateTimeException
     */
    #[DataProvider('dataProviderCreateFromMysqlDateTime6')]
    public function testCreateFromMysqlDateTime6(string $mysqlDateTime6, array $expectedDataArray)
    {
        $utcDateTime = UtcDateTimeImmutable::createFromMysqlDateTime6(mysqlDateTime6: $mysqlDateTime6);

        $actualDataArray = [
            'unixMicroTimestamp' => $utcDateTime->getUnixMicroTimestamp(),
            'mysqlDateTime6'     => $utcDateTime->formatMysqlDateTime6(),
        ];
        $this->assertSame(expected: $expectedDataArray, actual: $actualDataArray);
    }

    public static function dataProviderCreateFromMysqlDateTime6(): array
    {
        return [
            '1000-01-01 00:00:00.000000' => [
                'mysqlDateTime6'    => '1000-01-01 00:00:00.000000',
                'expectedDataArray' => [
                    'unixMicroTimestamp' => -30610224000000000,
                    'mysqlDateTime6'     => '1000-01-01 00:00:00.000000',
                ],
            ],
            '1999-12-31 23:59:59.999999' => [
                'mysqlDateTime6'    => '1999-12-31 23:59:59.999999',
                'expectedDataArray' => [
                    'unixMicroTimestamp' => 946684799999999,
                    'mysqlDateTime6'     => '1999-12-31 23:59:59.999999',
                ],
            ],
            '2000-01-01 00:00:00.000000' => [
                'mysqlDateTime6'    => '2000-01-01 00:00:00.000000',
                'expectedDataArray' => [
                    'unixMicroTimestamp' => 946684800000000,
                    'mysqlDateTime6'     => '2000-01-01 00:00:00.000000',
                ],
            ],
            '9999-12-31 23:59:59.999999' => [
                'mysqlDateTime6'    => '9999-12-31 23:59:59.999999',
                'expectedDataArray' => [
                    'unixMicroTimestamp' => 253402300799999999,
                    'mysqlDateTime6'     => '9999-12-31 23:59:59.999999',
                ],
            ],
        ];
    }

    #[DataProvider('dataProviderCreateFromMysqlDateTime6_UtcDateTimeException')]
    public function testCreateFromMysqlDateTime6_UtcDateTimeException(string $mysqlDateTime6, string $expectedExceptionMessage)
    {
        $this->expectException(exception: UtcDateTimeException::class);
        $this->expectExceptionMessage(message: $expectedExceptionMessage);

        UtcDateTimeImmutable::createFromMysqlDateTime6(mysqlDateTime6: $mysqlDateTime6);
    }

    public static function dataProviderCreateFromMysqlDateTime6_UtcDateTimeException(): array
    {
        return [
            '0999-12-31 23:59:59.999999' => [
                'mysqlDateTime6'           => '0999-12-31 23:59:59.999999',
                'expectedExceptionMessage' => 'MySQL date and time "0999-12-31 23:59:59.999999" is less than minimum supported MySQL date and time "1000-01-01 00:00:00.000000".',
            ],
            '10000-01-01 00.00.00.000000' => [
                'mysqlDateTime6'           => '10000-01-01 00.00.00.000000',
                'expectedExceptionMessage' => 'MySQL date and time "10000-01-01 00.00.00.000000" is not valid.',
            ],

            '19991231 12:34:56.000000 (missing delimiters)' => [
                'mysqlDateTime6'           => '19991231 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "19991231 12:34:56.000000" is not valid.',
            ],
            '1999-12-3112:34:56.000000 (missing delimiter)' => [
                'mysqlDateTime6'           => '1999-12-3112:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-3112:34:56.000000" is not valid.',
            ],
            '1999-12-31 123456.000000 (missing delimiters)' => [
                'mysqlDateTime6'           => '1999-12-31 123456.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 123456.000000" is not valid.',
            ],
            '1999-12-31 12:34:56000000 (missing delimiter)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:56000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:56000000" is not valid.',
            ],

            '1999_12_31 12:34:56.000000 (invalid delimiters)' => [
                'mysqlDateTime6'           => '1999_12_31 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999_12_31 12:34:56.000000" is not valid.',
            ],
            '1999-12-31_12:34:56.000000 (invalid delimiter)' => [
                'mysqlDateTime6'           => '1999-12-31_12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31_12:34:56.000000" is not valid.',
            ],
            '1999-12-31 12_34_56.000000 (invalid delimiters)' => [
                'mysqlDateTime6'           => '1999-12-31 12_34_56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12_34_56.000000" is not valid.',
            ],
            '1999-12-31 12:34:56_000000 (invalid delimiter)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:56_000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:56_000000" is not valid.',
            ],

            'YYYY-12-31 12:34:56.000000 (invalid year)' => [
                'mysqlDateTime6'           => 'YYYY-12-31 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "YYYY-12-31 12:34:56.000000" is not valid.',
            ],
            '1999-00-31 12:34:56.000000 (invalid month)' => [
                'mysqlDateTime6'           => '1999-00-31 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-00-31 12:34:56.000000" is not valid.',
            ],
            '1999-1-31 12:34:56.000000 (invalid month)' => [
                'mysqlDateTime6'           => '1999-1-31 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-1-31 12:34:56.000000" is not valid.',
            ],
            '1999-13-31 12:34:56.000000 (invalid month)' => [
                'mysqlDateTime6'           => '1999-13-31 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-13-31 12:34:56.000000" is not valid.',
            ],
            '1999-MM-31 12:34:56.000000 (invalid month)' => [
                'mysqlDateTime6'           => '1999-MM-31 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-MM-31 12:34:56.000000" is not valid.',
            ],
            '1999-12-00 12:34:56.000000 (invalid day)' => [
                'mysqlDateTime6'           => '1999-12-00 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-00 12:34:56.000000" is not valid.',
            ],
            '1999-12-1 12:34:56.000000 (invalid day)' => [
                'mysqlDateTime6'           => '1999-12-1 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-1 12:34:56.000000" is not valid.',
            ],
            '1999-12-32 12:34:56.000000 (invalid day)' => [
                'mysqlDateTime6'           => '1999-12-32 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-32 12:34:56.000000" is not valid.',
            ],
            '1999-02-29 12:34:56.000000 (invalid day)' => [
                'mysqlDateTime6'           => '1999-02-29 12:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-02-29 12:34:56.000000" is not valid.',
            ],
            '1999-12-31 24:00:00.000000 (invalid hour)' => [
                'mysqlDateTime6'           => '1999-12-31 24:00:00.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 24:00:00.000000" is not valid.',
            ],
            '1999-12-31 1:34:56.000000 (invalid hour)' => [
                'mysqlDateTime6'           => '1999-12-31 1:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 1:34:56.000000" is not valid.',
            ],
            '1999-12-31 HH:34:56.000000 (invalid hour)' => [
                'mysqlDateTime6'           => '1999-12-31 HH:34:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 HH:34:56.000000" is not valid.',
            ],
            '1999-12-31 12:60:00.000000 (invalid minute)' => [
                'mysqlDateTime6'           => '1999-12-31 12:60:00.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:60:00.000000" is not valid.',
            ],
            '1999-12-31 12:1:56.000000 (invalid minute)' => [
                'mysqlDateTime6'           => '1999-12-31 12:1:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:1:56.000000" is not valid.',
            ],
            '1999-12-31 12:MM:56.000000 (invalid minute)' => [
                'mysqlDateTime6'           => '1999-12-31 12:MM:56.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:MM:56.000000" is not valid.',
            ],
            '1999-12-31 12:34:60.000000 (invalid second)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:60.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:60.000000" is not valid.',
            ],
            '1999-12-31 12:34:1.000000 (invalid second)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:1.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:1.000000" is not valid.',
            ],
            '1999-12-31 12:34:SS.000000 (invalid second)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:SS.000000',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:SS.000000" is not valid.',
            ],
            '1999-12-31 12:34:56.1 (invalid microsecond)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:56.1',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:56.1" is not valid.',
            ],
            '1999-12-31 12:34:56.UUUUUU (invalid microsecond)' => [
                'mysqlDateTime6'           => '1999-12-31 12:34:56.UUUUUU',
                'expectedExceptionMessage' => 'MySQL date and time "1999-12-31 12:34:56.UUUUUU" is not valid.',
            ],
        ];
    }


    /**
     * @throws UtcDateTimeException
     */
    public function test__toString()
    {
        $utcDateTime = UtcDateTimeImmutable::createFromMysqlDateTime6(mysqlDateTime6: '1999-12-31 12:34:56.999999');

        $this->assertSame(
            expected: 'MawebDK\UtcDateTime\UtcDateTimeImmutable{"mysqlDateTime6": "1999-12-31 12:34:56.999999"}',
            actual: (string) $utcDateTime
        );
    }
}