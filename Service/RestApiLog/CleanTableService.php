<?php
/**
 * Fast_Checkout
 *
 * PHP version 7.3
 *
 * @author    Fast <hi@fast.co>
 * @copyright 2021 Copyright Fast AF, Inc., https://www.fast.co/
 * @license   https://opensource.org/licenses/OSL-3.0 OSL-3.0
 * @link      https://www.fast.co/
 */

declare(strict_types=1);

namespace Fast\Checkout\Service\RestApiLog;

use Fast\Checkout\Model\RestApiLogRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * class to clean older log rows out of table
 * Class CleanTableService
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CleanTableService
{

    const DAYS_TO_RETAIN = 'fast_checkout/rest_api_log/table_clean_cron_retain_days';
    const ENABLE_LOG_CLEANER = 'fast_checkout/rest_api_log/enable_log_cleaner';
    const JOB_NAME = 'fast_checkout_rest_api_log_table_clean';
    const STRING_CRON_JOB = 'cron job: ';
    const TABLE_NAME = 'fast_checkout_rest_api_log';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var SearchCriteriaBuilder
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private $searchCriteriaBuilder;

    /**
     * @var RestApiLogRepository
     */
    private $restApiLogRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    private $sortOrderBuilder;

    /**
     * CleanTableService constructor.
     * @param LoggerInterface $loggerInterface
     * @param TimezoneInterface $timezone
     * @param ScopeConfig $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RestApiLogRepository $restApiLogRepository
     * @param ResourceConnection $resourceConnection
     * @param SortOrderBuilder $sortOrderBuilder
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        TimezoneInterface $timezone,
        ScopeConfig $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RestApiLogRepository $restApiLogRepository,
        ResourceConnection $resourceConnection,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->logger = $loggerInterface;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->restApiLogRepository = $restApiLogRepository;
        $this->resourceConnection = $resourceConnection;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    public function execute()
    {
        $count = 0;
        $deleteRows = 0;
        if ($this->isEnabled()) {
            $time = $this->startLogging(static::JOB_NAME);

            // get filtered collection from table where created_at is > days to retain
            while ($logRows = $this->getLogRows()) {
                //phpcs:ignore Ecg.Performance.Loop
                $count += count($logRows);
                $rowIds = [];
                foreach ($logRows as $row) {
                    $rowIds[] = $row->getApiLogId();
                }
                if ($count > 0) {
                    $connection = $this->resourceConnection->getConnection();
                    $tableName = $connection->getTableName(static::TABLE_NAME);
                    $where = [
                        $connection->quoteInto('api_log_id IN (?)', $rowIds),
                    ];
                    //phpcs:ignore Ecg.Performance.Loop
                    $deleteRows += $connection->delete($tableName, $where);
                }
            }

            $this->logger->info($count . ' rows counted to delete. Rows Cleaned from Table: ' . $deleteRows);
            $this->stopLogging(static::JOB_NAME, $time);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(static::ENABLE_LOG_CLEANER);
    }

    /**
     * Start logging
     *
     * @param string $jobName
     * @return int
     */
    private function startLogging(string $jobName): int
    {
        $date = $this->timezone->date();
        $this->logger->debug(static::STRING_CRON_JOB . $jobName . ' started at ' . $date->format('m/j/Y H:i:s'));
        $this->logger->info('Begin: Fast\Checkout\Service\RestApiLog\CleanTableService');

        return time();
    }

    /**
     * @return mixed
     */
    private function getLogRows()
    {
        $customDate = $this->timezone->date()->format('Y-m-d H:i:s');
        if ($this->getDaysToRetain() != 0) {
            $customDate = $this->timezone->date()->modify($this->getDaysToRetain())->format('Y-m-d H:i:s');
        }
        $this->logger->info($customDate);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $customDate, 'lteq')
            ->addSortOrder($this->sortOrderBuilder->setField('api_log_id')
                ->setDescendingDirection()->create())
            ->setPageSize(100)->setCurrentPage(1)->create();

        $collection = $this->restApiLogRepository->getList($searchCriteria);

        return count($collection) > 0
            ? $collection
            : 0;
    }

    /**
     * @return string
     */
    private function getDaysToRetain()
    {

        $days = (int)$this->scopeConfig->getValue(
            static::DAYS_TO_RETAIN,
            ScopeInterface::SCOPE_WEBSITES
        );
        $this->logger->info("scope config " . $days . " days");
        if ($days < 1 || !is_int($days)) {
            return 0;
        }

        return "-" . $days . " days";
    }

    /**
     * Stop logging
     *
     * @param string $jobName
     * @param int $startTime
     * @return void
     */
    private function stopLogging(string $jobName, int $startTime): void
    {
        $processTime = time() - $startTime;
        $date = $this->timezone->date();
        $this->logger->debug(static::STRING_CRON_JOB . $jobName . ' finished at ' . $date->format('m/j/Y H:i:s'));
        $this->logger->debug(static::STRING_CRON_JOB . $jobName . ' execution time (sec) ' . $processTime);
        $this->logger->info('End: Fast\Checkout\Service\RestApiLog\CleanTableService');
    }
}
