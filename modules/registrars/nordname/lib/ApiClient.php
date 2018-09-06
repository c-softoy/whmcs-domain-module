<?php

namespace WHMCS\Module\Registrar\NordName;

/**
 * Simple API Client for communicating with the domain API of NordName.
 */
class ApiClient
{

    protected $results = array();

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
    public function call($method, $action, $getfields, $body = null, $sandbox = false, $op = false)
    {
        $url = '';
        if (!$sandbox) {
            if ($op)
                $url = 'https://c-soft.net/api/v1.1/operation/';
            else
                $url = 'https://c-soft.net/api/v1.1/domain/';
        } else {
            if ($op)
                $url = 'https://c-soft.net/sandbox_api/v1.1/operation/';
            else
                $url = 'https://c-soft.net/sandbox_api/v1.1/domain/';
        }
      
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $action . "?" . http_build_query($getfields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 360);
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
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($body)))
                    );  
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
                break;
            case "PUT":
                if (!empty($body)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($body)))
                    );  
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
        }
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->results = $this->processResponse($response);

        logModuleCall(
            'NordName',
            $action,
            $body,
            $response,
            $this->results,
            array(
                $getfields['api_key'], // Mask username & password in request/response data
            )
        );

        if ($this->results === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Bad response received from API');
        }
      
        if ($httpcode != 200 && $httpcode != 201 && $httpcode != 202) {
          throw new \Exception('API ERROR: ' . $this->results["desc"]);
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