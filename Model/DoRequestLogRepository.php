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

use Exception;
use Fast\Checkout\Api\Data\DoRequestLogInterface;
use Fast\Checkout\Api\Data\DoRequestLogInterfaceFactory;
use Fast\Checkout\Api\Data\DoRequestLogSearchResultsInterfaceFactory;
use Fast\Checkout\Api\DoRequestLogRepositoryInterface;
use Fast\Checkout\Model\ResourceModel\DoRequestLog as ResourceDoRequestLog;
use Fast\Checkout\Model\ResourceModel\DoRequestLog\CollectionFactory as DoRequestLogCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DoRequestLogRepository
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DoRequestLogRepository implements DoRequestLogRepositoryInterface
{

    /**
     * @var DoRequestLogSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    /**
     * @var DoRequestLogFactory
     */
    protected $doRequestLogFactory;
    /**
     * @var DoRequestLogInterfaceFactory
     */
    protected $dataDoRequestLogFactory;
    /**
     * @var DoRequestLogCollectionFactory
     */
    protected $doRequestLogCollectionFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var ResourceDoRequestLog
     */
    protected $resource;
    protected $extensionAttributesJoinProcessor;
    protected $extensibleDataObjectConverter;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceDoRequestLog $resource
     * @param DoRequestLogFactory $doRequestLogFactory
     * @param DoRequestLogInterfaceFactory $dataDoRequestLogFactory
     * @param DoRequestLogCollectionFactory $doRequestLogCollectionFactory
     * @param DoRequestLogSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceDoRequestLog $resource,
        DoRequestLogFactory $doRequestLogFactory,
        DoRequestLogInterfaceFactory $dataDoRequestLogFactory,
        DoRequestLogCollectionFactory $doRequestLogCollectionFactory,
        DoRequestLogSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->doRequestLogFactory = $doRequestLogFactory;
        $this->doRequestLogCollectionFactory = $doRequestLogCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataDoRequestLogFactory = $dataDoRequestLogFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        DoRequestLogInterface $doRequestLog
    ) {

        $doRequestLogData = $this->extensibleDataObjectConverter->toNestedArray(
            $doRequestLog,
            [],
            DoRequestLogInterface::class
        );

        $doRequestLogModel = $this->doRequestLogFactory->create()->setData($doRequestLogData);

        try {
            $this->resource->save($doRequestLogModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the doRequestLog: %1',
                $exception->getMessage()
            ));
        }
        return $doRequestLogModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->doRequestLogCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            DoRequestLogInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($doRequestLogId)
    {
        return $this->delete($this->get($doRequestLogId));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        DoRequestLogInterface $doRequestLog
    ) {
        try {
            $doRequestLogModel = $this->doRequestLogFactory->create();
            $this->resource->load($doRequestLogModel, $doRequestLog->getDorequestlogId());
            $this->resource->delete($doRequestLogModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the DoRequestLog: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($doRequestLogId)
    {
        $doRequestLog = $this->doRequestLogFactory->create();
        $this->resource->load($doRequestLog, $doRequestLogId);
        if (!$doRequestLog->getId()) {
            throw new NoSuchEntityException(__('DoRequestLog with id "%1" does not exist.', $doRequestLogId));
        }
        return $doRequestLog->getDataModel();
    }
}
