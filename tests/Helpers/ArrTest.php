<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Arr;
use TinyFramework\Helpers\Str;

class CR
{
    private float $priv_member;

    public function __construct(float $val)
    {
        $this->priv_member = $val;
    }

    public static function comp_func_cr(CR $a, CR $b)
    {
        if ($a->priv_member === $b->priv_member) {
            return 0;
        }
        return ($a->priv_member > $b->priv_member) ? 1 : -1;
    }

    public static function comp_func_key(int|string $a, int|string $b)
    {
        if ($a === $b) {
            return 0;
        }
        return ($a > $b) ? 1 : -1;
    }

    public function getPrivNumber(): float
    {
        return $this->priv_member;
    }
}


class ArrTest extends TestCase
{
    public function testStaticFactory(): void
    {
        $arr = Arr::factory([1, 2, 3]);
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(3, $arr);
        $this->assertEquals(1, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(3, $arr->shift());
        $this->assertEquals(null, $arr->shift());
    }

    public function testStaticWrap(): void
    {
        $arr = Arr::wrap(1);
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(1, $arr);
        $this->assertEquals(1, $arr->shift());
    }

    public function testStaticFill(): void
    {
        $arr = Arr::fill(1, 3, 'a');
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(3, $arr);
        $this->assertEquals('a', $arr->shift());
        $this->assertEquals('a', $arr->shift());
        $this->assertEquals('a', $arr->shift());
        $this->assertEquals(null, $arr->shift());
    }

    public function testStaticRange(): void
    {
        $arr = Arr::range(0, 4, 2);
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(3, $arr);
        $this->assertEquals(0, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(4, $arr->shift());
        $this->assertEquals(null, $arr->pop());
    }

    public function testClone(): void
    {
        $arr1 = Arr::factory(['a']);
        $arr2 = clone $arr1;
        $arr1->push('b');
        $this->assertCount(2, $arr1);
        $this->assertCount(1, $arr2);
        $this->assertEquals('a', $arr2->shift());
    }

    public function testArray(): void
    {
        $arr = Arr::factory(['a'])->array();
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr);
        $this->assertEquals('a', $arr[0]);
    }

    public function testToArray(): void
    {
        $arr = Arr::factory(['a'])->toArray();
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr);
        $this->assertEquals('a', $arr[0]);
    }

    public function testMagicObject(): void
    {
        $arr = new Arr();
        $arr->hello = 'hello';
        $arr->world = 'world';
        $this->assertCount(2, $arr);
        $this->assertEquals('hello', $arr->hello);
        $this->assertEquals('world', $arr->world);
    }

    public function testArrayAccess(): void
    {
        $arr = new Arr();
        $arr['hello'] = 'hello';
        $arr['world'] = 'world';
        $this->assertCount(2, $arr);
        $this->assertEquals('hello', $arr['hello']);
        $this->assertEquals('world', $arr['world']);
    }

    public function testIterator(): void
    {
        $arr = new Arr();
        $arr->push('hello');
        $arr->push('world');
        foreach ($arr as $key => $value) {
            if ($key === 0) {
                $this->assertEquals('hello', $value);
            } else {
                $this->assertEquals('world', $value);
            }
        }
    }

