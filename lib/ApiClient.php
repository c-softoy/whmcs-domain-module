<?php

namespace WHMCS\Module\Registrar\NordName;

class NotFoundException extends \Exception {}

/**
 * Simple API Client for communicating with the domain API of NordName.
 */
class ApiClient
{

    protected $results = array();
    protected $apiKey = '';
    protected $sandbox = false;

    public function __construct($apiKey, $sandbox = false)
    {
        $this->apiKey = $apiKey;
        $this->sandbox = boolval($sandbox);
    }

    /**
     * Make external API call to registrar API.
     *
     * @param string $action
     * @param array $postfields
     *
     * @throws \Exception Connection error
     * @throws \Exception Bad API response
     *
     * @return array
     */
    public function call($method, $action, $getfields = array(), $body = null)
    {
        $url = '';
        if (!$this->sandbox) {
            $url = 'https://api.nordname.fi/api/v3/';
        } else {
            $url = 'https://api.ote.nordname.fi/api/v3/';
        }
      
        $ch = curl_init();
        $queryString = '';
        if (!empty($getfields)) {
            $queryString = '?' . http_build_query($getfields);
        }
        $targetUrl = $url . $action . $queryString;
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 360);
        $headers = array('X-Module-Version: 3.0', 'Accept: application/json');
        if (!empty($this->apiKey)) {
            $headers[] = 'Authorization: token ' . $this->apiKey;
        }
        switch ($method) {
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PATCH":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                break;
            case "POST":
                if (!empty($body)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen(json_encode($body));  
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
                break;
            case "PUT":
                if (!empty($body)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen(json_encode($body));
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->results = $this->processResponse($response);

        logModuleCall(
            'NordName',
            $targetUrl,
            $body,
            $response,
            $this->results,
            array(
                $this->apiKey,
            )
        );

        if ($this->results === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Bad response received from API');
        }

        if ($httpcode == 404) {
            throw new NotFoundException('Resource not found');
        }
      
        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 202) {
          throw new \Exception('Registrar API Error: ' . $this->results["detail"]);
        }

        return $this->results;
    }

    /**
     * Process API response.
     *
     * @param string $response
     *
     * @return array
     */
    public function processResponse($response)
    {
        return json_decode($response, true);
    }

    /**
     * Get from response results.
     *
     * @param string $key
     *
     * @return string
     */
    public function getFromResponse($key)
    {
        return isset($this->results[$key]) ? $this->results[$key] : '';
    }
}
