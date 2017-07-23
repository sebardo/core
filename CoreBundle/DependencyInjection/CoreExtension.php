<?php

namespace CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
              
        $core = $container->getParameter('core');
        $container->setParameter('core.name', $core['name']);
        $container->setParameter('core.extended_layout', $config['extended_layout']);
        $container->setParameter('core.extended_layout_admin', $config['extended_layout_admin']);
        $container->setParameter('core.authentication_handler_class', $config['authentication_handler_class']);
        $container->setParameter('core.email_header_template', $config['email_header_template']);
        $container->setParameter('core.email_footer_template', $config['email_footer_template']);
        $container->setParameter('core.notification_navbar_template', $config['notification_navbar_template']);
        $container->setParameter('core.upload_directory', $config['upload_directory']);
        $container->setParameter('core.server_base_url', $config['server_base_url']);
        $container->setParameter('core.fixtures_dev', $config['fixtures_dev']);
        $container->setParameter('core.fixtures_test', $config['fixtures_test']);
        $container->setParameter('core.admin_email', $config['admin_email']);
        $container->setParameter('core.validate_time', $config['validate_time']);  
        $container->setParameter('core.dynamic_discriminator_map.mapping', $config['dynamic_discriminator_map']);
        //payment
        $container->setParameter('core.currency_symbol', $config['currency_symbol']);
        $container->setParameter('core.vat', $config['vat']);
        $container->setParameter('core.special_percentage_charge', $config['special_percentage_charge']);
        $container->setParameter('core.delivery_expenses_type', $config['delivery_expenses_type']);  
        $container->setParameter('core.delivery_expenses_percentage', $config['delivery_expenses_percentage']);  

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
   
}

