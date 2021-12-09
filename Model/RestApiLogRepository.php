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
use Fast\Checkout\Api\Data\RestApiLogSearchResultsInterfaceFactory;
use Fast\Checkout\Api\RestApiLogRepositoryInterface;
use Fast\Checkout\Model\ResourceModel\RestApiLog\Collection;
use Fast\Checkout\Model\ResourceModel\RestApiLog\CollectionFactory as RestApiLogCollectionFactory;
use Fast\Checkout\Model\RestApiLogFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * class file for rest api log repository
 * Class RestApiLogRepository
 */
class RestApiLogRepository implements RestApiLogRepositoryInterface
{

    /**
     * @var RestApiLogCollectionFactory
     */
    protected $restApiLogCollFact;

    /**
     * @var RestApiLogSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * RestApiLogRepository constructor.
     * @param RestApiLogCollectionFactory $restApiLogCollFact
     * @param RestApiLogSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        RestApiLogCollectionFactory $restApiLogCollFact,
        RestApiLogSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->restApiLogCollFact = $restApiLogCollFact;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->restApiLogCollFact->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $collection;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * @inheritdoc
     */
    public function save(RestApiLogInterface $restApiLog): RestApiLogInterface
    {
        $restApiLog->getResource()->save($restApiLog);
        return $restApiLog;
    }

    /**
     * @inheritdoc
     */
    public function delete(RestApiLogInterface $restApiLog)
    {
        $restApiLog->getResource()->delete($restApiLog);
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $restApiLogId): bool
    {
        $restApiLog = $this->getById($restApiLogId);
        return $restApiLog->getResource()->delete($restApiLog);
    }

    /**
     * @inheritdoc
     */
    public function getById(int $apiLogId): RestApiLogInterface
    {
        $apiLog = $this->restApiLogFactory()->create();
        $apiLog->getResource()->load($apiLog, $apiLogId);
        if (!$apiLog->getId()) {
            throw new NoSuchEntityException(__('Unable to find rest api log  with api_log_id "%1"', $apiLogId));
        }
        return $apiLog;
    }
}
