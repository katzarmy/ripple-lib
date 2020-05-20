<?php

namespace Kilvn\RippleAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RippleRpcClient
{
    /**
     * RPC NODE URL
     *
     * @var string
     */
    protected string $url;

    /**
     * Guzzle Http Client
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Number of requests
     *
     * @var integer
     */
    protected int $requestCount = 0;

    /**
     * Create a new RippleClient object
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
        $config['base_uri'] = $url;

        $this->client = new Client($config);
    }

    /**
     * send request
     * @param $method
     * @param $params
     * @return array
     */
    public function __call($method, $params)
    {
        $this->requestCount++;

        try {
            $body = [
                'id' => $this->requestCount,
                'method' => $method,
                'json_rpc' => '2.0',
            ];

            if (is_array($params) && count($params)) {
                $body['params'] = array_values($params);  // no keys
            }

            $response = $this->client->request('POST', $this->url, [
                'body' => json_encode($body)
            ]);

            return $this->toArray($response->getBody()->getContents());
        } catch (GuzzleException $exception) {
            return ['result' => 'error', 'message' => $exception->getMessage()];
        }
    }

    /**
     * Convert any response to an array
     *
     * @param $data
     * @return array|bool|mixed|null
     */
    public function toArray($data)
    {
        $decodedBody = json_decode($data, true);

        if ($decodedBody === null) {
            $decodedBody = [];
        } elseif (is_bool($decodedBody)) {
            $decodedBody = ['success' => $decodedBody];
        }

        if (!is_array($decodedBody)) {
            $decodedBody = [];
        }

        return $decodedBody;
    }
}
