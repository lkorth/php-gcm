<?php

namespace PHP_GCM;

class AggregateResultTest extends \PHPUnit_Framework_TestCase {

  public function testConstructsCorrectly() {
    $results = array();
    $results[] = new MulticastResult(1, 2, 1, 'multicast-id1', array());
    $results[] = new MulticastResult(1, 2, 1, 'multicast-id2', array());
    $results[] = new MulticastResult(1, 2, 1, 'multicast-id3', array());

    $aggregateResult = new AggregateResult($results);

    $this->assertEquals('multicast-id1', $aggregateResult->getMulticastId());
    $this->assertEquals(3, $aggregateResult->getSuccess());
    $this->assertEquals(9, $aggregateResult->getTotal());
    $this->assertEquals(6, $aggregateResult->getFailure());
    $this->assertEquals(3, $aggregateResult->getCanonicalIds());
    $this->assertTrue(is_array($aggregateResult->getResults()) && empty($aggregateResult->getResults()));
    $this->assertTrue(is_array($aggregateResult->getRetryMulticastIds()) && empty($aggregateResult->getRetryMulticastIds()));
  }

  public function testGetMulticastId() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());

    $this->assertEquals('multicast-id', $result->getMulticastId());
  }

  public function testGetSuccess() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());

    $this->assertEquals(1, $result->getSuccess());
  }

  public function testGetTotal() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());

    $this->assertEquals(3, $result->getTotal());
  }

  public function testGetFailure() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());

    $this->assertEquals(2, $result->getFailure());
  }

  public function testGetCanonicalIds() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());

    $this->assertEquals(1, $result->getCanonicalIds());
  }

  public function testGetResults() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());
    $result->addResult(new Result());

    $this->assertTrue(is_array($result->getResults()));
    $this->assertEquals(array(new Result()), $result->getResults());
  }

  public function testGetRetryMulticastIds() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array(1));

    $this->assertEquals(array(1), $result->getRetryMulticastIds());
  }
}
