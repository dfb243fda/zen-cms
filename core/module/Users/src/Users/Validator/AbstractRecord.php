<?php

namespace Users\Validator;

use Zend\Validator\AbstractValidator;
use Users\Collection\Users as UsersCollection;

abstract class AbstractRecord extends AbstractValidator
{
    /**
     * Error constants
     */
    const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_NO_RECORD_FOUND => "No record matching the input was found",
        self::ERROR_RECORD_FOUND    => "A record matching the input was found",
    );

    /**
     * @var UsersCollection
     */
    protected $usersCollection;

    /**
     * @var string
     */
    protected $key;

    /**
     * Required options are:
     *  - key     Field to use, 'emial' or 'username'
     */
    public function __construct(array $options)
    {
        if (!array_key_exists('key', $options)) {
            throw new Exception\InvalidArgumentException('No key provided');
        }

        $this->setKey($options['key']);

        parent::__construct($options);
    }

    /**
     * getMapper
     *
     * @return UserInterface
     */
    public function getUsersCollection()
    {
        return $this->usersCollection;
    }

    /**
     * setMapper
     *
     * @param UserInterface $mapper
     * @return AbstractRecord
     */
    public function setUsersCollection(UsersCollection $usersCollection)
    {
        $this->usersCollection = $usersCollection;
        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Grab the user from the mapper
     *
     * @param string $value
     * @return mixed
     */
    protected function query($value)
    {
        $result = false;

        switch ($this->getKey()) {
            case 'email':
                $result = $this->getUsersCollection()->getUserByEmail($value);
                break;

            case 'user_name':
                $result = $this->getUsersCollection()->getUserByName($value);
                break;

            default:
                throw new \Exception('Invalid key used in Users validator');
                break;
        }

        return $result;
    }
}
