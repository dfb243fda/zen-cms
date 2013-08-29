<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;

class DeleteUser extends AbstractMethod
{
    public function main()
    {
        $usersModel = new Users($this->serviceLocator);    
        
        $result = array(
            'success' => false,
        );
        
        $userId = $this->params()->fromPost('id');
        if (null === $userId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $userId = (int)$userId;
        
        if ($usersModel->delete($userId)) {
            $result['success'] = true;
            $result['msg'] = 'Пользователь успешно удален';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не удалось удалить пользователя';
        }
        
        return $result;             
    }
}