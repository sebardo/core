<?php

namespace CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('core');

        $rootNode
            ->children()
            ->scalarNode('name')
                 ->defaultValue('Default name')
            ->end()
            ->scalarNode('extended_layout')
                 ->defaultValue('CoreBundle:Base:base.layout.html.twig')
            ->end()
            ->variableNode('dynamic_discriminator_map')
                ->validate()
                ->always(function ($v) {
                    if (is_string($v) || is_array($v)) {
                        return $v;
                    }
                    throw new InvalidTypeException();
                })
            ->end()->end()
            ->scalarNode('special_percentage_charge')
                 ->defaultValue('0')
            ->end()
            ->scalarNode('delivery_expenses_type')
                 ->defaultValue('by_percentage')
            ->end()
            ->scalarNode('delivery_expenses_value')
                 ->defaultValue('0')
            ->end()
            ->scalarNode('vat')
                 ->defaultValue('0')
            ->end()
        ->end()
//            ->scalarNode('extended_layout_admin')
//                ->defaultValue('AdminBundle:Base:layout.html.twig')
//            ->end() 
//            ->scalarNode('notification_navbar_template')
//                ->defaultValue('CoreExtraBundle:Notification:navbar.top.html.twig')
//            ->end() 
//            ->scalarNode('email_footer_template')
//                ->defaultValue('CoreBundle:Email:_footer.html.twig')
//            ->end()
//            ->scalarNode('email_header_template')
//                ->defaultValue('CoreBundle:Email:_header.html.twig')
//            ->end()
//            ->scalarNode('authentication_handler_class')
//                ->defaultValue('CoreBundle\Handler\AuthenticationHandler')
//            ->end() 
//            ->scalarNode('upload_directory')
//                ->defaultValue('uploads')
//            ->end() 
//            ->scalarNode('server_base_url')
//                ->defaultValue('http://localhost')
//            ->end() 
//            ->scalarNode('fixtures_dev')
//                ->defaultTrue()
//            ->end() 
//            ->scalarNode('fixtures_test')
//                ->defaultFalse()
//            ->end() 
//            ->scalarNode('admin_email')
//                ->defaultValue('admin@admin.com')
//            ->end()
//            ->scalarNode('validate_time')
//                ->defaultValue('86400')
//            ->end()
             ;
                
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        return $treeBuilder;
    }
    
}
