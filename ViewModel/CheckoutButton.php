<?php
/**
 * Fast_Checkout
 *
 * PHP version 7.3
 *
 * @package   Fast_Checkout
 * @author    Fast <hi@fast.co>
 * @copyright 2021 Copyright Fast AF, Inc., https://www.fast.co/
 * @license   https://opensource.org/licenses/OSL-3.0 OSL-3.0
 * @link      https://www.fast.co/
 */

declare(strict_types=1);

namespace Fast\Checkout\ViewModel;

use Exception;
use Fast\Checkout\Helper\FastCheckout as FastCheckoutHelper;
use Fast\Checkout\Model\Config\FastIntegrationConfig;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;

/**
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CheckoutButton implements ArgumentInterface
{

    /**
     * @var FastIntegrationConfig
     */
    protected $fastIntegrationConfig;
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var FastCheckoutHelper
     */
    protected $fastCheckoutHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var MaskedQuoteIdToQuoteId
     */
    protected $maskedQuoteIdToQuoteId;
    /**
     * @var GuestCartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * CheckoutButton constructor.
     * @param FastIntegrationConfig $fastIntegrationConfig
     * @param FastCheckoutHelper $fastCheckoutHelper
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Registry $registry
     * @param QuoteRepository $quoteRepository
     * @param QuoteFactory $quoteFactory
     * @param GuestCartManagementInterface $cartManagement
     * @param MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     * @param UrlInterface $urlInterface
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FastIntegrationConfig $fastIntegrationConfig,
        FastCheckoutHelper $fastCheckoutHelper,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Registry $registry,
        QuoteRepository $quoteRepository,
        QuoteFactory $quoteFactory,
        GuestCartManagementInterface $cartManagement,
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        UrlInterface $urlInterface
    ) {
        $this->fastIntegrationConfig = $fastIntegrationConfig;
        $this->fastCheckoutHelper = $fastCheckoutHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->cartManagement = $cartManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @return bool
     */
    public function isFastEnabled()
    {
        return $this->fastIntegrationConfig->isEnabled();
    }

    /**
     * @return bool
     */
    public function isFastProduct()
    {
        $product = $this->registry->registry('product');
        if ($product !== null && $product->getData('hide_fast_option') == 0) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function useDarkTheme()
    {
        return $this->fastIntegrationConfig->useDarkTheme();
    }

    /**
     * @return string
     */
    public function getFastAppId()
    {
        return $this->fastIntegrationConfig->getAppId();
    }

    /**
     * @return string
     */
    public function getFastUri()
    {
        return $this->fastIntegrationConfig->getFastApiUri();
    }

    /**
     * @return string
     */
    public function getFastJsUrl()
    {
        return $this->fastIntegrationConfig->getFastJsUrl();
    }

    /**
     * @return mixed|null
     */
    public function getCustomerCartDiscount()
    {
        $couponCode = '';
        $quoteId = $this->getQuoteId();
        if (! empty($quoteId)) {
            $quote = $this->quoteRepository->get($quoteId);

            if (! empty($quote)) {
                $couponCode = $quote->getCouponCode();
            }
        }
        return $couponCode;
    }

    /**
     * @return string
     * @throws CouldNotSaveException The empty cart and quote could not be created.
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getInvisibleCartFromCart(): string
    {
        if ($this->getQuoteId()) {
            //Get cart from cart ID
            $currentCart = $this->quoteRepository->get($this->getQuoteId());
            $maskedId = $this->cartManagement->createEmptyCart();
            $this->fastCheckoutHelper->log($maskedId);
            $newCartId = $this->maskedQuoteIdToQuoteId->execute($maskedId);
            /** @var Quote $newCart */
            $newCart = $this->quoteFactory->create()->load($newCartId, 'entity_id');
            $newCart->setData($currentCart->getData());
            $newCart->setActive(0);
            $newCart->removeAllItems();
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
        return $this->cartManagement->createEmptyCart();
    }

    /**
     * @return string
     */
    protected function getQuoteId(): string
    {
        $registered = $this->customerSession->isLoggedIn();
        //Get current cart ID
        $quoteId = $this->getCurrentCartId();
        if (!$registered) {
            $quoteId = $this->quoteIdMaskFactory->create()->load($quoteId, 'masked_id')->getQuoteId();
        }
        return (string)$quoteId;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentCartId()
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        if ($this->customerSession->isLoggedIn()) {
            return $quoteId;
        }
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }

    /**
     * @return string
     */
    public function getClearCartUrl(): string
    {
        return $this->urlInterface->getUrl(
            'fast/cart/clear'
        );
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function areAllProductsFast()
    {
        //get all products in cart - if product is not fast able return false
        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $cartItem) {
            if ((int)$cartItem->getProduct()->getData('hide_fast_option') == 0) {
                return false;
            }
            //reject non enabled product types
            if ($cartItem->getProductType() === 'bundle' ||
                $cartItem->getProductType() === 'downloadable') {
                return false;
            }
        }
        return true;
    }
}
