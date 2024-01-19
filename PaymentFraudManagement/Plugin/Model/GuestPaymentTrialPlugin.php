<?php

declare(strict_types=1);

namespace Echidna\PaymentFraudManagement\Plugin\Model;

use Echidna\PaymentFraudManagement\Helper\PreventFraud;
use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;

class GuestPaymentTrialPlugin {


    /**
     * @var \Echidna\PaymentFraudManagement\Helper\PreventFraud
     */
    protected $preventFraud;

    public function __construct(
        PreventFraud $preventFraud
    )
    {
        $this->preventFraud = $preventFraud;
    }


    /**
     * @param GuestPaymentInformationManagement $subject
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $this->preventFraud->preventFraud();
        return [$cartId,$email, $paymentMethod, $billingAddress];

    }


}
