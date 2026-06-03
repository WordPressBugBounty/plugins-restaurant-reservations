<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service;

class MandateService extends \rtbStripe\Service\AbstractService
{
    /**
     * Retrieves a Mandate object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Mandate
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/mandates/%s', $id), $params, $opts);
    }
}
