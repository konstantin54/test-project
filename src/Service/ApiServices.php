<?php

namespace App\Service;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Symfony\Component\HttpFoundation\Response;

class ApiServices
{



    //Проверка на доступ к API
    public function checkAccess($request)
    {
    
        $access = false;
        $headers = $request->headers->all();
        if(isset($headers['X-API-User-Name'])){
            if($headers['X-API-User-Name'] == 'admin')  $access = true;
        };

        
        return $access;
        
    }

   




}
