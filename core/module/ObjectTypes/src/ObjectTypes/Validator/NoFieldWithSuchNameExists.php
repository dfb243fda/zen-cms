<?php

namespace ObjectTypes\Validator;

use Zend\Validator\AbstractValidator;
use App\Field\FieldsGroup;

class NoFieldWithSuchNameExists extends AbstractValidator
{
    protected $fieldsGroup;
    
    protected $fieldId;
    
    const ERROR_FIELD_WITH_SUCH_NAME_FOUND = 'field_with_such_name_found';
    
    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_FIELD_WITH_SUCH_NAME_FOUND => "Field with such name was found",
    );
    
    /**
     * Required options are:
     *  - fieldsGroup
     */
    public function __construct(array $options)
    {        
        if (!isset($options['fieldsGroup']) || !$options['fieldsGroup'] instanceof FieldsGroup) {
            throw new Exception\InvalidArgumentException('No fieldsGroup');
        }

        $this->setFieldsGroup($options['fieldsGroup']);
        
        if (isset($options['fieldId'])) {
            $this->setFieldId($options['fieldId']);
        }
        
        parent::__construct($options);
    }
    
    protected function setFieldsGroup($fieldsGroup)
    {
        $this->fieldsGroup = $fieldsGroup;
    }
    
    protected function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
    }
    
    public function isValid($value)
    {        
        $valid = true;
        $this->setValue($value);

        $field = $this->fieldsGroup->getFieldByName($value);
        
        $result = false;
        if ($field) {
            if ($this->fieldId) {
                if ($this->fieldId != $field->getId()) {
                    $result = true;
                }                
            } else {
                $result = true;
            }
        }
        
        if ($result) {
            $valid = false;
            $this->error(self::ERROR_FIELD_WITH_SUCH_NAME_FOUND);
        }

        return $valid;
    }
}
