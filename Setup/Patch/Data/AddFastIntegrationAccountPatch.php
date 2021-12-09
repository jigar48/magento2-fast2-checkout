<?php

namespace Fast\Checkout\Setup\Patch\Data;

use Fast\Checkout\Logger\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Oauth\Exception;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\Oauth\TokenFactory as Token;
use Magento\Integration\Model\OauthService;
use Magento\Store\Model\StoreManagerInterface;

class AddFastIntegrationAccountPatch implements DataPatchInterface
{
    const INTEGRATION_NAME = 'FAST Checkout';
    const FAST_EMAIL = 'support@fast.co';
    const FAST_CALLBACK_URL = 'https://api.fast.co/';
    const DEPENDENCIES = [];
    const ALIASES = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Token
     */
    private $tokenFactory;
    /**
     * @var AuthorizationService
     */
    private $authorizationService;
    /**
     * @var OauthService
     */
    private $oAuthService;
    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * AddFastIntegrationAccountPatch constructor.
     * @param StoreManagerInterface $storeManager
     * @param Token $token
     * @param AuthorizationService $authorizationService
     * @param OauthService $oAuthService
     * @param IntegrationFactory $integrationFactory
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Token $token,
        AuthorizationService $authorizationService,
        OauthService $oAuthService,
        IntegrationFactory $integrationFactory,
        Logger $logger
    ) {
        $this->tokenFactory = $token;
        $this->storeManager = $storeManager;
        $this->authorizationService = $authorizationService;
        $this->oAuthService = $oAuthService;
        $this->integrationFactory = $integrationFactory;
        $this->logger = $logger;
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return static::DEPENDENCIES;
    }

    /**
     * @return DataPatchInterface|void
     * @throws LocalizedException
     * @throws Exception
     */
    public function apply()
    {
        $integrationExists = $this->integrationFactory->create()->load(static::INTEGRATION_NAME, 'name')->getData();

        if (empty($integrationExists)) {
            $integrationData = array(
                'name' => static::INTEGRATION_NAME,
                'email' => static::FAST_EMAIL,
                'status' => '1',
                'endpoint' => static::FAST_CALLBACK_URL,
                'setup_type' => '0'
            );
            try {
                // Code to create Integration
                $integration = $this->integrationFactory->create()->setData($integrationData);
                $integration->save();
                $integrationId = $integration->getId();
                $consumerName = 'Integration' . $integrationId;
                // Code to create consumer
                $oauthService = $this->oAuthService;
                $consumer = $oauthService->createConsumer(['name' => $consumerName]);
                $consumerId = $consumer->getId();
                $integration->setConsumerId($consumer->getId());
                $integration->save();
                // Code to grant permission
                $permissions = [
                    'Magento_Backend::admin',
                    'Magento_Sales::sales',
                    'Magento_Sales::sales_operation',
                    'Magento_Sales::sales_order',
                    'Magento_Sales::actions',
                    'Magento_Sales::create',
                    'Magento_Sales::actions_view',
                    'Magento_Sales::email',
                    'Magento_Sales::review_payment',
                    'Magento_Sales::capture',
                    'Magento_Sales::invoice',
                    'Magento_Sales::creditmemo',
                    'Magento_Sales::hold',
                    'Magento_Sales::ship',
                    'Magento_Sales::comment',
                    'Magento_Sales::emails',
                    'Magento_Paypal::authorization',
                    'Magento_Sales::sales_invoice',
                    'Magento_Sales::shipment',
                    'Magento_Sales::sales_creditmemo',
                    'Magento_Paypal::billing_agreement',
                    'Magento_Paypal::billing_agreement_actions',
                    'Magento_Paypal::billing_agreement_actions_view',
                    'Magento_Paypal::use',
                    'Magento_Sales::transactions',
                    'Magento_Sales::transactions_fetch',
                    'Magento_Catalog::catalog',
                    'Magento_Catalog::catalog_inventory',
                    'Magento_Catalog::products',
                    'Magento_Catalog::categories',
                    'Magento_Catalog::sets',
                    'Magento_Customer::customer',
                    'Magento_Customer::manage',
                    'Magento_Customer::actions',
                    'Magento_Customer::online',
                    'Magento_Customer::group',
                    'Magento_Cart::cart',
                    'Magento_Cart::manage',
                    'Magento_Backend::stores',
                    'Magento_Backend::stores_settings',
                    'Magento_Config::config',
                    'Magento_Payment::payment',
                    'Magento_Payment::payment_services',
                    'Magento_Shipping::carriers',
                    'Magento_Shipping::shipping_policy',
                    'Magento_Shipping::config_shipping',
                    'Magento_Multishipping::config_multishipping',
                    'Magento_Config::config_general',
                    'Magento_Checkout::checkout',
                    'Magento_Sales::config_sales',
                    'Magento_InventoryApi::inventory',
                    'Magento_InventoryApi::source',
                    'Magento_InventoryApi::stock',
                    'Magento_InventorySalesApi::stock',
                    'Magento_Tax::manage_tax',
                    'Magento_CurrencySymbol::system_currency',
                    'Magento_CurrencySymbol::currency_rates',
                    'Magento_CurrencySymbol::symbols',
                    'Magento_Backend::stores_attributes',
                    'Magento_Catalog::attributes_attributes',
                    'Magento_Swatches::iframe'
                ];
                $authorizeService = $this->authorizationService;
                $authorizeService->grantPermissions($integrationId, $permissions);

                // Code to Activate and Authorize
                $token = $this->tokenFactory->create();
                $token->createVerifierToken($consumerId);
                $token->setType('access');
                $token->save();

            } catch (Exception $e) {
                echo 'Error : ' . $e->getMessage();
            }
        }
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return static::ALIASES;
    }
}
