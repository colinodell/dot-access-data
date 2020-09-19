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

use ColinODell\DotAccessData\Data;
use ColinODell\DotAccessData\DataInterface;
use ColinODell\DotAccessData\Exception\DataException;
use ColinODell\DotAccessData\Exception\InvalidPathException;
use PHPUnit\Framework\TestCase;

final class DataTest extends TestCase
{
    private const SAMPLE_DATA = [
        'a' => 'A',
        'b' => [
            'b' => 'B',
            'c' => ['C1', 'C2', 'C3'],
            'd' => [
                'd1' => 'D1',
                'd2' => 'D2',
                'd3' => 'D3',
            ],
        ],
        'c' => ['c1', 'c2', 'c3'],
        'f' => [
            'g' => [
                'h' => 'FGH',
            ],
        ],
        'h' => [
            'i' => 'I',
        ],
        'i' => [
            'j' => 'J',
        ],
    ];

    protected function runSampleDataTests(DataInterface $data): void
    {
        $this->assertEquals('A', $data->get('a'));
        $this->assertEquals('B', $data->get('b.b'));
        $this->assertEquals('B', $data->get('b/b'));
        $this->assertEquals(['C1', 'C2', 'C3'], $data->get('b.c'));
        $this->assertEquals(['C1', 'C2', 'C3'], $data->get('b/c'));
        $this->assertEquals('D3', $data->get('b.d.d3'));
        $this->assertEquals('D3', $data->get('b/d/d3'));
        $this->assertEquals(['c1', 'c2', 'c3'], $data->get('c'));
        $this->assertNull($data->get('foo', null), 'Foo should not exist');
        $this->assertNull($data->get('f.g.h.i', null));
        $this->assertNull($data->get('f/g/h/i', null));
        $this->assertEquals($data->get('foo', 'default-value-1'), 'default-value-1', 'Return default value');
        $this->assertEquals($data->get('f.g.h.i', 'default-value-2'), 'default-value-2');
        $this->assertEquals($data->get('f/g/h/i', 'default-value-2'), 'default-value-2');

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessageMatches('/No data exists at the given path/');

        $data->get('non/existent/path');
    }

    public function testAppend(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        $data->append('a', 'B');
        $data->append('c', 'c4');
        $data->append('b.c', 'C4');
        $data->append('b/d/d3', 'D3b');
        $data->append('b.d.d4', 'D');
        $data->append('e', 'E');
        $data->append('f/a', 'b');
        $data->append('h.i', 'I2');
        $data->append('i/k/l', 'L');

        $this->assertEquals(['A', 'B'], $data->get('a'));
        $this->assertEquals(['c1', 'c2', 'c3', 'c4'], $data->get('c'));
        $this->assertEquals(['C1', 'C2', 'C3', 'C4'], $data->get('b.c'));
        $this->assertEquals(['D3', 'D3b'], $data->get('b.d.d3'));
        $this->assertEquals(['D'], $data->get('b.d.d4'));
        $this->assertEquals(['E'], $data->get('e'));
        $this->assertEquals(['b'], $data->get('f.a'));
        $this->assertEquals(['I', 'I2'], $data->get('h.i'));
        $this->assertEquals(['L'], $data->get('i.k.l'));

        $this->expectException(InvalidPathException::class);

        $data->append('', 'broken');
    }

    public function testSet(): void
    {
        $data = new Data();

        $this->assertNull($data->get('a', null));
        $this->assertNull($data->get('b/c', null));
        $this->assertNull($data->get('d.e', null));

        $data->set('a', 'A');
        $data->set('b/c', 'C');
        $data->set('d.e', ['f' => 'F', 'g' => 'G']);

        $this->assertEquals('A', $data->get('a'));
        $this->assertEquals(['c' => 'C'], $data->get('b'));
        $this->assertEquals('C', $data->get('b.c'));
        $this->assertEquals('F', $data->get('d/e/f'));
        $this->assertEquals(['e' => ['f' => 'F', 'g' => 'G']], $data->get('d'));

        $this->expectException(InvalidPathException::class);

        $data->set('', 'broken');
    }

    public function testSetWithNonIndexablePath(): void
    {
        $this->expectException(DataException::class);
        $this->expectDeprecationMessageMatches('/cannot be indexed into/');

        $data = new Data(self::SAMPLE_DATA);
        $data->set('a.b', 'c');
    }

    public function testSetClobberStringInPath(): void
    {
        $data = new Data();

        $data->set('a.b.c', 'Should not be able to write to a.b.c.d.e');

        $this->expectException(DataException::class);

        $data->set('a.b.c.d.e', 'broken');
    }

    public function testRemove(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        $data->remove('a');
        $data->remove('b.c');
        $data->remove('b/d/d3');
        $data->remove('d');
        $data->remove('d.e.f');
        $data->remove('empty.path');

        $this->assertNull($data->get('a', null));
        $this->assertNull($data->get('b.c', null));
        $this->assertNull($data->get('b.d.d3', null));
        $this->assertNull(null);
        $this->assertEquals('D2', $data->get('b.d.d2'));

        $this->expectException(InvalidPathException::class);

        $data->remove('');
    }

