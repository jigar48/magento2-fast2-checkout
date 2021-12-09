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

namespace Fast\Checkout\Controller\Cart;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class Clear
 *
 * removes items from carts in Magento
 */
class Clear extends Action
{
    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Clear constructor.
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context);
    }

    /**
     * clear the current user's shopping cart
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteId = $quote->getId();

        if ($quoteId) {
            $cart = $this->quoteRepository->get($quoteId);
            $cart->delete();
        }
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->clearStorage();
        $this->checkoutSession->restoreQuote();
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
