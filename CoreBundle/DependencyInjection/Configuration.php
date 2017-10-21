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
                ->scalarNode('extended_layout')->defaultValue('CoreBundle:Base:base.layout.html.twig')->end()
                ->scalarNode('extended_layout_admin')->defaultValue('AdminBundle:Base:layout.html.twig')->end() 
                ->scalarNode('notification_navbar_template')->defaultValue('CoreExtraBundle:Notification:navbar.top.html.twig')->end() 
                ->scalarNode('email_footer_template')->defaultValue('CoreBundle:Email:_footer.html.twig')->end()
                ->scalarNode('email_header_template')->defaultValue('CoreBundle:Email:_header.html.twig')->end()
                ->scalarNode('authentication_handler_class')->defaultValue('CoreBundle\Handler\AuthenticationHandler')->end() 
                ->scalarNode('upload_directory')->defaultValue('uploads')->end()
                ->scalarNode('server_base_url')->defaultValue('http://localhost')->end() 
                ->scalarNode('fixtures_bundle')->defaultTrue()->end()
                ->scalarNode('fixtures_bundle_blog')->defaultTrue()->end()
                ->scalarNode('fixtures_bundle_catalogue')->defaultTrue()->end()
                ->scalarNode('fixtures_bundle_payment')->defaultTrue()->end()    
                ->scalarNode('fixtures_bundle_ticket')->defaultTrue()->end()                
                ->scalarNode('fixtures_dev')->defaultTrue()->end()
                ->scalarNode('fixtures_dev_blog')->defaultTrue()->end()
                ->scalarNode('fixtures_dev_catalogue')->defaultTrue()->end()
                ->scalarNode('fixtures_dev_payment')->defaultTrue()->end()  
                ->scalarNode('fixtures_dev_ticket')->defaultTrue()->end()  
                ->scalarNode('fixtures_test')->defaultFalse()->end() 
                ->scalarNode('admin_email')->defaultValue('admin@admin.com')->end()
                ->scalarNode('validate_time')->defaultValue('86400')->end()
                ->arrayNode('dynamic_discriminator_map')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('entity')
                                ->isRequired()
                            ->end()
                            ->arrayNode('map')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue(array())
                ->end()
                ->scalarNode('currency_symbol')->defaultValue(null)->end()
                ->scalarNode('vat')->defaultValue(null)->end()
                ->scalarNode('special_percentage_charge')->defaultValue(null)->end()
                ->scalarNode('delivery_expenses_type')->defaultValue(null)->end()
                ->scalarNode('delivery_expenses_percentage')->defaultValue(null)->end()
                ->scalarNode('bank_account')->defaultValue(null)->end()
                ->scalarNode('address')->defaultValue(null)->end()
                ->scalarNode('postal_code')->defaultValue(null)->end()
                ->scalarNode('city')->defaultValue(null)->end()
             ->end()
             ;
                
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        return $treeBuilder;
    }
    
}