    public function testGet(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        $this->runSampleDataTests($data);
    }

    public function testGetWithEmptyPath(): void
    {
        $this->expectException(InvalidPathException::class);

        $data = new Data();
        $data->get('');
    }

    public function testHas(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        foreach (
            ['a', 'i', 'b.d', 'b/d', 'f.g.h', 'f/g/h', 'h.i', 'h/i', 'b.d.d1', 'b/d/d1'] as $existentKey
        ) {
            $this->assertTrue($data->has($existentKey));
        }

        foreach (
            ['p', 'b.b1', 'b/b1', 'b.c.C1', 'b/c/C1', 'h.i.I', 'h/i/I', 'b.d.d1.D1', 'b/d/d1/D1'] as $notExistentKey
        ) {
            $this->assertFalse($data->has($notExistentKey));
        }
    }

    public function testHasWithEmptyPath(): void
    {
        $this->expectException(InvalidPathException::class);

        $data = new Data();
        $data->get('');
    }

    public function testGetData(): void
    {
        $wrappedData = new Data([
            'wrapped' => [
                'sampleData' => self::SAMPLE_DATA,
            ],
        ]);

        $data = $wrappedData->getData('wrapped.sampleData');

        $this->runSampleDataTests($data);

        $this->expectException(InvalidPathException::class);

        $data = $wrappedData->getData('wrapped.sampleData.a');
    }

    public function testImport(): void
    {
        $data = new Data();
        $data->import(self::SAMPLE_DATA);

        $this->runSampleDataTests($data);
    }

    public function testImportData(): void
    {
        $data = new Data();
        $data->importData(new Data(self::SAMPLE_DATA));

        $this->runSampleDataTests($data);
    }

    public function testExport(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        $this->assertEquals(self::SAMPLE_DATA, $data->export());
    }

    public function testOffsetExists(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        foreach (
            ['a', 'i', 'b.d', 'b/d', 'f.g.h', 'f/g/h', 'h.i', 'h/i', 'b.d.d1', 'b/d/d1'] as $existentKey
        ) {
            $this->assertTrue(isset($data[$existentKey]));
        }

        foreach (
            ['p', 'b.b1', 'b/b1', 'b.c.C1', 'b/c/C1', 'h.i.I', 'h/i/I', 'b.d.d1.D1', 'b/d/d1/D1'] as $notExistentKey
        ) {
            $this->assertFalse(isset($data[$notExistentKey]));
        }
    }

    public function testOffsetGet(): void
    {
        $wrappedData = new Data([
            'wrapped' => [
                'sampleData' => self::SAMPLE_DATA,
            ],
        ]);

        $data = $wrappedData->getData('wrapped.sampleData');

        $this->assertEquals('A', $data['a']);
        $this->assertEquals('B', $data['b.b']);
        $this->assertEquals('B', $data['b/b']);
        $this->assertEquals(['C1', 'C2', 'C3'], $data['b.c']);
        $this->assertEquals(['C1', 'C2', 'C3'], $data['b/c']);
        $this->assertEquals('D3', $data['b.d.d3']);
        $this->assertEquals('D3', $data['b/d/d3']);
        $this->assertEquals(['c1', 'c2', 'c3'], $data['c']);
        $this->assertNull($data['foo'], 'Foo should not exist');
        $this->assertNull($data['f.g.h.i']);
        $this->assertNull($data['f/g/h/i']);

        $this->expectException(DataException::class);
        $this->expectExceptionMessageMatches('/could not be represented as a DataInterface/');

        $data = $wrappedData->getData('wrapped.sampleData.a');
    }

    public function testOffsetSet(): void
    {
        $data = new Data();

        $this->assertNull($data['a']);
        $this->assertNull($data['b.c']);
        $this->assertNull($data['d.e']);

        $data['a']   = 'A';
        $data['b/c'] = 'C';
        $data['d.e'] = ['f' => 'F', 'g' => 'G'];

        $this->assertEquals('A', $data['a']);
        $this->assertEquals(['c' => 'C'], $data['b']);
        $this->assertEquals('C', $data['b.c']);
        $this->assertEquals('F', $data['d/e/f']);
        $this->assertEquals(['e' => ['f' => 'F', 'g' => 'G']], $data['d']);

        $this->expectException(InvalidPathException::class);

        $data->set('', 'broken');
    }

    public function testOffsetSetWithNumericKey(): void
    {
        $this->expectException(InvalidPathException::class);

        $data    = new Data();
        $data[1] = 1;
    }

    public function testOffsetUnset(): void
    {
        $data = new Data(self::SAMPLE_DATA);

        unset($data['a']);
        unset($data['b/c']);
        unset($data['b.d.d3']);
        unset($data['d']);
        unset($data['d.e.f']);
        unset($data['empty.path']);

        $this->assertNull($data['a']);
        $this->assertNull($data['b.c']);
        $this->assertNull($data['b.d.d3']);
        $this->assertNull(null);
        $this->assertEquals('D2', $data['b.d.d2']);

        $this->expectException(InvalidPathException::class);

        unset($data['']);
    }
}
