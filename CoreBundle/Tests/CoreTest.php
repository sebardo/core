<?php

namespace CoreBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EcommerceBundle\Entity\Brand;
use CoreBundle\Entity\Pack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use CoreBundle\Entity\Optic;
use CoreBundle\Entity\Actor;


class CoreTest  extends WebTestCase
{

    protected $client = null;
    
    protected $optic = null;
    
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
                $form['bundle_corebundle_actor[email]'] = 'user+'.$uid.'@gmail.com';
                $form['bundle_corebundle_actor[username]'] = 'user'.$uid;
                $form['bundle_corebundle_actor[password]'] = $uid;
                $form['bundle_corebundle_actor[name]'] = 'Name_'.$uid;
                $form['bundle_corebundle_actor[surnames]'] = 'Surname_'.$uid;
                $form['bundle_corebundle_actor[isActive]']->tick();
                $form['bundle_corebundle_actor[newsletter]']->tick();
                $crawler = $this->client->submit($form);// submit the form

                //Asserts
                $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
                $crawler = $this->client->followRedirect();
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("user'.$uid.'")')->count());
                break;
            case 'optic':
                //Optic index
                $crawler = $this->client->request('GET', '/admin/optic', array(), array(), array(
                    'PHP_AUTH_USER' => 'admin',
                    'PHP_AUTH_PW'   => 'admin',
                ));
                //Asserts
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("Opticas")')->count());

                //Click new
                $link = $crawler
                    ->filter('a:contains("Añadir nuevo")') // find all links with the text "Greet"
                    ->eq(0) // select the second link in the list
                    ->link()
                ;
                $crawler = $this->client->click($link);// and click it
                //Asserts
                $this->assertTrue($this->client->getResponse()->isSuccessful());
                $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva optica")')->count());

                //fill form
                $form = $crawler->selectButton('Guardar')->form();
                $form['corebundle_optictype[email]'] = 'optic+'.$uid.'@gmail.com';
                $form['corebundle_optictype[username]'] = 'optic'.$uid;
                $form['corebundle_optictype[password]'] = $uid;
                $form['corebundle_optictype[name]'] = 'Name_'.$uid;
                $form['corebundle_optictype[description]'] = 'Description_'.$uid;
                $form['corebundle_optictype[isActive]']->tick();
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
                $this->assertGreaterThan(0, $crawler->filter('html:contains("optic+'.$uid.'@gmail.com")')->count());

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
        $form['post[title]'] = 'post '.$uid;
        $form['post[description]'] = '<p>post <b>description</b> '.$uid. '</p>';
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
        $form['ecommercebundle_categorytype[name]'] = 'category '.$uid;
        $form['ecommercebundle_categorytype[description]'] = 'category description'.$uid;
        $form['ecommercebundle_categorytype[metaTitle]'] = 'Meta title_'.$uid;
        $form['ecommercebundle_categorytype[metaDescription]'] = 'Meta description_'.$uid;
        $form['ecommercebundle_categorytype[active]']->tick();
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
        $form['ecommercebundle_brandtype[name]'] = 'brand '.$uid;
        $form['ecommercebundle_brandtype[available]']->tick();
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
        $form['ecommercebundle_brandmodeltype[name]'] = 'brandmodel '.$uid;
        if($brand instanceof Brand){
            $form['ecommercebundle_brandmodeltype[brand]']->select($brand->getId());
        }
        $form['ecommercebundle_brandmodeltype[available]']->tick();
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
    
