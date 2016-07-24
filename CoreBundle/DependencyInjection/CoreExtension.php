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
        
        if (isset($bundles['CoreBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'dashboard' => array(
                        'icon_class' => 'fa fa-dashboard',
                        'label' => 'dashboard',
                        'options' => array(
                            'menuitems' => 'core_menuitem_index',
                            'sliders' => 'core_slider_index'
                        )
                     ),
                    'user' => array(
                        'icon_class' => 'fa fa-users',
                        'label' => 'actor.plural',
                        'options' => array(
                            'actors' => 'core_actor_index',
                            )
                    ),
                    'newsletter' => array(
                        'icon_class' => 'fa fa-envelope-o',
                        'label' => 'newsletter.plural',
                        'options' => array(
                            'subscriptions' => 'core_newsletter_subscription',
                            'newsletters' => 'core_newsletter_index',
                            'shippings' => 'core_newsletter_shipping',
                        )
                    )
                )
            ),$config);
        }
        if (isset($bundles['AdminBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'dashboard' => array(
                        'options' => array(
                            'analytics' => 'admin_default_analytics'
                            )
                        )
                    )
                ),$config);
        }
        if (isset($bundles['BlogBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'blog' => array(
                        'icon_class' => 'fa ion-ios-compose-outline',
                        'label' => 'blog.singular',
                        'options' => array(
                            'posts' => 'blog_post_index',
                            'postcategories' => 'blog_category_index',
                            'posttags' => 'blog_tag_index',
                            'postcomments' => 'blog_comment_index'
                        )
                    )
                )
            ),$config);
        }
        if (isset($bundles['EcommerceBundle'])) {
            $config = $this->arraymap(array(
                'admin_menus' => array(
                    'ecommerce' => array(
                        'icon_class' => 'fa ion-ios-compose-outline',
                        'label' => 'ecommerce',
                        'options' => array(
                            'catalogue' => array(
                                'options' => array(
                                    'products' => 'ecommerce_product_index',
                                    'categories' => 'ecommerce_category_index',
                                    'features' => 'ecommerce_feature_index',
                                    'attributes' => 'ecommerce_attribute_index',
                                    'brands' => 'ecommerce_brand_index',
                                    'models' => 'ecommerce_brandmodel_index',
                                )
                            ),
                            'sales' => array(
                                'options' => array(
                                    'transactions' => 'ecommerce_transaction_index',
                                    'invoices' => 'ecommerce_invoice_index',
                                    'taxes' => 'ecommerce_tax_index',
                                )
                            ),
                            'recurrings' => array(
                                'options' => array(
                                    'contracts' => 'ecommerce_contract_index',
                                    'plans' => 'ecommerce_plan_index',
                                )
                            )
                        )
                    ),
                    'advert' => array(
                        'icon_class' => 'fa fa-picture-o',
                        'label' => 'advert.plural',
                        'options' => array(
                            'adverts' => 'ecommerce_advert_index',
                            'advertslocated' => 'ecommerce_located_index',
                        )
                    ),
                )),$config);
        }
        return $config;
    }

    private function arraymap($arr1, $arr2)
    {
            foreach ($arr1 as $key => $menu_item) {
                if(!array_key_exists($key, $arr2)){
                    $arr2[$key] = $menu_item;
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

