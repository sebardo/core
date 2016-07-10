<?php
namespace CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CoreBundle\Form\Model\Import;
use CoreBundle\Entity\Optic;
use CoreBundle\Entity\Image;
use CoreBundle\Entity\State;
use CoreBundle\Entity\OpticStats;
use DateTime;

class ImportCommand extends Command
{

    CONST SERVER = '127.0.0.1';
    CONST DB_NAME = 'prod_database';
    CONST DB_USER = 'user';
    CONST DB_PASS = 'pass';

    protected function configure()
    {
        $this
        ->setName('core:import')
        ->setDescription('Import from old database')
        ->addArgument('start', InputArgument::REQUIRED, 'Start id to import')
        ->addArgument('end', InputArgument::REQUIRED, 'End id limit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = $input->getArgument("start");
        $end = $input->getArgument("end");

        $this->container = $this->getApplication()->getKernel()->getContainer();

        $time1 = new \DateTime();
        $seconds1 = $time1->getTimestamp();
        //Import
        $entity = new Import();
        $entity->setServer(self::SERVER);
        $entity->setDbname(self::DB_NAME);
        $entity->setUsername(self::DB_USER);
        $entity->setPassword(self::DB_PASS);
        $entity->setLimitStart($start);
        $entity->setLimitEnd($end);
        
//        $this->importCategories($entity, $output);
//        $this->importBrands($entity, $output);
//        $this->importModels($entity, $output);
        
//        $this->importOptics($entity, $output);
        $this->importProducts($entity, $output);
//        $this->importProductStats($entity, $output);
//        $this->importOpticStats($entity, $output);

        $time2 = new \DateTime();
        $seconds2 = $time2->getTimestamp();
        $time = $seconds2 - $seconds1;
        $output->writeln("<info>Import success in: ".$time." seconds</info>");
    }
    
    public function importCategories(Import $entity, $output)
    {
        $em = $this->container->get('doctrine')->getManager();
        $sql = ' SELECT s.id, s.nombre, s.slug FROM  `servicio` AS s '
                . ' ORDER BY s.id ';
//               . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql); 
        if (mysqli_num_rows($resultado)>0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                //Create Categories
                $category = new Category();
                $category->setName(utf8_encode($fila['nombre']));
                $category->setDescription(utf8_encode($fila['nombre']));
                $category->setMetaTitle(utf8_encode($fila['nombre']));
                $category->setMetaDescription(utf8_encode($fila['nombre']));
                $category->setSlug(utf8_encode($fila['slug']));
                $em->persist($category);
            }
            
            $em->flush();
        }
    }
    
