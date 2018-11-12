<?php

namespace PHP_GCM;

/**
 * Aggregate results of GCM multicast message requests.
 */
class AggregateResult {

  private $success;
  private $failure;
  private $canonicalIds;
  private $results;
  private $multicastResults;
  private $retryMulticastIds;

  /**
   * @param MulticastResult[] $multicastResults
   */
  public function __construct(array $multicastResults = array()) {
    $this->multicastResults = $multicastResults;

    $this->results = [];
    $this->retryMulticastIds = [];
    $this->success = $this->failure = $this->canonicalIds = 0;
    foreach($multicastResults as $multicastResult) {
      $this->success += $multicastResult->getSuccess();
      $this->failure += $multicastResult->getFailure();
      $this->canonicalIds += $multicastResult->getCanonicalIds();
      array_splice($this->results, count($this->results), 0, $multicastResult->getResults());
      array_splice($this->retryMulticastIds, count($this->retryMulticastIds), 0, $multicastResult->getRetryMulticastIds());
    }
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
   * @return Result[]
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * Gets additional ids if more than one multicast message was sent.
   *
   * @return MulticastResult[]
   */
  public function getMulticastResults() {
    return $this->multicastResults;
  }

  /**
   * Gets the multicast id of the first try.
   *
   * @return string
   */
  public function getMulticastId() {
    return !!count($this->multicastResults) ? $this->multicastResults[0]->getMulticastId() : 0;
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
