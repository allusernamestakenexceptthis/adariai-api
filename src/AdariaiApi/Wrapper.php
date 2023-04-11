<?php
/**
 * Wrapper to connect to OpenAI API
 *
 * PHP version 7.4.3
 *
 * @category AdariAI
 * @package  AdariaiApi
 * @author   Ari Adari <admin@ariadari.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @version  GIT:1.0.0
 * @link     http://aridadari.com http://gomilkyway.com
 */

namespace AdariaiApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;


/**
 * Api wrapper class
 *
 * @category AdariAI
 * @package  AdariaiApi
 * @author   Ari Adari <admin@ariadari.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     http://aridadari.com http://gomilkyway.com
 * @see      https://openai.com/docs/api-reference/completions/create
 */
class Wrapper
{
    /**
     * The API version
     *
     * @var string
     */
    public $apiVersion = "v1";

    /**
     * The API base URL
     *
     * @var string
     */
    public $apiBaseUrl = "https://api.openai.com/";

    /**
     * The API client
     *
     * @var Client
     */
    private $_client;

    /**
     * The API response headers
     *
     * @var array
     */
    private $_response_headers;

    /**
     * Work in progress do not use
     * send messages instead see message_with_history example
     * 
     * @var boolean
     */
    public $shouldSendHistory = true;

    /**
     * Work in progress do not use
     * Holds the maximum number of history turns
     * 
     * @var number
     */
    private $_maxHistoryTurns = 3;

    /**
     * Work in progress do not use
     * Holds the history
     * 
     * @var array
     */
    private $_history = [];

    /**
     * Indicates whether we should stream the response or not
     * 
     * @var boolean
     */
    private $_streamIt = false;

    /**
     * Class constructor
     *
     * @param string $apiKey The API key
     */
    public function __construct(string $apiKey)
    {
        $this->_client = new Client(
            [
                'base_uri' => $this->apiBaseUrl.$this->apiVersion.'/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    /**
     * Set the API response to streaming or not
     *
     * @param boolean $value The value to set
     *
     * @return void
     */
    public function setToStream($value = true)
    {
        $this->_streamIt = $value;
    }

    /**
     * Get the API response
     *
     * @param string $model   The model to use
     * @param string $prompt  The prompt to use
     * @param array  $options The options to use
     *
     * @return array
     */
    public function getCompletion(string $model, string $prompt, array $options = [])
    {
        $json = [
            'model' => $model,
            'prompt' => $prompt,
        ];

        
        try {
            $type = "completions";
            if (isset($options['type'])) {
                $type = $options['type'];
                unset($options['type']);
            }
            switch ($type) {
            case "search":
            case "classify":
            case "completions":
                break;
            default:
                throw new \BadFunctionCallException("Invalid type");
            }

            if (!empty($options)) {
                $json = array_merge($json, $options);
            }

            if ($this->_streamIt) {
                $json['stream'] = true;
            }
    
            $response = $this->_client->post(
                $type,
                ['json' => $json, 'stream' => $this->_streamIt]
            );
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get the API response
     *
     * @param string $model    The model to use
     * @param string $messages The messages to use
     * @param array  $options  The options to use
     *
     * @return \Psr\Http\Message\StreamInterface | array
     */
    public function getChat(string $model, /*mixed*/ $messages, array $options = [])
    {
        if (!is_array($messages)) {
            $messages = [
                ["role" => "system", "content" => "You are a helpful assistant."],
                ["role" => "user", "content" => $messages]
            ];
        }
        $json = [
            'model' => $model,
            'messages' => $messages,
        ];

        if (!empty($options)) {
            $json = array_merge($json, $options);
        }


        if ($this->_streamIt) {
            $json['stream'] = true;
        }

        try {
            $type = "chat/completions";
            $response = $this->_client->post(
                $type,
                [
                    'json' => $json,
                    'stream' => $this->_streamIt,
                    'on_headers' => function (ResponseInterface $response) {
                        $this->_response_headers = $response->getHeaders();
                    }
                ]
            );
           
            if ($this->_streamIt) {
                return $response->getBody();
            }
            $ret = json_decode($response->getBody(), true);
            if (!empty($ret['error'])) {
                $this->_handleErrorResponse(
                    $response->getBody(),
                    $response->getStatusCode(),
                    $json
                );
            }
            return $ret;
        } catch (ClientException $e) {
            $this->_handleErrorResponse($e->getMessage(), $e->getCode(), $json);
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Work in progress, do not use, mnake api request
     * TODO: make it universal call to other types of calls
     * 
     * @param string $type The type of call
     * @param array  $json The json to send
     * 
     * @return array The response
     */
    public function mainApiCall(string $type, array $json)
    {
        try {
            $response = $this->_client->post($type, ['json' => $json]);
            $resp = json_decode($response->getBody(), true);

            $this->processForHistory($resp);
            return $resp;
        } catch (ClientException $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Work in progress, do not use, sets the history
     * TODO: records history
     * 
     * @param string $resp The response
     * 
     * @return void
     */
    public function processForHistory($resp)
    {
        $this->_history[] = $resp;
        if (count($this->_history) > $this->_maxHistoryTurns) {
            array_shift($this->_history);
        }
    }


    /**
     * Handle log messages
     *
     * @param string $response      The response
     * @param string $response_code The response code
     * @param string $request       The request
     * 
     * @return void
     */
    private function _handleErrorResponse($response, $response_code, $request)
    {

        $headers = $this->getLastResponseHeaders();

        if (!empty($response['error'])) {
            $error = $response['error'];
        } else {
            $error = 'Invalid response from API';
        }

        
        log::error(
            'Received error from OpenAI API: '.$error,
            array(
                'response_code' => $response_code,
                'headers' => $headers,
                'request' => $request
            )
        );

    }

    /**
     * Get the API response headers
     *
     * @return array
     */   
    function getLastResponseHeaders()
    {
        return $this->_response_headers;
    }

}