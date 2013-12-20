<?php
/*
 * Copyright 2013 Luke Korth
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PHP_GCM;

class Sender {

    /**
     * Initial delay before first retry, without jitter.
     */
    private static $BACKOFF_INITIAL_DELAY = 1000;

    /**
     * Maximum delay before a retry.
     */
    private static $MAX_BACKOFF_DELAY = 1024000;

    private $key;

    /**
     * Default constructor.
     *
     * @param string $key API key obtained through the Google API Console.
     */
    public function __construct($key) {
        $this->key = $key;
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
     * @return Result result of the request (see its javadoc for more details)
     *
     * @throws \InvalidArgumentException if registrationId is {@literal null}.
     * @throws InvalidRequestException if GCM didn't return a 200 or 503 status.
     * @throws \Exception if message could not be sent.
     */
    public function send(Message $message, $registrationId, $retries) {
        $attempt = 0;
        $result = null;
        $backoff = Sender::$BACKOFF_INITIAL_DELAY;

        do {
            $attempt++;

            $result = $this->sendNoRetry($message, $registrationId);
            $tryAgain = $result == null && $attempt <= $retries;
            if($tryAgain) {
                $sleepTime = $backoff / 2 + rand(0, $backoff);
                sleep($sleepTime / 1000);
                if(2 * $backoff < Sender::$MAX_BACKOFF_DELAY)
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
     * @return result of the post, or {@literal null} if the GCM service was
     *         unavailable.
     *
     * @throws InvalidRequestException if GCM didn't returned a 200 or 503 status.
     * @throws \InvalidArgumentException if registrationId is {@literal null}.
     */
    public function sendNoRetry(Message $message, $registrationId) {
        if(empty($registrationId))
            throw new \InvalidArgumentException('registrationId can\'t be empty');

        $body = Constants::$PARAM_REGISTRATION_ID . '=' . $registrationId;

        $delayWhileIdle = $message->getDelayWhileIdle();
        if(!is_null($delayWhileIdle))
            $body .= '&' . Constants::$PARAM_DELAY_WHILE_IDLE . '=' . ($delayWhileIdle ? '1' : '0');

        $collapseKey = $message->getCollapseKey();
        if($collapseKey != '')
            $body .= '&' . Constants::$PARAM_COLLAPSE_KEY . '=' . $collapseKey;

        $timeToLive = $message->getTimeToLive();
        if($timeToLive != -1)
            $body .= '&' . Constants::$PARAM_TIME_TO_LIVE . '=' . $timeToLive;

        foreach($message->getData() as $key => $value) {
            $body .= '&' . Constants::$PARAM_PAYLOAD_PREFIX . $key . '=' . urlencode($value);
        }

        $headers = array('Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                            'Authorization: key=' . $this->key);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::$GCM_SEND_ENDPOINT);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($status == 503)
            return null;
        if($status != 200)
            throw new InvalidRequestException($status);
        if($response == '')
            throw new \Exception('Received empty response from GCM service.');

        $lines = explode("\n", $response);
        $responseParts = explode('=', $lines[0]);
        $token = $responseParts[0];
        $value = $responseParts[1];
        if($token == Constants::$TOKEN_MESSAGE_ID) {
            $result = new Result();
            $result->setMessageId($value);

            if(isset($lines[1]) && $lines[1] != '') {
                $responseParts = explode('=', $lines[1]);
                $token = $responseParts[0];
                $value = $responseParts[1];

                if($token == Constants::$TOKEN_CANONICAL_REG_ID)
                    $result->setCanonicalRegistrationId($value);
            }

            return $result;
        } else if($token == Constants::$TOKEN_ERROR) {
            $result = new Result();
            $result->setErrorCode($value);
            return $result;
        } else {
            throw new \Exception('Received invalid response from GCM: ' . $lines[0]);
        }
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
        $backoff = Sender::$BACKOFF_INITIAL_DELAY;

        // results by registration id, it will be updated after each attempt
        // to send the messages
        $results = array();
        $unsentRegIds = $registrationIds;

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
                if(2 * $backoff < Sender::$MAX_BACKOFF_DELAY)
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
     * @return {@literal true} if the message was sent successfully,
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

        $request = array();

        if($message->getTimeToLive() != -1)
            $request[Constants::$PARAM_TIME_TO_LIVE] = $message->getTimeToLive();

        if($message->getCollapseKey() != '')
            $request[Constants::$PARAM_COLLAPSE_KEY] = $message->getCollapseKey();

        if($message->getDelayWhileIdle() != '')
            $request[Constants::$PARAM_DELAY_WHILE_IDLE] = $message->getDelayWhileIdle();

        $request[Constants::$JSON_REGISTRATION_IDS] = $registrationIds;

        if(!is_null($message->getData()) && count($message->getData()) > 0)
            $request[Constants::$JSON_PAYLOAD] = $message->getData();

        $request = json_encode($request);

        $headers = array('Content-Type: application/json',
            'Authorization: key=' . $this->key);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::$GCM_SEND_ENDPOINT);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($status != 200)
            throw new InvalidRequestException($status, $response);

        $response = json_decode($response, true);
        $success = $response[Constants::$JSON_SUCCESS];
        $failure = $response[Constants::$JSON_FAILURE];
        $canonicalIds = $response[Constants::$JSON_CANONICAL_IDS];
        $multicastId = $response[Constants::$JSON_MULTICAST_ID];

        $multicastResult = new MulticastResult($success, $failure, $canonicalIds, $multicastId);

        if(isset($response[Constants::$JSON_RESULTS])){
            $individualResults = $response[Constants::$JSON_RESULTS];

            foreach($individualResults as $singleResult) {
                $messageId = isset($singleResult[Constants::$JSON_MESSAGE_ID]) ? $singleResult[Constants::$JSON_MESSAGE_ID] : null;
                $canonicalRegId = isset($singleResult[Constants::$TOKEN_CANONICAL_REG_ID]) ? $singleResult[Constants::$TOKEN_CANONICAL_REG_ID] : null;
                $error = isset($singleResult[Constants::$JSON_ERROR]) ? $singleResult[Constants::$JSON_ERROR] : null;

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
     * @param array unsentRegIds list of devices that are still pending an update.
     * @param array allResults map of status that will be updated.
     * @param MulticastResult multicastResult result of the last multicast sent.
     *
     * @return array updated version of devices that should be retried.
     */
    private function updateStatus($unsentRegIds, &$allResults, MulticastResult $multicastResult) {
        $results = $multicastResult->getResults();
        if(count($results) != count($unsentRegIds)) {
            // should never happen, unless there is a flaw in the algorithm
            throw new \RuntimeException('Internal error: sizes do not match. currentResults: ' . $results .
                '; unsentRegIds: ' + $unsentRegIds);
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