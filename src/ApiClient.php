<?php

namespace EOV;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use EOV\Exception\RequestFailureException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiClient
 *
 * An EOV API Client
 *
 * @package EOV
 * @author George Webb <george.webb1@pb.com>
 */
class ApiClient
{
    /**
     * The GuzzleHttp client - used to do http requests
     *
     * @var Client
     */
    private $guzzle_client;

    /**
     * The base url of the EOV service
     *
     * @var string
     */
    private $service_url;

    /**
     * The name of the EOV project
     *
     * @var string
     */
    private $project_name;

    /**
     * EOV api username
     *
     * @var string
     */
    private $username;

    /**
     * EOV api password
     *
     * @var string
     */
    private $password;

    /**
     * Details of last failed request - e.g. validation errors
     *
     * @var array
     */
    private $failure_detail;

    /**
     * ApiClient constructor.
     *
     * @param Client $guzzle_client     A guzzle http client, used for requests
     */
    public function __construct(Client $guzzle_client)
    {
        $this->guzzle_client = $guzzle_client;
    }

    /**
     * Gets a param
     *
     * @param string $param_name    The name of the param
     * @return mixed                The param value
     */
    public function get($param_name)
    {
        return $this->$param_name;
    }

    /**
     * Sets a param
     *
     * @param string $param_name    The name of the param
     * @param mixed $value          The param value
     */
    public function set($param_name, $value)
    {
        $this->$param_name = $value;
    }

    /**
     * Returns an ApiClient object
     *
     * Used the service url, the name of the project as well as credentials to build an ApiClient
     *
     * @param string $service_url       The url of the EOV install, e.g. https://preprod.rtcvid.net
     * @param string $project_name      The name of the project, e.g. ez_energy
     * @param string $username          The api username, as defined in the project settings editor "eov_api_user"
     * @param string $password          The api password, as defined in the project settings editor "eov_api_password"
     * @return ApiClient                The instance of ApiClient
     */
    public static function factory($service_url, $project_name, $username, $password)
    {
        $obj = new static(new Client());

        $obj->set('service_url', $service_url);
        $obj->set('project_name', $project_name);
        $obj->set('username', $username);
        $obj->set('password', $password);

        return $obj;
    }

    /**
     * Sends a request to the project API in the given EOV install
     *
     * @param string $url               The url of the endpoint to request, e.g. /api/purls/create
     * @param array $data               The array of post data to send to the endpoint
     * @return bool|ResponseInterface   Returns the response object on success, false on failure
     * @throws RequestFailureException  If there is a error - authentication, validation or otherwise
     */
    public function request($url, $data)
    {
        $service_url = $this->get('service_url');
        $service_url .= substr($service_url, -1) == '/' ? '' : '/';
        $uri = $service_url . $this->get('project_name') . $url;

        try {
            $res = $this->guzzle_client->post($uri, array(
                'auth' => array($this->get('username'), $this->get('password')),
                'form_params' => $data
            ));
        } catch (ClientException $e) {
            $this->handleFailure($e->getResponse());
            return false;
        } catch (ServerException $e) {
            $this->handleFailure($e->getResponse());
            return false;
        } catch (\Exception $e) {
            return false;
        }

        return $res;
    }

    /**
     * Handles a failed response, throwing a RequestFailureException with details
     *
     * @param ResponseInterface $response   The failed response
     * @throws RequestFailureException      Containing details of failure
     */
    public function handleFailure($response)
    {
        switch ($response->getStatusCode()) {
            case 400:
                // Validation
                throw new RequestFailureException(
                    'There were validation errors from your request.',
                    400,
                    $this->getValidationErrors($response->getBody())
                );
                break;
            case 401:
                // Unauthorized
                throw new RequestFailureException('Unauthorized', 401);
                break;
            case 403:
                throw new RequestFailureException('Forbidden', 403);
                break;
            case 404:
                throw new RequestFailureException('Not found', 404);
                break;
            case 500:
                throw new RequestFailureException('Server error', 500);
                break;
            default:
                throw new RequestFailureException('There was a problem with your request');
                break;
        }
    }

    /**
     * Returns a list of validation errors from the response body.
     *
     * @param string $body  The response body
     * @return array        The array of validation errors
     */
    public function getValidationErrors($body)
    {
        $lines = explode("\n", trim($body));

        // Remove first line, as it just contains  "400 Bad Request"
        array_shift($lines);

        return $lines;
    }
}
