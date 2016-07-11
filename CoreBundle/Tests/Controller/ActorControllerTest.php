<?php

namespace CoreBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  ActorControllerTest
 * @brief Test the  Actor entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/core/CoreBundle/Tests/Controller/ActorControllerTest.php
 * @endcode
 */
class ActorControllerTest  extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testActor -c app vendor/sebardo/core/CoreBundle/Tests/Controller/ActorControllerTest.php
     * @endcode
     * 
     */
    public function testActor()
    {
        $uid = rand(999,9999);
        $crawler = $this->createUser('actor', $uid);

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar Name_'.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['actor_edit[email]'] = 'actor+'.$uid.'@email.com';
        $form['actor_edit[username]'] = 'actor'.$uid;
        $form['actor_edit[password]'] = $uid;
        $form['actor_edit[name]'] = 'Name_'.$uid;
        $form['actor_edit[surnames]'] = 'Surname_'.$uid;
        $form['actor_edit[active]']->tick();
        $form['actor_edit[newsletter]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("actor'.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el usuario satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el usuario satisfactoriamente")')->count());
    }
    
    
}
