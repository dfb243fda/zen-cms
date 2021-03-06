<?php

namespace Users\Validator;

class NoRecordExists extends AbstractRecord
{
    public function isValid($value)
    {
        $valid = true;
        $this->setValue($value);

        $result = $this->query($value);
        if ($result) {            
            if (!$this->exclusionUserId || ($this->exclusionUserId != $result->getId()) ) {
                $valid = false;
                $this->error(self::ERROR_RECORD_FOUND);
            }
        }

        return $valid;
    }
}
