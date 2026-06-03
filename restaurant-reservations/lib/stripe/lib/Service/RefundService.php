<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service;

class RefundService extends \rtbStripe\Service\AbstractService
{
    /**
     * Returns a list of all refunds you’ve previously created. The refunds are
     * returned in sorted order, with the most recent refunds appearing first. For
     * convenience, the 10 most recent refunds are always available by default on the
     * charge object.
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
        return $this->requestCollection('get', '/v1/refunds', $params, $opts);
    }

    /**
     * Create a refund.
     *
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Refund
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/refunds', $params, $opts);
    }

    /**
     * Retrieves the details of an existing refund.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Refund
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/refunds/%s', $id), $params, $opts);
    }

    /**
     * Updates the specified refund by setting the values of the parameters passed. Any
     * parameters not provided will be left unchanged.
     *
     * This request only accepts <code>metadata</code> as an argument.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Refund
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/refunds/%s', $id), $params, $opts);
    }
}
