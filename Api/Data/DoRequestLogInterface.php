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

namespace Fast\Checkout\Api\Data;

use Fast\Checkout\Api\Data\DoRequestLogExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface DoRequestLogInterface
 */
interface DoRequestLogInterface extends ExtensibleDataInterface //NOSONAR
{

    const DOREQUESTLOG_ID = 'dorequestlog_id';
    const RETRY_REQUIRED = 'retry_required';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PRIORITY = 'priority';
    const REQUEST_ID = 'request_id';
    const BODY = 'body';
    const RESPONSE_CONTENT = 'response_content';
    const STATUS = 'status';
    const ATTEMPTS = 'attempts';
    const URI_ENDPOINT = 'uri_endpoint';
    const REQUEST_METHOD = 'request_method';

    /**
     * Get dorequestlog_id
     * @return int
     */
    public function getDorequestlogId();

    /**
     * Set dorequestlog_id
     * @param string $dorequestlogId
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setDorequestlogId($dorequestlogId);

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId();

    /**
     * Set request_id
     * @param string $requestId
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setRequestId($requestId);

    /**
     * Get body
     * @return string|null
     */
    public function getBody();

    /**
     * Set body
     * @param string $body
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setBody($body);

    /**
     * Get attempts
     * @return int
     */
    public function getAttempts();

    /**
     * Set attempts
     * @param int $attempts
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setAttempts($attempts);

    /**
     * Get status
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setStatus($status);

    /**
     * Get response_content
     * @return string|null
     */
    public function getResponseContent();

    /**
     * Set response_content
     * @param string $responseContent
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setResponseContent($responseContent);

    /**
     * Get priority
     * @return int|null
     */
    public function getPriority();

    /**
     * Set priority
     * @param int $priority
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setPriority($priority);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get retry_required
     * @return int|null
     */
    public function getRetryRequired();

    /**
     * Set retry_required
     * @param int $retryRequired
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setRetryRequired($retryRequired);

    /**
     * Get uri_endpoint
     * @return string|null
     */
    public function getUriEndpoint();

    /**
     * Set uri_endpoint
     * @param string $uriEndpoint
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setUriEndpoint($uriEndpoint);

    /**
     * Get request_method
     * @return string|null
     */
    public function getRequestMethod();

    /**
     * Set request_method
     * @param string $requestMethod
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     */
    public function setRequestMethod($requestMethod);

    /**
     * @return \Fast\Checkout\Api\Data\DoRequestLogExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \Fast\Checkout\Api\Data\DoRequestLogExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(DoRequestLogExtensionInterface $extensionAttributes);
}
