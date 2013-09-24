<?php

namespace Users\Method;

use App\Method\AbstractMethod;

class UsersList extends AbstractMethod
{
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $usersList = $this->serviceLocator->get('Users\Service\UsersList');
            
            $result = $usersList->getItems();
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Users/users_list.phtml',
                ),
            );
        }
        return $result;
    }    
}