    public function createProduct($uid, $active=false) 
    {
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        ///////////////////////////////////////////////////////////////////////////
        // Optic //////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////
        $opticId = rand(999,9999);
        $crawler = $this->createUser('optic', $opticId);
        $username = 'optic+'.$opticId.'@gmail.com';
        $optic = $manager->getRepository('CoreBundle:Optic')->findOneByEmail($username);
        $this->optic = $optic;
        $this->password = $opticId;
        
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
        $form = $crawler->filter('form[name="ecommercebundle_producttype"]')->form();
        $form['ecommercebundle_producttype[optic]']->select($optic->getId());
        $form['ecommercebundle_producttype[category]']->select($category->getId());
        $form['ecommercebundle_producttype[brand]']->select($brand->getId());
        $form['ecommercebundle_producttype[model]']->select($brandModel->getId());
        $form['ecommercebundle_producttype[name]'] = 'product '.$uid;
        $form['ecommercebundle_producttype[description]'] = 'product description'.$uid;
        $form['ecommercebundle_producttype[initPrice]'] = 100;
        $form['ecommercebundle_producttype[price]'] = 100;
        $form['ecommercebundle_producttype[priceType]'] = 0;
        $form['ecommercebundle_producttype[weight]'] = 1;
        $form['ecommercebundle_producttype[stock]'] = 20;
        $form['ecommercebundle_producttype[metaTitle]'] = 'Meta title_'.$uid;
        $form['ecommercebundle_producttype[metaDescription]'] = 'Meta description_'.$uid;
        if($active)$form['ecommercebundle_producttype[active]']->tick();
        $form['ecommercebundle_producttype[available]']->tick();
        $form['ecommercebundle_producttype[public]']->tick();
        $form['ecommercebundle_producttype[publishDateRange]'] = '01/01/'.(date('Y')-1).' 30/12/'.(date('Y')+1);
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("product '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el producto satisfactoriamente")')->count());
        
        return $crawler;
    }
    
    protected function registerUser($uid, $crawler)
    {
            
        //fill form
        $form = $crawler->filter('form[name="corebundle_registrationtype"]')->form();
        $form['corebundle_registrationtype[actor][email]'] = 'email+'.$uid.'@gmail.com';
        $form['corebundle_registrationtype[actor][username]'] = 'user'.$uid;
        $form['corebundle_registrationtype[actor][password][password]'] = $uid;
        $form['corebundle_registrationtype[actor][password][confirm]'] = $uid;
        $form['corebundle_registrationtype[actor][name]'] = 'Name_'.$uid;
        $form['corebundle_registrationtype[actor][surnames]'] = 'Surname_'.$uid;
        $form['corebundle_registrationtype[actor][newsletter]']->tick();
        $form['corebundle_registrationtype[terms]']->tick();
        $form['corebundle_registrationtype[city]'] = 'Surname_'.$uid;
        $form['corebundle_registrationtype[state]']->select(32);
        $form['corebundle_registrationtype[country]']->select('es');
        $crawler = $this->client->submit($form);// submit the form

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful()); 
        
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $this->user = $manager->getRepository('CoreBundle:Actor')->findOneByEmail('email+'.$uid.'@gmail.com');
        
