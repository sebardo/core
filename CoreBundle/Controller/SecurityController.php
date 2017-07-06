<?php
namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/" , name="index")
     * @Template("CoreBundle:Security:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return array();
    }

//    
//    /**
//     * @Route("/{_locale}" , name="locale", requirements={"_locale"="es|en|de"})
//     */
//    public function localeAction(Request $request)
//    {
//        if(!empty($this->get('core_manager')->getRefererPath($request))){
//            return $this->redirect($this->get('core_manager')->getRefererPath($request));
//        }else{
//            return $this->redirect($this->generateUrl('index'));
//        }
//        
//    }
//    
    
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
     * @Route("/company/login" , name="company_login")
     * @Template("AdminBundle:Security:login.html.twig")
     */
    public function companyLoginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                return $this->redirect($this->get('router')->generate('admin_default_dashboard'));
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
                return $this->redirect($this->get('router')->generate('admin_default_dashboard'));
        }
        
        return $this->login($request);
    }
    
    private function login(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        return array(
                // last username entered by the user
                'last_username' => $session->get(Security::LAST_USERNAME),
                'error'         => $error,
            );
    }
}
