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

namespace Fast\Checkout\Model\ResourceModel\RestApiLog;

use Fast\Checkout\Model\ResourceModel\RestApiLog;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * collection class for RestApiLog
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * RestApiLog Collection Constructor
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Fast\Checkout\Model\RestApiLog::class,
            RestApiLog::class
        );
    }
}
