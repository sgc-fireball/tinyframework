<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Arr;
use TinyFramework\Helpers\Str;

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
        $this->markTestSkipped('TODO');
    }

    public function testCombineWithKeys(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCountBy(): void
    {
        $this->markTestSkipped('SKIP');
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
        $this->markTestSkipped('TODO');
    }

    public function testDiffUAssoc(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testDiffKey(): void
    {
        $arr = new Arr(['blau' => 1, 'rot' => 2, 'grün' => 3, 'violett' => 4]);
        $result = $arr->diffKey(['grün' => 5, 'gelb' => 7, 'türkis' => 8]);
        $this->assertEquals(['blau' => 1, 'rot' => 2, 'violett' => 4], $result->toArray());
    }

    public function testDiffUKey(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testDiff(): void
    {
        $this->markTestSkipped('TODO');
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
        $this->assertEquals([0 => 1, 2 => 3], $arr->filter(fn(int $i) => $i % 2)->toArray());
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

    public function testIntersectAssoc(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersectUAssoc(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersectByKeys(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersectUKey(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testIntersect(): void
    {
        $this->markTestSkipped('TODO');
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
        $arr2 = $arr1->map(fn($value) => $value * 2);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr1->toArray());
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $arr2->toArray());
    }

    public function testTransform(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->transform(fn($value) => $value * 2);
        $this->assertEquals(['a' => 2, 'b' => 4, 'c' => 6], $arr->toArray());
    }

    public function testMapWithKeys(): void
    {
        $arr1 = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr2 = $arr1->mapWithKeys(fn($value, $key) => [$key . 'a' => $value * 2]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $arr1->toArray());
        $this->assertEquals(['aa' => 2, 'ba' => 4, 'ca' => 6], $arr2->toArray());
    }

    public function testTransformWithKeys(): void
    {
        $arr = new Arr(['a' => 1, 'b' => 2, 'c' => 3]);
        $arr->transformWithKeys(fn($value, $key) => [$key . 'a' => $value * 2]);
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
        $result = $arr->reduce(fn(int $carry, int $item): int => $carry + $item, 0);
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
        $this->markTestSkipped('TODO');
    }

    public function testUDiffUAssoc(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUDiff(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUIntersectAssoc(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUIntersectUAssoc(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUIntersect(): void
    {
        $this->markTestSkipped('TODO');
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
        $this->markTestSkipped('TODO');
    }

    public function testWalk(): void
    {
        $this->markTestSkipped('TODO');
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
        $this->markTestSkipped('TODO');
    }

    public function testNatcasesort(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testNatsort(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testShuffle(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUksort(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUsort(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testEach(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testOnly(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testPrepend(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testAppend(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testDivide(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testDot(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUndot(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testImplode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testHttpBuildQuery(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testForget(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testExcept(): void
    {
        $this->markTestSkipped('TODO');
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
        $arr = new Arr([['name' => 'Peter'], ['name' => 'Peter'], ['name' => 'Maike']]);
        $this->expectException(\RuntimeException::class);
        $arr->groupBy(fn(array $v) => $v['name']);
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
