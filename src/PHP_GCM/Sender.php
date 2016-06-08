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

  const DEVICE_GROUP_PROJET_ID_HEADER = 'project_id';
  const DEVICE_GROUP_ENDPOINT = 'https://android.googleapis.com/gcm/notification';
  const DEVICE_GROUP_OPERATION = 'operation';
  const DEVICE_GROUP_CREATE = 'create';
  const DEVICE_GROUP_ADD = 'add';
  const DEVICE_GROUP_REMOVE = 'remove';
  const DEVICE_GROUP_NOTIFICATION_KEY_NAME = 'notification_key_name';
  const DEVICE_GROUP_REGISTRATION_IDS = 'registration_ids';
  const DEVICE_GROUP_NOTIFICATION_KEY = 'notification_key';

  private $key;
  private $endpoint;
  private $retries;
  private $certificatePath;

  /**
   * Default constructor.
   *
   * @param string $key API key obtained through the Google API Console.
   */
  public function __construct($key, $endpoint=self::GCM_ENDPOINT) {
    $this->key = $key;
    $this->endpoint = $endpoint;
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
   * @return AggregateResult combined result of all requests made.
   * @throws \InvalidArgumentException If registrationIds is empty
   * @throws InvalidRequestException If GCM did not return a 200 after the specified number of retries.
   */
  public function send(Message $message, $registrationIds) {
    if (empty($registrationIds)) {
      throw new \InvalidArgumentException('registrationIds cannot be empty');
    }

    if (!is_array($registrationIds)) {
      $registrationIds = array($registrationIds);
    }

    $chunks = array_chunk($registrationIds, 1000);

    $multicastResults = array();
    foreach($chunks as $chunk) {
      $attempt = 0;
      $backoff = self::BACKOFF_INITIAL_DELAY;
      $results = array();
      $unsentRegistrationIds = array_values($chunk);
      $multicastIds = array();
      do {
        $attempt++;

        try {
          $multicastResult = $this->makeRequest($message, $unsentRegistrationIds);
          $multicastIds[] = $multicastResult->getMulticastId();
          $unsentRegistrationIds = $this->updateStatus($unsentRegistrationIds, $results, $multicastResult);
        } catch (InvalidRequestException $e) {
          if ($attempt >= $this->retries) {
            throw $e;
          }
        }

        $tryAgain = count($unsentRegistrationIds) > 0 && $attempt <= $this->retries;
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
      foreach ($registrationIds as $registrationId) {
        $result->addResult($registrationId, $results[$registrationId]);
      }

      $multicastResults[] = $result;
    }

    return new AggregateResult($multicastResults);
  }

  /**
   * Creates a device group under the specified senderId with the specificed notificationKeyName and adds all
   * devices in registrationIds to the group.
   *
   * @param $senderId A unique numerical value created when you configure your API project
   *        (given as "Project Number" in the Google Developers Console). The sender ID is used in the registration
   *        process to identify an app server that is permitted to send messages to the client app.
   * @param $notificationKeyName a name or identifier (e.g., it can be a username) that is unique to a given group.
   *        The notificationKeyName and notificationKey are unique to a group of registration tokens. It is important
   *        that notificationKeyName is unique per client app if you have multiple client apps for the same sender ID.
   *        This ensures that messages only go to the intended target app.
   * @param string|array $registrationIds String registration id or an array of registration ids of the devices to add to the group.
   * @return string notificationKey used to send a notification to all the devices in the group.
   * @throws \InvalidArgumentException If senderId, notificationKeyName or registrationIds are empty.
   * @throws InvalidRequestException If GCM did not return a 200.
   */
  public function createDeviceGroup($senderId, $notificationKeyName, array $registrationIds) {
    return $this->performDeviceGroupOperation($senderId, $notificationKeyName, null, $registrationIds, self::DEVICE_GROUP_CREATE);
  }

  /**
   * Adds devices to an existing device group.
   *
   * @param $senderId A unique numerical value created when you configure your API project
   *        (given as "Project Number" in the Google Developers Console). The sender ID is used in the registration
   *        process to identify an app server that is permitted to send messages to the client app.
   * @param $notificationKeyName a name or identifier (e.g., it can be a username) that is unique to a given group.
   *        The notificationKeyName and notificationKey are unique to a group of registration tokens. It is important
   *        that notificationKeyName is unique per client app if you have multiple client apps for the same sender ID.
   *        This ensures that messages only go to the intended target app.
   * @param $notificationKey the notificationKey returned from Sender#createDeviceGroup.
   * @param string|array $registrationIds String registration id or an array of registration ids of the devices to add to the group.
   * @return string notificationKey used to send a notification to all the devices in the group.
   * @throws \InvalidArgumentException If senderId, notificationKeyName, notificationKey or registrationIds are empty.
   * @throws InvalidRequestException If GCM did not return a 200.
   */
  public function addDeviceToGroup($senderId, $notificationKeyName, $notificationKey, array $registrationIds) {
    if (empty($notificationKey)) {
      throw new \InvalidArgumentException('notificationKey cannot be empty');
    }

    return $this->performDeviceGroupOperation($senderId, $notificationKeyName, $notificationKey, $registrationIds, self::DEVICE_GROUP_ADD);
  }

  /**
   * Removes devices from an existing device group.
   *
   * @param $senderId A unique numerical value created when you configure your API project
   *        (given as "Project Number" in the Google Developers Console). The sender ID is used in the registration
   *        process to identify an app server that is permitted to send messages to the client app.
   * @param $notificationKeyName a name or identifier (e.g., it can be a username) that is unique to a given group.
   *        The notificationKeyName and notificationKey are unique to a group of registration tokens. It is important
   *        that notificationKeyName is unique per client app if you have multiple client apps for the same sender ID.
   *        This ensures that messages only go to the intended target app.
   * @param $notificationKey the notificationKey returned from Sender#createDeviceGroup.
   * @param string|array $registrationIds String registration id or an array of registration ids of the devices to add to the group.
   * @return string notificationKey used to send a notification to all the devices in the group.
   * @throws \InvalidArgumentException If senderId, notificationKeyName, notificationKey or registrationIds are empty.
   * @throws InvalidRequestException If GCM did not return a 200.
   */
  public function removeDeviceFromGroup($senderId, $notificationKeyName, $notificationKey, array $registrationIds) {
    if (empty($notificationKey)) {
      throw new \InvalidArgumentException('notificationKey cannot be empty');
    }

    return $this->performDeviceGroupOperation($senderId, $notificationKeyName, $notificationKey, $registrationIds, self::DEVICE_GROUP_REMOVE);
  }

  private function performDeviceGroupOperation($senderId, $notificationKeyName, $notificationKey, array $registrationIds, $operation) {
    if (empty($senderId)) {
      throw new \InvalidArgumentException('senderId cannot be empty');
    }

    if (empty($notificationKeyName)) {
      throw new \InvalidArgumentException('notificationKeyName cannot be empty');
    }

    if (empty($registrationIds)) {
      throw new \InvalidArgumentException('registrationIds cannot be empty');
    }

    if (!is_array($registrationIds)) {
      $registrationIds = array($registrationIds);
    }

    $request = array();
    $request[self::DEVICE_GROUP_OPERATION] = $operation;
    $request[self::DEVICE_GROUP_NOTIFICATION_KEY_NAME] = $notificationKeyName;
    $request[self::DEVICE_GROUP_REGISTRATION_IDS] = $registrationIds;

    if ($notificationKey != null) {
      $request[self::DEVICE_GROUP_NOTIFICATION_KEY] = $notificationKey;
    }

    $ch = $this->getCurlRequest();
    curl_setopt($ch, CURLOPT_URL, self::DEVICE_GROUP_ENDPOINT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: key=' . $this->key, self::DEVICE_GROUP_PROJET_ID_HEADER . ': ' . $senderId));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status != 200) {
      $response = json_decode($response, true);
      throw new InvalidRequestException($status, $response[self::ERROR]);
    }

    $response = json_decode($response, true);
    return $response[self::DEVICE_GROUP_NOTIFICATION_KEY];
  }

  private function makeRequest(Message $message, array $registrationIds) {
    $ch = $this->getCurlRequest();
    curl_setopt($ch, CURLOPT_URL, $this->endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: key=' . $this->key));
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

      $i = 0;
      foreach($individualResults as $singleResult) {
        $messageId = isset($singleResult[self::MESSAGE_ID]) ? $singleResult[self::MESSAGE_ID] : null;
        $canonicalRegId = isset($singleResult[self::REGISTRATION_ID]) ? $singleResult[self::REGISTRATION_ID] : null;
        $error = isset($singleResult[self::ERROR]) ? $singleResult[self::ERROR] : null;

        $result = new Result();
        $result->setMessageId($messageId);
        $result->setCanonicalRegistrationId($canonicalRegId);
        $result->setErrorCode($error);

        $multicastResult->addResult($devices[$i], $result);
        ++$i;
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

  private function getCurlRequest() {
    $ch = curl_init();

    if ($this->certificatePath != null) {
      curl_setopt($ch, CURLOPT_CAINFO, $this->certificatePath);
    }

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    return $ch;
  }
}
