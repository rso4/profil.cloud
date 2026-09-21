<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_hex_to_rgb_converts_valid_6_digit_hex(): void
    {
        $this->assertSame('255,0,0', hex_to_rgb('#ff0000'));
        $this->assertSame('0,0,0', hex_to_rgb('#000000'));
        $this->assertSame('255,255,255', hex_to_rgb('#ffffff'));
        $this->assertSame('37,99,235', hex_to_rgb('#2563eb'));
    }

    public function test_hex_to_rgb_converts_3_digit_hex(): void
    {
        $this->assertSame('255,0,0', hex_to_rgb('#f00'));
        $this->assertSame('0,255,0', hex_to_rgb('#0f0'));
        $this->assertSame('0,0,255', hex_to_rgb('#00f'));
    }

    public function test_hex_to_rgb_handles_hex_without_hash(): void
    {
        $this->assertSame('255,0,0', hex_to_rgb('ff0000'));
    }

    public function test_hex_to_rgb_returns_fallback_for_invalid_input(): void
    {
        $this->assertSame('37,99,235', hex_to_rgb('invalid'));
        $this->assertSame('37,99,235', hex_to_rgb('#12345'));
        $this->assertSame('37,99,235', hex_to_rgb('#gggggg'));
        $this->assertSame('37,99,235', hex_to_rgb(null));
        $this->assertSame('37,99,235', hex_to_rgb(''));
    }

    public function test_hex_to_rgb_returns_custom_fallback(): void
    {
        $this->assertSame('1,2,3', hex_to_rgb('invalid', '1,2,3'));
    }
}
