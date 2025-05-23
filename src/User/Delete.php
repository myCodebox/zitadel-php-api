<?php

namespace ZitadelPhpApi\User;

use Exception;

/**
 * Class to delete a user.
 * The state of the user will be changed to 'deleted'.
 * The user will not be able to log in anymore.
 * Endpoints requesting this user will return an error 'User not found'
 */
class Delete
{
    private array $settings;
    private int $userid;

    /** 
     * Initialize the user deletion
     * 
     * @param $settings array The settings array
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /** 
     * Set the user ID of the user you want to delete
     * 
     * @param $userid int The id of the user
     * @return void
     */
    public function setUserId(int $userid)
    {
        $this->userid = $userid;
    }

    /** 
     * Deletes the user and sends the request to Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function delete()
    {
        $token = $this->settings["serviceUserToken"];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
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
