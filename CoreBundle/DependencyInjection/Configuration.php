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
     * Array of supported resource owners, indentation is intentional to easily notice
     * which resource is of which type.
     *
     * @var array
     */
    private static $menuItems = array(
        'dashboard' => array(
            'analytics',
            'menuitems',
            'sliders',
        ),
        'newsletter' => array(
            'shippings',
            'newsletters',
        ),
    );
    
    /**
     * Checks that given menu item is supported by this bundle.
     *
     * @param string $menuItem
     *
     * @return Boolean
     */
    public static function isMenuItemSupported($menuItem)
    {
        if ('dashboard' === $menuItem || 'newsletter' === $menuItem) {
            return true;
        }

        return in_array($menuItem, static::$menuItems['dashboard']) || in_array($menuItem, static::$menuItems['newsletter']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('core');

        $this->addMenuConfiguration($rootNode);


        return $treeBuilder;
    }
    
    private function addMenuConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('admin_menus')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode('icon_class')->defaultNull()->end()
                            ->scalarNode('label')->defaultNull()->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
