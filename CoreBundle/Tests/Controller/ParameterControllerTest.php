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
 * phpunit -v vendor/sebardo/core/CoreBundle/Tests/Controller/ParameterControllerTest.php
 * @endcode
 */
class ParameterControllerTest extends CoreTest
{
    /**
     * @code
     * phpunit -v --filter testParameter vendor/sebardo/core/CoreBundle/Tests/Controller/ParameterControllerTest.php
     * @endcode
     * 
     */
    public function testParameter()
    {
        $uid = rand(99,999);
        $crawler = $this->createParameter($uid);

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
        $form['parameter[parameter]'] = $uid;
        $form['parameter[value]'] = $uid;
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        //$this->assertGreaterThan(0, $crawler->filter('html:contains("'.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Parameter has been edited successfully")')->count());
//
//        ///////////////////////////////////////////////////////////////////////////////////////////
//        //Click delete/////////////////////////////////////////////////////////////////////////////
//        ///////////////////////////////////////////////////////////////////////////////////////////
//        $form = $crawler->filter('form[id="delete-entity"]')->form();
//        $crawler = $this->client->submit($form);// submit the form
//        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
//        $crawler = $this->client->followRedirect();
//        //Asserts
//        $this->assertTrue($this->client->getResponse()->isSuccessful());
//        $this->assertGreaterThan(0, $crawler->filter('html:contains("Translation has been deleted successfully")')->count());
    }
}