    public function testChunk(): void
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 0]);
        $chunk = $arr->chunk(2);
        $this->assertCount(5, $chunk);
        $this->assertEquals([3, 4], $chunk[1]);
    }

    public function testColumn(): void
    {
        $arr = new Arr([['a' => 'a', 'b' => '10'], ['a' => 'a', 'b' => '20'], ['a' => 'a', 'b' => '30']]);
        $column = $arr->clone()->column('a');
        $this->assertInstanceOf(Arr::class, $column);
        $this->assertEquals(['a', 'a', 'a'], $column->toArray());

        $column = $arr->column('a', 'b');
        $this->assertInstanceOf(Arr::class, $column);
        $this->assertEquals(['10' => 'a', '20' => 'a', '30' => 'a'], $column->toArray());
    }

    public function testCombineWithValues(): void
    {
        $arr = new Arr(['firstname', 'lastname']);
        $arr->combineWithValues(['richard', 'huelsberg']);
        $this->assertEquals(['firstname' => 'richard', 'lastname' => 'huelsberg'], $arr->toArray());
    }

    public function testCombineWithKeys(): void
    {
        $arr = new Arr(['richard', 'huelsberg']);
        $arr->combineWithKeys(['firstname', 'lastname']);
        $this->assertEquals(['firstname' => 'richard', 'lastname' => 'huelsberg'], $arr->toArray());
    }

    public function testCountBy(): void
    {
        $arr = new Arr(["one", "two", "two", "three", "three", "four"]);
        $result = $arr->countBy()->toArray();
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('one', $result);
        $this->assertArrayHasKey('two', $result);
        $this->assertArrayHasKey('three', $result);
        $this->assertArrayHasKey('four', $result);
        $this->assertEquals(1, $result['one']);
        $this->assertEquals(2, $result['two']);
        $this->assertEquals(2, $result['three']);
        $this->assertEquals(1, $result['four']);
    }

    public function testCountByWithCallable(): void
    {
        $arr = new Arr([
            ['id' => 1, 'name' => 'a', 'role' => 'Admin'],
            ['id' => 2, 'name' => 'b', 'role' => 'Admin'],
            ['id' => 3, 'name' => 'c', 'role' => 'User'],
        ]);
        $result = $arr->countBy(function (array $value, int $key) {
            return $value['role'];
        })->toArray();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('Admin', $result);
        $this->assertArrayHasKey('User', $result);
        $this->assertEquals(2, $result['Admin']);
        $this->assertEquals(1, $result['User']);
    }

    public function testCountValues(): void
    {
        $arr = new Arr(['a', 'b', 'b', 'c', 'c', 'c', 1]);
        $results = $arr->countValues();
        $this->assertCount(4, $results);
        $this->assertEquals(1, $results['a']);
        $this->assertEquals(2, $results['b']);
        $this->assertEquals(3, $results['c']);
        $this->assertEquals(1, $results[1]);
    }

    public function testDiffAssoc(): void
    {
        $arr1 = new Arr(["a" => "gruen", "b" => "braun", "c" => "blau", "rot"]);
        $result = $arr1->diffAssoc(["a" => "gruen", "gelb", "rot"])->toArray();
        $this->assertCount(3, $result);
        $this->assertEquals('b', array_key_first($result));
        $this->assertEquals(0, array_key_last($result));
        $this->assertEquals('braun', $result['b']);
        $this->assertEquals('blau', $result['c']);
        $this->assertEquals('rot', $result[0]);
    }

    public function testDiffUAssoc(): void
    {
        $arr1 = new Arr(["a" => "gruen", "b" => "braun", "c" => "blau", "rot"]);
        $result = $arr1->diffUAssoc(
            ["a" => "gruen", "gelb", "rot"],
            function (mixed $a, mixed $b) {
                if ($a === $b) {
                    return 0;
                }
                return ($a > $b) ? 1 : -1;
            }
        )->toArray();
        $this->assertCount(3, $result);
        $this->assertEquals('b', array_key_first($result));
        $this->assertEquals(0, array_key_last($result));
        $this->assertEquals('braun', $result['b']);
        $this->assertEquals('blau', $result['c']);
        $this->assertEquals('rot', $result[0]);
    }

    public function testDiffKey(): void
    {
        $arr = new Arr(['blau' => 1, 'rot' => 2, 'grün' => 3, 'violett' => 4]);
        $result = $arr->diffKey(['grün' => 5, 'gelb' => 7, 'türkis' => 8]);
        $this->assertEquals(['blau' => 1, 'rot' => 2, 'violett' => 4], $result->toArray());
    }

    public function testDiffUKey(): void
    {
        $arr1 = new Arr(['blau' => 1, 'rot' => 2, 'grün' => 3, 'violett' => 4]);
        $result = $arr1->diffUKey(
            ['grün' => 5, 'blau' => 6, 'gelb' => 7, 'türkis' => 8],
            function (mixed $key1, mixed $key2) {
                if ($key1 == $key2) {
                    return 0;
                } elseif ($key1 > $key2) {
                    return 1;
                } else {
                    return -1;
                }
            }
        )->toArray();
        $this->assertCount(2, $result);
        $this->assertEquals('rot', array_key_first($result));
        $this->assertEquals('violett', array_key_last($result));
        $this->assertEquals(2, $result['rot']);
        $this->assertEquals(4, $result['violett']);
    }

    public function testDiff(): void
    {
        $arr = new Arr(["a" => "grün", "rot", "blau", "rot"]);
        $result = $arr->diff(["b" => "grün", "gelb", "rot"])->toArray();
        $this->assertCount(1, $result);
        $this->assertEquals(1, array_key_first($result));
        $this->assertEquals('blau', $result[1]);
    }

    public function testFlat(): void
    {
        $arr = new Arr([
            'a' => [
                'b' => [
                    'c' => 'c',
                    'd' => 'd',
                ],
                'e' => 'e',
            ],
            'f' => 'f',
        ]);
        $this->assertEquals([
            'a.b.c' => 'c',
            'a.b.d' => 'd',
            'a.e' => 'e',
            'f' => 'f',
        ], $arr->flat()->toArray());
    }

    public function testFlatten(): void
    {
        $arr = new Arr([
            'a' => [
                'b' => [
                    'c' => 'c',
                    'd' => 'd',
                ],
                'e' => 'e',
            ],
            'f' => 'f',
        ]);
        $this->assertEquals(['c', 'd', 'e', 'f'], $arr->flatten()->toArray());
    }

    public function testFilter(): void
    {
        $arr = new Arr([1, 2, 3, 4]);
        $this->assertEquals([0 => 1, 2 => 3], $arr->filter(fn (int $i) => $i % 2)->toArray());
    }

    public function testFirst(): void
    {
        $arr = new Arr([1, 2, 3]);
        $this->assertEquals(1, $arr->first());
    }

    public function testFlip(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->flip();
        $this->assertEquals([1 => 'a', 2 => 'b', 3 => 'c'], $arr->toArray());
    }

    public function testIntersectAssoc()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->intersectAssoc(['a' => 1, 'b' => 4, 'c' => 3]);
        $this->assertEquals(['a' => 1, 'c' => 3], $arr->toArray());
    }

    public function testIntersectUAssoc()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->intersectUAssoc(['a' => 1, 'b' => 4, 'c' => 3], function ($a, $b) {
            return $a <=> $b;
        });
        $this->assertEquals(['a' => 1, 'c' => 3], $arr->toArray());
    }

    public function testIntersectByKeys()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->intersectByKeys(['a' => 4, 'b' => 5]);
        $this->assertEquals(['a' => 1, 'b' => 2], $arr->toArray());
    }

    public function testIntersectUKey()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->intersectUKey(['a' => 4, 'b' => 5], function ($a, $b) {
            return $a <=> $b;
        });
        $this->assertEquals(['a' => 1, 'b' => 2], $arr->toArray());
    }

    public function testIntersect()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->intersect(['a' => 1, 'b' => 4, 'c' => 3]);
        $this->assertEquals(['a' => 1, 'c' => 3], $arr->toArray());
    }

    public function testKeyExists(): void
    {
        $arr = new Arr(['a' => 'a', 'b' => 'b']);
        $this->assertTrue($arr->keyExists('a'));
        $this->assertFalse($arr->keyExists('c'));
    }

    public function testKeyFirst(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals('a', $arr->keyFirst());
    }

    public function testKeyLast(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals('c', $arr->keyLast());
    }

    public function testKeys(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a', 'b', 'c'], $arr->keys()->toArray());
    }

    public function testLast(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(3, $arr->last());
    }

    public function testMap(): void
    {
        $arr1 = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr2 = $arr1->map(fn ($value) => $value * 2);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr1->toArray());
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $arr2->toArray());
    }

    public function testTransform(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->transform(fn ($value) => $value * 2);
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $arr->toArray());
    }

    public function testMapWithKeys(): void
    {
        $arr1 = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr2 = $arr1->mapWithKeys(fn ($value, $key) => [$key . 'a' => $value * 2]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr1->toArray());
        $this->assertEquals(['aa' => 2, 'ba' => 4, 'ca' => 6], $arr2->toArray());
    }

    public function testTransformWithKeys(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->transformWithKeys(fn ($value, $key) => [$key . 'a' => $value * 2]);
        $this->assertEquals(['aa' => 2, 'ba' => 4, 'ca' => 6], $arr->toArray());
    }

    public function testMergeRecursive(): void
    {
        $arr = new Arr(['a' => ['b' => ['c' => 'c']]]);
        $result = $arr->mergeRecursive(
            ['a' => ['b' => ['d' => 'd']], 'e' => 'e']
        )->toArray();
        $this->assertEquals(['a' => ['b' => ['c' => 'c', 'd' => 'd']], 'e' => 'e'], $result);
    }

    public function testMerge(): void
    {
        $arr1 = new Arr(['a' => 'a', 'b' => 'b']);
        $arr2 = new Arr(['b' => ['b'], 'c' => 'c']);
        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => 'c'], $arr1->union($arr2)->toArray());
    }

    public function testMultisort(): void
    {
        $arr = new Arr([10, 100, 100, 0]);
        $this->assertEquals(
            [0 => 0, 1 => 10, 2 => 100, 3 => 100],
            $arr->multisort([1, 3, 2, 4])->toArray()
        );
    }

    public function testPad(): void
    {
        $arr = new Arr([12, 10, 9]);
        $this->assertEquals([12, 10, 9], $arr->clone()->pad(1, 0)->toArray());
        $this->assertEquals([12, 10, 9], $arr->clone()->pad(3, 0)->toArray());
        $this->assertEquals([12, 10, 9, 0, 0], $arr->clone()->pad(5, 0)->toArray());
    }

    public function testPushPop(): void
    {
        $arr = new Arr();
        $this->assertEquals(null, $arr->pop());
        $arr->push(1);
        $arr->push(2);
        $arr->push(3);
        $this->assertEquals(3, $arr->pop());
        $this->assertEquals(2, $arr->pop());
        $this->assertEquals(1, $arr->pop());
        $this->assertEquals(null, $arr->pop());
    }

    public function testProduct(): void
    {
        $arr = new Arr();
        $arr->push(1);
        $arr->push(2);
        $arr->push(3);
        $arr->push(4);
        $this->assertEquals(24, $arr->product());
    }

    public function testConcat(): void
    {
        $arr = new Arr([1, 2, 3]);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $arr->concat([4, 5, 6])->toArray());
    }

    public function testRandomInt(): void
    {
        $arr = new Arr([1, 2, 3]);
        /** @var int $result */
        $result = $arr->random();
        $this->assertTrue(is_integer($result));
        $this->assertTrue(in_array($result, [0, 1, 2]));
    }

    public function testRandomString(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        /** @var Str $result */
        $result = $arr->random(1);
        $this->assertInstanceOf(Str::class, $result);
        $this->assertTrue(in_array($result->toString(), ['a', 'b', 'c']));
    }

    public function testRandomArray(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        /** @var Str $result */
        $result = $arr->random(2);
        $this->assertInstanceOf(Arr::class, $result);
        $this->assertCount(2, $result);
        $this->assertTrue(in_array($result[0], ['a', 'b', 'c']));
        $this->assertTrue(in_array($result[1], ['a', 'b', 'c']));
    }

    public function testReduce(): void
    {
        $arr = new Arr([1, 2, 3]);
        $result = $arr->reduce(fn (int $carry, int $item): int => $carry + $item, 0);
        $this->assertTrue(is_integer($result));
        $this->assertEquals(6, $result);
    }

    public function testReplaceRecursive(): void
    {
        $arr = new Arr(['citrus' => ['orange'], 'berries' => ['blackberry', 'raspberry']]);
        $result = $arr->replaceRecursive(['citrus' => ['pineapple'], 'berries' => ['blueberry']])->toArray();
        $this->assertEquals(['citrus' => ['pineapple'], 'berries' => ['blueberry', 'raspberry']], $result);
    }

    public function testReplace(): void
    {
        $arr = new Arr(['Orange', 'Banane', 'Apfel', 'Himbeere']);
        $arr->replace([0 => 'Ananas', 4 => 'Kirsche'], [0 => 'Traube']);
        $this->assertEquals(['Traube', 'Banane', 'Apfel', 'Himbeere', 'Kirsche'], $arr->toArray());
    }

    public function testReverseWithoutPreserved(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->reverse();
        $this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $arr->toArray());
    }

    public function testReverseWithPreserved(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->reverse(true);
        $this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $arr->toArray());
    }

    public function testSearch(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals('b', $arr->search('2'));
        $this->assertEquals('b', $arr->search(2, true));
    }

    public function testShift(): void
    {
        $arr = new Arr();
        $arr->push(1);
        $arr->push(2);
        $arr->push(3);
        $this->assertEquals(1, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(3, $arr->shift());
        $this->assertEquals(null, $arr->shift());
    }

    public function testSlice(): void
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['c', 'd', 'e'], $arr->clone()->slice(2)->toArray());
        $this->assertEquals(['d'], $arr->clone()->slice(-2, 1)->toArray());
        $this->assertEquals(['a', 'b', 'c'], $arr->clone()->slice(0, 3)->toArray());
    }

    public function testSkip(): void
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['c', 'd', 'e'], $arr->skip(2)->toArray());
    }

    public function testTake(): void
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['a', 'b', 'c'], $arr->clone()->take(3)->toArray());
        $this->assertEquals(['c', 'd', 'e'], $arr->clone()->take(-3)->toArray());
    }

    public function testSplice(): void
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['b', 'c', 'd', 'e'], $arr->clone()->splice(1)->toArray());
        $this->assertEquals(['b', 'c', 'd'], $arr->clone()->splice(1, -1)->toArray());
    }

    public function testSum(): void
    {
        $arr = new Arr([1, 10, 100, 1000]);
        $this->assertEquals(1111, $arr->sum());
    }

    public function testAverage(): void
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertEquals(2, $arr->average());
    }

    public function testMedian(): void
    {
        $arr = new Arr([4, 1, 2]);
        $this->assertEquals(2, $arr->median());
    }

    public function testUDiffAssoc(): void
    {
        $arr1 = new Arr([
            "0.1" => new CR(9),
            "0.5" => new CR(12),
            0 => new CR(23),
            1 => new CR(4),
            2 => new CR(-15),
        ]);
        $arr2 = [
            "0.2" => new CR(9),
            "0.5" => new CR(22),
            0 => new CR(3),
            1 => new CR(4),
            2 => new CR(-15),
        ];
        $result = $arr1->uDiffAssoc($arr2, [CR::class, "comp_func_cr"])->toArray();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('0.1', array_key_first($result));
        $this->assertArrayHasKey('0.5', $result);
        $this->assertEquals(0, array_key_last($result));
        $this->assertInstanceOf(CR::class, $result['0.1']);
        $this->assertInstanceOf(CR::class, $result['0.5']);
        $this->assertInstanceOf(CR::class, $result[0]);
        $this->assertEquals(9, $result['0.1']->getPrivNumber());
        $this->assertEquals(12, $result['0.5']->getPrivNumber());
        $this->assertEquals(23, $result[0]->getPrivNumber());
    }

    public function testUDiffUAssoc(): void
    {
        $arr1 = new Arr([
            "0.1" => new CR(9),
            "0.5" => new CR(12),
            0 => new CR(23),
            1 => new CR(4),
            2 => new CR(-15),
        ]);
        $arr2 = [
            "0.2" => new CR(9),
            "0.5" => new CR(22),
            0 => new CR(3),
            1 => new CR(4),
            2 => new CR(-15),
        ];
        $result = $arr1->uDiffUAssoc($arr2, [CR::class, "comp_func_cr"], [CR::class, "comp_func_key"])->toArray();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('0.1', array_key_first($result));
        $this->assertArrayHasKey('0.5', $result);
        $this->assertEquals(0, array_key_last($result));
        $this->assertInstanceOf(CR::class, $result['0.1']);
        $this->assertInstanceOf(CR::class, $result['0.5']);
        $this->assertInstanceOf(CR::class, $result[0]);
        $this->assertEquals(9, $result['0.1']->getPrivNumber());
        $this->assertEquals(12, $result['0.5']->getPrivNumber());
        $this->assertEquals(23, $result[0]->getPrivNumber());
    }

    public function testUDiff(): void
    {
        $arr1 = new Arr([['Bob', 42], ['Phil', 37], ['Frank', 39]]);
        $arr2 = [['Phil', 37], ['Mark', 45]];
        $arr3 = $arr1->uDiff($arr2, function (mixed $a, mixed $b) {
            return strcmp(implode("", $a), implode("", $b));
        })->toArray();
        $this->assertIsArray($arr3);
        $this->assertCount(2, $arr3);
        $this->assertEquals(0, array_key_first($arr3));
        $this->assertEquals(2, array_key_last($arr3));

        $this->assertIsArray($arr3[0]);
        $this->assertCount(2, $arr3[0]);
        $this->assertEquals(0, array_key_first($arr3[0]));
        $this->assertEquals(1, array_key_last($arr3[0]));
        $this->assertEquals('Bob', $arr3[0][0]);
        $this->assertEquals(42, $arr3[0][1]);

        $this->assertIsArray($arr3[2]);
        $this->assertCount(2, $arr3[2]);
        $this->assertEquals(0, array_key_first($arr3[2]));
        $this->assertEquals(1, array_key_last($arr3[2]));
        $this->assertEquals('Frank', $arr3[2][0]);
        $this->assertEquals(39, $arr3[2][1]);
    }

    public function testUIntersectAssoc()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersectAssoc(['a' => 1, 'b' => 4, 'c' => 3], function ($a, $b) {
            return $a <=> $b;
        });
        $this->assertEquals(['a' => 1, 'c' => 3], $arr->toArray());
    }

    public function testUIntersectAssocThrowsExceptionWhenCallableMissing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last value must be a callable');

        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersectAssoc(['a' => 1, 'b' => 4, 'c' => 3]);
    }

    public function testUIntersectUAssoc()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersectUAssoc(['a' => 1, 'b' => 4, 'c' => 3], function ($a, $b) {
            return $a <=> $b;
        }, function ($a, $b) {
            return $a <=> $b;
        });
        $this->assertEquals(['a' => 1, 'c' => 3], $arr->toArray());
    }

    public function testUIntersectUAssocThrowsExceptionWhenCallableMissing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last value must be a callable');

        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersectUAssoc(['a' => 1, 'b' => 4, 'c' => 3]);
    }

    public function testUIntersectUAssocThrowsExceptionWhenSecondCallableMissing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last value must be a callable');

        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersectUAssoc(['a' => 1, 'b' => 4, 'c' => 3], function ($a, $b) {
            return $a <=> $b;
        }, 'not_a_callable');
    }

    public function testUIntersect()
    {
        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersect(['a' => 1, 'b' => 4, 'c' => 3], function ($a, $b) {
            return $a <=> $b;
        });
        $this->assertEquals(['a' => 1, 'c' => 3], $arr->toArray());
    }

    public function testUIntersectThrowsExceptionWhenCallableMissing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The last value must be a callable');

        $arr = Arr::factory(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->uIntersect(['a' => 1, 'b' => 4, 'c' => 3]);
    }

    public function testUnique(): void
    {
        $arr = new Arr();
        $arr->push(1);
        $arr->push(1);
        $arr->push(1);
        $arr->push(2);
        $arr->unique();
        $this->assertEquals(2, $arr->pop());
        $this->assertEquals(1, $arr->pop());
        $this->assertEquals(null, $arr->pop());
    }

    public function testUnshift(): void
    {
        $arr = new Arr();
        $arr->unshift(1);
        $arr->unshift(2);
        $arr->unshift(3);
        $this->assertEquals(3, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(1, $arr->shift());
    }

    public function testValues(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals([1, 2, 3], $arr->values()->toArray());
    }

    public function testWalkRecursive(): void
    {
        $fruits = new Arr(['süß' => ['a' => 'Apfel', 'b' => 'Banane'], 'sauer' => 'Zitrone']);
        $result = '';
        $fruits->walkRecursive(function (mixed $value, mixed $key) use (&$result) {
            $result .= sprintf('%s beinhaltet %s ', $key, $value);
        });
        $result = trim($result);
        $this->assertEquals('a beinhaltet Apfel b beinhaltet Banane sauer beinhaltet Zitrone', $result);
    }

    public function testWalk(): void
    {
        $arr = new Arr(['testa' => 1, 'testb' => 2, 'testc' => 3]);
        $result = '';
        $arr->walk(function (mixed $value, mixed $key) use (&$result) {
            $result .= sprintf('%s%d', $key, $value);
        });
        $this->assertEquals('testa1testb2testc3', $result);
    }

    public function testSort(): void
    {
        $arr = new Arr([3, 1, 4, 2]);
        $this->assertEquals([1 => 1, 3 => 2, 0 => 3, 2 => 4], $arr->sort(SORT_NUMERIC)->toArray());
    }

    public function testSortDesc(): void
    {
        $arr = new Arr([3, 1, 4, 2]);
        $this->assertEquals([2 => 4, 0 => 3, 3 => 2, 1 => 1], $arr->sortDesc(SORT_NUMERIC)->toArray());
    }

    public function testCount(): void
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertEquals(3, $arr->count());
    }

    public function testInArray(): void
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->inArray('1'));
        $this->assertTrue($arr->inArray(1, true));
        $this->assertFalse($arr->inArray('4'));
        $this->assertFalse($arr->inArray(4, true));
    }

    public function testContains(): void
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->contains('1'));
        $this->assertTrue($arr->contains(1, true));
        $this->assertFalse($arr->contains('4'));
        $this->assertFalse($arr->contains(4, true));
    }

    public function testContainsOne(): void
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->containsOne(['3', 4]));
        $this->assertTrue($arr->containsOne([3, 4]));
        $this->assertFalse($arr->containsOne(['3', 4], true));
    }

    public function testContainsAll(): void
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->containsAll(['2', 3]), 'Check 1');
        $this->assertTrue($arr->containsAll([2, 3], true), 'Check 2');
        $this->assertFalse($arr->containsAll(['3', 4], true), 'Check 3');
        $this->assertFalse($arr->containsAll([4, 5], true), 'Check 4');
    }

    public function testSortKeys(): void
    {
        $arr = new Arr(['c' => 2, 'b' => 3, 'a' => 1]);
        $this->assertEquals(
            ['a' => 1, 'b' => 3, 'c' => 2],
            $arr->sortKeys()->toArray()
        );
    }

    public function testNatcasesort(): void
    {
        $arr = new Arr(['IMG0.png', 'img12.png', 'img10.png', 'img2.png', 'img1.png', 'IMG3.png']);
        $arr->natcasesort();
        $array = $arr->toArray();
        $this->assertIsArray($array);
        $this->assertCount(6, $array);
        $this->assertEquals(0, array_key_first($array));
        $this->assertEquals('IMG0.png', $array[0]);
        $this->assertEquals('img1.png', $array[4]);
        $this->assertEquals('img2.png', $array[3]);
        $this->assertEquals('IMG3.png', $array[5]);
        $this->assertEquals('img10.png', $array[2]);
        $this->assertEquals('img12.png', $array[1]);
        $this->assertEquals(1, array_key_last($array));
    }

    public function testNatsort(): void
    {
        $arr = new Arr(['img12.png', 'img10.png', 'img2.png', 'img1.png']);
        $arr->natsort();
        $array = $arr->toArray();
        $this->assertIsArray($array);
        $this->assertCount(4, $array);
        $this->assertEquals(3, array_key_first($array));
        $this->assertEquals('img1.png', $array[3]);
        $this->assertEquals('img2.png', $array[2]);
        $this->assertEquals('img10.png', $array[1]);
        $this->assertEquals('img12.png', $array[0]);
        $this->assertEquals(0, array_key_last($array));
    }

    public function testShuffle(): void
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 0]);
        $arr->shuffle();
        $array = $arr->toArray();
        $this->assertIsArray($array);
        $this->assertCount(10, $array);
        $this->assertNotEquals('1234567890', implode('', $array));
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue(in_array(1, $array));
        }
    }

    public function testUksort(): void
    {
        $arr = new Arr(['John' => 1, 'the Earth' => 2, 'an apple' => 3, 'a banana' => 4]);
        $arr->uksort(function (mixed $a, mixed $b): int {
            $a = preg_replace('@^(a|an|the) @', '', $a);
            $b = preg_replace('@^(a|an|the) @', '', $b);
            return strcasecmp($a, $b);
        });
        $array = $arr->toArray();
        $this->assertTrue(array_key_first($array) === 'an apple');
        $this->assertEquals(3, $array['an apple']);
        $this->assertTrue(array_key_last($array) === 'John');
        $this->assertEquals(1, $array['John']);
    }

    public function testUsort(): void
    {
        $arr = new Arr([4, 3, 2, 1, 5, 6, 7, 8, 9, 0]);
        $arr->usort(fn (mixed $a, mixed $b): int => $a - $b);
        $this->assertEquals('0123456789', implode('', $arr->toArray()));
        $arr->usort(fn (mixed $a, mixed $b): int => $b - $a);
        $this->assertEquals('9876543210', implode('', $arr->toArray()));
    }

    public function testEach1(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $count = 0;
        $arr->each(function (int $value, string $key) use (&$count) {
            $count += $value;
        });
        $this->assertEquals(6, $count);
    }

    public function testEach2(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $count = 0;
        $arr->each(function (int $value, string $key) use (&$count) {
            $count += $value;
            if ($value === 2) {
                return false;
            }
        });
        $this->assertEquals(3, $count);
    }

    public function testOnly1(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(
            ['b' => 2],
            $arr->only('b')->toArray()
        );
    }

    public function testOnly2(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(
            ['b' => 2],
            $arr->only(['b'])->toArray()
        );
    }

    public function testPrepend1(): void
    {
        $arr = new Arr(['b' => 2, 'c' => 3]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr->prepend(1, 'a')->toArray());
    }

    public function testPrepend2(): void
    {
        $arr = new Arr(['b', 'c']);
        $this->assertEquals(['a', 'b', 'c'], $arr->prepend('a')->toArray());
    }

    public function testAppend1(): void
    {
        $arr = new Arr(['a', 'b']);
        $this->assertEquals(['a', 'b', 'c'], $arr->append('c')->toArray());
    }

    public function testAppend2(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr->append(3, 'c')->toArray());
    }

    public function testDivide(): void
    {
        $arr = new Arr(['a' => 3, 'b' => 2, 'c' => 1]);
        $this->assertEquals([['a', 'b', 'c'], [3, 2, 1]], $arr->divide()->toArray());
    }

    public function testDot(): void
    {
        $arr = new Arr([
            'a' => 1,
            'b' => [
                'c' => 2,
                'd' => 3,
            ],
            'e' => 4,
        ]);
        $this->assertEquals([
            'a' => 1,
            'b.c' => 2,
            'b.d' => 3,
            'e' => 4,
        ], $arr->dot()->toArray());
    }

    public function testUndot(): void
    {
        $arr = new Arr([
            'a' => 1,
            'b.c' => 2,
            'b.d' => 3,
            'e' => 4,
        ]);
        $this->assertEquals([
            'a' => 1,
            'b' => [
                'c' => 2,
                'd' => 3,
            ],
            'e' => 4,
        ], $arr->undot()->toArray());
    }

    public function testImplode(): void
    {
        $arr = new Arr(['a', 'b', 'c']);
        $this->assertEquals('a,b,c', $arr->implode(','));
    }

    public function testHttpBuildQuery(): void
    {
        $arr = new Arr(['a' => 1, 'b' => ['c' => 2, 'd' => 3, 'e' => 4]]);
        $this->assertEquals('a=1&b%5Bc%5D=2&b%5Bd%5D=3&b%5Be%5D=4', $arr->httpBuildQuery()->toString());
    }

    public function testForget1(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(
            ['a' => 1, 'c' => 3],
            $arr->forget('b')->toArray()
        );
    }

    public function testForget2(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(
            ['a' => 1, 'c' => 3],
            $arr->forget(['b'])->toArray()
        );
    }

    public function testForget3(): void
    {
        $arr = new Arr(['a' => 1, 'b' => ['c' => 2, 'd' => 3, ], 'e' => 4]);
        $this->assertEquals(
            ['a' => 1, 'b' => ['c' => 2, ], 'e' => 4],
            $arr->forget(['b.d'])->toArray()
        );
    }

    public function testExcept(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(
            ['a' => 1, 'c' => 3],
            $arr->except(['b'])->toArray()
        );
    }

    public function testIsEmpty(): void
    {
        $arr = new Arr(['a' => 'a', 'b' => ['b' => 'b']]);
        $this->assertFalse($arr->isEmpty());
        $arr = new Arr();
        $this->assertTrue($arr->isEmpty());
    }

    public function testIsNotEmpty(): void
    {
        $arr = new Arr(['a' => 'a', 'b' => ['b' => 'b']]);
        $this->assertTrue($arr->isNotEmpty());
        $arr = new Arr();
        $this->assertFalse($arr->isNotEmpty());
    }

    public function testGet(): void
    {
        $arr = new Arr(['a' => 'a', 'b' => ['b' => 'b']]);
        $this->assertEquals('a', $arr->get('a'));
        $this->assertNull($arr->get('c'));
        $this->assertEquals('b', $arr->get('b.b'));
        $this->assertNull($arr->get('b.c'));
    }

    public function testGroupBy(): void
    {
        $arr = new Arr([
            1 => ['id' => 1, 'name' => 'a', 'role' => 'Admin'],
            2 => ['id' => 2, 'name' => 'b', 'role' => 'Admin'],
            3 => ['id' => 3, 'name' => 'c', 'role' => 'User'],
        ]);
        $result = $arr->groupBy(function (array $value, int $key) {
            return $value['role'];
        })->toArray();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('Admin', $result);
        $this->assertArrayHasKey('User', $result);

        $this->assertIsArray($result['Admin']);
        $this->assertCount(2, $result['Admin']);
        $this->assertArrayHasKey(1, $result['Admin']);
        $this->assertArrayHasKey(2, $result['Admin']);
        $this->assertIsArray($result['Admin'][1]);
        $this->assertArrayHasKey('id', $result['Admin'][1]);
        $this->assertEquals(1, $result['Admin'][1]['id']);
        $this->assertEquals('Admin', $result['Admin'][1]['role']);

        $this->assertIsArray($result['User']);
        $this->assertCount(1, $result['User']);
        $this->assertArrayHasKey(3, $result['User']);
        $this->assertIsArray($result['User'][3]);
        $this->assertArrayHasKey('id', $result['User'][3]);
        $this->assertEquals(3, $result['User'][3]['id']);
        $this->assertEquals('User', $result['User'][3]['role']);
    }

    public function testSet(): void
    {
        $arr = new Arr();
        $arr->set('a', 'a');
        $arr->set('b.b', 'b');
        $this->assertEquals(['a' => 'a', 'b' => ['b' => 'b']], $arr->toArray());
    }

    public function testHas(): void
    {
        $arr = new Arr(['a' => 'a', 'b' => ['a' => 'a', 'b' => 'b']]);
        $this->assertTrue($arr->has('a'));
        $this->assertTrue($arr->has('b'));
        $this->assertTrue($arr->has('b.a'));
        $this->assertTrue($arr->has('b.b'));
        $this->assertFalse($arr->has('c'));
        $this->assertFalse($arr->has('b.c'));
    }

    public function testUnion(): void
    {
        $arr1 = new Arr(['a' => 'a', 'b' => 'b']);
        $arr2 = new Arr(['b' => ['b'], 'c' => 'c']);
        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => 'c'], $arr1->union($arr2)->toArray());
    }

    public function testNthStep2(): void
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals([1, 3, 5, 7, 9], $arr->nth(2)->toArray());
    }

    public function testNthStep3(): void
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals([1, 4, 7, 10], $arr->nth(3)->toArray());
    }

    public function testNthStep3Offset3(): void
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals([3, 6, 9], $arr->nth(3, 2)->toArray());
    }

    public function testPluck(): void
    {
        $arr = new Arr([['id' => 1, 'name' => 'a'], ['id' => 2, 'name' => 'b']]);
        $this->assertEquals([1 => 'a', 2 => 'b'], $arr->pluck('name', 'id')->toArray());
    }
}
