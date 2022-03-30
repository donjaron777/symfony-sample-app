<?php

namespace App\EventSubscribers;

use App\Events\UserAuthenticatedEvent;
use App\Entity\User;
use App\Entity\ApiToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

class UserSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public static function getSubscribedEvents(): array
    {
        return [
            UserAuthenticatedEvent::NAME => 'onUserAuthenticated',
        ];
    }

    public function onUserAuthenticated(UserAuthenticatedEvent $event)
    {
        $em = $this->doctrine->getManager();

        $user = $event->getUser();
        $tokens = $user->getApiTokens();

        $outdatedTokens = $tokens->filter(function ($element) {
            return ($element->getExpireDate() < new DateTime());
        });

        foreach ($outdatedTokens as $outdatedToken) {
            $user->removeApiToken($outdatedToken);
        }

        if (count($tokens) == 0) {
            $userAPIToken = new ApiToken();
            $user->addApiToken($userAPIToken);
        }

        $em->persist($user);
        $em->flush();

        $event->stopPropagation();
    }
}
