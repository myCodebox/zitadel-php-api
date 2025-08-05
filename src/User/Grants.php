<?php

namespace ZitadelPhpApi\User;

use Exception;

/**
 * Class to edit user data. 
 * Important: To change the email address, phone number or the password, use the Email or Password Class!
 */
class Grants
{
    protected array $settings;
    private int $userid;
    private string $grantId;
    private array $request;
    private array $activeRoleKeys = [];
    private array $removeRoleKeys = [];

    /** 
     * Initialize the user data change. Important: To change the email address or the password, use the Email or Password Class!
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
     * Set the grant id of the users project
     * 
     * @param $grantId string
     * @return void
     */
    public function setGrantId(string $grantId)
    {
        $this->grantId = $grantId;
    }

    /** 
     * Set the project id for the request
     * 
     * @param $projectId string
     * @return void
     */
    public function setProjectId(string $projectId)
    {
        $this->request["projectId"] = $projectId;
    }

    /** 
     * Set the project grant id for the request
     * Is needed if the user grant is for a granted project and the organization is not the owner of the project.
     * 
     * @param $projectGrantId string
     * @return void
     */
    public function setProjectGrantId(string $projectGrantId)
    {
        $this->request["projectGrantId"] = $projectGrantId;
    }

    /** 
     * Set the grant role
     * 
     * @param $role string
     * @return void
     */
    public function setRoleKey(string $role)
    {
        $this->request["roleKeys"][] = $role;
    }

    /** 
     * remove the grant role
     * 
     * @param $role string
     * @return void
     */
    public function removeRoleKey(string $role)
    {
        $this->removeRoleKeys[] = $role;
    }

    /** 
     * Set given roels to user in Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function grantRoles()
    {
        $roleKeyArray = [];

        // get all roles
        $this->getGrantsByUserId();

        // remove roles
        foreach ($this->activeRoleKeys as $roleKey) {
            if (!in_array($roleKey, $this->removeRoleKeys)) {
                $roleKeyArray[] = $roleKey;
            }
        }

        // add new to existing roleKeys
        $this->request["roleKeys"] = array_unique(array_merge($roleKeyArray, $this->request["roleKeys"] ?? []));

        if (!empty($this->request["roleKeys"])) {

            // set roles for user
            $token = $this->settings["serviceUserToken"];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/$this->userid/grants",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($this->request),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Authorization: Bearer $token"
                )
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            if (isset($response->code) && $response->code != 6) {
                throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
            }
            if (isset($response->code) && $response->code == 6) {
                $this->updateGrantById();
            }
        }
    }

    /** 
     * Set given roels to user in Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function getGrantsByUserId()
    {
        $token = $this->settings["serviceUserToken"];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/grants/_search",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "queries": [
                    {
                        "userIdQuery": {
                            "userId": "' . $this->userid . '"
                        }
                    }
                ]
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Accept: application/json",
                "Authorization: Bearer $token"
            )
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        } else {
            if (isset($response->result)) {
                foreach ($response->result as $grant) {
                    if ($grant->projectId == $this->request["projectId"]) {
                        $this->grantId = $grant->id;

                        if (isset($grant->roleKeys)) {
                            $this->activeRoleKeys = $grant->roleKeys;
                        }
                    }
                }
            }
        }
    }

    /** 
     * Set given roels to user in Zitadel
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function updateGrantById()
    {
        $token = $this->settings["serviceUserToken"];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/$this->userid/grants/$this->grantId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($this->request),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Accept: application/json",
                "Authorization: Bearer $token"
            )
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code) && $response->code != 9) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        }
    }
}
