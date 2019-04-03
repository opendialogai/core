<?php

namespace OpenDialogAi\Core\Utterances;

/**
 * An object to hold details we can gather about a user sending a message.
 *
 * This might be specific to webchat chat open messages
 */
class User
{
    private $IPAddress;

    private $country;

    private $browserLanguage;

    private $OS;

    private $browser;

    private $timezone;

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
}
