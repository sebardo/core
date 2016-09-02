<?php

namespace CoreBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  MenuItemControllerTest
 * @brief Test the  MenuItem entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/core/CoreBundle/Tests/Controller/MenuItemControllerTest.php
 * @endcode
 */
class MenuItemControllerTest  extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testMenuItem -c app vendor/sebardo/core/CoreBundle/Tests/Controller/MenuItemControllerTest.php
     * @endcode
     * 
     */
    public function testMenuItem()
    {
        $uid = rand(999,9999);
        $crawler = $this->createMenuItem($uid);

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click edit///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Editar")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar menuitem '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $locales = $this->client->getContainer()->get('core_manager')->getLocales();
        foreach ($locales as $locale) {
            $form['menu_item[translations]['.$locale.'][name]'] = 'menuitem '.$uid.' ('.$locale.')';
            $form['menu_item[translations]['.$locale.'][shortDescription]'] = 'shortDescription '.$uid. ' ('.$locale.')';
            $form['menu_item[translations]['.$locale.'][description]'] = 'menuitem description '.$uid. ' ('.$locale.')';
            $form['menu_item[translations]['.$locale.'][metaTitle]'] = 'meta title '.$uid. ' ('.$locale.')';
            $form['menu_item[translations]['.$locale.'][metaDescription]'] = ' meta description '.$uid. ' ('.$locale.')';
        }
        $form['menu_item[visible]']->tick();
        $form['menu_item[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("menuitem '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el item del menú satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el item del menú satisfactoriamente")')->count());
    }
    
    
    /**
     * @code
     * phpunit -v --filter testSubMenuItem -c app vendor/sebardo/core/CoreBundle/Tests/Controller/MenuItemControllerTest.php
     * @endcode
     * 
     */
    public function testSubMenuItem()
    {
        $uid = rand(999,9999);
        $crawler = $this->createMenuItem($uid);
        $entity = $this->getEntity($uid, 'CoreBundle:MenuItem');

        //Submenu page
        $crawler = $this->client->request('GET', '/admin/menuitems/'.$entity->getId().'/submenuitems/');
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nueva")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo sub-menú item")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $locales = $this->client->getContainer()->get('core_manager')->getLocales();
        foreach ($locales as $locale) {
            $form['sub_menu_item[translations]['.$locale.'][name]'] = 'menuitem '.$uid;
            $form['sub_menu_item[translations]['.$locale.'][shortDescription]'] = 'menuitem short description '.$uid;
            $form['sub_menu_item[translations]['.$locale.'][description]'] = 'menuitem description '.$uid;
            $form['sub_menu_item[translations]['.$locale.'][metaTitle]'] = 'meta title '.$uid;
            $form['sub_menu_item[translations]['.$locale.'][metaDescription]'] = ' meta description '.$uid;
        }
        $form['sub_menu_item[visible]']->tick();
        $form['sub_menu_item[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("menuitem '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el item del menú")')->count());


        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click edit///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Editar")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar menuitem '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        
        $locales = $this->client->getContainer()->get('core_manager')->getLocales();
        foreach ($locales as $locale) {
            $form['menu_item[translations]['.$locale.'][name]'] = 'menuitem '.$uid.' '.$locale;
            $form['menu_item[translations]['.$locale.'][shortDescription]'] = 'menuitem short description '.$uid;
            $form['menu_item[translations]['.$locale.'][description]'] = 'menuitem description '.$uid;
            $form['menu_item[translations]['.$locale.'][metaTitle]'] = 'meta title '.$uid;
            $form['menu_item[translations]['.$locale.'][metaDescription]'] = ' meta description '.$uid;
        }

        $form['menu_item[visible]']->tick();
        $form['menu_item[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("menuitem '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el item del menú satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el item del menú satisfactoriamente")')->count());
    }
    
    
}
