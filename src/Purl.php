<?php

namespace EOV;

use EOV\Exception\RequestFailureException;

/**
 * Class Purl
 *
 * Contains API actions for EOV Purls
 *
 * @package EOV
 * @author George Webb <george.webb1@pb.com>
 */
class Purl
{
    /**
     * The EOV API Client
     *
     * @var ApiClient
     */
    public $api_client;

    /**
     * The url for the create purl
     */
    const CREATE_URL = "/api/purls/create";

    /**
     * Purl constructor.
     *
     * @param ApiClient $api_client
     */
    public function __construct(ApiClient $api_client)
    {
        $this->api_client = $api_client;
    }

    /**
     * Creates a purl from the given data
     *
     * @param array $data               The PURL data
     * @return string                   The PUID of the new Purl
     * @throws RequestFailureException  If there is a error - authentication, validation or otherwise
     */
    public function create($data)
    {
        return $this->api_client->request(static::CREATE_URL, $data)->getBody();
    }
}
