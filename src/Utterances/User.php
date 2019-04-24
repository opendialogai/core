<?php

namespace OpenDialogAi\Core\Utterances;

/**
 * An object to hold details we can gather about a user sending a message.
 *
 * This might be specific to webchat chat open messages
 */
class User
{
    private $firstName;

    private $lastName;

    private $email;

    private $externalId;

    private $IPAddress;

    private $country;

    private $browserLanguage;

    private $OS;

    private $browser;

    private $timezone;

    private $custom;


    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return bool
     */
    public function hasFirstName(): bool
    {
        if (isset($this->firstName)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return bool
     */
    public function hasLastName(): bool
    {
        if (isset($this->lastName)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function hasEmail(): bool
    {
        if (isset($this->email)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param mixed $externalId
     */
    public function setExternalId($externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return bool
     */
    public function hasExternalId(): bool
    {
        if (isset($this->externalId)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getIPAddress()
    {
        return $this->IPAddress;
    }

    /**
     * @param mixed $IPAddress
     */
    public function setIPAddress($IPAddress): void
    {
        $this->IPAddress = $IPAddress;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getBrowserLanguage()
    {
        return $this->browserLanguage;
    }

    /**
     * @param mixed $browserLanguage
     */
    public function setBrowserLanguage($browserLanguage): void
    {
        $this->browserLanguage = $browserLanguage;
    }

    /**
     * @return mixed
     */
    public function getOS()
    {
        return $this->OS;
    }

    /**
     * @param mixed $OS
     */
    public function setOS($OS): void
    {
        $this->OS = $OS;
    }

    /**
     * @return mixed
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @param mixed $browser
     */
    public function setBrowser($browser): void
    {
        $this->browser = $browser;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getCustomParameters()
    {
        return $this->custom;
    }

    /**
     * @param mixed $custom
     */
    public function setCustomParameters($custom): void
    {
        $this->custom = $custom;
    }

    /**
     * @return bool
     */
    public function hasCustomParameters(): bool
    {
        if (isset($this->custom)) {
            return true;
        }

        return false;
    }
}
