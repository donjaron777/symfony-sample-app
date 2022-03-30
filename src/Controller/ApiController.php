<?php

namespace App\Controller;

use ProxyManager\Factory\RemoteObject\Adapter\JsonRpc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/users", name="api_userlist")
     */

    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $em = $doctrine->getManager();
        $arrayCollection = [];

        $users = $em->getRepository(User::Class)->findBy([], ['id' => 'ASC']);

        foreach ($users as $user) {
            $arrayCollection[] = array(
                'id' => $user->getId(),
                'username' => $user->getUsername()
            );
        }

        return new JsonResponse($arrayCollection);
    }
}
