<?php

namespace ObjectTypes\Validator;

use Zend\Validator\AbstractValidator;
use App\Object\ObjectType;

class NoGroupWithSuchNameExists extends AbstractValidator
{
    protected $objectType;
    
    const ERROR_GROUP_WITH_SUCH_NAME_FOUND = 'group_with_such_name_found';
    
    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_GROUP_WITH_SUCH_NAME_FOUND => "Group with such name was found",
    );
    
    /**
     * Required options are:
     *  - fieldsGroup
     */
    public function __construct(array $options)
    {        
        if (!isset($options['objectType']) || !$options['objectType'] instanceof ObjectType) {
            throw new Exception\InvalidArgumentException('objectType option does not transferred');
        }

        $this->setObjectType($options['objectType']);
        
        parent::__construct($options);
    }
    
    protected function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }
    
    public function isValid($value)
    {        
        $valid = true;
        $this->setValue($value);

        $result = $this->objectType->getFieldsGroupByName($value);
        if ($result) {
            $valid = false;
            $this->error(self::ERROR_GROUP_WITH_SUCH_NAME_FOUND);
        }

        return $valid;
    }
}
