<?php

namespace App\Controller\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiController extends AbstractController
{
    #[Route('/auth/auth_token', name: 'security_api')]
    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager): Response
    {
        return $this->render('security/api/auth_token.html.twig', [
            'token' => $JWTManager->create($user),
        ]);
    }
}
