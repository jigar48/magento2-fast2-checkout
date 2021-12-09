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

use Magento\Framework\Api\SearchResultsInterface;
use Fast\Checkout\Api\Data\DoRequestLogInterface;

/**
 * Interface DoRequestLogSearchResultsInterface
 */
interface DoRequestLogSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get DoRequestLog list.
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface[]
     */
    public function getItems();

    /**
     * Set request_id list.
     * @param DoRequestLogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
