<?php
namespace CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Description of CoreEventListener
 *
 * @author sebastian
 */
class CoreEventListener 
{
    protected $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        //////////////////////////////////////////////
        //Add global twig var for core configuration//
        //////////////////////////////////////////////
        $bundles = $this->container->getParameter('kernel.bundles');
        
        $this->container->get('twig')->addGlobal('use_core', false);
        $this->container->get('twig')->addGlobal('use_admin', false);
        $this->container->get('twig')->addGlobal('use_blog', false);
        $this->container->get('twig')->addGlobal('use_ecommerce', false);
        
        if (isset($bundles['CoreBundle'])) {
            $this->container->get('twig')->addGlobal('use_core', true);
        }
        if (isset($bundles['AdminBundle'])) {
            $this->container->get('twig')->addGlobal('use_admin', true);
        }
        if (isset($bundles['BlogBundle'])) {
            $this->container->get('twig')->addGlobal('use_blog', true);
        }
        if (isset($bundles['EcommerceBundle'])) {
            $this->container->get('twig')->addGlobal('use_ecommerce', true);
        }
    }

}
