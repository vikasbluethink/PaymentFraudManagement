<?php

namespace Echidna\PaymentFraudManagement\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends Action
{

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;


    /**
     * @param Action\Context $context
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteManagement $quoteManagement
     */
    public function __construct(
        Action\Context $context,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $cartRepository,
        QuoteManagement $quoteManagement,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
        $this->quoteManagement = $quoteManagement;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        try {
            $customerId = $this->getRequest()->getPost('customerId');
            $customer = $this->customerRepository->getById($customerId);
            if($customer){
                $quoteId = $this->quoteManagement->getCartForCustomer($customerId)->getId();
                if ($quoteId) {
                    $quote = $this->cartRepository->getActive($quoteId);
                    $quote->setData('payment_trials', 0);
                    $this->cartRepository->save($quote);
                    $response = [
                        'message' => 'PaymentFraudManagement Trial has been reset.',
                        'data' => [
                            'customer id' => $customerId,
                            'quote id' => $quoteId
                        ]
                    ];

                }else{
                    $response = [
                        'message' => 'Customer has not active quote.',
                        'data' => [
                            'customer id' => $customerId,
                            'quote id' => $quoteId
                        ]
                    ];

                }
            }
            $response = [
                'message' => 'Customer has not been registered.',
                'data' => [
                    'customer id' => $customerId,
                    'quote id' => $quoteId
                ]
            ];


            $resultJson = $this->jsonFactory->create();
            $resultJson->setData($response);

            return $resultJson;
        } catch (\Exception $e) {
            $response = [
                'message' => 'Customer has not found in quote or not registered.'
            ];


            $resultJson = $this->jsonFactory->create();
            $resultJson->setData($response);

            return $resultJson;
        }



    }
}
