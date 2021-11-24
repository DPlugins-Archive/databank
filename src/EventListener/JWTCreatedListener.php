<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\Billing;
use App\Repository\BillingRepository;
use Carbon\CarbonImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedEventSubscriber implements EventSubscriberInterface
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

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return ['lexik_jwt_authentication.on_jwt_created' => 'onJWTCreated'];
    }
}
