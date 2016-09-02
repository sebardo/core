<?php

namespace CoreBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  SliderControllerTest
 * @brief Test the  Slider entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/core/CoreBundle/Tests/Controller/SliderControllerTest.php
 * @endcode
 */
class SliderControllerTest  extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testSlider -c app vendor/sebardo/core/CoreBundle/Tests/Controller/SliderControllerTest.php
     * @endcode
     * 
     */
    public function testSlider()
    {
        $uid = rand(999,9999);
        $crawler = $this->createSlider($uid);

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar slider '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $locales = $this->client->getContainer()->get('core_manager')->getLocales();
        foreach ($locales as $locale) {
            $form['slider[translations]['.$locale.'][title]'] = 'slider '.$uid.' ('.$locale.')';
            $form['slider[translations]['.$locale.'][caption]'] = 'caption slider '.$uid. ' ('.$locale.')</p>';
        }
        $form['slider[url]'] = 'http://www.google.es';
        $form['slider[openInNewWindow]']->tick();
        $form['slider[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("slider '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el ítem del Slider satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el ítem del Slider")')->count());
    }
    
    
}
