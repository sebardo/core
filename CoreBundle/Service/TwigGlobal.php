<?php

namespace CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of TwigGlobal
 *
 * @author sebastian
 */
class TwigGlobal 
{

    protected $container = null;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
    
    public function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
    
    
    public function checkUse($bundle) {
        //////////////////////////////////////////////
        //Add global twig var for core configuration//
        //////////////////////////////////////////////
        $bundles = $this->container->getParameter('kernel.bundles');

        if (isset($bundles[$bundle])) {
            return true;
        }else{
            return false;
        }
        
    }
    
    public function getLocales() {
        return $this->container->getParameter('a2lix_translation_form.locales');
    }
    
    public function getParameter($parameter, $bundle='core') {
        
        $parameter = $this->container->getParameter($bundle.'.'.$parameter);

        return $parameter;
        
    }
}
