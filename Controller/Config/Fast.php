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

namespace Fast\Checkout\Controller\Config;

use Fast\Checkout\Helper\FastCheckout as FastCheckoutHelper;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

/**
 * Class Fast
 *
 * makes fast config settings available to js
 */
class Fast extends Action
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
    protected $quoteIdMaskFactory;
    protected $cartManagement;
    protected $maskedQuoteIdToQuoteId;
    protected $fastCheckoutHelper;
    protected $quoteFactory;
    protected $fastConfig;

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
        CartRepositoryInterface $quoteRepository,
        GuestCartManagementInterface $cartManagement,
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        FastCheckoutHelper $fastCheckoutHelper,
        QuoteFactory $quoteFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        FastConfig $fastConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->cartManagement = $cartManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->fastCheckoutHelper = $fastCheckoutHelper;
        $this->quoteFactory = $quoteFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->fastConfig = $fastConfig;
        parent::__construct($context);
    }

    /**
     * get the user's cart and other config values
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $cartId = $this->getInvisibleCartFromCart($quote);
        $result = $this->resultJsonFactory->create();
        $cartIsFast = true;
        foreach ($quote ->getAllVisibleItems() as $cartItem) {
            if ((int)$cartItem->getProduct()->getData('hide_fast_option') == 0) {
                $cartIsFast = false;
            }
            if ($cartItem->getProductType() === 'bundle' ||
                $cartItem->getProductType() === 'downloadable') {
                $cartIsFast = false;
            }
        }
        return $result->setData([
            'areAllProductsFast' => $cartIsFast,
            'success' => true,
            'appId' => $this->fastConfig->getAppId(),
            'cartId' => $cartId,
            'theme' => $this->fastConfig->useDarkTheme() ? 'dark' : ''
        ]);
    }

    public function getInvisibleCartFromCart(Quote $quote): string
    {
        //Get cart from cart ID
        /** @var Quote $currentCart */
        $currentCart = $this->quoteRepository->get($quote->getEntityId());
        $maskedId = $this->cartManagement->createEmptyCart();
        $this->fastCheckoutHelper->log($maskedId);
        $newCartId = $this->maskedQuoteIdToQuoteId->execute($maskedId);
        /** @var Quote $newCart */
        $newCart = $this->quoteFactory->create()->load($newCartId, 'entity_id');
        //$newCart->setData($currentCart->getData());
        $newCart->setActive(0);
        $newCart->setCouponCode($currentCart->getCouponCode());
        //$newCart->removeAllItems();
        foreach ($currentCart->getAllVisibleItems() as $item) {
            $this->fastCheckoutHelper->log('item ' . $item->getName());
            $newItem = clone $item;
            $newCart->addItem($newItem);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $newChild = clone $child;
                    $newChild->setParentItem($newItem);
                    $newCart->addItem($newChild);
                }
            }
        }
        /**
         * Init shipping and billing address if quote is new
         */
        if (!$newCart->getId()) {
            $newCart->getShippingAddress();
            $newCart->getBillingAddress();
        }
        $newCart->setId($newCartId);
        $newCart->save();
        $this->fastCheckoutHelper->log(json_encode($newCart->getData()));
        foreach ($newCart->getAllVisibleItems() as $item) {
            $this->fastCheckoutHelper->log($item->getName());
        }
        return $maskedId;
    }
}
