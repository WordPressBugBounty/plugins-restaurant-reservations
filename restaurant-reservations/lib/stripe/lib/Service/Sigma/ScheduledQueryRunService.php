<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Service\Sigma;

class ScheduledQueryRunService extends \rtbStripe\Service\AbstractService
{
    /**
     * Returns a list of scheduled query runs.
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
        return $this->requestCollection('get', '/v1/sigma/scheduled_query_runs', $params, $opts);
    }

    /**
     * Retrieves the details of an scheduled query run.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\rtbStripe\Util\RequestOptions $opts
     *
     * @throws \rtbStripe\Exception\ApiErrorException if the request fails
     *
     * @return \rtbStripe\Sigma\ScheduledQueryRun
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/sigma/scheduled_query_runs/%s', $id), $params, $opts);
    }
}
