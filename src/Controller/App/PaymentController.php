<?php

namespace App\Controller\App;

use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class PaymentController extends AbstractController
{
    private $payum;

    // public function __construct(Payum $payum)
    // {
    //     $this->payum = $payum;
    // }

    #[Route('/app/add-balance', name: 'app_add_balance', methods: ['POST'])]
    public function addBalance(Request $request): Response
    {
        $gatewayName = 'paypal_rest';

        $amount = $request->request->get('amount');

        $validator = Validation::createValidator();
        $validation = $validator->validate($amount, [
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

        // return $this->redirectToRoute('app_dashboard');


        $storage = $this->payum->getStorage(\Payum\Core\Model\Payment::class);

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount($amount * 100); // pow(10, 2)
        $payment->setCurrencyCode('USD');
        $payment->setClientId($this->getUser()->getId());
        $payment->setClientEmail($this->getUser()->getEmail());

        // $payment->setDetails([
        //     // put here any fields in a gateway format.
        // ]);

        $storage->update($payment);

        $captureToken = $this->payum->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'app_add_balance_done' // the route to redirect after capture
        );

        return $this->json([
            'next' => $captureToken->getTargetUrl(),
        ]);

        return $this->redirect($captureToken->getTargetUrl());
    }

    #[Route('/app/add-balance-done', name: 'app_add_balance_done')]
    public function captureDoneAction(Request $request)
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);
        
        $identity = $token->getDetails();
        $model = $this->payum->getStorage($identity->getClass())->find($identity);

        $gateway = $this->payum->getGateway($token->getGatewayName());

        // you can invalidate the token. The url could not be requested any more.
        // $this->payum->getHttpRequestVerifier()->invalidate($token);
        
        // Once you have token you can get the model from the storage directly. 
        //$identity = $token->getDetails();
        //$details = $payum->getStorage($identity->getClass())->find($identity);
        
        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $details = $status->getFirstModel();

        // you have order and payment status 
        // so you can do whatever you want for example you can just print status and payment details.
        
        return $this->json([
            'status' => $status->getValue(),
            'details' => iterator_to_array($details),
        ]);
    }
}
