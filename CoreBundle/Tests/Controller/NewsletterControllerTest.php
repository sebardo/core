<?php

namespace CoreBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  NewsletterControllerTest
 * @brief Test the  Newsletter entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app src/CoreBundle/Tests/Controller/NewsletterControllerTest.php
 * @endcode
 */
class NewsletterControllerTest  extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testNewsletterUsers -c app src/CoreBundle/Tests/Controller/NewsletterControllerTest.php
     * @endcode
     * 
     */
    public function testNewsletterUsers()
    {
        //Newsletter index
        $crawler = $this->client->request('GET', '/admin/newsletter', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Newsletters")')->count());
        
        //Click new
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo newsletter")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['corebundle_newslettertype[title]'] = 'title '.$uid;
        $form['corebundle_newslettertype[body]'] = 'description '.$uid;
        $form['corebundle_newslettertype[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar title '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['corebundle_newslettertype[title]'] = 'title '.$uid;
        $form['corebundle_newslettertype[body]'] = 'description '.$uid;
        $form['corebundle_newslettertype[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el registro satisfactoriamente")')->count());
 
        //////////////////////////////////////////////////////////////////////
        ////SHIPPING//////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////
        //Newsletter shipping
        $crawler = $this->client->request('GET', '/admin/shipping', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Envios")')->count());
        
        //Click new
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo envío")')->count());
        
        //get entity newsletter
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $entity = $manager->getRepository('CoreBundle:Newsletter')->findOneByTitle('title '.$uid);
  
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['corebundle_newslettershippingtype[newsletter]']->select($entity->getId());
        $form['corebundle_newslettershippingtype[type]']->select('users');        
        $crawler = $this->client->submit($form);// submit the form
        
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el registro satisfactoriamente")')->count());
        
        //Newsletter show
        $crawler = $this->client->request('GET', '/admin/newsletter/'.$entity->getId(), array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        
         ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el registro satisfactoriamente")')->count());
        
    }
    
    /**
     * @code
     * phpunit -v --filter testNewsletterOptic -c app src/CoreBundle/Tests/Controller/NewsletterControllerTest.php
     * @endcode
     * 
     */
    public function testNewsletterOptic()
    {
        //Newsletter index
        $crawler = $this->client->request('GET', '/admin/newsletter', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Newsletters")')->count());
        
        //Click new
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo newsletter")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['corebundle_newslettertype[title]'] = 'title '.$uid;
        $form['corebundle_newslettertype[body]'] = 'description '.$uid;
        $form['corebundle_newslettertype[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar title '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['corebundle_newslettertype[title]'] = 'title '.$uid;
        $form['corebundle_newslettertype[body]'] = 'description '.$uid;
        $form['corebundle_newslettertype[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el registro satisfactoriamente")')->count());
 
        //////////////////////////////////////////////////////////////////////
        ////SHIPPING//////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////
        //Newsletter shipping
        $crawler = $this->client->request('GET', '/admin/shipping', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Envios")')->count());
        
        //Click new
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo envío")')->count());
        
        //get entity newsletter
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $entity = $manager->getRepository('CoreBundle:Newsletter')->findOneByTitle('title '.$uid);
  
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['corebundle_newslettershippingtype[newsletter]']->select($entity->getId());
        $form['corebundle_newslettershippingtype[type]']->select('optics');        
        $crawler = $this->client->submit($form);// submit the form
        
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el registro satisfactoriamente")')->count());
        
        //Newsletter show
        $crawler = $this->client->request('GET', '/admin/newsletter/'.$entity->getId(), array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        
         ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el registro satisfactoriamente")')->count());
        
    }
    
    /**
     * @code
     * phpunit -v --filter testNewsletterSuscript -c app src/CoreBundle/Tests/Controller/NewsletterControllerTest.php
     * @endcode
     * 
     */
    public function testNewsletterSuscript()
    {
        //Newsletter index
        $crawler = $this->client->request('GET', '/admin/newsletter', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Newsletters")')->count());
        
        //Click new
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo newsletter")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['corebundle_newslettertype[title]'] = 'title '.$uid;
        $form['corebundle_newslettertype[body]'] = 'description '.$uid;
        $form['corebundle_newslettertype[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar title '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['corebundle_newslettertype[title]'] = 'title '.$uid;
        $form['corebundle_newslettertype[body]'] = 'description '.$uid;
        $form['corebundle_newslettertype[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el registro satisfactoriamente")')->count());
 
        //////////////////////////////////////////////////////////////////////
        ////SHIPPING//////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////
        //Newsletter shipping
        $crawler = $this->client->request('GET', '/admin/shipping', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Envios")')->count());
        
        //Click new
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo envío")')->count());
        
        //get entity newsletter
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $entity = $manager->getRepository('CoreBundle:Newsletter')->findOneByTitle('title '.$uid);
  
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['corebundle_newslettershippingtype[newsletter]']->select($entity->getId());
        $form['corebundle_newslettershippingtype[type]']->select('subscripts');        
        $crawler = $this->client->submit($form);// submit the form
        
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el registro satisfactoriamente")')->count());
        
        //Newsletter show
        $crawler = $this->client->request('GET', '/admin/newsletter/'.$entity->getId(), array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("title '.$uid.'")')->count());
        
         ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el registro satisfactoriamente")')->count());
        
    }
    
    
}
