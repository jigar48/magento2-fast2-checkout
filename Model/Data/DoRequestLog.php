<?php
/**
 * Fast_Checkout
 *
 * PHP version 7.3
 *
 * @package   Fast_Checkout
 * @author    Fast <hi@fast.co>
 * @copyright 2021 Copyright Fast AF, Inc., https://www.fast.co/
 * @license   https://opensource.org/licenses/OSL-3.0 OSL-3.0
 * @link      https://www.fast.co/
 */

declare(strict_types=1);

namespace Fast\Checkout\Model\Data;

use Fast\Checkout\Api\Data\DoRequestLogExtensionInterface;
use Fast\Checkout\Api\Data\DoRequestLogInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class DoRequestLog
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DoRequestLog extends AbstractExtensibleObject implements DoRequestLogInterface //NOSONAR
{

    /**
     * Get dorequestlog_id
     * @return int
     */
    public function getDorequestlogId()
    {
        return $this->_get(static::DOREQUESTLOG_ID);
    }

    /**
     * Set dorequestlog_id
     * @param int $dorequestlogId
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setDorequestlogId($dorequestlogId)
    {
        return $this->setData(static::DOREQUESTLOG_ID, $dorequestlogId);
    }

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->_get(static::REQUEST_ID);
    }

    /**
     * Set request_id
     * @param string $requestId
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setRequestId($requestId)
    {
        return $this->setData(static::REQUEST_ID, $requestId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return  DoRequestLogExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param DoRequestLogExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        DoRequestLogExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get body
     * @return string|null
     */
    public function getBody()
    {
        return $this->_get(static::BODY);
    }

    /**
     * Set body
     * @param string $body
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setBody($body)
    {
        return $this->setData(static::BODY, $body);
    }

    /**
     * Get attempts
     * @return int
     */
    public function getAttempts()
    {
        return $this->_get(static::ATTEMPTS);
    }

    /**
     * Set attempts
     * @param int $attempts
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setAttempts($attempts)
    {
        return $this->setData(static::ATTEMPTS, $attempts);
    }

    /**
     * Get status
     * @return int
     */
    public function getStatus()
    {
        return $this->_get(static::STATUS);
    }

    /**
     * Set status
     * @param int $status
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setStatus($status)
    {
        return $this->setData(static::STATUS, $status);
    }

    /**
     * Get response_content
     * @return string|null
     */
    public function getResponseContent()
    {
        return $this->_get(static::RESPONSE_CONTENT);
    }

    /**
     * Set response_content
     * @param string $responseContent
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setResponseContent($responseContent)
    {
        return $this->setData(static::RESPONSE_CONTENT, $responseContent);
    }

    /**
     * Get priority
     * @return int|null
     */
    public function getPriority()
    {
        return $this->_get(static::PRIORITY);
    }

    /**
     * Set priority
     * @param int $priority
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(static::PRIORITY, $priority);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(static::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(static::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(static::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(static::UPDATED_AT, $updatedAt);
    }

    /**
     * Get retry_required
     * @return int|null
     */
    public function getRetryRequired()
    {
        return $this->_get(static::RETRY_REQUIRED);
    }

    /**
     * Set retry_required
     * @param int $retryRequired
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setRetryRequired($retryRequired)
    {
        return $this->setData(static::RETRY_REQUIRED, $retryRequired);
    }

    /**
     * Get uri_endpoint
     * @return string|null
     */
    public function getUriEndpoint()
    {
        return $this->_get(static::URI_ENDPOINT);
    }

    /**
     * Set uri_endpoint
     * @param string $uriEndpoint
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setUriEndpoint($uriEndpoint)
    {
        return $this->setData(static::URI_ENDPOINT, $uriEndpoint);
    }

    /**
     * Get request_method
     * @return string|null
     */
    public function getRequestMethod()
    {
        return $this->_get(static::REQUEST_METHOD);
    }

    /**
     * Set request_method
     * @param string $requestMethod
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setRequestMethod($requestMethod)
    {
        return $this->setData(static::REQUEST_METHOD, $requestMethod);
    }
}
