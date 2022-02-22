<?php

namespace App\Controller\Api;

use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountController extends AbstractController
{
    #[Route('/api/account', name: 'api_account')]
    public function index(UserInterface $user): Response
    {
        /** @var Person $user */
        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['person', 'billing']]);
    }
}
