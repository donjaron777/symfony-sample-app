<?php

namespace App\Security;

use App\Entity\ApiToken;
use App\Events\UserAuthenticatedEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private ManagerRegistry $doctrine;
    private EventDispatcherInterface $dispatcher;

    public function __construct(UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine, EventDispatcherInterface $dispatcher)
    {
        $this->urlGenerator = $urlGenerator;
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
        //     return new RedirectResponse($targetPath);
        // }
        $this->triggerUserAuthenticationEventWithToken($token);
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function triggerUserAuthenticationEventWithToken(TokenInterface $token)
    {
        $user = $token->getUser();
        $event = new UserAuthenticatedEvent($user);
        $this->dispatcher->dispatch($event, UserAuthenticatedEvent::NAME);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
