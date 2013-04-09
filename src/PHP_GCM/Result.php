<?php

namespace PHP_GCM;

/**
 * Result of a GCM message request that returned HTTP status code 200.
 *
 * <p>
 * If the message is successfully created, the {@link #getMessageId()} returns
 * the message id and {@link #getErrorCodeName()} returns {@literal null};
 * otherwise, {@link #getMessageId()} returns {@literal null} and
 * {@link #getErrorCodeName()} returns the code of the error.
 *
 * <p>
 * There are cases when a request is accept and the message successfully
 * created, but GCM has a canonical registration id for that device. In this
 * case, the server should update the registration id to avoid rejected requests
 * in the future.
 *
 * <p>
 * In a nutshell, the workflow to handle a result is:
 * <pre>
 *   - Call {@link #getMessageId()}:
 *     - {@literal null} means error, call {@link #getErrorCodeName()}
 *     - non-{@literal null} means the message was created:
 *       - Call {@link #getCanonicalRegistrationId()}
 *         - if it returns {@literal null}, do nothing.
 *         - otherwise, update the server datastore with the new id.
 * </pre>
 */
class Result {

    private $messageId;
    private $canonicalRegistrationId;
    private $errorCode;

    /**
     * Sets the message id
     *
     * @param string $messageId
     */
    public function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    /**
     * Gets the message id, if any
     *
     * @return string
     */
    public function getMessageId() {
        return $this->messageId;
    }

    /**
     * Sets the canonical registration id
     *
     * @param string $canonicalRegistrationId
     */
    public function setCanonicalRegistrationId($canonicalRegistrationId) {
        $this->canonicalRegistrationId = $canonicalRegistrationId;
    }

    /**
     * Gets the canonical registration id, if any
     *
     * @return string
     */
    public function getCanonicalRegistrationId() {
        return $this->canonicalRegistrationId;
    }

    /**
     * Sets the error code
     *
     * @param string $errorCode
     */
    public function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    /**
     * Gets the error code, if any
     *
     * @return string
     */
    public function getErrorCode() {
        return $this->errorCode;
    }
}