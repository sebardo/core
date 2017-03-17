<?php

namespace CoreBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EcommerceBundle\Entity\Brand;
use CoreBundle\Entity\Pack;
use EcommerceBundle\Entity\Address;
use CoreBundle\Entity\Actor;


class CoreTest  extends WebTestCase
{

    protected $client = null;
    
    protected $actor = null;
    
    protected $plan = null;
    
    protected $password = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }
    
    protected function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('admin', 'admin', $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    
    protected function createUser($type, $uid)
    {
        switch ($type) {
            case 'actor':
                //Actor index
                $crawler = $this->client->request('GET', '/admin/actor', array(), array(), array(
                    'PHP_AUTH_USER' => 'admin',
                    'PHP_AUTH_PW'   => 'admin',
                ));
                //Asserts
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("Usuarios")')->count());

                //Click new
                $link = $crawler
                    ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
                    ->eq(0) // select the second link in the list
                    ->link()
                ;
                $crawler = $this->client->click($link);// and click it
                //Asserts
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo usuario")')->count());

                //fill form
                $form = $crawler->selectButton('Guardar')->form();
                $form['actor[email]'] = 'actor+'.$uid.'@email.com';
                $form['actor[username]'] = 'actor'.$uid;
                $form['actor[password]'] = $uid;
                $form['actor[name]'] = 'Name_'.$uid;
                $form['actor[lastname]'] = 'Surname_'.$uid;
                $form['actor[active]']->tick();
                $form['actor[newsletter]']->tick();
                $crawler = $this->client->submit($form);// submit the form

                
                //Asserts
                $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
                $crawler = $this->client->followRedirect();
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("actor'.$uid.'")')->count());
                break;
            case 'company':
                //Optic index
                $crawler = $this->client->request('GET', '/admin/company', array(), array(), array(
                    'PHP_AUTH_USER' => 'admin',
                    'PHP_AUTH_PW'   => 'admin',
                ));
                //Asserts
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("Companias")')->count());

                //Click new
                $link = $crawler
                    ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
                    ->eq(0) // select the second link in the list
                    ->link()
                ;
                $crawler = $this->client->click($link);// and click it
                //Asserts
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva companía")')->count());

                //fill form
                $form = $crawler->selectButton('Guardar')->form();
                $form['corebundle_optictype[email]'] = 'optic+'.$uid.'@gmail.com';
                $form['corebundle_optictype[username]'] = 'optic'.$uid;
                $form['corebundle_optictype[password]'] = $uid;
                $form['corebundle_optictype[name]'] = 'Name_'.$uid;
                $form['corebundle_optictype[description]'] = 'Description_'.$uid;
                $form['corebundle_optictype[active]']->tick();
                $form['corebundle_optictype[newsletter]']->tick();
                $form['corebundle_optictype[address]'] = 'Av address 123'.$uid;
                $form['corebundle_optictype[city]'] = 'Cabrera de Mar'.$uid;
                $form['corebundle_optictype[state]']->select(10);
                $form['corebundle_optictype[country]']->select('es');
                $form['corebundle_optictype[postalCode]'] = '08349';
                $form['corebundle_optictype[metaTitle]'] = 'Meta title_'.$uid;
                $form['corebundle_optictype[metaDescription]'] = 'Meta description_'.$uid;
                $crawler = $this->client->submit($form);// submit the form

                //Asserts
                $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
                $crawler = $this->client->followRedirect();
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("company+'.$uid.'@email.com")')->count());

                break;
            default:
                throw new \Exception('No type defined');
                break;
        }
        
        return $crawler;
    }
    
    protected function createPost($uid, $username=null, $password=null)
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        //////////////////////////////////////////////////////////////////////////////
        // Category///////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $categoryId = rand(999,9999);
        $crawler = $this->createCategoryBlog($categoryId);
        $category = $manager->getRepository('BlogBundle:Category')->findOneByName('category '.$categoryId);
        
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/post/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/post/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Publicaciones")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva publicación")')->count());
   
        //fill form
        $form = $crawler->filter('form[name="post"]')->form();
        
        $locales = $this->client->getContainer()->get('core_manager')->getLocales();
        foreach ($locales as $locale) {
            $form['post[translations]['.$locale.'][title]'] = 'post '.$uid.' ('.$locale.')';
            $form['post[translations]['.$locale.'][shortDescription]'] = 'post shot description'.$uid. ' ('.$locale.')</p>';
            $form['post[translations]['.$locale.'][description]'] = '<p>post <b>description</b> '.$uid. ' ('.$locale.')</p>';
            $form['post[translations]['.$locale.'][metaTitle]'] = 'meta title  ('.$locale.')'.$uid;
            $form['post[translations]['.$locale.'][metaDescription]'] = 'meta description ('.$locale.')'.$uid;
        }
        
        $form['post[categories]']->setValue(array($category->getId()));
        $form['post[published]'] = date('d').'/'.date('m').'/'.date('Y');
        $form['post[highlighted]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("post '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la publicación satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    protected function createCategory($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/categories/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/categories/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Categorías")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva categoría")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['category[name]'] = 'category '.$uid;
        $form['category[description]'] = 'category description'.$uid;
        $form['category[metaTitle]'] = 'Meta title_'.$uid;
        $form['category[metaDescription]'] = 'Meta description_'.$uid;
        $form['category[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("category '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la categoría satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    protected function createCategoryBlog($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/post/category/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/post/category/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Categorías")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva categoría")')->count());
         //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['category[name]'] = 'category '.$uid;
        $form['category[description]'] = 'category description'.$uid;
        $form['category[metaTitle]'] = 'Meta title_'.$uid;
        $form['category[metaDescription]'] = 'Meta description_'.$uid;
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("category '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la categoría satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    protected function createBrand($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/brands/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/brands/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Marcas")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva marca")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['brand[name]'] = 'brand '.$uid;
        $form['brand[available]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("brand '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la marca satisfactoriamente")')->count());

        return $crawler;
    }
 
    public function createBrandModel($uid, Brand $brand) 
    {
        //index
        $crawler = $this->client->request('GET', '/admin/models/', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Modelos")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo modelo")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['brand_model[name]'] = 'brandmodel '.$uid;
        if($brand instanceof Brand){
            $form['brand_model[brand]']->select($brand->getId());
        }
        $form['brand_model[available]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("brandmodel '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el modelo satisfactoriamente")')->count());

        return $crawler;
    }
    
    protected function createTagBlog($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/post/tag/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/post/tag/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Etiquetas")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva etiqueta")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['tag[name]'] = 'tag '.$uid;
        $form['tag[description]'] = 'tag description'.$uid;
        $form['tag[metaTitle]'] = 'Meta title_'.$uid;
        $form['tag[metaDescription]'] = 'Meta description_'.$uid;
        $form['tag[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("tag '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la etiqueta satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    public function createProduct($uid, $active=false, $admin=true) 
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        if(!$admin){
            ///////////////////////////////////////////////////////////////////////////
            // Actor //////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////
            $actorId = rand(999,9999);
            $crawler = $this->createUser('actor', $actorId);
            $username = 'actor+'.$actorId.'@email.com';
            $actor = $manager->getRepository('CoreBundle:Actor')->findOneByEmail($username);
            $this->actor = $actor;
            $this->password = $actorId;
        }
        
        
        ////////////////////////////////////////////////////////////////////////////
        // Category ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $categoryId = rand(999,9999);
        $crawler = $this->createCategory($categoryId);
        $categoryName = 'category '.$categoryId;
        $category = $manager->getRepository('EcommerceBundle:Category')->findOneByName($categoryName);
        
        ////////////////////////////////////////////////////////////////////////////
        // Brand ///////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $brandId = rand(999,9999);
        $crawler = $this->createBrand($brandId);
        $brandName = 'brand '.$brandId;
        $brand = $manager->getRepository('EcommerceBundle:Brand')->findOneByName($brandName);
        
        //////////////////////////////////////////////////////////////////////////
        // Model /////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////
        $modelId = rand(999,9999);
        $crawler = $this->createBrandModel($modelId, $brand);
        $brandModelName = 'brandmodel '.$modelId;
        $brandModel = $manager->getRepository('EcommerceBundle:BrandModel')->findOneByName($brandModelName);
        
        //////////////////////////////////////////////////////////////////////////
        // Product ///////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////
        $crawler = $this->client->request('GET', '/admin/products/', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Productos")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo producto")')->count());
   
        //fill form
        $form = $crawler->filter('form[name="product"]')->form();
//        $form['product[actor]']->select($actor->getId());
        $form['product[category]']->select($category->getId());
        $form['product[brand]']->select($brand->getId());
        $form['product[model]']->select($brandModel->getId());
        $form['product[name]'] = 'product '.$uid;
        $form['product[description]'] = 'product description'.$uid;
        $form['product[initPrice]'] = 100;
        $form['product[price]'] = 100;
        $form['product[priceType]'] = 0;
        $form['product[weight]'] = 1;
        $form['product[stock]'] = 20;
        $form['product[metaTitle]'] = 'Meta title_'.$uid;
        $form['product[metaDescription]'] = 'Meta description_'.$uid;
        if($active)$form['product[active]']->tick();
        $form['product[available]']->tick();
        $form['product[publishDateRange]'] = '01/01/'.(date('Y')-1).' 30/12/'.(date('Y')+1);
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("product '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el producto satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    protected function fillRegisterFormUser($uid, $crawler)
    {
            
        //fill form
        $form = $crawler->filter('form[name="registration"]')->form();
        $form['registration[actor][email]'] = 'actor+'.$uid.'@email.com';
        $form['registration[actor][username]'] = 'actor'.$uid;
        $form['registration[actor][name]'] = 'Name_'.$uid;
        $form['registration[actor][password][first]'] = $uid;
        $form['registration[actor][password][second]'] = $uid;
        $form['registration[actor][lastname]'] = 'Surname_'.$uid;
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
        
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $this->user = $manager->getRepository('CoreBundle:Actor')->findOneByEmail('actor+'.$uid.'@email.com');
        
        return $crawler;
            
    }
    
    protected function fillDeliveryInfo($uid, $crawler)
    {
            
        //fill form
        $form = $crawler->filter('form[name="delivery"]')->form();
        $form['delivery[fullName]'] = 'full name '.$uid;
        $form['delivery[dni]'] = '30110048N';
        $form['delivery[address]'] = 'Address '.$uid;      
        $form['delivery[city]'] = 'City '.$uid;   
        $form['delivery[state]']->select(32);
        $form['delivery[postalCode]'] = '1234';
        $form['delivery[phone]'] = '123123123';
        $form['delivery[phone2]'] = '321321321';
        $form['delivery[preferredSchedule]']->select(1);
        $form['delivery[notes]'] = 'notes '.$uid;
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful()); 
        
        return $crawler;
            
    }
    
    protected function checkDeliveryInfo($uid, $crawler)
    {
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Datos del cliente")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("full name '.$uid.'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Address '.$uid.'")')->count()); 
        $this->assertGreaterThan(0,$crawler->filter('html:contains("1234 City '.$uid.'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Madrid, spain")')->count());
                
        $container = $this->client->getContainer();
        $core = $container->getParameter('core');
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Datos del cliente")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['name'].'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['id'].'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['address'].'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['postal_code'].' '.$core['company']['city'].'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['sales_phone'].'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['sales_fax'].'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("'.$core['company']['website_url'].'")')->count());
        return $crawler;
    }
     
    protected function fillSummary($uid, $crawler)
    {
        //fill form
        $form = $crawler->filter('form[name="credit_card"]')->form();
        $form['credit_card[firstname]'] = 'name '.$uid;
        $form['credit_card[lastname]'] = 'buyer '.$uid;
        $form['credit_card[cardNo]'] = '4548812049400004';      
        $form['credit_card[expirationDate][month]']->select(12);
        $form['credit_card[expirationDate][year]']->select(2017);
        $form['credit_card[CVV]'] = '123';
        $crawler = $this->client->submit($form);// submit the form
        
        
        //Asserts
        //$this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        //$crawler = $this->client->followRedirect();
        //$this->assertTrue($this->client->getResponse()->isSuccessful()); 
        
        return $crawler;
            
    }
    
    protected function checkSummary($uid, $crawler)
    {
        $this->assertGreaterThan(0,$crawler->filter('html:contains("product '.$uid.'")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Pago mediante tarjeta de crédito")')->count());
        return $crawler;
    }
    
    protected function getTransaction()
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $this->transaction = $manager->getRepository('EcommerceBundle:Transaction')->findOneBy(
                array('actor' => $this->user),
                array('id' => 'ASC')
                );
    }
    
    protected function createPlan($uid)
    {
        //index
        $crawler = $this->client->request('GET', '/admin/plan/', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
      
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Planes")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo plan")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['plan[name]'] = 'plan '.$uid;
        $form['plan[description]'] = 'description '.$uid;
        $form['plan[setupAmount]'] = '4.99';
        $form['plan[frequency]']->select('DAY'); 
        $form['plan[frequencyInterval]'] = '1';
        $form['plan[cycles]'] = '10';
        $form['plan[amount]'] = '5.10';
        $form['plan[visible]']->tick();
        $form['plan[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("plan '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el plan satisfactoriamente")')->count());

        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $this->plan = $manager->getRepository('EcommerceBundle:Plan')->findOneByName('plan '.$uid);
        
        return $crawler;
    }
    
    protected function createPack($name)
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $pack = new Pack();
        $pack->setName($name);
        $pack->setPrice(5);
        $manager->persist($pack);
        $manager->flush();
           
        return $pack;
    }
    
    protected function createContract($uid, $actor, $plan)
    {
        //index
        $crawler = $this->client->request('GET', '/admin/contract/', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Contratos")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo contrato")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['ecommercebundle_contract[actor]']->select($actor->getId());
        $form['ecommercebundle_contract[url]'] =  'http://www.local.com/terminos-condiciones';
        $form['ecommercebundle_contract[agreement][name]'] = 'contract '.$uid;
        $form['ecommercebundle_contract[agreement][description]']= 'contract description'.$uid;
        $form['ecommercebundle_contract[agreement][plan]']->select($plan->getId());
        //fill cc form
        $form['ecommercebundle_contract[agreement][creditCard][firstname]'] = 'name '.$uid;
        $form['ecommercebundle_contract[agreement][creditCard][lastname]'] = 'buyer '.$uid;
        $form['ecommercebundle_contract[agreement][creditCard][cardNo]'] = '4548812049400004';      
        $form['ecommercebundle_contract[agreement][creditCard][expirationDate][month]']->select(12);
        $form['ecommercebundle_contract[agreement][creditCard][expirationDate][year]']->select(2017);
        $form['ecommercebundle_contract[agreement][creditCard][CVV]'] = '123';
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el contrato satisfactoriamente")')->count());

        return $crawler;
    }
    
    protected function createAdvert($uid, $user, $username=null, $password=null, $actorView=false)
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        ////////////////////////////////////////////////////////////////////////////
        // Located /////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $locatedId = rand(999,9999);
        $crawler = $this->createLocated($locatedId);
        $locatedName = 'located '.$locatedId;
        $located = $manager->getRepository('EcommerceBundle:Located')->findOneByName($locatedName);
        
        //index
        if($actorView){
            $crawler = $this->client->request('GET', '/admin/actor/'.$user->getId().'?adverts=1', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }else{
            if(is_null($username) && is_null($password)){
                $crawler = $this->client->request('GET', '/admin/advert/', array(), array(), array(
                    'PHP_AUTH_USER' => 'admin',
                    'PHP_AUTH_PW'   => 'admin',
                ));
            }else{
                $crawler = $this->client->request('GET', '/admin/advert/', array(), array(), array(
                    'PHP_AUTH_USER' => $username,
                    'PHP_AUTH_PW'   => $password,
                ));
            }
        }
                    
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        if($actorView){
            ///////////////////////////////////////////////////////////////////////////////////////////
            //Click new ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////////////////
            $link = $crawler
                ->filter('a:contains("Crear publicidad")') // find all links with the text "Greet"
                ->eq(0) // select the second link in the list
                ->link()
            ;
        }else{
            $this->assertGreaterThan(0, $crawler->filter('html:contains("Publicidades")')->count());
      
            ///////////////////////////////////////////////////////////////////////////////////////////
            //Click new ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////////////////
            $link = $crawler
                ->filter('a:contains("Añadir nueva")') // find all links with the text "Greet"
                ->eq(0) // select the second link in the list
                ->link()
            ;
        }
        
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva publicidad")')->count());
  
        
        # Select the file from the filesystem
//        $image = new UploadedFile(
//            # Path to the file to send
//            dirname(__FILE__).'/images/image.jpg',
//            # Name of the sent file
//            uniqid().'.jpg', 
//            # MIME type
//            'image/jpeg',
//            # Size of the file
//            9988
//        );
//        //fill form
//        $form = $crawler->selectButton('Guardar')->form();
//        $values = array(
//            'advert' => array(
//                '_token' => $form['advert[_token]']->getValue(),
//                'actor' => $actor->getId(),
//                'geolocated' => array('all'),
//                'located' => array($located->getId()),
//                'codes' => '08349,08340',
//                'title' => 'advert '.$uid,
//                'description' => 'advert description '.$uid,
//                'rangeDate' => '08/06/2016 28/06/2016',
//                'days' => '10',
//                'creditCard' => array(
//                    'firstname' => 'sebas',
//                    'lastname' => 'buyer',
//                    'cardType' => 'visa',
//                    'cardNo' => '4548812049400004',
//                    'expirationDate' => array(
//                        'day' => '1',
//                        'month' => '12',
//                        'year' => '2017'
//                    ),
//                    'CVV' => '123',
//                    'ts' => '',
//                ),
//            ),
//        );
//            
//        $files = array(
//            'advert' => array('image' => array('0' => (array('file' => $image))))
//        );
//
//        $crawler = $this->client->request(
//          $form->getMethod(),
//          $form->getUri(),
//          $values,
//          $files, 
//          array(
//            'PHP_AUTH_USER' => 'admin',
//            'PHP_AUTH_PW'   => 'admin',
//          )
//        );
//        print_r($crawler->html());die();
        
        $form = $crawler->selectButton('Guardar')->form();
        if(!$actorView){
            $form['advert[actor]']->select($user->getId());
        }
        
        $form['advert[located]']->select(array($located->getId()));
        $form['advert[title]'] = 'advert '.$uid;
        $form['advert[description]']= 'advert description'.$uid;
//        $form['advert[image]']->upload($image);
        $form['advert[rangeDate]']= '08/06/2016 28/06/2016';
        $form['advert[days]']= '20';
        
        //fill cc form
        $form['advert[creditCard][firstname]'] = 'name '.$uid;
        $form['advert[creditCard][lastname]'] = 'buyer '.$uid;
        $form['advert[creditCard][cardNo]'] = '4548812049400004';    
        $form['advert[creditCard][expirationDate][day]']->select(1);
        $form['advert[creditCard][expirationDate][month]']->select(12);
        $form['advert[creditCard][expirationDate][year]']->select(2017);
        $form['advert[creditCard][CVV]'] = '123';
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la publicidad satisfactoriamente.")')->count());
        

        return $crawler;
    }
    
    protected function createLocated($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/located/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/located/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Ubicaciones")')->count());
      
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva ubicación")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $form['located[name]'] = 'located '.$uid;
        $form['located[height]'] = '235px';
        $form['located[width]'] = '235px';
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("located '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la ubicación satisfactoriamente")')->count());

        return $crawler;
    }
    
    protected function createSlider($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/sliders/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/sliders/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Carrusel de imagenes")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo slider")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el ítem del Slider satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    protected function createMenuItem($uid, $username=null, $password=null)
    {
        //index
        if(is_null($username) && is_null($password)){
            $crawler = $this->client->request('GET', '/admin/menuitems/', array(), array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'admin',
            ));
        }else{
            $crawler = $this->client->request('GET', '/admin/menuitems/', array(), array(), array(
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW'   => $password,
            ));
        }
        
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Menú Items")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nuevo item del menú")')->count());
   
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el item del menú")')->count());
        
        return $crawler;
    }
    
    public function getEntity($uid, $repository) {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $repo = $manager->getRepository($repository);
        $all = $repo->findAll();

        if(method_exists($all[0], 'getTranslations')){
            $qb = $repo->createQueryBuilder('r')
                        ->join('r.translations', 't')
                        ->where('t.name LIKE :search')
                        ->andWhere('t.locale = :locale')
                        ->setParameter('locale', 'es')
                        ->setParameter('search', '%'.$uid.'%');
        }else{
            $qb = $repo->createQueryBuilder('r')
                    ->where('r.name LIKE :search')
                    ->setParameter('search', '%'.$uid.'%');
        }
        
        $query = $qb->getQuery();
        $entity = $query->getOneOrNullResult();
       
        return $entity;

    }
    
    public function addTestAddress($actor)
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        $country = $manager->getRepository('CoreBundle:Country')->find('es');
        $state = $manager->getRepository('CoreBundle:State')->findOneByName('Barcelona');
            
        $address = new Address();
        $address->setAddress('Test address 113');
        $address->setPostalCode('08349');
        $address->setCity('Cabrera de Mar');
        $address->setState($state);
        $address->setCountry($country);
        $address->setPhone('123123123');
        $address->setPreferredSchedule(1);
        $address->setContactPerson('Testo Ramon');
        $address->setForBilling(true);
        $address->setDni('33956669K');
        $address->setActor($actor);
        
        $manager->persist($address);
        $manager->flush();
    }
}