        return $crawler;
            
    }
    
    protected function fillDeliveryInfo($uid, $crawler)
    {
            
        //fill form
        $form = $crawler->filter('form[name="ecommercebundle_deliverytype"]')->form();
        $form['ecommercebundle_deliverytype[fullName]'] = 'full name '.$uid;
        $form['ecommercebundle_deliverytype[dni]'] = '30110048N';
        $form['ecommercebundle_deliverytype[address]'] = 'Address '.$uid;      
        $form['ecommercebundle_deliverytype[city]'] = 'City '.$uid;   
        $form['ecommercebundle_deliverytype[state]']->select(32);
        $form['ecommercebundle_deliverytype[postalCode]'] = '1234';
        $form['ecommercebundle_deliverytype[phone]'] = '123123123';
        $form['ecommercebundle_deliverytype[phone2]'] = '321321321';
        $form['ecommercebundle_deliverytype[preferredSchedule]']->select(1);
        $form['ecommercebundle_deliverytype[notes]'] = 'notes '.$uid;
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
        
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Datos del cliente")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("VIRUAL MATRIX, S.L.")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("B-86665544")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Torrejón de Ardoz Calle La Solana, nº17")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("28850 Madrid")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Tel. 918 266 588 | Fax. 918 266 588")')->count());
        $this->assertGreaterThan(0,$crawler->filter('html:contains("www.local.com")')->count());
        return $crawler;
    }
     
    protected function fillSummary($uid, $crawler)
    {
        //fill form
        $form = $crawler->filter('form[name="ecommerce_creditcard"]')->form();
        $form['ecommerce_creditcard[firstname]'] = 'name '.$uid;
        $form['ecommerce_creditcard[lastname]'] = 'buyer '.$uid;
        $form['ecommerce_creditcard[cardNo]'] = '4548812049400004';      
        $form['ecommerce_creditcard[expirationDate][month]']->select(12);
        $form['ecommerce_creditcard[expirationDate][year]']->select(2017);
        $form['ecommerce_creditcard[CVV]'] = '123';
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
        $this->assertGreaterThan(0,$crawler->filter('html:contains("100 €")')->count()); 
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
        $pack = $this->createPack('Pack '.rand(999,9999));
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
        $form['ecommercebundle_plan[name]'] = 'plan '.$uid;
        $form['ecommercebundle_plan[description]'] = 'description '.$uid;
        $form['ecommercebundle_plan[setupAmount]'] = '4.99';
        $form['ecommercebundle_plan[frequency]']->select('DAY'); 
        $form['ecommercebundle_plan[frequencyInterval]'] = '1';
        $form['ecommercebundle_plan[cycles]'] = '10';
        $form['ecommercebundle_plan[amount]'] = '5.10';
        $form['ecommercebundle_plan[pack]']->select($pack->getId()); 
        $form['ecommercebundle_plan[visible]']->tick();
        $form['ecommercebundle_plan[active]']->tick();
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
    
    protected function createContract($uid, $optic, $plan)
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
        $form['ecommercebundle_contract[optic]']->select($optic->getId());
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
    
    protected function createAdvert($uid, $user, $username=null, $password=null, $opticView=false)
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
        
        ////////////////////////////////////////////////////////////////////////////
        // Brand ///////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $brandId = rand(999,9999);
        $crawler = $this->createBrand($brandId);
        $brandName = 'brand '.$brandId;
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $brand = $manager->getRepository('EcommerceBundle:Brand')->findOneByName($brandName);

        //index
        if($opticView){
            $crawler = $this->client->request('GET', '/admin/optic/'.$user->getId().'?adverts=1', array(), array(), array(
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
        if($opticView){
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
//            'ecommercebundle_advert' => array(
//                '_token' => $form['ecommercebundle_advert[_token]']->getValue(),
//                'optic' => $optic->getId(),
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
//            'ecommercebundle_advert' => array('image' => array('0' => (array('file' => $image))))
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
        if(!$opticView){
           if($user instanceof Optic){
                $form['ecommercebundle_advert[optic]']->select($user->getId());
            }elseif($user instanceof Actor){
                $form['ecommercebundle_advert[actor]']->select($user->getId());
                
                $form['ecommercebundle_advert[brand]']->select($brand->getId());
            } 
        }
        
        $form['ecommercebundle_advert[geolocated]']->select('all');
        $form['ecommercebundle_advert[located]']->select(array($located->getId()));
        $form['ecommercebundle_advert[codes]'] =  '08349,08340';
        $form['ecommercebundle_advert[title]'] = 'advert '.$uid;
        $form['ecommercebundle_advert[description]']= 'advert description'.$uid;
//        $form['ecommercebundle_advert[image]']->upload($image);
        $form['ecommercebundle_advert[rangeDate]']= '08/06/2016 28/06/2016';
        $form['ecommercebundle_advert[days]']= '20';
        
        //fill cc form
        $form['ecommercebundle_advert[creditCard][firstname]'] = 'name '.$uid;
        $form['ecommercebundle_advert[creditCard][lastname]'] = 'buyer '.$uid;
        $form['ecommercebundle_advert[creditCard][cardNo]'] = '4548812049400004';    
        $form['ecommercebundle_advert[creditCard][expirationDate][day]']->select(1);
        $form['ecommercebundle_advert[creditCard][expirationDate][month]']->select(12);
        $form['ecommercebundle_advert[creditCard][expirationDate][year]']->select(2017);
        $form['ecommercebundle_advert[creditCard][CVV]'] = '123';
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
        $form['ecommercebundle_located[name]'] = 'located '.$uid;
        $form['ecommercebundle_located[height]'] = '235px';
        $form['ecommercebundle_located[width]'] = '235px';
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
        $form['slider[title]'] = 'slider '.$uid;
        $form['slider[caption]'] = 'caption slider '.$uid;
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado el item del menú")')->count());
        
        return $crawler;
    }
}
