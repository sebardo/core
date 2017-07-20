<?php

namespace CoreBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  PageControllerTest
 * @brief Test the  Page entity
 *
 * To run the testcase:
 * @code
 * php vendor/bin/phpunit -v vendor/sebardo/core/CoreBundle/Tests/Controller/PageControllerTest.php
 * @endcode
 */
class PageControllerTest extends CoreTest
{
    /**
     * @code
     * php vendor/bin/phpunit -v --filter testPage vendor/sebardo/core/CoreBundle/Tests/Controller/PageControllerTest.php
     * @endcode
     * 
     */
    public function testPage()
    {
        $uid = rand(99,999);
        $crawler = $this->createPage($uid);

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Edit post '.$uid.' (en)")')->count());
        
        //fill form
        $form = $crawler->selectButton('Save')->form();
        $uid = rand(99,999);
        $locales = $this->client->getContainer()->get('core_manager')->getLocales();
        foreach ($locales as $locale) {
            $form['page[translations]['.$locale.'][title]'] = 'post '.$uid.' ('.$locale.')';
            $form['page[translations]['.$locale.'][description]'] = '<p>post <b>description</b> '.$uid. ' ('.$locale.')</p>';
            $form['page[translations]['.$locale.'][metaTitle]'] = 'meta title  ('.$locale.')'.$uid;
            $form['page[translations]['.$locale.'][metaDescription]'] = 'meta description ('.$locale.')'.$uid;
            $form['page[translations]['.$locale.'][metaTags]'] = 'meta tags ('.$locale.')'.$uid;
        }
        $form['page[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        //$this->assertGreaterThan(0, $crawler->filter('html:contains("'.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Page has been edited successfully")')->count());
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $entity = $this->getEntity($uid, 'CoreBundle:Page', 'title');
        //edit page
        $crawler = $this->client->request('GET', '/admin/pages/'.$entity->getId(), array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Page has been deleted successfully")')->count());
    }
}
