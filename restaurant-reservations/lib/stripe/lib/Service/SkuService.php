<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service;

class SkuService extends \rtbStripe\Service\AbstractService
{
    /**
     * Returns a list of your SKUs. The SKUs are returned sorted by creation date, with
     * the most recently created SKUs appearing first.
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
        return $this->requestCollection('get', '/v1/skus', $params, $opts);
    }

    /**
     * Creates a new SKU associated with a product.
     *
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\SKU
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/skus', $params, $opts);
    }

    /**
     * Delete a SKU. Deleting a SKU is only possible until it has been used in an
     * order.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\SKU
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/skus/%s', $id), $params, $opts);
    }

    /**
     * Retrieves the details of an existing SKU. Supply the unique SKU identifier from
     * either a SKU creation request or from the product, and Stripe will return the
     * corresponding SKU information.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\SKU
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/skus/%s', $id), $params, $opts);
    }

    /**
     * Updates the specific SKU by setting the values of the parameters passed. Any
     * parameters not provided will be left unchanged.
     *
     * Note that a SKU’s <code>attributes</code> are not editable. Instead, you would
     * need to deactivate the existing SKU and create a new one with the new attribute
     * values.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\SKU
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/skus/%s', $id), $params, $opts);
    }
}
