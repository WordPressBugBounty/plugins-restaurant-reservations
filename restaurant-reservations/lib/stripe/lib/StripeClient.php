<?php

// File generated from our OpenAPI spec

namespace rtbStripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \rtbStripe\Service\AccountLinkService $accountLinks
 * @property \rtbStripe\Service\AccountService $accounts
 * @property \rtbStripe\Service\ApplePayDomainService $applePayDomains
 * @property \rtbStripe\Service\ApplicationFeeService $applicationFees
 * @property \rtbStripe\Service\BalanceService $balance
 * @property \rtbStripe\Service\BalanceTransactionService $balanceTransactions
 * @property \rtbStripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \rtbStripe\Service\ChargeService $charges
 * @property \rtbStripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \rtbStripe\Service\CountrySpecService $countrySpecs
 * @property \rtbStripe\Service\CouponService $coupons
 * @property \rtbStripe\Service\CreditNoteService $creditNotes
 * @property \rtbStripe\Service\CustomerService $customers
 * @property \rtbStripe\Service\DisputeService $disputes
 * @property \rtbStripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \rtbStripe\Service\EventService $events
 * @property \rtbStripe\Service\ExchangeRateService $exchangeRates
 * @property \rtbStripe\Service\FileLinkService $fileLinks
 * @property \rtbStripe\Service\FileService $files
 * @property \rtbStripe\Service\InvoiceItemService $invoiceItems
 * @property \rtbStripe\Service\InvoiceService $invoices
 * @property \rtbStripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \rtbStripe\Service\MandateService $mandates
 * @property \rtbStripe\Service\OAuthService $oauth
 * @property \rtbStripe\Service\OrderReturnService $orderReturns
 * @property \rtbStripe\Service\OrderService $orders
 * @property \rtbStripe\Service\PaymentIntentService $paymentIntents
 * @property \rtbStripe\Service\PaymentMethodService $paymentMethods
 * @property \rtbStripe\Service\PayoutService $payouts
 * @property \rtbStripe\Service\PlanService $plans
 * @property \rtbStripe\Service\PriceService $prices
 * @property \rtbStripe\Service\ProductService $products
 * @property \rtbStripe\Service\PromotionCodeService $promotionCodes
 * @property \rtbStripe\Service\Radar\RadarServiceFactory $radar
 * @property \rtbStripe\Service\RefundService $refunds
 * @property \rtbStripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \rtbStripe\Service\ReviewService $reviews
 * @property \rtbStripe\Service\SetupAttemptService $setupAttempts
 * @property \rtbStripe\Service\SetupIntentService $setupIntents
 * @property \rtbStripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \rtbStripe\Service\SkuService $skus
 * @property \rtbStripe\Service\SourceService $sources
 * @property \rtbStripe\Service\SubscriptionItemService $subscriptionItems
 * @property \rtbStripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \rtbStripe\Service\SubscriptionService $subscriptions
 * @property \rtbStripe\Service\TaxRateService $taxRates
 * @property \rtbStripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \rtbStripe\Service\TokenService $tokens
 * @property \rtbStripe\Service\TopupService $topups
 * @property \rtbStripe\Service\TransferService $transfers
 * @property \rtbStripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \rtbStripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \rtbStripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}
