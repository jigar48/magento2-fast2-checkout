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

namespace Fast\Checkout\Api;

use Fast\Checkout\Api\Data\DoRequestLogInterface;
use Fast\Checkout\Api\Data\DoRequestLogSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface DoRequestLogRepositoryInterface
 */
interface DoRequestLogRepositoryInterface
{

    /**
     * Save DoRequestLog
     * @param DoRequestLogInterface $doRequestLog
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     * @throws LocalizedException
     */
    public function save(
        DoRequestLogInterface $doRequestLog
    );

    /**
     * Retrieve DoRequestLog
     * @param string $dorequestlogId
     * @return \Fast\Checkout\Api\Data\DoRequestLogInterface
     * @throws LocalizedException
     */
    public function get($dorequestlogId);

    /**
     * Retrieve DoRequestLog matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Fast\Checkout\Api\Data\DoRequestLogSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete DoRequestLog
     * @param DoRequestLogInterface $doRequestLog
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        DoRequestLogInterface $doRequestLog
    );

    /**
     * Delete DoRequestLog by ID
     * @param string $dorequestlogId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($dorequestlogId);
}
