<?php
namespace CoreBundle\Handler;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
//, AuthenticationFailureHandlerInterface
class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, LogoutSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception    
     * @return Response the response to return
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $result = array('success' => false, 'message' => $exception->getMessage());
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        $this->container->get('session')->set('error', 'Bad credentials.');
                
        return new RedirectResponse($this->container->get('router')->generate('login'));
    }

    public function onLogoutSuccess(Request $request)
    {

    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Security\Http\Authentication.AuthenticationSuccessHandlerInterface::onAuthenticationSuccess()
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        
        // if AJAX login
        if ( $request->isXmlHttpRequest() ) {

                $array = array( 'success' => true ); // data to return via JSON
                $response = new Response( json_encode( $array ) );
                $response->headers->set( 'Content-Type', 'application/json' );
                return $response;

        } 
        if ($this->hasRole('ROLE_ADMIN', $token->getUser())) {
            return new RedirectResponse($this->container->get('router')->generate('admin_default_dashboard'));
        } else {
            $referer = $this->getRefererPath($request);
            if ($referer == '/identification') {
                return new RedirectResponse($this->container->get('router')->generate('ecommerce_checkout_deliveryinfo'));
            }
            return new RedirectResponse($this->container->get('router')->generate('index'));
        }
    }

    public function getRefererPath(Request $request=null)
    {
        $referer = $request->headers->get('referer');

        $baseUrl = $request->getSchemeAndHttpHost();

        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));

        return $lastPath;
    }
    
    public function hasRole($searchRole, $user)
    {
        
        foreach ($user->getRoles() as $role) {
            if($searchRole == $role) return true;
        }

        return false;
    }
    
}
