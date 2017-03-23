<?php

namespace CoreBundle\Tests\Controller;


use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  TaxControllerTest
 * @brief Test the  Tax entity
 *
 * To run the testcase:
 * @code
 * phpunit -v vendor/sebardo/core/CoreBundle/Tests/Controller/TranslationControllerTest.php
 * @endcode
 */
class TranslationControllerTest extends CoreTest
{
    /**
     * @code
     * phpunit -v --filter testTranslation vendor/sebardo/core/CoreBundle/Tests/Controller/TranslationControllerTest.php
     * @endcode
     * 
     */
    public function testTranslation()
    {
        $uid = rand(99,999);
        $crawler = $this->createTranslation($uid);

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click edit///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Edit")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Edit '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Save')->form();
        $uid = rand(99,999);
        $form['translation[key]'] = $uid;
        $form['translation[translations][en][value]'] = $uid;
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        //$this->assertGreaterThan(0, $crawler->filter('html:contains("'.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Translation has been edited successfully")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Translation has been deleted successfully")')->count());
    }

}
