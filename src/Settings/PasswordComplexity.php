<?php

namespace ZitadelPhpApi\Settings;

use Exception;

/**
 * Class for the management of the password complexity settings
 */
class PasswordComplexity
{
    protected array $settings;
    private ?string $orgId = null;
    private ?int $minLength;
    private bool $rawPasswordSettings;
    private bool $requiresUppercase;
    private bool $requiresLowercase;
    private bool $requiresNumber;
    private bool $requiresSymbol;
    private ?string $resourceOwnerType;

    /** 
     * Initialize the password complexity settings class
     * 
     * @param array $settings The settings array
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Set the organization ID.
     *
     * @param string $orgId The organization ID to set.
     * @return void
     */
    public function setOrgId(string $orgId): void
    {
        $this->orgId = $orgId;
    }

    /**
     * Get the minimum length of the password
     *
     * @return int The minimum length of the password
     */
    public function getminLength(): int
    {
        return $this->minLength;
    }

    /**
     * Returns true if the password requires an uppercase letter.
     *
     * @return bool True if the password requires an uppercase letter, false otherwise.
     */
    public function requiresUppercase(): bool
    {
        return $this->requiresUppercase;
    }

    /**
     * Returns true if the password requires a lowercase letter.
     *
     * @return bool True if the password requires a lowercase letter, false otherwise.
     */
    public function requiresLowercase(): bool
    {
        return $this->requiresLowercase;
    }

    /**
     * Returns true if the password requires a number.
     *
     * @return bool True if the password requires a number, false otherwise.
     */
    public function requiresNumber(): bool
    {
        return $this->requiresNumber;
    }

    /**
     * Returns true if the password requires a symbol.
     *
     * @return bool True if the password requires a symbol, false otherwise.
     */
    public function requiresSymbol(): bool
    {
        return $this->requiresSymbol;
    }

    /**
     * Returns the resource owner type.
     *
     * @return string The resource owner type.
     */
    public function getResourceOwnerType(): string
    {
        return $this->resourceOwnerType;
    }

    /**
     * Returns the raw password settings as a JSON string.
     *
     * @return string The raw password settings as a JSON string.
     */
    public function getRawPasswoerdSettings(): string
    {
        return $this->rawPasswordSettings;
    }

    /**
     * Send a GET request to the ZITADEL API to retrieve the current password complexity settings.
     *
     * @throws Exception If the request fails or the ZITADEL API returns an error.
     * @return void
     */
    public function sendRequest(): void
    {
        $token = $this->settings["serviceUserToken"];
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->settings["domain"] . "/v2/settings/password/complexity?ctx.orgId=" . $this->orgId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
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
            $response = $response->settings;
            $this->rawPasswordSettings = $response ?? "";
            $this->minLength = $response->minLength ?? null;
            $this->requiresUppercase = $response->requiresUppercase ?? "";
            $this->requiresLowercase = $response->requiresLowercase ?? "";
            $this->requiresNumber = $response->requiresNumber ?? "";
            $this->requiresSymbol = $response->requiresSymbol ?? "";
            $this->resourceOwnerType = $response->resourceOwnerType ?? null;
        }
    }
}
