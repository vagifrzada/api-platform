<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function testAddition(): void
    {
        $value = true;
        $arr = [
            'key' => 'value',
        ];

        $this->assertEquals(5, 2 + 3);
        $this->assertTrue($value);
        $this->assertArrayHasKey('key', $arr);
        $this->assertCount(1, $arr);
        $this->assertEquals('value', $arr['key']);
    }
}