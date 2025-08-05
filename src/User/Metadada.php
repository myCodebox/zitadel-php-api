<?php

namespace ZitadelPhpApi\User;

use Exception;

/**
 * Class to get, set & delete user metadata. 
 */
class Metadata
{
    protected array $settings;
    private int $userid;
    private array $set_request;
    private array $list_request;
    private array $delete_request;
    private array $metadata;

    /** 
     * Initialize the user metadata change.
     * 
     * @param $settings array The settings array
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /** 
     * Set the user id of the user
     * 
     * @param $userid int The user id of the user
     * @return void
     */
    public function setUserId(int $userid)
    {
        $this->userid = $userid;
    }
    
    /** 
     * Returns the user metadata
     * 
     * @return array
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function getMetaData() : array
    {
        return $this->metadata;
    }

    /**
     * Add Metadata to the user Profile (optional). The value will be automatically Base64 encoded.
     * 
     * @param $key string Key
     * @param $value string Value
     * @return void
     */
    public function addMetaData(string $key, string $value): void
    {
        $this->set_request["metadata"][] = [
            "key" => $key,
            "value" => base64_encode($value)
        ];
    }
    
    /**
     * Add Metadata Key for deletion
     * 
     * @param $key string Key
     * @return void
     */
    public function queryMetaDataKey(string $key): void
    {
        // API v2
        // $this->delete_request["metadata"][] = [
        //     "key" => $key
        // ];

        $this->delete_request["keys"][] = $key;
    }
    
    /**
     * Add pagination to metadata list
     * 
     * @param $offset int 
     * @param $limit int
     * @param $asc bool
     * @return void
     */
    public function setPagination(int $offset = 0, int $limit = 0, bool $asc = false): void
    {
        if($limit > 0){
            // $this->list_request["pagination"] = [
            //     "offset" => $offset,
            //     "limit" => $limit,
            //     "asc" => $asc
            // ];
            $this->list_request["query"] = [
                "offset" => $offset,
                "limit" => $limit,
                "asc" => $asc
            ];
        }
    }
    
    /**
     * Add filter to metadata list
     * 
     * @param $key string 
     * @param $offset string
     * @return void
     */
    public function setFilters(string $key, string $method = "TEXT_QUERY_METHOD_EQUALS"): void
    {
        // $this->list_request["filters"][] = [
        //     "keyFilter" => [
        //         "key" => $key,
        //         "method" => $method,
        //     ]
        // ];
        $this->list_request["queries"][] = [
            "keyQuery" => [
                "key" => $key,
                "method" => $method,
            ]
        ];
    }

    /** 
     * Requests the user metadata from Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function requestMetaData()
    {
        $token = $this->settings["serviceUserToken"];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid/metadata",
            CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/$this->userid/metadata/_search",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => (isset($this->list_request)) ? json_encode($this->list_request) : "{}",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Authorization: Bearer $token"
            )
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        
        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        }else{
            // $this->metadata = $response->metadata;
            $this->metadata = $response->result ?? [];
        }
    }

    /** 
     * Set & Change the user metadata in Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function setMetaData()
    {
        $token = $this->settings["serviceUserToken"];
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid/metadata",
            CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/$this->userid/metadata/_bulk",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($this->set_request),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer $token"
            )
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);        

        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        }
    }

    /** 
     * Delete the user metadata from Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function deleteMetaData()
    {
        $token = $this->settings["serviceUserToken"];
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid/metadata",
            CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/$this->userid/metadata/_bulk",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => json_encode($this->delete_request),
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Authorization: Bearer $token"
            )
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        
        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        }
    }
}