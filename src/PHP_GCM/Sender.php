<?php

namespace PHP_GCM;

class Sender {

  const GCM_ENDPOINT = 'https://gcm-http.googleapis.com/gcm/send';
  const BACKOFF_INITIAL_DELAY = 1000;
  const MAX_BACKOFF_DELAY = 1024000;
  const SUCCESS = 'success';
  const FAILURE = 'failure';
  const REGISTRATION_ID = 'registration_id';
  const CANONICAL_IDS = 'canonical_ids';
  const MULTICAST_ID = 'multicast_id';
  const RESULTS = 'results';
  const ERROR = 'error';
  const MESSAGE_ID = 'message_id';

  private $key;
  private $retries;
  private $certificatePath;

  /**
   * Default constructor.
   *
   * @param string $key API key obtained through the Google API Console.
   */
  public function __construct($key) {
    $this->key = $key;
    $this->retries = 3;
    $this->certificatePath = null;
  }

  /**
   * Allows a non-default certificate chain to be used when communicating
   * with Google APIs.
   *
   * @param string $certificatePath full qualified path to a certificate store.
   * @return Sender Returns the instance of this Sender for method chaining.
   */
  public function certificatePath($certificatePath) {
    $this->certificatePath = $certificatePath;
    return $this;
  }

  /**
   * Set the number of retries to attempt in the case of service unavailability. Defaults to 3 retries.
   *
   * Note: Retries use exponential back-off in the case of service unavailability and
   * could block the calling thread for many seconds.
   *
   * @param int $retries number of retries in case of service unavailability errors.
   * @return Sender Returns the instance of this Sender for method chaining.
   */
  public function retries($retries) {
    $this->retries = $retries;
    return $this;
  }

  /**
   * Sends a GCM message to one or more devices, retrying up to the specified
   * number of retries in case of unavailability.
   *
   * Note: Retries use exponential back-off in the case of service unavailability and
   * could block the calling thread for many seconds.
   *
   * @param Message $message to be sent
   * @param string|array $registrationIds String registration id or an array of registration ids of the devices where the message will be sent.
   * @return MulticastResult combined result of all requests made.
   * @throws \InvalidArgumentException If registrationIds is empty
   * @throws InvalidRequestException If GCM did not return a 200 after the specified number of retries.
   */
  public function send(Message $message, $registrationIds) {
    if (empty($registrationIds)) {
      throw new \InvalidArgumentException('registrationId cannot be empty');
    }

    if (!is_array($registrationIds)) {
      $registrationIds = array($registrationIds);
    }

    $attempt = 0;
    $backoff = self::BACKOFF_INITIAL_DELAY;
    $results = array();
    $unsentRegistrationIds = array_values($registrationIds);
    $multicastIds = array();
    do {
      $attempt++;

      try {
        $multicastResult = $this->makeRequest($message, $unsentRegistrationIds);
        $multicastIds[] = $multicastResult->getMulticastId();
        $unsentRegistrationIds = $this->updateStatus($unsentRegistrationIds, $results, $multicastResult);
      } catch (InvalidRequestException $e) {
        if ($attempt >= $retries) {
          throw $e;
        }
      }

      $tryAgain = count($unsentRegistrationIds) > 0 && $attempt <= $retries;
      if ($tryAgain) {
        $sleepTime = $backoff / 2 + rand(0, $backoff);
        sleep($sleepTime / 1000);
        if (2 * $backoff < self::MAX_BACKOFF_DELAY) {
          $backoff *= 2;
        }
      }
    } while ($tryAgain);

    $success = $failure = $canonicalIds = 0;
    foreach ($results as $result) {
      if (!is_null($result->getMessageId())) {
        $success++;

        if (!is_null($result->getCanonicalRegistrationId())) {
          $canonicalIds++;
        }
      } else {
        $failure++;
      }
    }

    $result = new MulticastResult($success, $failure, $canonicalIds, $multicastIds[0], $multicastIds);
    foreach($registrationIds as $registrationId) {
      $result->addResult($results[$registrationId]);
    }

    return $result;
  }

  private function makeRequest(Message $message, array $registrationIds) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, self::GCM_ENDPOINT);

    if ($this->certificatePath != null) {
      curl_setopt($ch, CURLOPT_CAINFO, $this->certificatePath);
    }

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: key=' . $this->key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $message->build($registrationIds));
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status == 400) {
      throw new InvalidRequestException($status, 'Check that the JSON message is properly formatted and contains valid fields.');
    } else if ($status == 401) {
      throw new InvalidRequestException($status, 'The sender account used to send a message could not be authenticated.');
    } else if ($status == 500) {
      throw new InvalidRequestException($status, 'The server encountered an error while trying to process the request.');
    } else if ($status == 503) {
      throw new InvalidRequestException($status, 'The server could not process the request in time.');
    } else if ($status != 200) {
      throw new InvalidRequestException($status, $response);
    }

    $response = json_decode($response, true);
    $success = $response[self::SUCCESS];
    $failure = $response[self::FAILURE];
    $canonicalIds = $response[self::CANONICAL_IDS];
    $multicastId = $response[self::MULTICAST_ID];

    $multicastResult = new MulticastResult($success, $failure, $canonicalIds, $multicastId);

    if(isset($response[self::RESULTS])){
      $individualResults = $response[self::RESULTS];

      foreach($individualResults as $singleResult) {
        $messageId = isset($singleResult[self::MESSAGE_ID]) ? $singleResult[self::MESSAGE_ID] : null;
        $canonicalRegId = isset($singleResult[self::REGISTRATION_ID]) ? $singleResult[self::REGISTRATION_ID] : null;
        $error = isset($singleResult[self::ERROR]) ? $singleResult[self::ERROR] : null;

        $result = new Result();
        $result->setMessageId($messageId);
        $result->setCanonicalRegistrationId($canonicalRegId);
        $result->setErrorCode($error);

        $multicastResult->addResult($result);
      }
    }

    return $multicastResult;
  }

  /**
   * Updates the status of the messages sent to devices and the list of devices
   * that should be retried.
   *
   * @param array $unsentRegIds list of devices that are still pending an update.
   * @param array $allResults map of status that will be updated.
   * @param MulticastResult multicastResult result of the last multicast sent.
   *
   * @return array updated version of devices that should be retried.
   */
  private function updateStatus($unsentRegIds, &$allResults, MulticastResult $multicastResult) {
    $results = $multicastResult->getResults();
    if(count($results) != count($unsentRegIds)) {
      // should never happen, unless there is a flaw in the algorithm
      throw new \RuntimeException('Internal error: sizes do not match. currentResults: ' . $results .
        '; unsentRegIds: ' . $unsentRegIds);
    }

    $newUnsentRegIds = array();
    for ($i = 0; $i < count($unsentRegIds); $i++) {
      $regId = $unsentRegIds[$i];
      $result = $results[$i];
      $allResults[$regId] = $result;
      $error = $result->getErrorCode();

      if(!is_null($error) && $error == Constants::$ERROR_UNAVAILABLE)
        $newUnsentRegIds[] = $regId;
    }

    return $newUnsentRegIds;
  }
}
