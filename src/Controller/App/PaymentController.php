<?php

namespace App\Controller\App;

use App\Entity\Billing;
use App\Entity\BillingHistory;
use App\Entity\Payment;
use App\Entity\Person;
use App\Entity\Plan;
use Carbon\CarbonImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Happyr\Validator\Constraint\EntityExist;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Payum\ISO4217\ISO4217;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends AbstractController
{
    private $currency;
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->currency = (new ISO4217())->findByAlpha3('USD');
        $this->validator = $validator;
    }

    #[Route('/app/payment/buy-subscription', name: 'app_payment_buy_subscription', methods: ['POST'])]
    public function buySubscription(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $subscription = $request->request->get('subscription');

        $validation = $this->validator->validate($subscription, [
            new Assert\NotBlank(),
            new EntityExist(['entity' => Plan::class, 'property' => 'slug', 'message' => 'This choosen plan does not exist.']),
        ]);

        if (count($validation) > 0) {
            $this->addFlash(
                'subscription_notice',
                $validation->get(0)->getMessage()
            );

            return $this->redirectToRoute('app_dashboard');
        }

        /** @var Plan $plan */
        $plan = $doctrine->getRepository(Plan::class)->findOneBy([
            'slug' => $subscription,
        ]);

        /** @var Billing $billing */
        $billing = $this->getUser()->getBilling();

        if ($plan->getPrice() > $billing->getCredit()) {
            $this->addFlash(
                'subscription_notice',
                'You do not have enough credit to buy this plan'
            );

            return $this->redirectToRoute('app_dashboard');
        }

        $expiredAt = CarbonImmutable::instance($billing->getExpiredAt() ?? CarbonImmutable::now());

        $billing->setCredit($billing->getCredit() - $plan->getPrice());
        $billing->setIsActive(true);
        $billing->setIsAutoRenewal(true);
        $billing->setPlan($plan);
        $billing->setExpiredAt($expiredAt->add($plan->getUnit(), $plan->getDuration()));

        $billingHistory = new BillingHistory();
        $billingHistory->setType(BillingHistory::TYPE_CREDIT);
        $billingHistory->setBilling($billing);
        $billingHistory->setAmount($plan->getPrice());
        $billingHistory->setDescription(sprintf('%s plan', $plan->getName()));
        $billingHistory->setStatus(BillingHistory::STATUS_COMPLETED);

        $entityManager->persist($billingHistory);

        $entityManager->flush();

        $this->addFlash(
            'subscription_notice',
            sprintf('You have successfully purchased %s plan', $plan->getName())
        );

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/app/payment/add-balance', name: 'app_payment_add_balance', methods: ['POST'])]
    public function addBalance(Request $request, Payum $payum): Response
    {
        $gatewayName = 'expressCheckout';

        $amount = $request->request->get('amount');

        $validation = $this->validator->validate($amount, [
            new Assert\NotBlank(),
            new Assert\Type(['type' => 'numeric']),
            new Assert\GreaterThanOrEqual(['value' => 15]),
        ]);

        if (count($validation) > 0) {
            $this->addFlash(
                'deposit_amount_notice',
                $validation->get(0)->getMessage()
            );

            return $this->redirectToRoute('app_dashboard');
        }

        $storage = $payum->getStorage(Payment::class);

        /** @var Person $person */
        $person = $this->getUser();

        $payment = $storage->create();

        $payment->setNumber(uniqid());
        $payment->setTotalAmount($amount * pow(10, $this->currency->getExp()));
        $payment->setCurrencyCode($this->currency->getAlpha3());
        $payment->setClientId($person->getId());
        $payment->setClientEmail($person->getEmail());

        $payment->setDetails([
            'L_PAYMENTREQUEST_0_NAME0' => 'Deposit',
            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => Api::PAYMENTREQUEST_ITERMCATEGORY_DIGITAL,
            'NOSHIPPING' => Api::NOSHIPPING_NOT_DISPLAY_ADDRESS,
            'REQCONFIRMSHIPPING' => Api::REQCONFIRMSHIPPING_NOT_REQUIRED,
        ]);

        $storage->update($payment);

        $captureToken = $payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'app_payment_add_balance_done'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    #[Route('/app/payment/add-balance-done', name: 'app_payment_add_balance_done')]
    public function captureDoneAction(Request $request, ManagerRegistry $doctrine, Payum $payum)
    {
        $entityManager = $doctrine->getManager();

        $token = $payum->getHttpRequestVerifier()->verify($request);

        $gateway = $payum->getGateway($token->getGatewayName());

        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();

        if ($status->isCaptured()) {
            $this->addFlash(
                'deposit_amount_notice',
                'Your deposit has been successfully added.'
            );

            /** @var Person $person */
            $person = $this->getUser();

            $billing = $person->getBilling();
            $billing->setCredit($billing->getCredit() + ($payment->getTotalAmount() / pow(10, $this->currency->getExp())));

            $billingHistory = new BillingHistory();
            $billingHistory->setType(BillingHistory::TYPE_DEBIT);
            $billingHistory->setBilling($billing);
            $billingHistory->setPayment($payment);
            $billingHistory->setAmount($payment->getTotalAmount() / pow(10, $this->currency->getExp()));
            $billingHistory->setDescription('Deposit via PayPal');
            $billingHistory->setStatus(BillingHistory::STATUS_COMPLETED);

            $entityManager->persist($billingHistory);
            $entityManager->flush();
        } else {
            $this->addFlash(
                'deposit_amount_notice',
                'Your deposit has been failed.'
            );
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
