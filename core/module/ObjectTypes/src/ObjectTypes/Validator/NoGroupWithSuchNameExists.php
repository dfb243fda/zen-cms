<?php

namespace ObjectTypes\Validator;

use Zend\Validator\AbstractValidator;
use App\Field\FieldsGroupCollection;

class NoGroupWithSuchNameExists extends AbstractValidator
{
    protected $fieldsGroupCollection;
    
    protected $groupId;
    
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
        if (!isset($options['fieldsGroupCollection']) || !$options['fieldsGroupCollection'] instanceof FieldsGroupCollection) {
            throw new Exception\InvalidArgumentException('fieldsGroup option does not transferred');
        }

        $this->setFieldsGroupCollection($options['fieldsGroupCollection']);
        
        if (isset($options['groupId'])) {
            $this->setGroupId($options['groupId']);
        }
        
        parent::__construct($options);
    }
    
    protected function setFieldsGroupCollection($fieldsGroupCollection)
    {
        $this->fieldsGroupCollection = $fieldsGroupCollection;
    }
    
    protected function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }
    
    public function isValid($value)
    {        
        $valid = true;
        $this->setValue($value);

        $group = $this->fieldsGroupCollection->getFieldsGroupByName($value);
        
        $result = false;
        if ($group) {
            if ($this->groupId) {
                if ($this->groupId != $group->getId()) {
                    $result = true;
                }                
            } else {
                $result = true;
            }
        }
        
        if ($result) {
            $valid = false;
            $this->error(self::ERROR_GROUP_WITH_SUCH_NAME_FOUND);
        }

        return $valid;
    }
}
