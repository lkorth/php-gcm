<?php

namespace PHP_GCM;

class MulticastResultTest extends \PHPUnit_Framework_TestCase {

  public function testConstructsCorrectly() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());

    $this->assertEquals('multicast-id', $result->getMulticastId());
    $this->assertEquals(1, $result->getSuccess());
    $this->assertEquals(3, $result->getTotal());
    $this->assertEquals(2, $result->getFailure());
    $this->assertEquals(1, $result->getCanonicalIds());
    $this->assertTrue(is_array($result->getResults()) && empty($result->getResults()));
    $this->assertTrue(is_array($result->getRetryMulticastIds()) && empty($result->getRetryMulticastIds()));
  }

  public function testAddResult() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array());
    $this->assertTrue(is_array($result->getResults()) && empty($result->getResults()));

    $result->addResult('', new Result());

    $this->assertTrue(is_array($result->getResults()) && count($result->getResults()) == 1);
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
    $result->addResult('123', new Result());

    $this->assertTrue(is_array($result->getResults()));
    $this->assertEquals(array('123' => new Result()), $result->getResults());
  }

  public function testGetRetryMulticastIds() {
    $result = new MulticastResult(1, 2, 1, 'multicast-id', array(1));

    $this->assertEquals(array(1), $result->getRetryMulticastIds());
  }
}
