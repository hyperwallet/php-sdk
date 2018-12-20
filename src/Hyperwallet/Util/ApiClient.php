<?php
namespace Hyperwallet\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\UriTemplate;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Model\BaseModel;
use Hyperwallet\Response\ErrorResponse;
use Hyperwallet\Util\HyperwalletEncryption;

/**
 * The internal API client
 *
 * @package Hyperwallet\Util
 */
class ApiClient {

    /**
     * The SDK version number
     *
     * @var string
     */
    const VERSION = '0.1.0';

    /**
     * The Guzzle http client
     * 
     * @var Client
     */
    private $client;

    /**
     * The Encryption service for http client requests/responses
     *
     * @var HyperwalletEncryption
     */
    private $encryption;

    /**
     * Boolean flag that checks if ApiClient is constructed with encryption enabled or not
     *
     * @var boolean
     */
    private $isEncrypted = false;

    /**
     * Creates a instance of the API client
     *
     * @param string $username The API username
     * @param string $password The API password
     * @param string $server The API server to connect to
     * @param array $clientOptions Guzzle Client Options
     * @param array $encryptionData Encryption data to initialize ApiClient with encryption enabled
     */
    public function __construct($username, $password, $server, $clientOptions = array(), $encryptionData = array()) {
        // Setup http client if not specified
        $this->client = new Client(array_merge_recursive(array(
            'base_uri' => $server,
            'auth' => array($username, $password),
            'headers' => array(
                'User-Agent' => 'Hyperwallet PHP SDK v' . self::VERSION,
                'Accept' => 'application/json'
            )
        ), $clientOptions));
        if (!empty($encryptionData) && isset($encryptionData['clientPrivateKeySetLocation']) &&
            isset($encryptionData['hyperwalletKeySetLocation'])) {
            $this->isEncrypted = true;
            $this->encryption = new HyperwalletEncryption($encryptionData['clientPrivateKeySetLocation'], $encryptionData['hyperwalletKeySetLocation']);
        }
    }

    /**
     * Do a POST call to the Hyperwallet API server
     *
     * @param string $partialUrl The url template
     * @param array $uriParams The url template parameters
     * @param BaseModel $data The data to submit
     * @param array $query Query parameters
     * @param array $headers Additional headers
     * @return array
     *
     * @throws HyperwalletApiException
     */
    public function doPost($partialUrl, array $uriParams, BaseModel $data = null, array $query = array(), array $headers = array()) {
        return $this->doRequest('POST', $partialUrl, $uriParams, array(
            'query' => $query,
            'body' => $data ? \GuzzleHttp\json_encode($data->getPropertiesForCreate(), JSON_FORCE_OBJECT) : '{}',
            'headers' => array_merge($headers, array(
                'Content-Type' => 'application/json'
            ))
        ));
    }

    /**
     * Do a PUT call to the Hyperwallet API server
     *
     * @param string $partialUrl The url template
     * @param array $uriParams The url template parameters
     * @param BaseModel $data The data to update
     * @param array $query Query parameters
     * @return array
     *
     * @throws HyperwalletApiException
     */
    public function doPut($partialUrl, array $uriParams, BaseModel $data, array $query) {
        return $this->doRequest('PUT', $partialUrl, $uriParams, array(
            'query' => $query,
            'body' => \GuzzleHttp\json_encode($data->getPropertiesForUpdate(), JSON_FORCE_OBJECT),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
    }

    /**
     * Do a GET call to the Hyperwallet API server
     *
     * @param string $partialUrl The url template
     * @param array $uriParams The url template parameters
     * @param array $query Query parameters
     * @return array
     *
     * @throws HyperwalletApiException
     */
    public function doGet($partialUrl, array $uriParams, array $query) {
        return $this->doRequest('GET', $partialUrl, $uriParams, array(
            'query' => $query
        ));
    }

    /**
     * Execute API call and map error messages
     *
     * @param string $method The http method
     * @param string $url The url template
     * @param array $urlParams The url template parameters
     * @param array $options The request options
     * @return array
     *
     * @throws HyperwalletApiException
     */
    private function doRequest($method, $url, array $urlParams, array $options) {
        try {
            $uri = new UriTemplate();
            if (!isset($options['headers'])) {
                $options[] = array('headers' => array());
            }
            $options['headers']['Accept'] = 'application/json';
            if ($this->isEncrypted) {
                $options['headers']['Accept'] = 'application/jose+json';
                $options['headers']['Content-Type'] = 'application/jose+json';
                if (isset($options['body'])) {
                    $options['body'] = $this->encryption->encrypt(json_decode($options['body'], true));
                }
            }
            $response = $this->client->request($method, $uri->expand($url, $urlParams), $options);
            if ($response->getStatusCode() === 204) {
                return array();
            }
            $this->checkResponseHeaderContentType($response);
            $body = $this->isEncrypted ? \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($this->encryption->decrypt($response->getBody())), true) :
                \GuzzleHttp\json_decode($response->getBody(), true);
            if (isset($body['links'])) {
                unset($body['links']);
            }
            return $body;
        } catch (ConnectException $e) {
            $errorResponse = new ErrorResponse(0, array('errors' => array(
                array(
                    'message' => 'Could not communicate with ' . $this->client->getConfig('base_uri'),
                    'code' => 'COMMUNICATION_ERROR'
                )
            )));
            throw new HyperwalletApiException($errorResponse, $e);
        } catch (BadResponseException $e) {
            $body = \GuzzleHttp\json_decode($e->getResponse()->getBody(), true);
            $errorResponse = new ErrorResponse($e->getResponse()->getStatusCode(), $body);
            throw new HyperwalletApiException($errorResponse, $e);
        }
    }

    /**
     * Checks whether Content-Type header is valid in response
     *
     * @param string $response Response to be checked
     *
     * @throws HyperwalletException
     */
    private function checkResponseHeaderContentType($response) {
        $contentTypes = $response->getHeader('Content-Type');
        if (empty($contentTypes) || ((!$this->isEncrypted && $contentTypes[0] != 'application/json') ||
            ($this->isEncrypted && $contentTypes[0] != 'application/jose+json'))) {
             throw new HyperwalletException('Invalid Content-Type specified in Response Header');
        }
    }

}
