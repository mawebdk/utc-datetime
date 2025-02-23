# utc-datetime
This package contains a representation of a UTC date and time with easy mocking of current date and time for testing.

Use UtcDateTime and UtcDateTimeImmutable to handle UTC date and times.

UtcDateTime and UtcDateTimeImmutable behaves the same, except:
* When calling modification methods on a UtcDateTime object, the object is modified itself.
* When calling modification methods on a UtcDateTimeImmutable object, a new object will be returned.

## Usage
Create a new UtcDateTime object with the current UTC date and time.

Note: Method now() uses Clock to get current date and time to enable support for mocking of current date and time.
```
$UtcDateTime = UtcDateTime::now();
```

Create a new UtcDateTime object with a given Unix timestamp with optional milliseconds or microseconds.
```
$utcDateTime = UtcDateTime::createFromUnixTimestamp(unixTimestamp: $unixTimestamp);
$utcDateTime = UtcDateTime::createFromUnixMilliTimestamp(unixMilliTimestamp: $unixMilliTimestamp);
$utcDateTime = UtcDateTime::createFromUnixMicroTimestamp(unixMicroTimestamp: $unixMicroTimestamp);
```

Get Unix timestamp with optional milliseconds or microseconds from a UtcDateTime object.
```
$unixTimestamp      = $utcDateTime->getUnixTimestamp;
$unixMilliTimestamp = $utcDateTime->getUnixMilliTimestamp;
$unixMicroTimestamp = $utcDateTime->getUnixMicroTimestamp;
```

Get MySQL date and time with microseconds format from a UtcDateTime object.
```
$mysqlDateTime6 = $utcDateTime->formatMysqlDateTime6();
```

## Mocking of current UTC date and time
Use ReflectionClass to set the Clock singleton to a mocked class implementing ClockInterface.
```
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
```

Remember to reset singleton to ensure usage of correct date and time in subsequent tests.
```
protected function tearDown(): void
{
    // Reset singleton to ensure usage of correct date and time in subsequent tests.
    $reflectionClass = new ReflectionClass(objectOrClass: Clock::class);
    $reflectionClass->setStaticPropertyValue(name: 'singleton', value: null);
}
```
 