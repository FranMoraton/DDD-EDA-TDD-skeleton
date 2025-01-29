<?php

declare(strict_types=1);

namespace App\Tests\System\Domain\ValueObject;

use App\System\Domain\ValueObject\CollectionValueObject;
use App\System\Domain\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class CollectionValueObjectTest extends TestCase
{
    public function testGivenEmptyCollectionWhenAskToGetInfoThenReturnExpectedInfo()
    {
        $collection = CollectionValueObject::from([]);

        $this->assertEquals([], $collection->jsonSerialize());
        $this->assertTrue($collection->isEmpty());
    }

    public function testGivenCollectionWhenAskToGetInfoThenReturnExpectedInfo()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);

        $this->assertEquals([1, 2, 3, 4], $collection->jsonSerialize());
        $this->assertFalse($collection->isEmpty());
    }

    public function testGivenIntegerishCollectionWhenAskToSortThenReturnNewSortedCollection()
    {
        $collection = CollectionValueObject::from([5, 1, 4, 2, 3]);
        $sorted = $collection->sort(
            function ($a, $b) {
                return $a - $b;
            }
        );

        $this->assertEquals([5, 1, 4, 2, 3], $collection->jsonSerialize());
        $this->assertEquals([1, 2, 3, 4, 5], $sorted->jsonSerialize());
    }

    public function testGivenCollectionWhenAskToSortThenReturnNewSortedCollection()
    {
        $collection = CollectionValueObject::from(['5a', '1', '4', '2', '3']);
        $sorted = $collection->sort(
            function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }
                return ($a < $b) ? -1 : 1;
            }
        );

        $this->assertEquals(['5a', '1', '4', '2', '3'], $collection->jsonSerialize());
        $this->assertEquals(['1', '2', '3', '4', '5a'], $sorted->jsonSerialize());
    }

    public function testGivenCollectionWhenAskToFilterThenReturnExpectedInfo()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);
        $newCollection = $collection->filter(
            function ($current) {
                return 2 !== $current;
            }
        );

        $this->assertEquals([1, 3, 4], $newCollection->jsonSerialize());
    }

    public function testGivenTwoIdenticalCollectionsWhenAskToCheckEqualityThenReturnTrue()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);
        $other = CollectionValueObject::from([1, 2, 3, 4]);

        $this->assertTrue($collection->equalTo($other));
    }

    public function testGivenTwoDifferentCollectionsWhenAskToCheckEqualityThenReturnFalse()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);
        $other = CollectionValueObject::from([5, 6, 7, 8]);

        $this->assertFalse($collection->equalTo($other));
    }

    public function testGivenCollectionWhenAskToAddItemThenReturnNewCollection()
    {
        $collection = CollectionValueObjectTested::from([1, 2, 3, 4]);
        $newCollection = $collection->add(5);

        $this->assertEquals([1, 2, 3, 4], $collection->jsonSerialize());
        $this->assertEquals([1, 2, 3, 4, 5], $newCollection->jsonSerialize());
    }

    public function testGivenCollectionWhenAskToRemoveItemThenReturnNewCollection()
    {
        $collection = CollectionValueObjectTested::from([1, 2, 3, 4]);
        $newCollection = $collection->remove(3);

        $this->assertEquals([1, 2, 3, 4], $collection->jsonSerialize());
        $this->assertEquals([1, 2, 4], $newCollection->jsonSerialize());
    }

    public function testGivenAnEmptyCollectionWhenAskToObtainFirstItemThenReturnNull()
    {
        $collection = CollectionValueObjectTested::from([]);
        $item = $collection->first();

        $this->assertEquals(null, $item);
    }

    public function testGivenAHashMapCollectionWhenAskToObtainFirstItemThenReturnFirstItem()
    {
        $firstItem = 1;
        $collection = CollectionValueObjectTested::from(['a' => $firstItem, 'b' => 2, 'c' => 3, 'd' => 4]);
        $item = $collection->first();

        $this->assertEquals(['a' => $firstItem, 'b' => 2, 'c' => 3, 'd' => 4], $collection->jsonSerialize());
        $this->assertEquals($firstItem, $item);
    }

    public function testGivenACollectionWhenAskToObtainFirstItemThenReturnFirstItem()
    {
        $firstItem = 1;
        $collection = CollectionValueObjectTested::from([$firstItem, 2, 3, 4]);
        $item = $collection->first();

        $this->assertEquals([$firstItem, 2, 3, 4], $collection->jsonSerialize());
        $this->assertEquals($firstItem, $item);
        $this->assertNotEquals(2, $item);
        $this->assertNotEquals(3, $item);
        $this->assertNotEquals(4, $item);
    }

    public function testGivenTwoDifferentCollectionsWhenAskToHaveSameValuesThenReturnFalse()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);
        $other = CollectionValueObject::from([5, 6, 7, 8]);

        $this->assertFalse($collection->equalTo($other));
    }

    public function testGivenTwoDifferentCollectionsWhenOneContainsTheOtherAndAskToHaveSameValuesThenReturnFalse()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);
        $other = CollectionValueObject::from([1, 2, 3, 4, 5]);

        $this->assertFalse($collection->equalTo($other));
    }

    public function testGivenTwoUnorderedEqualsCollectionsWhenAskToHaveSameValuesThenReturnTrue()
    {
        $collection = CollectionValueObject::from([1, 1, 3, 4]);
        $other = CollectionValueObject::from([4, 1, 3, 1]);

        $this->assertTrue($collection->equalTo($other));
    }

    public function testGivenTwoOrderedEqualsCollectionsWhenAskToHaveSameValuesThenReturnTrue()
    {
        $collection = CollectionValueObject::from([1, 2, 3, 4]);
        $other = CollectionValueObject::from([1, 2, 3, 4]);

        $this->assertTrue($collection->equalTo($other));
    }

    public function testGivenTwoOrderedEqualsHashedCollectionsWhenAskToHaveSameValuesThenReturnTrue()
    {
        $collection = CollectionValueObject::from(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
        $other = CollectionValueObject::from(['b' => 1, 'a' => 2, 'd' => 3, 'c' => 4]);

        $this->assertTrue($collection->equalTo($other));
    }

    public function testGivenTwoUnorderedEqualsHashedCollectionsWhenAskToHaveSameValuesThenReturnTrue()
    {
        $collection = CollectionValueObject::from(['a' => 2, 'b' => 1, 'c' => 3, 'd' => 4]);
        $other = CollectionValueObject::from(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

        $this->assertTrue($collection->equalTo($other));
    }

    public function testGivenTwoUnorderedDifferentHashedCollectionsWhenAskToHaveSameValuesThenReturnFalse()
    {
        $collection = CollectionValueObject::from(['a' => 1, 'b' => 3, 'c' => 3, 'd' => 4]);
        $other = CollectionValueObject::from(['b' => 1, 'a' => 2, 'd' => 3, 'c' => 4]);

        $this->assertFalse($collection->equalTo($other));
    }

    public function testGivenTwoUnorderedEqualsObjectCollectionsWhenAskToHaveSameValuesThenReturnFalse()
    {
        $uuid1 = new \stdClass();
        $uuid2 = new \stdClass();
        $uuid3 = new \stdClass();
        $uuid4 = new \stdClass();
        $uuid1->id = Uuid::v4();
        $uuid2->id = Uuid::v4();
        $uuid3->id = Uuid::v4();
        $uuid4->id = Uuid::v4();


        $collection = CollectionValueObject::from([$uuid1, $uuid2, $uuid3, $uuid4]);
        $other = CollectionValueObject::from([$uuid1, $uuid3, $uuid4, $uuid2]);

        $this->assertTrue($collection->equalTo($other));
    }

    public function testGivenTwoUnorderedDifferentObjectCollectionsWhenAskToHaveSameValuesThenReturnFalse()
    {
        $uuid1 = new \stdClass();
        $uuid2 = new \stdClass();
        $uuid3 = new \stdClass();
        $uuid4 = new \stdClass();
        $uuid5 = new \stdClass();
        $uuid1->id = Uuid::v4();
        $uuid2->id = Uuid::v4();
        $uuid3->id = Uuid::v4();
        $uuid4->id = Uuid::v4();
        $uuid5->id = Uuid::v4();

        $collection = CollectionValueObject::from([$uuid1, $uuid5, $uuid3, $uuid4]);
        $other = CollectionValueObject::from([$uuid1, $uuid3, $uuid4, $uuid2]);

        $this->assertFalse($collection->equalTo($other));
    }
}
