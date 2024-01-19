<?php
namespace Echidna\PaymentFraudManagement\Model\ResourceModel;


class CustomerIpAddress extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('echidna_customer_ip_address', null);
    }

}
