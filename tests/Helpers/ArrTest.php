<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Arr;
use TinyFramework\Helpers\Str;

class ArrTest extends TestCase
{
    public function testStaticFactory()
    {
        $arr = Arr::factory([1, 2, 3]);
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(3, $arr);
        $this->assertEquals(1, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(3, $arr->shift());
        $this->assertEquals(null, $arr->shift());
    }

    public function testStaticWrap()
    {
        $arr = Arr::wrap(1);
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(1, $arr);
        $this->assertEquals(1, $arr->shift());
    }

    public function testStaticFill()
    {
        $arr = Arr::fill(1, 3, 'a');
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(3, $arr);
        $this->assertEquals('a', $arr->shift());
        $this->assertEquals('a', $arr->shift());
        $this->assertEquals('a', $arr->shift());
        $this->assertEquals(null, $arr->shift());
    }

    public function testStaticRange()
    {
        $arr = Arr::range(0, 4, 2);
        $this->assertInstanceOf(Arr::class, $arr);
        $this->assertCount(3, $arr);
        $this->assertEquals(0, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(4, $arr->shift());
        $this->assertEquals(null, $arr->pop());
    }

    public function testClone()
    {
        $arr1 = Arr::factory(['a']);
        $arr2 = clone $arr1;
        $arr1->push('b');
        $this->assertCount(2, $arr1);
        $this->assertCount(1, $arr2);
        $this->assertEquals('a', $arr2->shift());
    }

    public function testArray()
    {
        $arr = Arr::factory(['a'])->array();
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr);
        $this->assertEquals('a', $arr[0]);
    }

    public function testToArray()
    {
        $arr = Arr::factory(['a'])->toArray();
        $this->assertIsArray($arr);
        $this->assertCount(1, $arr);
        $this->assertEquals('a', $arr[0]);
    }

    public function testMagicObject()
    {
        $arr = new Arr();
        $arr->hello = 'hello';
        $arr->world = 'world';
        $this->assertCount(2, $arr);
        $this->assertEquals('hello', $arr->hello);
        $this->assertEquals('world', $arr->world);
    }

    public function testArrayAccess()
    {
        $arr = new Arr();
        $arr['hello'] = 'hello';
        $arr['world'] = 'world';
        $this->assertCount(2, $arr);
        $this->assertEquals('hello', $arr['hello']);
        $this->assertEquals('world', $arr['world']);
    }

    public function testIterator()
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

    public function testChunk()
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 0]);
        $chunk = $arr->chunk(2);
        $this->assertCount(5, $chunk);
        $this->assertEquals([3, 4], $chunk[1]);
    }

    public function testColumn()
    {
        $arr = new Arr([['a' => 'a', 'b' => '10'], ['a' => 'a', 'b' => '20'], ['a' => 'a', 'b' => '30']]);
        $column = $arr->clone()->column('a');
        $this->assertInstanceOf(Arr::class, $column);
        $this->assertEquals(['a', 'a', 'a'], $column->toArray());

        $column = $arr->column('a', 'b');
        $this->assertInstanceOf(Arr::class, $column);
        $this->assertEquals(['10' => 'a', '20' => 'a', '30' => 'a'], $column->toArray());
    }

    public function testCombineWithValues()
    {
        $this->markTestSkipped('TODO');
    }

    public function testCombineWithKeys()
    {
        $this->markTestSkipped('TODO');
    }

    public function testCountBy()
    {
        $this->markTestSkipped('SKIP');
    }

    public function testCountValues()
    {
        $arr = new Arr(['a', 'b', 'b', 'c', 'c', 'c', 1]);
        $results = $arr->countValues();
        $this->assertCount(4, $results);
        $this->assertEquals(1, $results['a']);
        $this->assertEquals(2, $results['b']);
        $this->assertEquals(3, $results['c']);
        $this->assertEquals(1, $results[1]);
    }

    public function testDiffAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testDiffUAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testDiffKey()
    {
        $arr = new Arr(['blau' => 1, 'rot' => 2, 'grün' => 3, 'violett' => 4]);
        $result = $arr->diffKey(['grün' => 5, 'gelb' => 7, 'türkis' => 8]);
        $this->assertEquals(['blau' => 1, 'rot' => 2, 'violett' => 4], $result->toArray());
    }

    public function testDiffUKey()
    {
        $this->markTestSkipped('TODO');
    }

    public function testDiff()
    {
        $this->markTestSkipped('TODO');
    }

    public function testFlat()
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

    public function testFlatten()
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

    public function testFilter()
    {
        $arr = new Arr([1, 2, 3, 4]);
        $this->assertEquals([0 => 1, 2 => 3], $arr->filter(fn(int $i) => $i % 2)->toArray());
    }

    public function testFirst()
    {
        $arr = new Arr([1, 2, 3]);
        $this->assertEquals(1, $arr->first());
    }

    public function testFlip()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->flip();
        $this->assertEquals([1 => 'a', 2 => 'b', 3 => 'c'], $arr->toArray());
    }

    public function testIntersectAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersectUAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersectByKeys()
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersectUKey()
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersect()
    {
        $this->markTestSkipped('TODO');
    }

    public function testKeyExists()
    {
        $arr = new Arr(['a' => 'a', 'b' => 'b']);
        $this->assertTrue($arr->keyExists('a'));
        $this->assertFalse($arr->keyExists('c'));
    }

    public function testKeyFirst()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals('a', $arr->keyFirst());
    }

    public function testKeyLast()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals('c', $arr->keyLast());
    }

    public function testKeys()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a', 'b', 'c'], $arr->keys()->toArray());
    }

    public function testLast()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(3, $arr->last());
    }

    public function testMap()
    {
        $arr1 = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr2 = $arr1->map(fn($value) => $value * 2);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr1->toArray());
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $arr2->toArray());
    }

    public function testTransform()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->transform(fn($value) => $value * 2);
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $arr->toArray());
    }

    public function testMapWithKeys()
    {
        $arr1 = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr2 = $arr1->mapWithKeys(fn($value, $key) => [$key . 'a' => $value * 2]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr1->toArray());
        $this->assertEquals(['aa' => 2, 'ba' => 4, 'ca' => 6], $arr2->toArray());
    }

    public function testTransformWithKeys()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->transformWithKeys(fn($value, $key) => [$key . 'a' => $value * 2]);
        $this->assertEquals(['aa' => 2, 'ba' => 4, 'ca' => 6], $arr->toArray());
    }

    public function testMergeRecursive()
    {
        $arr = new Arr(['a' => ['b' => ['c' => 'c']]]);
        $result = $arr->mergeRecursive(
            ['a' => ['b' => ['d' => 'd']], 'e' => 'e']
        )->toArray();
        $this->assertEquals(['a' => ['b' => ['c' => 'c', 'd' => 'd']], 'e' => 'e'], $result);
    }

    public function testMerge()
    {
        $arr1 = new Arr(['a' => 'a', 'b' => 'b']);
        $arr2 = new Arr(['b' => ['b'], 'c' => 'c']);
        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => 'c'], $arr1->union($arr2)->toArray());
    }

    public function testMultisort()
    {
        $arr = new Arr([10, 100, 100, 0]);
        $this->assertEquals(
            [0 => 0, 1 => 10, 2 => 100, 3 => 100],
            $arr->multisort([1, 3, 2, 4])->toArray()
        );
    }

    public function testPad()
    {
        $arr = new Arr([12, 10, 9]);
        $this->assertEquals([12, 10, 9], $arr->clone()->pad(1, 0)->toArray());
        $this->assertEquals([12, 10, 9], $arr->clone()->pad(3, 0)->toArray());
        $this->assertEquals([12, 10, 9, 0, 0], $arr->clone()->pad(5, 0)->toArray());
    }

    public function testPushPop()
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

    public function testProduct()
    {
        $arr = new Arr();
        $arr->push(1);
        $arr->push(2);
        $arr->push(3);
        $arr->push(4);
        $this->assertEquals(24, $arr->product());
    }

    public function testConcat()
    {
        $arr = new Arr([1, 2, 3]);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $arr->concat([4, 5, 6])->toArray());
    }

    public function testRandomInt()
    {
        $arr = new Arr([1, 2, 3]);
        /** @var int $result */
        $result = $arr->random();
        $this->assertTrue(is_integer($result));
        $this->assertTrue(in_array($result, [0, 1, 2]));
    }

    public function testRandomString()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        /** @var Str $result */
        $result = $arr->random(1);
        $this->assertInstanceOf(Str::class, $result);
        $this->assertTrue(in_array($result->toString(), ['a', 'b', 'c']));
    }

    public function testRandomArray()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        /** @var Str $result */
        $result = $arr->random(2);
        $this->assertInstanceOf(Arr::class, $result);
        $this->assertCount(2, $result);
        $this->assertTrue(in_array($result[0], ['a', 'b', 'c']));
        $this->assertTrue(in_array($result[1], ['a', 'b', 'c']));
    }

    public function testReduce()
    {
        $arr = new Arr([1, 2, 3]);
        $result = $arr->reduce(fn(int $carry, int $item): int => $carry + $item, 0);
        $this->assertTrue(is_integer($result));
        $this->assertEquals(6, $result);
    }

    public function testReplaceRecursive()
    {
        $arr = new Arr(['citrus' => ['orange'], 'berries' => ['blackberry', 'raspberry']]);
        $result = $arr->replaceRecursive(['citrus' => ['pineapple'], 'berries' => ['blueberry']])->toArray();
        $this->assertEquals(['citrus' => ['pineapple'], 'berries' => ['blueberry', 'raspberry']], $result);
    }

    public function testReplace()
    {
        $arr = new Arr(['Orange', 'Banane', 'Apfel', 'Himbeere']);
        $arr->replace([0 => 'Ananas', 4 => 'Kirsche'], [0 => 'Traube']);
        $this->assertEquals(['Traube', 'Banane', 'Apfel', 'Himbeere', 'Kirsche'], $arr->toArray());
    }

    public function testReverseWithoutPreserved()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->reverse();
        $this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $arr->toArray());
    }

    public function testReverseWithPreserved()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->reverse(true);
        $this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $arr->toArray());
    }

    public function testSearch()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals('b', $arr->search('2'));
        $this->assertEquals('b', $arr->search(2, true));
    }

    public function testShift()
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

    public function testSlice()
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['c', 'd', 'e'], $arr->clone()->slice(2)->toArray());
        $this->assertEquals(['d'], $arr->clone()->slice(-2, 1)->toArray());
        $this->assertEquals(['a', 'b', 'c'], $arr->clone()->slice(0, 3)->toArray());
    }

    public function testSkip()
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['c', 'd', 'e'], $arr->skip(2)->toArray());
    }

    public function testTake()
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['a', 'b', 'c'], $arr->clone()->take(3)->toArray());
        $this->assertEquals(['c', 'd', 'e'], $arr->clone()->take(-3)->toArray());
    }

    public function testSplice()
    {
        $arr = new Arr(['a', 'b', 'c', 'd', 'e']);
        $this->assertEquals(['b', 'c', 'd', 'e'], $arr->clone()->splice(1)->toArray());
        $this->assertEquals(['b', 'c', 'd'], $arr->clone()->splice(1, -1)->toArray());
    }

    public function testSum()
    {
        $arr = new Arr([1, 10, 100, 1000]);
        $this->assertEquals(1111, $arr->sum());
    }

    public function testAverage()
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertEquals(2, $arr->average());
    }

    public function testMedian()
    {
        $arr = new Arr([4, 1, 2]);
        $this->assertEquals(2, $arr->median());
    }

    public function testUDiffAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUDiffUAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUDiff()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUIntersectAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUIntersectUAssoc()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUIntersect()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUnique()
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

    public function testUnshift()
    {
        $arr = new Arr();
        $arr->unshift(1);
        $arr->unshift(2);
        $arr->unshift(3);
        $this->assertEquals(3, $arr->shift());
        $this->assertEquals(2, $arr->shift());
        $this->assertEquals(1, $arr->shift());
    }

    public function testValues()
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals([1, 2, 3], $arr->values()->toArray());
    }

    public function testWalkRecursive()
    {
        $this->markTestSkipped('TODO');
    }

    public function testWalk()
    {
        $this->markTestSkipped('TODO');
    }

    public function testSort()
    {
        $arr = new Arr([3, 1, 4, 2]);
        $this->assertEquals([1 => 1, 3 => 2, 0 => 3, 2 => 4], $arr->sort(SORT_NUMERIC)->toArray());
    }

    public function testSortDesc()
    {
        $arr = new Arr([3, 1, 4, 2]);
        $this->assertEquals([2 => 4, 0 => 3, 3 => 2, 1 => 1], $arr->sortDesc(SORT_NUMERIC)->toArray());
    }

    public function testCount()
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertEquals(3, $arr->count());
    }

    public function testInArray()
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->inArray('1'));
        $this->assertTrue($arr->inArray(1, true));
        $this->assertFalse($arr->inArray('4'));
        $this->assertFalse($arr->inArray(4, true));
    }

    public function testContains()
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->contains('1'));
        $this->assertTrue($arr->contains(1, true));
        $this->assertFalse($arr->contains('4'));
        $this->assertFalse($arr->contains(4, true));
    }

    public function testContainsOne()
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->containsOne(['3', 4]));
        $this->assertTrue($arr->containsOne([3, 4]));
        $this->assertFalse($arr->containsOne(['3', 4], true));
    }

    public function testContainsAll()
    {
        $arr = new Arr([3, 1, 2]);
        $this->assertTrue($arr->containsAll(['2', 3]), 'Check 1');
        $this->assertTrue($arr->containsAll([2, 3], true), 'Check 2');
        $this->assertFalse($arr->containsAll(['3', 4], true), 'Check 3');
        $this->assertFalse($arr->containsAll([4, 5], true), 'Check 4');
    }

    public function testSortKeys()
    {
        $this->markTestSkipped('TODO');
    }

    public function testNatcasesort()
    {
        $this->markTestSkipped('TODO');
    }

    public function testNatsort()
    {
        $this->markTestSkipped('TODO');
    }

    public function testShuffle()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUksort()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUsort()
    {
        $this->markTestSkipped('TODO');
    }

    public function testEach()
    {
        $this->markTestSkipped('TODO');
    }

    public function testOnly()
    {
        $this->markTestSkipped('TODO');
    }

    public function testPrepend()
    {
        $this->markTestSkipped('TODO');
    }

    public function testAppend()
    {
        $this->markTestSkipped('TODO');
    }

    public function testDivide()
    {
        $this->markTestSkipped('TODO');
    }

    public function testDot()
    {
        $this->markTestSkipped('TODO');
    }

    public function testUndot()
    {
        $this->markTestSkipped('TODO');
    }

    public function testImplode()
    {
        $this->markTestSkipped('TODO');
    }

    public function testHttpBuildQuery()
    {
        $this->markTestSkipped('TODO');
    }

    public function testForget()
    {
        $this->markTestSkipped('TODO');
    }

    public function testExcept()
    {
        $this->markTestSkipped('TODO');
    }

    public function testIsEmpty()
    {
        $arr = new Arr(['a' => 'a', 'b' => ['b' => 'b']]);
        $this->assertFalse($arr->isEmpty());
        $arr = new Arr();
        $this->assertTrue($arr->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $arr = new Arr(['a' => 'a', 'b' => ['b' => 'b']]);
        $this->assertTrue($arr->isNotEmpty());
        $arr = new Arr();
        $this->assertFalse($arr->isNotEmpty());
    }

    public function testGet()
    {
        $arr = new Arr(['a' => 'a', 'b' => ['b' => 'b']]);
        $this->assertEquals('a', $arr->get('a'));
        $this->assertNull($arr->get('c'));
        $this->assertEquals('b', $arr->get('b.b'));
        $this->assertNull($arr->get('b.c'));
    }

    public function testGroupBy()
    {
        $arr = new Arr([['name' => 'Peter'], ['name' => 'Peter'], ['name' => 'Maike']]);
        $this->expectException(\RuntimeException::class);
        $arr->groupBy(fn(array $v) => $v['name']);
    }

    public function testSet()
    {
        $arr = new Arr();
        $arr->set('a', 'a');
        $arr->set('b.b', 'b');
        $this->assertEquals(['a' => 'a', 'b' => ['b' => 'b']], $arr->toArray());
    }

    public function testHas()
    {
        $arr = new Arr(['a' => 'a', 'b' => ['a' => 'a', 'b' => 'b']]);
        $this->assertTrue($arr->has('a'));
        $this->assertTrue($arr->has('b'));
        $this->assertTrue($arr->has('b.a'));
        $this->assertTrue($arr->has('b.b'));
        $this->assertFalse($arr->has('c'));
        $this->assertFalse($arr->has('b.c'));
    }

    public function testUnion()
    {
        $arr1 = new Arr(['a' => 'a', 'b' => 'b']);
        $arr2 = new Arr(['b' => ['b'], 'c' => 'c']);
        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => 'c'], $arr1->union($arr2)->toArray());
    }

    public function testNthStep2()
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals([1, 3, 5, 7, 9], $arr->nth(2)->toArray());
    }

    public function testNthStep3()
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals([1, 4, 7, 10], $arr->nth(3)->toArray());
    }

    public function testNthStep3Offset3()
    {
        $arr = new Arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertEquals([3, 6, 9], $arr->nth(3, 2)->toArray());
    }

    public function testPluck()
    {
        $arr = new Arr([['id' => 1, 'name' => 'a'], ['id' => 2, 'name' => 'b']]);
        $this->assertEquals([1 => 'a', 2 => 'b'], $arr->pluck('name', 'id')->toArray());
    }
}
