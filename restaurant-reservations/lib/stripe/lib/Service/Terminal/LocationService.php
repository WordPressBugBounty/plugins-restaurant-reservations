<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service\Terminal;

class LocationService extends \rtbStripe\Service\AbstractService
{
    /**
     * Returns a list of <code>Location</code> objects.
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
        return $this->requestCollection('get', '/v1/terminal/locations', $params, $opts);
    }

    /**
     * Creates a new <code>Location</code> object.
     *
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Terminal\Location
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/terminal/locations', $params, $opts);
    }

    /**
     * Deletes a <code>Location</code> object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Terminal\Location
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/terminal/locations/%s', $id), $params, $opts);
    }

    /**
     * Retrieves a <code>Location</code> object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Terminal\Location
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/terminal/locations/%s', $id), $params, $opts);
    }

    /**
     * Updates a <code>Location</code> object by setting the values of the parameters
     * passed. Any parameters not provided will be left unchanged.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Terminal\Location
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/terminal/locations/%s', $id), $params, $opts);
    }
}
