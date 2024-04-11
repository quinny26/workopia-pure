<?php
namespace Framework;

use Framework\Session;

class Authorization{
    /**
     * CHECK IF CURRENT LOGIN USER OWNS A RESOURCE
     * 
     * @param int $resourceId
     * @return bool
     */

     public static function isOwner($resourceId){
        $sessionUser = Session::get('user');

        if($sessionUser !== null && isset($sessionUser['id'])){
            $sessionUserId = (int) $sessionUser['id'];
            return $sessionUserId === $resourceId;
        }

        return false;
     }
}