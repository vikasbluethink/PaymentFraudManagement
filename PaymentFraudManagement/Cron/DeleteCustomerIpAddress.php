<?php

namespace Echidna\PaymentFraudManagement\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class DeleteCustomerIpAddress
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezoneInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezoneInterface,
        LoggerInterface $logger
    )
    {
        $this->timezoneInterface = $timezoneInterface;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');

            $previousMonth = strtotime('-30 day', strtotime($dateTime));
            $previousMonthDate = date('Y-m-d h:i:s', $previousMonth);

            $tableName = 'echidna_customer_ip_address';
            $whereCondition = "created_at < '{$previousMonthDate}'";

            $this->resourceConnection->getConnection()->delete($tableName, $whereCondition);
        }catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
