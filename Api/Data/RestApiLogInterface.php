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

/**
 * Interface RestApiLogInterface
 */
interface RestApiLogInterface
{
    const API_LOG_ID = 'api_log_id';
    const SOURCE = 'source';
    const CREATED_AT = 'created_at';
    const METHOD = 'method';
    const PATH = 'path';
    const CONTENT = 'content';

    /**
     * Get api_log_id
     * @return int|null
     */
    public function getApiLogId();

    /**
     * Set api_log_id
     * @param int $apiLogId
     * @return RestApiLogInterface
     */
    public function setApiLogId(int $apiLogId);

    /**
     * @return string
     */
    public function getSource();

    /**
     * @param string $source
     * @return RestApiLogInterface
     */
    public function setSource(string $source);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return RestApiLogInterface
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @param string $method
     * @return RestApiLogInterface
     */
    public function setMethod(string $method);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $path
     * @return RestApiLogInterface
     */
    public function setPath(string $path);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     * @return RestApiLogInterface
     */
    public function setContent(string $content);
}
