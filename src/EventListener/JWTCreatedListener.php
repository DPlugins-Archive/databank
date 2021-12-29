<?php

namespace App\EventListener;

use App\Entity\Billing;
use App\Repository\BillingRepository;
use Carbon\CarbonImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        /** @var Billing $billing */
        $billing = $event->getUser()->getBilling();

        $payload['billing'] = [
            'balance' => $billing->getCredit(),
            'is_active' => $billing->getIsActive(),
        ];

        if ($billing->getIsActive()) {
            $exp = CarbonImmutable::instance($billing->getExpiredAt())->addDays(BillingRepository::DAYS_GRACE_PERIOD);
            $payload['billing']['expired_at'] = CarbonImmutable::instance($billing->getExpiredAt())->getTimestamp();
            $payload['billing']['plan'] = $billing->getPlan()->getName();
        } else {
            $exp = CarbonImmutable::now()->addHour();
        }

        $payload['exp'] = $exp->getTimestamp();

        $event->setData($payload);
    }
}