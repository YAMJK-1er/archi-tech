<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{

    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager ;
    private $urlGenerator;
    private $csrfTokenManager ;
    private $passwordEncoder ;

    public function __construct(EntityManagerInterface $entityManager , UrlGeneratorInterface $urlGenerator , CsrfTokenManagerInterface $csrfTokenManager , UserPasswordHasherInterface $passwordEncoder) 
    {
        $this->entityManager = $entityManager ;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager ;
        $this->passwordEncoder = $passwordEncoder ;
    }

    /**
     * Should this authenticator be used for this request ?
     * 
     * @param Request $request
     * @return bool|void 
     */
    public function supports(Request $request) : bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod('POST') ;
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email'       => $request->request->get('email') ,
            'password'    => $request->request->get('password') ,
            '_csrf_token' => $request->request->get('_csrf_token') ,
        ] ;

        $request->getSession()->set(Security::LAST_USERNAME , $credentials['email']) ;

        return $credentials ;
    }

    /**
     * Return a UserInterface object based on the credentials
     * 
     * @param array $credentials
     * @param UserProviderInterface $userProvider
     * @return object|UserInterface|null
     */
    public function getUser($credentials , UserProviderInterface $userProvider)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]) ?? throw new UsernameNotFoundException(sprintf('Email "%s" not found' , $credentials['email'])) ;
       
        return $user ; 
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }
    /**
     * What should happen once the user is authenticated
     * 
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return Response|void|null 
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey) : ?Response
    {
        // if ($targetPath = $this->getTargetPath($request->getSession() , $providerKey))
        // {
        //     return new RedirectResponse($targetPath) ;
        // }

        return new RedirectResponse($this->urlGenerator->generate('app_projets')) ;
    }


    /**
     * Check credentials
     * 
     * Check csrf token is valid
     * Check password is valid
     * 
     * @param array $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials , UserInterface $user)
    {
        $token = new CsrfToken('authenticate' , $credentials['_csrf_token']) ;
        
        if (!$this->csrfTokenManager->isTokenValid($token))
        {
            throw new InvalidCsrfTokenException() ;
        }

        return $this->passwordEncoder->isPasswordValid($user , $credentials['password']) ;
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    // use TargetPathTrait;

    // public const LOGIN_ROUTE = 'app_login';

    // private UrlGeneratorInterface $urlGenerator;

    // public function __construct(UrlGeneratorInterface $urlGenerator)
    // {
    //     $this->urlGenerator = $urlGenerator;
    // }

    // public function authenticate(Request $request): Passport
    // {
    //     $email = $request->request->get('email', '');

    //     $request->getSession()->set(Security::LAST_USERNAME, $email);

    //     return new Passport(
    //         new UserBadge($email),
    //         new PasswordCredentials($request->request->get('password', '')),
    //         [
    //             new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
    //         ]
    //     );
    // }

    // public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    // {
    //     if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
    //         return new RedirectResponse($targetPath);
    //     }

    //     // For example:
    //     //return new RedirectResponse($this->urlGenerator->generate('some_route'));
    //     throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    // }

    // protected function getLoginUrl(Request $request): string
    // {
    //     return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    // }
}
