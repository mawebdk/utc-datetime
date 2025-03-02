<?php

namespace MawebDK\UtcDateTime\Test;

use MawebDK\UtcDateTime\UtcDateTimeInterface;
use PHPUnit\Framework\TestCase;

class UtcDateTimeInterfaceTest extends TestCase
{
    public function testUNIX_TIMESTAMP_MIN()
    {
        $this->assertSame(expected: -30610224000, actual: UtcDateTimeInterface::UNIX_TIMESTAMP_MIN);
    }

    public function testUNIX_TIMESTAMP_MAX()
    {
        $this->assertSame(expected: 253402300799, actual: UtcDateTimeInterface::UNIX_TIMESTAMP_MAX);
    }

    public function testUNIX_MILLI_TIMESTAMP_MIN()
    {
        $this->assertSame(expected: -30610224000000, actual: UtcDateTimeInterface::UNIX_MILLI_TIMESTAMP_MIN);
    }

    public function testUNIX_MILLI_TIMESTAMP_MAX()
    {
        $this->assertSame(expected: 253402300799999, actual: UtcDateTimeInterface::UNIX_MILLI_TIMESTAMP_MAX);
    }

    public function testUNIX_MICRO_TIMESTAMP_MIN()
    {
        $this->assertSame(expected: -30610224000000000, actual: UtcDateTimeInterface::UNIX_MICRO_TIMESTAMP_MIN);
    }

    public function testUNIX_MICRO_TIMESTAMP_MAX()
    {
        $this->assertSame(expected: 253402300799999999, actual: UtcDateTimeInterface::UNIX_MICRO_TIMESTAMP_MAX);
    }

    public function testMYSQL_DATETIME6_MIN()
    {
        $this->assertSame(expected: '1000-01-01 00:00:00.000000', actual: UtcDateTimeInterface::MYSQL_DATETIME6_MIN);
    }

    public function testMYSQL_DATETIME6_MAX()
    {
        $this->assertSame(expected: '9999-12-31 23:59:59.999999', actual: UtcDateTimeInterface::MYSQL_DATETIME6_MAX);
    }
}
