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
  private $certificatePath;

  /**
   * Default constructor.
   *
   * @param string $key API key obtained through the Google API Console.
   */
  public function __construct($key) {
    $this->key = $key;
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
   * Sends a message to one device, retrying in case of unavailability.
   *
   * <p>
   * <strong>Note: </strong> this method uses exponential back-off to retry in
   * case of service unavailability and hence could block the calling thread
   * for many seconds.
   *
   * @param Message $message to be sent, including the device's registration id.
   * @param string $registrationId device where the message will be sent.
   * @param int $retries number of retries in case of service unavailability errors.
   *
   * @return MulticastResult result of the request (see its javadoc for more details)
   *
   * @throws \InvalidArgumentException if registrationId is {@literal null}.
   * @throws InvalidRequestException if GCM didn't return a 200 or 503 status.
   * @throws \Exception if message could not be sent.
   */
  public function send(Message $message, $registrationId, $retries) {
    $attempt = 0;
    $result = null;
    $backoff = self::BACKOFF_INITIAL_DELAY;

    do {
      $attempt++;

      $result = $this->sendNoRetry($message, $registrationId);
      $tryAgain = $result == null && $attempt <= $retries;
      if($tryAgain) {
        $sleepTime = $backoff / 2 + rand(0, $backoff);
        sleep($sleepTime / 1000);
        if(2 * $backoff < self::MAX_BACKOFF_DELAY)
          $backoff *= 2;
      }
    } while ($tryAgain);

    if(is_null($result))
      throw new \Exception('Could not send message after ' . $attempt . ' attempts');

    return $result;
  }

  /**
   * Sends a message without retrying in case of service unavailability. See
   * send() for more info.
   *
   * @param Message $message to be sent, including the device's registration id.
   * @param string $registrationId device where the message will be sent.
   *
   * @return MulticastResult|null result of the post, or {@literal null} if the GCM service
   *         was unavailable.
   *
   * @throws InvalidRequestException if GCM did not return a 200 or 503 status.
   * @throws \InvalidArgumentException if registrationId is {@literal null}.
   * @throws \Exception if message could not be sent.
   */
  public function sendNoRetry(Message $message, $registrationId) {
    if(empty($registrationId))
      throw new \InvalidArgumentException('registrationId can\'t be empty');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, self::GCM_ENDPOINT);

    if ($this->certificatePath != null) {
      curl_setopt($ch, CURLOPT_CAINFO, $this->certificatePath);
    }

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: key=' . $this->key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $message->build($registrationId));
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($status == 503)
      return null;
    if($status != 200)
      throw new InvalidRequestException($status, $response);
    if($response == '')
      throw new \Exception('Received empty response from GCM service.');

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
   * Sends a message to many devices, retrying in case of unavailability.
   *
   * <p>
   * <strong>Note: </strong> this method uses exponential back-off to retry in
   * case of service unavailability and hence could block the calling thread
   * for many seconds.
   *
   * @param Message message to be sent.
   * @param array $registrationIds registration id of the devices that will receive
   *        the message.
   * @param int $retries number of retries in case of service unavailability errors.
   *
   * @return MulticastResult combined result of all requests made.
   *
   * @throws \InvalidArgumentException if registrationIds is {@literal null} or
   *         empty.
   * @throws InvalidRequestException if GCM didn't returned a 200 or 503 status.
   * @throws \Exception if message could not be sent.
   */
  public function sendMulti(Message $message, array $registrationIds, $retries) {
    $attempt = 0;
    $multicastResult = null;
    $backoff = self::BACKOFF_INITIAL_DELAY;

    // results by registration id, it will be updated after each attempt
    // to send the messages
    $results = array();
    $unsentRegIds = array_values($registrationIds);

    $multicastIds = array();

    do {
      $attempt++;

      $multicastResult = $this->sendNoRetryMulti($message, $unsentRegIds);
      $multicastId = $multicastResult->getMulticastId();
      $multicastIds[] = $multicastId;
      $unsentRegIds = $this->updateStatus($unsentRegIds, $results, $multicastResult);

      $tryAgain = count($unsentRegIds) > 0 && $attempt <= $retries;
      if($tryAgain) {
        $sleepTime = $backoff / 2 + rand(0, $backoff);
        sleep($sleepTime / 1000);
        if(2 * $backoff < self::MAX_BACKOFF_DELAY)
          $backoff *= 2;
      }
    } while ($tryAgain);

    $success = $failure = $canonicalIds = 0;
    foreach($results as $result) {
      if(!is_null($result->getMessageId())) {
        $success++;

        if(!is_null($result->getCanonicalRegistrationId()))
          $canonicalIds++;
      } else {
        $failure++;
      }
    }

    $multicastId = $multicastIds[0];
    $builder = new MulticastResult($success, $failure, $canonicalIds, $multicastId, $multicastIds);

    // add results, in the same order as the input
    foreach($registrationIds as $registrationId) {
      $builder->addResult($results[$registrationId]);
    }

    return $builder;
  }

  /**
   * Sends a message without retrying in case of service unavailability. See
   * sendMulti() for more info.
   *
   * @return bool {@literal true} if the message was sent successfully,
   *         {@literal false} if it failed but could be retried.
   *
   * @throws \InvalidArgumentException if registrationIds is {@literal null} or
   *         empty.
   * @throws InvalidRequestException if GCM didn't returned a 200 status.
   * @throws \Exception if message could not be sent or received.
   */
  public function sendNoRetryMulti(Message $message, array $registrationIds) {
    if(is_null($registrationIds) || count($registrationIds) == 0)
      throw new \InvalidArgumentException('registrationIds cannot be null or empty');

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

    if($status != 200)
      throw new InvalidRequestException($status, $response);

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
        $canonicalRegId = isset($singleResult[Constants::$TOKEN_CANONICAL_REG_ID]) ? $singleResult[Constants::$TOKEN_CANONICAL_REG_ID] : null;
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
