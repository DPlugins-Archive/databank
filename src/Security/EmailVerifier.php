<?php

namespace App\Security;

use App\Entity\Billing;
use App\Entity\Plan;
use App\Security\Exception\EmailHasVerifiedException;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(private readonly VerifyEmailHelperInterface $verifyEmailHelper, private readonly MailerInterface $mailer, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param \App\Entity\Person $user
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @param \App\Entity\Person $user
     *
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        if ($user->isVerified()) {
            throw new EmailHasVerifiedException();
        }

        $user->setIsVerified(true);

        $billing = new Billing();

        $plan = $this->entityManager->getRepository(Plan::class)->findOneBy(['slug' => 'monthly']);

        $billing->setPlan($plan);

        $billing->setPerson($user);
        $billing->setIsActive(true);
        $billing->setIsAutoRenewal(true);
        $billing->setCredit(0);
        $billing->setExpiredAt(CarbonImmutable::now()->add('month', 1));

        $this->entityManager->persist($billing);
        $this->entityManager->flush();
    }
}
