<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service;

class OrderReturnService extends \rtbStripe\Service\AbstractService
{
    /**
     * Returns a list of your order returns. The returns are returned sorted by
     * creation date, with the most recently created return appearing first.
     *
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Collection
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/order_returns', $params, $opts);
    }

    /**
     * Retrieves the details of an existing order return. Supply the unique order ID
     * from either an order return creation request or the order return list, and
     * Stripe will return the corresponding order information.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\OrderReturn
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/order_returns/%s', $id), $params, $opts);
    }
}
