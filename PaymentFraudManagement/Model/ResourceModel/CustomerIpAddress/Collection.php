<?php
namespace Echidna\PaymentFraudManagement\Model\ResourceModel\CustomerIpAddress;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Echidna\PaymentFraudManagement\Model\CustomerIpAddress;
use Echidna\PaymentFraudManagement\Model\ResourceModel\CustomerIpAddress as CustomerIpAddressResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(CustomerIpAddress::class, CustomerIpAddressResource::class);
    }
}
