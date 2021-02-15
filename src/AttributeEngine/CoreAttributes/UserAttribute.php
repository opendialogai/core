<?php

namespace OpenDialogAi\AttributeEngine\CoreAttributes;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Components\ODComponentTypes;

class UserAttribute extends BasicCompositeAttribute
{
    protected static ?string $componentId = 'attribute.core.user';
    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    public const CURRENT_USER = 'current_user';
    public const USER_ID = 'user_id';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const COUNTRY = 'country';
    public const EMAIL = 'email';
    public const EXTERNAL_ID = 'external_id';
    public const IP_ADDRESS = 'ip_address';
    public const BROWSER_LANGUAGE = 'browser_language';
    public const OS = 'os';
    public const BROWSER = 'browser';
    public const TIMEZONE = 'timezone';
    public const CUSTOM = 'custom_parameters';
    public const LAST_RECORD = 'last_record';
    public const USER_HISTORY_RECORD = 'history_record';


    public function setUserAttribute(string $type, $value)
    {
        // If the $value is not a rawValue but already an attribute just add it
        if ($value instanceof Attribute) {
            $this->addAttribute($value);
            return $this;
        }

        $attribute = AttributeResolver::getAttributeFor($type, $value);
        $this->addAttribute($attribute);
        return $this;
    }

    public function getUserAttribute(string $type)
    {
        if ($this->hasAttribute($type)) {
            $attribute = $this->getAttribute($type);
            if ($attribute instanceof CompositeAttribute) {
                return $attribute;
            } elseif ($attribute instanceof ScalarAttribute) {
                return $this->getAttribute($type)->getValue();
            }
        }

        // @todo - might make more sense to return null or through an exception but for now
        // going down the more permissive path
        return '';
    }

    public function setUserHistoryRecord(UserHistoryRecord $record)
    {
        $this->setUserAttribute(self::USER_HISTORY_RECORD, $record);
    }

    public function getUserHistoryRecord(): ?UserHistoryRecord
    {
        return $this->getUserAttribute(self::USER_HISTORY_RECORD);
    }

    public function setUserId($value)
    {
        return $this->setUserAttribute(self::USER_ID, $value);
    }

    public function getUserId()
    {
        return $this->getUserAttribute(self::USER_ID);
    }

    public function setIPAddress($value)
    {
        return $this->setUserAttribute(self::IP_ADDRESS, $value);
    }

    public function getIPAddress()
    {
        return $this->getUserAttribute(self::IP_ADDRESS);
    }

    public function setCountry($value)
    {
        return $this->setUserAttribute(self::COUNTRY, $value);
    }

    public function getCountry()
    {
        return $this->getUserAttribute(self::COUNTRY);
    }

    public function setBrowserLanguage($value)
    {
        return $this->setUserAttribute(self::BROWSER_LANGUAGE, $value);
    }

    public function getBrowserLanguage()
    {
        return $this->getUserAttribute(self::BROWSER_LANGUAGE);

    }

    public function setOS($value)
    {
        return $this->setUserAttribute(self::OS, $value);
    }

    public function getOS()
    {
        return $this->getUserAttribute(self::OS);
    }

    public function setBrowser($value)
    {
        return $this->setUserAttribute(self::BROWSER, $value);
    }

    public function getBrowser()
    {
        return $this->getUserAttribute(self::BROWSER);
    }

    public function setTimezone($value)
    {
        return $this->setUserAttribute(self::TIMEZONE, $value);
    }

    public function getTimezone()
    {
        return $this->getUserAttribute(self::TIMEZONE);
    }

    public function setFirstName($value)
    {
        return $this->setUserAttribute(self::FIRST_NAME, $value);
    }

    public function getFirstName()
    {
        return $this->getUserAttribute(self::FIRST_NAME);
    }

    public function setLastName($value)
    {
        return $this->setUserAttribute(self::LAST_NAME, $value);
    }

    public function getLastName()
    {
        return $this->getUserAttribute(self::LAST_NAME);
    }

    public function setEmail($value)
    {
        return $this->setUserAttribute(self::EMAIL, $value);
    }

    public function getEmail()
    {
        return $this->getUserAttribute(self::EMAIL);
    }

    public function setExternalId($value)
    {
        return $this->setUserAttribute(self::EXTERNAL_ID, $value);
    }

    public function getExternalId()
    {
        return $this->getUserAttribute(self::EXTERNAL_ID);
    }

    public function setCustomParameters($value)
    {
        return $this->setUserAttribute(self::CUSTOM, $value);
    }

    public function getCustomParameters()
    {
        return $this->getUserAttribute(self::CUSTOM);
    }

    public function hasConversationRecord(): bool
    {
        if ($this->hasAttribute(self::LAST_RECORD)) {
            return true;
        }

        return false;
    }

}
