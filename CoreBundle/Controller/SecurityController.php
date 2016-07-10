<?php
namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login" , name="login")
     * @Template("CoreBundle:Security:login.html.twig")
     */
    public function loginAction(Request $request)
    {
       
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                return $this->redirect($this->get('router')->generate('index'));
        }

        return $this->login($request);
    }
    
    /**
     * @Route("/admin/login" , name="admin_login")
     * @Template("AdminBundle:Security:login.html.twig")
     */
    public function adminLoginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                return $this->redirect($this->get('router')->generate('index'));
        }
        
        return $this->login($request);
    }
    
    private function login(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            );
    }
}
