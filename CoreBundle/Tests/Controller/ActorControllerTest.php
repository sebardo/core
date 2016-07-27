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
    
    
    /**
     * @code
     * phpunit -v --filter testRegister -c app vendor/sebardo/core/CoreBundle/Tests/Controller/ActorControllerTest.php
     * @endcode
     * 
     */
    public function testRegister()
    {
        //Actor register
        $crawler = $this->client->request('GET', '/register');
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Soy nuevo en el sitio")')->count());

        //fill form
        $form = $crawler->selectButton('Darme de alta')->form();
        $uid = rand(999,9999);
        $password = $uid;
        $form['registration[actor][email]'] = 'actor+'.$uid.'@email.com';
        $form['registration[actor][username]'] = 'actor'.$uid;
        $form['registration[actor][name]'] = 'Name_'.$uid;
        $form['registration[actor][password][first]'] = $uid;
        $form['registration[actor][password][second]'] = $uid;
        $form['registration[actor][surnames]'] = 'Surname_'.$uid;
        $form['registration[city]'] = 'City'.$uid;
        $form['registration[state]']->select(10);
        $form['registration[country]']->select('es');
        $form['registration[actor][newsletter]']->tick();
        $form['registration[terms]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Name_'.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Te hemos enviado un correo electrónico para que lo validez.")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Edit ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[name="profile_user"]')->form();
        $uid = rand(999,9999);
        $form['profile_user[name]'] = 'Name_'.$uid;
        $form['profile_user[surnames]'] = 'Surname_'.$uid;
        $form['profile_user[email]'] = 'actor+'.$uid.'@email.com';
        $crawler = $this->client->submit($form);
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Name_'.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("El perfil se ha actualizado satisfactoriamente.")')->count());
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Change password ////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[name="password"]')->form();
        $uid = rand(999,9999);
        $newPassword = $uid;
        $form['password[password_old]'] = $password;
        $form['password[password][first]'] = $newPassword;
        $form['password[password][second]'] = $newPassword;
        $crawler = $this->client->submit($form);
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("La constraseña se ha actualizado, en su próximo inicio de sesión podrá utilizarla.")')->count());
        
    }
  
    /**
     * @code
     * phpunit -v --filter testRecovery -c app vendor/sebardo/core/CoreBundle/Tests/Controller/ActorControllerTest.php
     * @endcode
     * 
     */
    public function testRecovery()
    {
        //Actor create
        $uid = rand(999,9999);
        $crawler = $this->createUser('actor', $uid);
        $entity = $this->getEntity($uid, 'CoreBundle:Actor');
        
        //Actor register
        $crawler = $this->client->request('GET', '/recovery');
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Recuperar contraseña")')->count());

        //fill form
        $form = $crawler->selectButton('Recuperar')->form();
        $form['recovery_email[email]'] = $entity->getEmail();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Mensaje de recuperación enviado con exito, revise su casilla de correo.")')->count());

        //Recovery Form
        $crawler = $this->client->request('GET', '/recovery/'.$entity->getEmail().'/'.$entity->getSalt());
        
        
        //fill form
        $uid = rand(999,9999);
        $form = $crawler->selectButton('Recuperar')->form();
        $form['recovery_password[password][first]'] = $uid;
        $form['recovery_password[password][second]'] = $uid;
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Tu contraseña ha sido actualizada con exito.")')->count());

        //Login Form
        //Check new password
        $crawler = $this->client->request('GET', '/login');
        
        //fill form
        $form = $crawler->selectButton('Entrar')->form();
        $form['_username'] = $entity->getEmail();
        $form['_password'] = $uid;
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Login success")')->count());
        
    }
}
