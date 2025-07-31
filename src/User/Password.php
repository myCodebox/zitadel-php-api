<?php

namespace ZitadelPhpApi\User;

use Exception;

/**
 * Class to manage Passwords
 */
class Password
{
    protected array $settings;
    private int $userid;
    private array $request;
    private string $verifyCode;

    /** 
     * Initialize the Password class
     * 
     * @param $settings array The settings array
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /** 
     * Set the userid
     * 
     * @param $userid int User id
     * @return void
     */
    public function setUserId(int $userid)
    {
        $this->userid = $userid;
    }

    /**
     * Set the verification code for the password change
     * 
     * @param string $verifyCode Verification code
     * @return void
     */
    public function setVerifyCode(string $verifyCode)
    {
        $this->request["verificationCode"] = $verifyCode;
    }

    /**
     * Set the current password for the password change
     * 
     * @param string $currentPassword Current password
     * @return void
     */
    public function setCurrentPassword(string $currentPassword)
    {
        $this->request["currentPassword"] = $currentPassword;
    }

    /**
     * Set the new password for the password change
     * 
     * @param string $newPassword New password
     * @param boolean $changeRequired Change required flag
     * @return void
     */
    public function setNewPassword(string $newPassword, boolean $changeRequired)
    {
        $this->request["newPassword"] = array(
            "password" => $newPassword,
            "changeRequired" => $changeRequired
        );
    }

    /**
     * Retrieve the stored verification code.
     * 
     * @return string The verification code.
     */
    public function getVerifyCode(): string
    {
        return $this->verifyCode;
    }

    /**
     * Change the password
     * 
     * @return bool Returns true on success, false on failure
     */
    public function change(): bool
    {
        $token = $this->settings["serviceUserToken"];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid/password",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($this->request),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer $token"
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code)) {
            return true;
        }

        return false;
    }

    /**
     * Request a verification code for password reset
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function requestVerifyCode()
    {
        $token = $this->settings["serviceUserToken"];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid/password_reset",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "{
                \"returnCode\": {}
            }",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer $token"
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        } else {
            $this->verifyCode = $response->verificationCode;
        }
    }

    /**
     * Request reset link for password reset
     * 
     * @return void
     * @throws Exception Returns an exception with an error code and a message if the communication with Zitadel fails
     */
    public function sendResetLink()
    {
        $token = $this->settings["serviceUserToken"];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/v2/users/$this->userid/password_reset",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "{
            \"sendLink\": {
                \"notificationType\": \"NOTIFICATION_TYPE_Unspecified\"
            }
        }",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer $token"
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        }
    }
}
