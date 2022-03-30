<?php

namespace App\Controller;

use App\Service\UserListProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();
        $apiToken = $currentUser->getApiTokens()[0]->getToken();

        //$userListProvider = new UserListProvider();
        $userListProvider = new UserListProvider();
        $users = $userListProvider->getUsersByAPIToken($apiToken);

        return $this->render(
            'users/users.html.twig',
            ['users' => $users]
        );
    }
}
