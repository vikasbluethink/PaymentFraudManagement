<?php

declare(strict_types=1);

namespace Echidna\PaymentFraudManagement\Helper;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\HTTP\PhpEnvironment\Request as IpRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Echidna\PaymentFraudManagement\Model\CustomerIpAddress;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class PreventFraud {

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Echidna\PaymentFraudManagement\Helper\MailSender
     */
    protected $mailSender;

    /**
     * @var \Echidna\PaymentFraudManagement\Model\CustomerIpAddress
     */
    protected $customerIpAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    protected $scopeConfig;

    protected $storeManager;


    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $ipRequest;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        MailSender $mailSender,
        RemoteAddress $remoteAddress,
        CustomerIpAddress $customerIpAddress,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        IpRequest $ipRequest,
        ManagerInterface $messageManager)
    {
        $this->quoteRepository = $quoteRepository;
        $this->mailSender = $mailSender;
        $this->remoteAddress = $remoteAddress;
        $this->customerIpAddress = $customerIpAddress;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->ipRequest = $ipRequest;
        $this->messageManager = $messageManager;
    }


    /**
     * @return bool
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function preventFraud()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ip_address.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Ip Address');


        if($this->getConfigValue('order/payment/enabled')){
            $clientIpAddresses = explode(',', $this->ipRequest->getClientIp());
            $logger->info(print_r($clientIpAddresses,true));
            $ipAddress = $clientIpAddresses[0];

            $this->customerIpAddress->saveData($ipAddress,$this->storeManager->getStore()->getWebsiteId());
            $incrementField = $this->customerIpAddress->getIncrementField($ipAddress);

            $quote = $this->checkoutSession->getQuote();
            $paymentAttempts = (int)$quote->getData('payment_trials');
            $configTrial = $this->getConfigValue('order/payment/max_trial');
            $configIpAddressRestriction = $this->getConfigValue('order/payment/ip_address');
            if ($paymentAttempts >= $configTrial || $incrementField >= $configIpAddressRestriction) {

                $message = $this->getConfigValue('order/payment/message');
                $name = $quote->getData('customer_firstname').' '.$quote->getData('customer_middlename').' '.$quote->getData('customer_lastname');

                $this->mailSender->sendEmail($name, $quote->getData('customer_email'), $message);
                $this->messageManager->addErrorMessage($message);
                throw new CouldNotSaveException(__($message));
            }

            $quote->setData('payment_trials', $paymentAttempts + 1);
            $this->quoteRepository->save($quote);
            return true;
        }
        return false;
    }

    /**
     * @param $path
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigValue($path)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }


}
