<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;

class DeleteUser extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->usersModel = new Users($this->rootServiceLocator);     
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        $userId = $this->request->getPost('id');
        if (null === $userId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $userId = (int)$userId;
        
        if ($this->usersModel->delete($userId)) {
            $result['success'] = true;
            $result['msg'] = 'Пользователь успешно удален';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не удалось удалить пользователя';
        }
        
        return $result;             
    }
}