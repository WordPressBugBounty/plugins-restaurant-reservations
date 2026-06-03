<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service\BillingPortal;

class SessionService extends \rtbStripe\Service\AbstractService
{
    /**
     * Creates a session of the customer portal.
     *
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\BillingPortal\Session
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing_portal/sessions', $params, $opts);
    }
}
