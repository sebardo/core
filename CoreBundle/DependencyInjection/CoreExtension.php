<?php

namespace CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
 
        $configs = $this->addMenuItemsByBundles($container, $config);
        $container->setParameter('core.admin_menus', $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
    
    public function prepend(ContainerBuilder $container)
    {
        
        
    }
    
    private function addMenuItemsByBundles($container, $config)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['AdminBundle'])) {
            $configs = $this->arraymap(array(
                'admin_menus' => array(
                    'dashboard' => array(
                        'options' => array(
                            'analytics' => 'admin_default_analytics'
                            )
                        )
                    )
                ),$config);
        }else{
            $configs = $config['admin_menus'];
        }
        
        if (isset($bundles['BlogBundle'])) {
            $configs = $this->arraymap(array(
                'admin_menus' => array(
                    'blog' => array(
                        'icon_class' => 'fa ion-ios-compose-outline',
                        'label' => 'blog.singular',
                        'options' => array(
                            'posts' => 'blog_post_index',
                            'postscategories' => 'blog_category_index',
                            'poststags' => 'blog_tag_index',
                            'postscomments' => 'blog_comment_index'
                            )
                        )
                    )
                ),$config);
        }
        
        return $configs;
    }

    private function arraymap($arr1, $arr2)
    {
            foreach ($arr1 as $key => $menu_item) {
                if(!array_key_exists($key, $arr2)){
                    array_push($arr2, $menu_item);
                }else{
                    foreach ($menu_item as $key2 => $menu_item2) {
                        if(!array_key_exists($key2, $arr2[$key])){
                            $arr2[$key][$key2] = $menu_item2;
                        }else{
                            foreach ($menu_item2 as $key3 => $menu_item3) {
                                $arr2[$key][$key2][$key3] = array_merge($arr2[$key][$key2][$key3], $menu_item3);
                            }
                        }
                    }
                }
            }
        return $arr2;
    }

}

