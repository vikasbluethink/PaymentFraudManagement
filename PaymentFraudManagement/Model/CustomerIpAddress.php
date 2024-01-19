<?php
namespace Echidna\PaymentFraudManagement\Model;

use \Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;
class CustomerIpAddress extends AbstractModel
{

    protected $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource,$resourceCollection, $data);
        $this->resourceConnection = $resourceConnection;
    }

    protected function _construct()
    {
        $this->_init(\Echidna\PaymentFraudManagement\Model\ResourceModel\CustomerIpAddress::class);
    }

    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    public function getIncrementField($ipAddress){
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('echidna_customer_ip_address');

        $select = $connection->select()
            ->from($tableName)
            ->columns(['increment_field'])
            ->where('ip_address = ?', $ipAddress);

        $result = $connection->fetchRow($select);
        if($result){
            return $result['increment_field'];
        }

        return 0;
    }

    public function saveData($ipAddress,$websiteId)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('echidna_customer_ip_address');

        $incrementId = $this->getIncrementField($ipAddress);

        if($incrementId > 0){
            $this->updateData($ipAddress,$websiteId, $incrementId, $tableName);
        }else{
            $this->insertData($ipAddress,$websiteId, $tableName);
        }
    }


    public function insertData($ipAddress,$websiteId, $tableName)
    {

        $data = [
            'ip_address' => $ipAddress,
            'increment_field' => 1,
            'website_id' => $websiteId,
        ];

        $this->getConnection()->insert($tableName, $data);
    }

    public function updateData($ipAddress,$websiteId, $incrementField, $tableName)
    {


        $data = [
            'increment_field' => $incrementField+1,
        ];

        $whereCondition = [
            'ip_address = ?' => $ipAddress,
            'website_id = ?' => $websiteId,
        ];

        $this->getConnection()->update($tableName, $data, $whereCondition);
    }



}
