<?php

namespace PHP_GCM;

/**
 * Result of a GCM multicast message request.
 */
class MulticastResult {

  private $success;
  private $failure;
  private $canonicalIds;
  private $multicastId;
  private $results;
  private $retryMulticastIds;

  /**
   * @param int $success
   * @param int $failure
   * @param int $canonicalIds
   * @param string $multicastId
   * @param array $retryMulticastIds
   */
  public function __construct($success, $failure, $canonicalIds, $multicastId, array $retryMulticastIds = array()) {
    $this->success = $success;
    $this->failure = $failure;
    $this->canonicalIds = $canonicalIds;
    $this->multicastId = $multicastId;
    $this->retryMulticastIds = $retryMulticastIds;

    $this->results = array();
  }

  /**
   * Add a result to the result property
   *
   * @param Result $result
   */
  public function addResult(Result $result) {
    $this->results[] = $result;
  }

  /**
   * Gets the multicast id.
   *
   * @return string
   */
  public function getMulticastId() {
    return $this->multicastId;
  }

  /**
   * Gets the number of successful messages.
   *
   * @return int
   */
  public function getSuccess() {
    return $this->success;
  }

  /**
   * Gets the total number of messages sent, regardless of the status.
   *
   * @return int
   */
  public function getTotal() {
    return $this->success + $this->failure;
  }

  /**
   * Gets the number of failed messages.
   *
   * @return int
   */
  public function getFailure() {
    return $this->failure;
  }

  /**
   * Gets the number of successful messages that also returned a canonical
   * registration id.
   *
   * @return int
   */
  public function getCanonicalIds() {
    return $this->canonicalIds;
  }

  /**
   * Gets the results of each individual message
   *
   * @return array
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * Gets additional ids if more than one multicast message was sent.
   *
   * @return array
   */
  public function getRetryMulticastIds() {
    return $this->retryMulticastIds;
  }
}
