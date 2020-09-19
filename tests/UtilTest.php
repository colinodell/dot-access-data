<?php

declare(strict_types=1);

/*
 * This file is a part of colinodell/dot-access-data.
 *
 * (c) Colin O'Dell
 *
 * Based on dflydev/dot-access-data, (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ColinODell\DotAccessData\Tests;

use ColinODell\DotAccessData\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testIsAssoc(): void
    {
        $this->assertTrue(Util::isAssoc(['a' => 'A']));
        $this->assertTrue(Util::isAssoc([]));
        $this->assertFalse(Util::isAssoc([1 => 'One']));
    }

    /**
     * @dataProvider mergeAssocArrayProvider
     *
     * @param array<mixed> $to
     * @param array<mixed> $from
     * @param array<mixed> $expectedResult
     */
    public function testMergeAssocArray(string $message, array $to, array $from, ?bool $clobber, array $expectedResult): void
    {
        if ($clobber === null) {
            $result = Util::mergeAssocArray($to, $from);
        } else {
            $result = Util::mergeAssocArray($to, $from, $clobber);
        }

        $this->assertEquals($expectedResult, $result, $message);
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function mergeAssocArrayProvider(): iterable
    {
        return [
            [
                'Clobber should replace to value with from value for strings (shallow)',
                // to
                ['a' => 'A'],
                // from
                ['a' => 'B'],
                // clobber
                true,
                // expected result
                ['a' => 'B'],
            ],

            [
                'Clobber should replace to value with from value for strings (deep)',
                // to
                ['a' => ['b' => 'B']],
                // from
                ['a' => ['b' => 'C']],
                // clobber
                true,
                // expected result
                ['a' => ['b' => 'C']],
            ],

            [
                'Clobber should  NOTreplace to value with from value for strings (shallow)',
                // to
                ['a' => 'A'],
                // from
                ['a' => 'B'],
                // clobber
                false,
                // expected result
                ['a' => 'A'],
            ],

            [
                'Clobber should NOT replace to value with from value for strings (deep)',
                // to
                ['a' => ['b' => 'B']],
                // from
                ['a' => ['b' => 'C']],
                // clobber
                false,
                // expected result
                ['a' => ['b' => 'B']],
            ],

            [
                'Associative arrays should be combined',
                // to
                ['a' => ['b' => 'B']],
                // from
                ['a' => ['c' => 'C']],
                // clobber
                null,
                // expected result
                ['a' => ['b' => 'B', 'c' => 'C']],
            ],

            [
                'Arrays should be replaced (with clobber enabled)',
                // to
                ['a' => ['b', 'c']],
                // from
                ['a' => ['B', 'C']],
                // clobber
                true,
                // expected result
                ['a' => ['B', 'C']],
            ],

            [
                'Arrays should be NOT replaced (with clobber disabled)',
                // to
                ['a' => ['b', 'c']],
                // from
                ['a' => ['B', 'C']],
                // clobber
                false,
                // expected result
                ['a' => ['b', 'c']],
            ],
        ];
    }
}
