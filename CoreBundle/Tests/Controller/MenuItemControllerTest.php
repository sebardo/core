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
        $form['menu_item[name]'] = 'menuitem '.$uid;
        $form['menu_item[shortDescription]'] = 'menuitem short description '.$uid;
        $form['menu_item[description]'] = 'menuitem description '.$uid;
        $form['menu_item[metaTitle]'] = 'meta title '.$uid;
        $form['menu_item[metaDescription]'] = ' meta description '.$uid;
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
