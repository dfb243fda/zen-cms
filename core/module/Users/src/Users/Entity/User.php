<?php

namespace Users\Entity;

class User implements UserInterface
{
    protected $userData = array();
    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->userData['id'];
    }

    /**
     * Set id.
     *
     * @param int $id
     * @return UserInterface
     */
    public function setId($id)
    {
        $this->userData['id'] = (int) $id;
        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userData['user_name'];
    }

    /**
     * Set username.
     *
     * @param string $username
     * @return UserInterface
     */
    public function setUsername($userName)
    {
        $this->userData['user_name'] = $userName;
        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->userData['email'];
    }

    /**
     * Set email.
     *
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email)
    {
        $this->userData['email'] = $email;
        return $this;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->userData['display_name'];
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     * @return UserInterface
     */
    public function setDisplayName($displayName)
    {
        $this->userData['display_name'] = $displayName;
        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->userData['password'];
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password)
    {
        $this->userData['password'] = $password;
        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->userData['state'];
    }

    /**
     * Set state.
     *
     * @param int $state
     * @return UserInterface
     */
    public function setState($state)
    {
        $this->userData['state'] = $state;
        return $this;
    }
    
    public function getObjectId()
    {
        return $this->userData['object_id'];
    }
    
    public function setObjectId($objectId)
    {
        $this->userData['object_id'] = $objectId;
        return $this;
    }
    
    public function toArray()
    {
        return $this->userData;
    }
    
    public function setData($data)
    {
        $this->userData = $data;
        return $this;
    }
}
