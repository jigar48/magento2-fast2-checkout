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

namespace Fast\Checkout\Model;

use Fast\Checkout\Api\Data\RestApiLogInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * identity model for RestApiLog
 * Class RestApiLog
 */
class RestApiLog extends AbstractModel implements RestApiLogInterface, IdentityInterface
{

    const CACHE_TAG = 'rest_api_log';

    /**
     * Get api_log_id
     *
     * @return int|null
     */
    public function getApiLogId()
    {
        return $this->getData(static::API_LOG_ID);
    }

    /**
     * @param int $apiLogId
     * @return RestApiLogInterface|RestApiLog
     */
    public function setApiLogId(int $apiLogId)
    {
        return $this->setData(static::API_LOG_ID, $apiLogId);
    }

    /**
     * Get Source At
     *
     * @return string|null
     */
    public function getSource()
    {
        return $this->getData(static::SOURCE);
    }

    /**
     * Set Source
     *
     * @param string $source
     * @return $this
     */
    public function setSource(string $source)
    {
        return $this->setData(static::SOURCE, $source);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(static::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt)
    {
        return $this->setData(static::CREATED_AT, $createdAt);
    }

    /**
     * Get Method
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->getData(static::METHOD);
    }

    /**
     * Set Method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        return $this->setData(static::METHOD, $method);
    }

    /**
     * Get Path
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->getData(static::PATH);
    }

    /**
     * Set Path
     *
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        return $this->setData(static::PATH, $path);
    }

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->getData(static::CONTENT);
    }

    /**
     * Set Content
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content)
    {
        return $this->setData(static::CONTENT, $content);
    }

    /**
     * Return identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * RestApiLog Initialization
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\RestApiLog::class);
    }
}
