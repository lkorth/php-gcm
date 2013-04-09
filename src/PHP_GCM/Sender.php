<?php

namespace PHP_GCM;

class Sender {

    private static $UTF8 = 'UTF-8';

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
     * @throws InvalidRequestException if GCM didn't returned a 200 or 503 status.
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
        if(is_null($registrationId))
            throw new \InvalidArgumentException('registrationId was null');

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
}