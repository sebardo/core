<?php
namespace CoreBundle\DataFixtures\ORM;

use CoreBundle\DataFixtures\SqlScriptFixture;
//use EcommerceBundle\Entity\Feature;
//use EcommerceBundle\Entity\FeatureValue;
//use EcommerceBundle\Entity\Category;
use CoreBundle\Entity\WebTemplate;
use CoreBundle\Entity\Slider;
use CoreBundle\Entity\Image;
use CoreBundle\Entity\Role;
use CoreBundle\Entity\Actor;

/*
 * php app/console doctrine:fixtures:load --fixtures=vendor/sebardo/core/CoreBundle/DataFixtures/ORM/LoadCoreData.php
 */
class LoadCoreData extends SqlScriptFixture
{

    public function createFixtures()
    {
        $core = $this->container->getParameter('core');
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder(new Actor());

        $this->runSqlScript('Country.sql');
        $this->runSqlScript('State.sql');
        $this->runSqlScript('PostalCode.sql');
        
        //Roles
        $userRole = new Role();
        $userRole->setName('user');
        $userRole->setRole(Role::USER);
        $this->getManager()->persist($userRole);
        
        $companyRole = new Role();
        $companyRole->setName('company');
        $companyRole->setRole(Role::COMPANY);
        $this->getManager()->persist($companyRole);
        
        $adminRole = new Role();
        $adminRole->setName('admin');
        $adminRole->setRole(Role::ADMIN);
        $this->getManager()->persist($adminRole);
        
        $superRole = new Role();
        $superRole->setName('root');
        $superRole->setRole(Role::SUPER_ADMIN);
        $this->getManager()->persist($superRole);
        
        $this->getManager()->flush();
        
        //User admin
        $password = 'admin';
        $admin = new Actor();
        $admin->setUsername('admin');
        $admin->setEmail('admin@admin.com');
        $admin->addRole($adminRole);
        $encodePassword = $encoder->encodePassword($password, $admin->getSalt());
        $admin->setPassword($encodePassword);
        $admin->setName('Admin');
        $admin->setSurnames('Lastname');
        $this->getManager()->persist($admin);
        
        $password = 'user';
        $user = new Actor();
        $user->setUsername('user');
        $user->setEmail('user@user.com');
        $user->addRole($userRole);
        $encodePassword = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($encodePassword);
        $user->setName('User');
        $user->setSurnames('Lastname');
        $this->getManager()->persist($user);

        $this->getManager()->flush();
        //copy profile imges
        //self::recurseCopy(__DIR__.'/images', __DIR__.'/../../../../../../web/uploads/images/');
        //self::recurseCopy(__DIR__.'/images/profile', __DIR__.'/../../../../../../web/uploads/images/profile');
        //self::recurseCopy(__DIR__.'/images/product', __DIR__.'/../../../../../../web/uploads/images/product');

        //slider 
        //$this->createSliderFixtures();
 

    }

    public function createSliderFixtures()
    {
        $core = $this->container->getParameter('core');
        $server_base_url = $core['server_base_url'];
        //Create Item
        $slider  = new Slider();
//        $slider->setTitle('Proyecto Nº1 ciencia');
//        $slider->setCaption('Quisque venenatis et orci non pretium. Nunc pellentesque suscipit lorem, non volutpat ex mattis id. Vivamus dictum dolor metus. Aliquam erat volutpat.');
        $slider->setActive(true);
        $slider->setOpenInNewWindow(true);
        $slider->setUrl('http://local.com');
        $slider->setOrder(0);
        $this->getManager()->persist($slider);
        
        $slider2  = new Slider();
//        $slider2->setTitle('Proyecto Nº1 biologia');
//        $slider2->setCaption(' Nunc pellentesque suscipit lorem, non volutpat ex mattis id. Vivamus dictum dolor metus. Aliquam erat volutpat. Nunc pellentesque suscipit lorem, non volutpat ex mattis id. Vivamus dictum dolor metus. Aliquam erat volutpat. ');
        $slider2->setActive(true);
        $slider2->setOpenInNewWindow(true);
        $slider2->setUrl('http://www.google.com');
        $slider2->setOrder(1);
        $this->getManager()->persist($slider2);
//        
//        
//        $slider3  = new Slider();
//        $slider3->setTitle('Proyecto Nº2 biologia');
//        $slider3->setCaption(' Nunc pellentesque suscipit lorem, non volutpat ex mattis id. Vivamus dictum dolor metus. Aliquam erat volutpat. Nunc pellentesque suscipit lorem, non volutpat ex mattis id. Vivamus dictum dolor metus. Aliquam erat volutpat. ');
//        $slider3->setActive(true);
//        $slider3->setOpenInNewWindow(false);
//        $slider3->setUrl($server_base_url.'/quienes-somos');
//        $slider3->setOrder(2);
//        $this->getManager()->persist($slider3);
     
        $this->getManager()->flush();
        
        //Brand
        $image = new Image();
        $image->setPath('slider3.jpg');
        $filename =  __DIR__ . '/../../Resources/public/images/slider3.jpg' ;
        copy($filename, __DIR__ . '/../../../../../../web/uploads/images/slider3.jpg' );
        $slider->setImage($image);
        $this->getManager()->persist($image);
        
        $image2 = new Image();
        $image2->setPath('slider1.png');
        $filename =  __DIR__ . '/../../Resources/public/images/slider1.png';
        copy($filename, __DIR__ . '/../../../../../../web/uploads/images/slider1.png' );
        $slider2->setImage($image2);
        $this->getManager()->persist($image2);
//        
//        $image3 = new Image();
//        $image3->setPath('slide4.jpg');
//        $filename =  __DIR__ . '/../../Resources/public/images/slide4.jpg';
//        copy($filename, __DIR__ . '/../../../../../../web/uploads/images/slide4.jpg' );
//        $slider3->setImage($image3);
//        $this->getManager()->persist($image3);
        
        $this->getManager()->flush();
        
        
    }
   
    public static function createPath($path)
    {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createPath($prev_path);

        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
    }

    public static function recurseCopy($src,$dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            self::createPath($dst);
        }

        while (false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                } else {
//                    print_r($src . '/' . $file);echo PHP_EOL;
//                    print_r($dst . '/' . $file);
//                    die();
                    @copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function recurseRemove($directory, $empty=FALSE)
    {
       // if the path has a slash at the end we remove it here
       if (substr($directory,-1) == '/') {
           $directory = substr($directory,0,-1);
       }

      // if the path is not valid or is not a directory ...
       if (!file_exists($directory) || !is_dir($directory)) {
           // ... we return false and exit the function
           return FALSE;

       // ... if the path is not readable
       } elseif (!is_readable($directory)) {
           // ... we return false and exit the function
           return FALSE;

       // ... else if the path is readable
       } else {

           // we open the directory
           $handle = opendir($directory);

           // and scan through the items inside
           while (FALSE !== ($item = readdir($handle))) {
               // if the filepointer is not the current directory
               // or the parent directory
               if ($item != '.' && $item != '..') {
                   // we build the new path to delete
                   $path = $directory.'/'.$item;

                   // if the new path is a directory
                   if (is_dir($path)) {
                       // we call this function with the new path
                       self::recurseRemove($path);

                   // if the new path is a file
                   } else {
                       // we remove the file
                       unlink($path);
                   }
               }
          }
           // close the directory
           closedir($handle);

           // if the option to empty is not set to true
           if ($empty == FALSE) {
               // try to delete the now empty directory
               if (!rmdir($directory)) {
                   // return false if not possible
                   return FALSE;
               }
           }
           // return success
           return TRUE;
       }
    }
    
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
