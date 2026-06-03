<?php

// File generated from our OpenAPI spec

namespace rtbStripe;

/**
 * PaymentMethod objects represent your customer's payment instruments. They can be
 * used with <a
 * href="https://stripe.com/docs/payments/payment-intents">PaymentIntents</a> to
 * collect payments or saved to Customer objects to store instrument details for
 * future payments.
 *
 * Related guides: <a
 * href="https://stripe.com/docs/payments/payment-methods">Payment Methods</a> and
 * <a href="https://stripe.com/docs/payments/more-payment-scenarios">More Payment
 * Scenarios</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \rtbStripe\StripeObject $acss_debit
 * @property \rtbStripe\StripeObject $afterpay_clearpay
 * @property \rtbStripe\StripeObject $alipay
 * @property \rtbStripe\StripeObject $au_becs_debit
 * @property \rtbStripe\StripeObject $bacs_debit
 * @property \rtbStripe\StripeObject $bancontact
 * @property \rtbStripe\StripeObject $billing_details
 * @property \rtbStripe\StripeObject $card
 * @property \rtbStripe\StripeObject $card_present
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|string|\rtbStripe\Customer $customer The ID of the Customer to which this PaymentMethod is saved. This will not be set when the PaymentMethod has not been saved to a Customer.
 * @property \rtbStripe\StripeObject $eps
 * @property \rtbStripe\StripeObject $fpx
 * @property \rtbStripe\StripeObject $giropay
 * @property \rtbStripe\StripeObject $grabpay
 * @property \rtbStripe\StripeObject $ideal
 * @property \rtbStripe\StripeObject $interac_present
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\rtbStripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property \rtbStripe\StripeObject $oxxo
 * @property \rtbStripe\StripeObject $p24
 * @property \rtbStripe\StripeObject $sepa_debit
 * @property \rtbStripe\StripeObject $sofort
 * @property string $type The type of the PaymentMethod. An additional hash is included on the PaymentMethod with a name matching this value. It contains additional information specific to the PaymentMethod type.
 */
class PaymentMethod extends ApiResource
{
    const OBJECT_NAME = 'payment_method';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return PaymentMethod the attached payment method
     */
    public function attach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/attach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return PaymentMethod the detached payment method
     */
    public function detach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/detach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
