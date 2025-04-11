<?php

namespace ZitadelPhpApi\User;

use Exception;

/**
 * Class to handle the avatar of a user
 */
class Avatar
{
    private array $settings;
    private int $userid;
    private String $boundary;
    private String $postData;

    /** 
     * Initialize the Email OTP setup
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
     * Set the image for the avatar
     *
     * @param string $avatar Path to the image file
     *
     * @return void
     * @throws Exception if the image file can't be read
     */
    public function setImagePath(string $avatar): void
    {
        if (!file_exists($avatar)) {
            throw new Exception("Image file not found at $avatar");
        }

        $imageData = file_get_contents($avatar);
        if ($imageData === false) {
            throw new Exception("Could not read image file at $avatar");
        }

        $imageType = mime_content_type($avatar);

        if ($imageType === false) {
            throw new Exception("Could not determine the MIME type of the image file at $avatar");
        }

        $boundary = uniqid();

        $postData = "--$boundary\r\n";
        $postData .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename($avatar) . "\"\r\n";
        $postData .= "Content-Type: $imageType\r\n\r\n";
        $postData .= $imageData . "\r\n";
        $postData .= "--$boundary--\r\n";

        $this->boundary = $boundary;
        $this->postData = $postData;
    }

    /**
     * Add the avatar to the user. Needs the userToken
     * 
     * @throws Exception If there's an error
     * @return void
     */
    public function add()
    {
        $token = $this->settings["userToken"];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/assets/v1/users/me/avatar",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $this->postData,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data; boundary=$this->boundary",
                "Content-Length: " . strlen($this->postData),
                "Authorization: Bearer $token"
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if (isset($response->code)) {
            throw new Exception("Error-Code: " . $response->code . " Message: " . $response->message);
        }
    }

    /**
     * Remove the user's avatar.
     *
     * @return void
     * @throws Exception If an error occurs during the request to remove the avatar from the server.
     */
    public function remove()
    {
        $token = $this->settings["serviceUserToken"];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/management/v1/users/$this->userid/avatar",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => "{}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
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
