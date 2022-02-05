<?php

namespace App\Controller\App;

use App\Entity\Person;
use App\Entity\Plan;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/app/dashboard', name: 'app_dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        /** @var Person $person */
        $person = $this->getUser();

        $plans = $doctrine->getRepository(Plan::class)->findBy([
            'isEnabled' => true,
        ]);

        $billingHistories = $person->getBilling()->getBillingHistories()->slice(0, 5);

        return $this->render('app/dashboard.html.twig', [
            'billing_histories' => $billingHistories,
            'plans' => $plans,
        ]);
    }
}