    public function importBrands(Import $entity, $output)
    {
        $em = $this->container->get('doctrine')->getManager();
        $sql = ' SELECT m.id_marca, m.nombre, m.slug FROM  `marca` AS m '
                . ' ORDER BY m.id_marca ';
//                . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql); 
        $arrayBrand = array();
        if (mysqli_num_rows($resultado)>0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                if(!in_array(utf8_encode($fila['nombre']), $arrayBrand)){
                    //Create Brands
                    $brand = new Brand();
                    $brand->setName(utf8_encode($fila['nombre']));
                    $brand->setAvailable(true);
                    $em->persist($brand);
                    $arrayBrand[] = utf8_encode($fila['nombre']);
                }
                
            }
            
            $em->flush();
        }
    }
    
    public function importModels(Import $entity, $output)
    {
        $em = $this->container->get('doctrine')->getManager();
        $sql = ' SELECT m.id_modelo, m.id_marca, m.nombre, m.slug, mm.nombre as marca FROM  `modelo` AS m '
                . ' LEFT JOIN marca AS mm ON m.id_marca = mm.id_marca '
                . ' ORDER BY m.id_marca ';
//                . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql); 
        $arrayBrand = array();
        if (mysqli_num_rows($resultado)>0) {
            
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $brand = $em->getRepository('EcommerceBundle:Brand')->findOneByName(utf8_encode($fila['marca']));
                $brandModel = $em->getRepository('EcommerceBundle:BrandModel')->findOneByName(utf8_encode($fila['nombre']));
                
                if(!$brandModel instanceof BrandModel) {
                    if(!in_array(utf8_encode($fila['nombre']), $arrayBrand)){
                        //Create BrandModel
                        $brandModel = new BrandModel();
                        $brandModel->setName(utf8_encode($fila['nombre']));
                        $brandModel->setAvailable(true);
                        if($brand instanceof Brand) $brandModel->setBrand($brand);
                        $em->persist($brandModel);
                        $arrayBrand[] = utf8_encode($fila['nombre']);
                    }
                }
                
            }
            
            $em->flush();
        }
    }
    
 
    public function importOptics(Import $entity, $output)
    {
        
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder(new Optic());
        $em = $this->container->get('doctrine')->getManager();
        $role = $em->getRepository('CoreBundle:Role')->findOneByRole('ROLE_OPTIC');

        $sql = ' SELECT o.id, o.id_usuario, o.id_pack, o.id_codigo_postal, o.nombre, o.asociacion, o.codigo, o.cuenta, o.recibir_paquete, '
                . ' o.direccion, o.provincia, o.cp, o.lat, o.lang, o.descripcion, o.url_img, slug, o.acu_visitas, o.acu_ventas, o.extension_img, '
                . ' o.autor, o.autor_descripcion, o.acu_productos, o.acu_servicios, o.url_facebook, o.url_twitter, o.tiene_web, o.newsletter, '
                . ' o.recibir_informacion, o.meta_titulo, o.meta_descripcion, o.meta_keywords, o.bank_account_token, o.bank_account_time, '
                . ' o.responsable, o.razon, o.cif, u.email, u.password, u.username, u.salt, u.estado, u.fecha_alta '
                . ' FROM  `optica` AS o '
                . ' LEFT JOIN usuario AS u ON o.id_usuario = u.id '
//                . ' WHERE o.url_img IS NOT NULL '
                . ' ORDER BY o.id '
                . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $title = null;
        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql); 
 
        if (mysqli_num_rows($resultado)>0) {
            $x=0;
            $count = 0;
            $count2 = 0;
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $x++;
                print_r($x.'-'.utf8_encode($fila['nombre']));echo PHP_EOL;
                $user = new Optic();
                if($fila['id_usuario'] == 1) {
                    $count++ ;
                    $username = $this->slugify(utf8_encode($fila['nombre']));
                    $user->setUsername($username);
                    $user->setEmail($username.$fila['id'].'local.com');
                }else {
                    $count2++;
                    $user->setUsername(utf8_encode($fila['username']));
                    $user->setEmail(utf8_encode($fila['email']));
                }
                
                $user->setName(utf8_encode($fila['nombre']));
                $user->setCreated(new DateTime($fila['fecha_alta']));
                $user->addRole($role);
                $user->setPassword($fila['password']);
                $user->setSalt($fila['salt']);
                $user->setDescription(utf8_encode($fila['descripcion']));
                $user->setSlug($this->slugify(utf8_encode($fila['nombre'].'-'.$fila['provincia'])));
                $user->setLatitude($fila['lat']);
                $user->setLongitude($fila['lang']);
                $user->setAssociation(utf8_encode($fila['asociacion']));
                $user->setBankAccountNumber($fila['cuenta']);
                $user->setAddress(utf8_encode($fila['direccion']));
                $user->setPostalCode($fila['cp']);
                $user->setCity(utf8_encode($fila['provincia']));
                $user->setFacebookUrl($fila['url_facebook']);
                $user->setTwitterUrl($fila['url_twitter']);
                if(utf8_encode($fila['meta_titulo']) != '' ) $user->setMetaTitle($fila['meta_titulo']);
                else $user->setMetaTitle($fila['nombre']);
                if(utf8_encode($fila['meta_descripcion']) != '' ) $user->setMetaDescription($fila['meta_descripcion']);
                else $user->setMetaDescription($fila['descripcion']);
                if(utf8_encode($fila['meta_keywords']) != '' ) $user->setMetaTags($fila['meta_keywords']);
                else $user->setMetaTags($fila['nombre']);
                
                //pack
                $pack = $em->getRepository('CoreBundle:Pack')->find($fila['id_pack']);
                $user->setPack($pack);
                
                //state and country
                $this->setStateAndCountry($fila, $user);
                $em->persist($user);
                $em->flush();
//                print_r($fila);
                //image
                if($fila['url_img']!=''){
                    $dir = '/home/sebastian/www/src/Symfony/web/uploads/documents';
                    
                    $imagePath = '';
                    $absoluteImagePath = $dir.'/'.utf8_encode($fila['url_img']);
                    if(file_exists($absoluteImagePath.'.jpeg')) $imagePath = utf8_encode($fila['url_img']).'.jpeg';
                    if(file_exists($absoluteImagePath.'.jpg')) $imagePath = utf8_encode($fila['url_img']).'.jpg';
                    if(file_exists($absoluteImagePath.'.gif')) $imagePath = utf8_encode($fila['url_img']).'.gif';
                    if(file_exists($absoluteImagePath.'.png')) $imagePath = utf8_encode($fila['url_img']).'.png';
                    
                    //Thumb
                    $imagePathThumb = '';
                    $absoluteImagePathThumb = $dir.'/'.utf8_encode($fila['url_img']).'_260.jpg';
                    if(file_exists($absoluteImagePathThumb)) {
                        $imagePathThumb = utf8_encode($fila['url_img']).'_260.jpg';
                    }
                    $imagePathThumb2 = '';
                    $absoluteImagePathThumb2 = $dir.'/'.utf8_encode($fila['url_img']).'_142.jpg';
                    if(file_exists($absoluteImagePathThumb2)) {
                        $imagePathThumb2 = utf8_encode($fila['url_img']).'_142.jpg';
                    }
                    
                    print_r($dir . '/' . $imagePath); echo PHP_EOL;
                    @mkdir(__DIR__ . '/../../../../../web/uploads/images/profile');
                    @mkdir(__DIR__ . '/../../../../../web/uploads/images/profile/'.$user->getId());
                    @mkdir(__DIR__ . '/../../../../../web/uploads/images/profile/'.$user->getId().'/thumbnail');
                    
                    copy($dir . '/' . $imagePath, __DIR__ . '/../../../../../web/uploads/images/profile/' . $user->getId().'/'.$imagePath);
                    if($imagePathThumb != '') copy($dir . '/' . $imagePathThumb, __DIR__ . '/../../../../../web/uploads/images/profile/' . $user->getId().'/thumbnail/'.$imagePathThumb);
                    if($imagePathThumb2 != '') copy($dir . '/' . $imagePathThumb2, __DIR__ . '/../../../../../web/uploads/images/profile/' . $user->getId().'/thumbnail/'.$imagePathThumb2);
                    
                    $image0 = new Image();
                    $image0->setPath($imagePath);
                    $user->setImage($image0);
                    $em->persist($image0); 
                }
                

                $em->flush();
                    

            }

            $output->writeln('Opticas sin usuarios = '.$count.' ');
            $output->writeln('Opticas con usuarios = '.$count2.' ');

        } else {
            $output->writeln('No se ha procesado ningun usuario');
        }

    }

    public function importProducts(Import $entity, $output)
    {
        $sql = ' SELECT p.*, o.nombre as opticName, m.nombre as brand, mo.nombre as model, s.nombre as category FROM  `producto` AS p '
                . ' LEFT JOIN optica AS o ON o.id = p.id_optica '
                . ' LEFT JOIN servicio AS s ON s.id = p.id_servicio '
                . ' LEFT JOIN marca AS m ON m.id_marca = p.id_marca '
                . ' LEFT JOIN modelo AS mo ON mo.id_modelo = p.id_modelo '
                . ' ORDER BY p.id '
                . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql);
        $x = 0;
        if (mysqli_num_rows($resultado)>0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $x++;
                print_r($x.'-'.utf8_encode($fila['nombre']));echo PHP_EOL;
                
                $em = $this->container->get('doctrine')->getManager();
                $optic = $em->getRepository('CoreBundle:Optic')->findOneByName(utf8_encode($fila['opticName']));
                $category = $em->getRepository('EcommerceBundle:Category')->findOneByName(utf8_encode($fila['category']));
                $brand = $em->getRepository('EcommerceBundle:Brand')->findOneByName(utf8_encode($fila['brand']));
                $model = $em->getRepository('EcommerceBundle:BrandModel')->findOneByName(utf8_encode($fila['model']));

                //Create Products
                if($optic instanceof Optic && 
                        $fila['id']!= 746 && 
                        $fila['id']!= 747 && 
                        $fila['id']!= 748 && 
                        $fila['id']!= 749 && 
                        $fila['id']!= 750 && 
                        $fila['id']!= 751 && 
                        $fila['id']!= 752 && 
                        $fila['id']!= 753 
                        ) {
                    $product = new Product();
                    $product->setName(utf8_encode($fila['nombre']));
                    $product->setDescription(utf8_encode($fila['descripcion']));
                    if($fila['precio_original']!= '') $product->setInitPrice($fila['precio_original']);
                    else $product->setInitPrice(0);
                    $product->setPrice($fila['precio']);
                    if($fila['tipo_precio']!= '') $product->setPriceType($fila['tipo_precio']);
                    else $product->setPriceType(0);
                    $product->setDiscount($fila['descuento']);
                    $product->setDiscountType($fila['tipo_descuento']);
                    if($fila['stock']!= '') $product->setStock($fila['stock']);
                    else $product->setStock(0);
                    $product->setWeight($fila['kilos']);
                    $product->setOutlet($fila['outlet']);
                    $product->setPublic($fila['publicado']);
                    if($fila['recoger_tienda']!= '') $product->setStorePickup($fila['recoger_tienda']);
                    else $product->setStorePickup(0);
                  
                    $product->setMetaTitle(utf8_encode($fila['meta_titulo']));
                    $product->setMetaDescription(utf8_encode($fila['meta_descripcion']));
                    $product->setMetaTags(utf8_encode($fila['meta_keywords']));
                    $product->setOptic($optic);
                    $product->setBrand($brand);
                    $product->setModel($model);
                    $product->setCategory($category);
                    $product->setActive(true);
                    $product->setAvailable(true);
                    $product->setHighlighted(false);
                    $product->setFreeTransport(false);
                    $slug = $this->slugify(utf8_encode($fila['nombre'].'-'.$optic->getCity()));
//                    $p = $em->getRepository('EcommerceBundle:Product')->findOneBySlug($slug);
//                    if($p instanceof Product) $slug = $slug.'_'.rand(1000,9999);
                    $product->setSlug($slug);
                    $em->persist($product); 
                    $em->flush();

                    //image
                    if($fila['imagen']!=''){

                        $imagePath = '';
                        $dir = '/home/sebastian/www/src/Symfony/web/uploads/documents';
                        $absoluteImagePath = $dir.'/'.utf8_encode($fila['imagen']);
                        if(file_exists($absoluteImagePath.'.jpeg')) $imagePath = utf8_encode($fila['imagen']).'.jpeg';
                        if(file_exists($absoluteImagePath.'.jpg')) $imagePath = utf8_encode($fila['imagen']).'.jpg';
                        if(file_exists($absoluteImagePath.'.gif')) $imagePath = utf8_encode($fila['imagen']).'.gif';
                        if(file_exists($absoluteImagePath.'.png')) $imagePath = utf8_encode($fila['imagen']).'.png';

                        //Thumb
                        $imagePathThumb = '';
                        $absoluteImagePathThumb = $dir.'/'.utf8_encode($fila['imagen']).'_260.jpg';
                        if(file_exists($absoluteImagePathThumb)) {
                            $imagePathThumb = utf8_encode($fila['imagen']).'_260.jpg';
                        }
                        $imagePathThumb2 = '';
                        $absoluteImagePathThumb2 = $dir.'/'.utf8_encode($fila['imagen']).'_142.jpg';
                        if(file_exists($absoluteImagePathThumb2)) {
                            $imagePathThumb2 = utf8_encode($fila['imagen']).'_142.jpg';
                        }
                     
                        if($imagePath==''){
                            $dir = 'http://web.com/uploads/documents/';
                            $imagePath = $fila['imagen'].'.'.$fila['extension_img'];
                        }
                        print_r($dir . '/' . $imagePath); echo PHP_EOL;
                        @mkdir(__DIR__ . '/../../../../../web/uploads/images/product');
                        @mkdir(__DIR__ . '/../../../../../web/uploads/images/product/'.$product->getId());
                        @mkdir(__DIR__ . '/../../../../../web/uploads/images/product/'.$product->getId().'/thumbnail');

                        copy($dir . '/' . $imagePath, __DIR__ . '/../../../../../web/uploads/images/product/' . $product->getId().'/'.$imagePath);
                        if($imagePathThumb != '') copy($dir . '/' . $imagePathThumb, __DIR__ . '/../../../../../web/uploads/images/product/' . $product->getId().'/thumbnail/'.$imagePathThumb);
                        if($imagePathThumb2 != '') copy($dir . '/' . $imagePathThumb2, __DIR__ . '/../../../../../web/uploads/images/product/' . $product->getId().'/thumbnail/'.$imagePathThumb2);
                    
                        $image0 = new Image();
                        $image0->setPath($imagePath);
                        $product->addImage($image0);
                        $em->persist($image0); 
                    }

                    $em->flush();
                }
            }
        }
    }
    
    public function importProductStats(Import $entity, $output)
    {
        $sql = ' SELECT p.*, o.nombre as productName '
                . ' FROM  `producto_estadistica` AS p '
                . ' LEFT JOIN producto AS o ON o.id = p.id_producto '
                . ' ORDER BY p.id '
                . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql);
        $x = 0;
        if (mysqli_num_rows($resultado)>0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                 $x++;
                $em = $this->container->get('doctrine')->getManager();
                
                if($x>1){
                    if($product instanceof Product){
                        if($product->getName() == utf8_encode($fila['productName'])){
                            if($product instanceof Product){
                                print_r($x.'-'.utf8_encode($fila['productName']));echo PHP_EOL;

                                $productStats = new ProductStats();
                                $productStats->setDay($fila['dia']);
                                $productStats->setVisits($fila['visitas']);
                                $productStats->setProduct($product);
                                $em->persist($productStats);

                            }
                        }else{
                            $product = $em->getRepository('EcommerceBundle:Product')->findOneByName(utf8_encode($fila['productName']));
                            if($product instanceof Product){
                                print_r($x.'-'.utf8_encode($fila['productName']));echo PHP_EOL;

                                $productStats = new ProductStats();
                                $productStats->setDay($fila['dia']);
                                $productStats->setVisits($fila['visitas']);
                                $productStats->setProduct($product);
                                $em->persist($productStats);

                            }
                        }
                    }else{
                        $product = $em->getRepository('EcommerceBundle:Product')->findOneByName(utf8_encode($fila['productName']));
                        if($product instanceof Product){
                            print_r($x.'-'.utf8_encode($fila['productName']));echo PHP_EOL;

                            $productStats = new ProductStats();
                            $productStats->setDay($fila['dia']);
                            $productStats->setVisits($fila['visitas']);
                            $productStats->setProduct($product);
                            $em->persist($productStats);

                        }
                    }
                    
                    
                }else{
                    $product = $em->getRepository('EcommerceBundle:Product')->findOneByName(utf8_encode($fila['productName']));
                    if($product instanceof Product){
                        print_r($x.'-'.utf8_encode($fila['productName']));echo PHP_EOL;

                        $productStats = new ProductStats();
                        $productStats->setDay($fila['dia']);
                        $productStats->setVisits($fila['visitas']);
                        $productStats->setProduct($product);
                        $em->persist($productStats);
                        
                    }
                }
                
                if($x == 1000) $em->flush();
                if($x == 2000) $em->flush();
                if($x == 3000) $em->flush();
                if($x == 4000) $em->flush();
                if($x == 5000) $em->flush();
                if($x == 6000) $em->flush();
                if($x == 7000) $em->flush();
                if($x == 8000) $em->flush();
                if($x == 9000) $em->flush();
                if($x == 10000) $em->flush();
                
            }
            $em->flush();
        }
    }
    
    public function importOpticStats(Import $entity, $output)
    {
        $sql = ' SELECT p.*, o.nombre as opticName '
                . ' FROM  `optica_estadistica` AS p '
                . ' LEFT JOIN optica AS o ON o.id = p.id_optica '
                . ' ORDER BY p.id '
                . ' LIMIT '.$entity->getLimitStart().', '.$entity->getLimitEnd();

        $link = mysqli_connect($entity->getServer(), $entity->getUsername(), $entity->getPassword(), $entity->getDbname()) or die('No se pudo conectar: ' . mysqli_error($link));
        
        $resultado = $link->query($sql);
        $x = 0;
        if (mysqli_num_rows($resultado)>0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                 $x++;
                $em = $this->container->get('doctrine')->getManager();
                
                if($x>1){
                    if($optic instanceof Optic){
                        if($optic->getName() == utf8_encode($fila['opticName'])){
                            if($optic instanceof Optic){
                                print_r($x.'-'.utf8_encode($fila['opticName']));echo PHP_EOL;

                                $opticStats = new OpticStats();
                                $opticStats->setDay($fila['dia']);
                                $opticStats->setVisits($fila['visitas']);
                                $opticStats->setOptic($optic);
                                $em->persist($opticStats);

                            }
                        }else{
                            $optic = $em->getRepository('CoreBundle:Optic')->findOneByName(utf8_encode($fila['opticName']));
                            if($optic instanceof Optic){
                                print_r($x.'-'.utf8_encode($fila['opticName']));echo PHP_EOL;

                                $opticStats = new OpticStats();
                                $opticStats->setDay($fila['dia']);
                                $opticStats->setVisits($fila['visitas']);
                                $opticStats->setOptic($optic);
                                $em->persist($opticStats);

                            }
                        }
                    }else{
                        $optic = $em->getRepository('CoreBundle:Optic')->findOneByName(utf8_encode($fila['opticName']));
                        if($optic instanceof Optic){
                            print_r($x.'-'.utf8_encode($fila['opticName']));echo PHP_EOL;

                            $opticStats = new OpticStats();
                            $opticStats->setDay($fila['dia']);
                            $opticStats->setVisits($fila['visitas']);
                            $opticStats->setOptic($optic);
                            $em->persist($opticStats);

                        }
                    }
                    
                    
                }else{
                    $optic = $em->getRepository('CoreBundle:Optic')->findOneByName(utf8_encode($fila['opticName']));
                    if($optic instanceof Optic){
                        print_r($x.'-'.utf8_encode($fila['opticName']));echo PHP_EOL;

                        $opticStats = new OpticStats();
                        $opticStats->setDay($fila['dia']);
                        $opticStats->setVisits($fila['visitas']);
                        $opticStats->setOptic($optic);
                        $em->persist($opticStats);
                        
                    }
                }
                
                if($x == 1000) $em->flush();
                if($x == 2000) $em->flush();
                if($x == 3000) $em->flush();
                if($x == 4000) $em->flush();
                if($x == 5000) $em->flush();
                if($x == 6000) $em->flush();
                if($x == 7000) $em->flush();
                if($x == 8000) $em->flush();
                if($x == 9000) $em->flush();
                if($x == 10000) $em->flush();
                
            }
            $em->flush();
        }
    }
    
    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
    
    public function setStateAndCountry($fila, $user) 
    {
        $em = $this->container->get('doctrine')->getManager();
        $country = $em->getRepository('CoreBundle:Country')->find('es');
        $user->setCountry($country);
                
        $address = $fila['provincia'].',+España';
        $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=Spain&language=es";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);
        
        if(is_object($result)){
            $array= $result->results;
            if(count($array)>0){
                //search "administrative_area_level_2"
                $state = '';
                foreach ($array[0]->address_components as $value) {
                    $types = $value->types;
                    
                    if(isset($types[0]) && $types[0] == 'administrative_area_level_2') $state = $value->long_name;
                    if($state == 'Islas Baleares') $state = 'Baleares';
                    if($state == 'Coruña') $state = 'A Coruña';
                }
                $stateEntity = $em->getRepository('CoreBundle:State')->findOneByName($state);
                if($stateEntity instanceof State){
                    $user->setState($stateEntity);
                }
            }
        }
    }
}
