<?php namespace App;

/**
 * A repository agnostic Contact entity
 */
class Contact
{

    public $custom1;
    public $custom2;
    public $custom3;
    public $custom4;
    public $custom5;
    public $email;
    public $externalId;
    public $firstName;
    public $id;
    public $lastName;
    public $phone;

    public function getCustom1()
    {
        return $this->custom1;
    }

    public function setCustom1($custom1)
    {
        $this->custom1 = $custom1;
    }

    public function getCustom2()
    {
        return $this->custom2;
    }

    public function setCustom2($custom2)
    {
        $this->custom2 = $custom2;
    }

    public function getCustom3()
    {
        return $this->custom3;
    }

    public function setCustom3($custom3)
    {
        $this->custom3 = $custom3;
    }

    public function getCustom4()
    {
        return $this->custom4;
    }

    public function setCustom4($custom4)
    {
        $this->custom4 = $custom4;
    }

    public function getCustom5()
    {
        return $this->custom5;
    }

    public function setCustom5($custom5)
    {
        $this->custom5 = $custom5;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}
