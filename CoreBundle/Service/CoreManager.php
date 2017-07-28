<?php
namespace CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use CoreBundle\Entity\Image;
use Symfony\Component\Filesystem\Filesystem;
use CoreBundle\Entity\NewsletterShipping;
use Symfony\Component\HttpFoundation\Request;
use CoreBundle\Entity\Actor;
use CoreBundle\Entity\Role;

class CoreManager 
{
    protected $container = null;

    private $parameters;
    
    /**
     * @param EntityManager $entityManager
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters['parameters'];
    }
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
    
    public function useAdmin()
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        if (isset($bundles['AdminBundle'])) {
            return true;
        }
        return false;
    }
    
    public function useBlog()
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        if (isset($bundles['BlogBundle'])) {
            return true;
        }
        return false;
    }
    
    public function useEcommerce()
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        if (isset($bundles['EcommerceBundle'])) {
            return true;
        }
        return false;
    }
    
    public function uploadMenuImage($entity)
    {
        $absPathImage = $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR .$entity->getImage()->getPath();
        $extension = pathinfo($entity->getImage()->getPath(), PATHINFO_EXTENSION);
        $name = pathinfo($entity->getImage()->getPath(), PATHINFO_FILENAME);
        $imageName = $name . '.' . $extension;

        $dir = $this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.$entity->getId();
        if(!is_dir($dir)) {
            $this->createPath($this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.$entity->getId());
        }
        if (copy($absPathImage, $this->getAbsolutePathMenuItem($entity->getId()).$imageName)) {
            
            $thumPath = $this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.$entity->getId().DIRECTORY_SEPARATOR.'thumbnail';
            if(!is_dir($thumPath)) {
                $this->createPath($thumPath);
            }
            
            $this->resizeImage($absPathImage, $name.'_380', 380, 180, $this->getAbsolutePathMenuItem($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_260', 260, 123, $this->getAbsolutePathMenuItem($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_142', 142, 88, $this->getAbsolutePathMenuItem($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_150', 150, 150, $this->getAbsolutePathMenuItem($entity->getId()));
            unlink($absPathImage);
            return $imageName;
        }
        else {
            return null;
        }
    }
    
    
    public function addMenuItem($fileName, $menuItem, $baseDir)
    {
        $absolutePath = $baseDir . '/../../../../web/uploads/images/menu/'.$menuItem->getId().'/'.$fileName;
        $image = new Image();
        $image->setPath($fileName);
        $filename =  $baseDir . '/../../Resources/public/images/'.$fileName ;
        $this->createPath($baseDir . '/../../../../web/uploads/images/menu/'.$menuItem->getId());
        $this->createPath($baseDir . '/../../../../web/uploads/images/menu/'.$menuItem->getId().'/thumbnail');
        copy($filename, $absolutePath );
        $menuItem->setImage($image);
        $this->getManager()->persist($image);
       
        $arr = array();
        if(preg_match('/\.jpeg/', $fileName)) $arr = explode('.jpeg', $fileName);
        if(preg_match('/\.jpg/', $fileName)) $arr = explode('.jpg', $fileName);
        $img_name = $arr[0];
        $this->resizeImage($absolutePath, $img_name.'_380', 380, 180, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
        $this->resizeImage($absolutePath, $img_name.'_260', 260, 123, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
        $this->resizeImage($absolutePath, $img_name.'_142', 142, 88, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
        $this->resizeImage($absolutePath, $img_name.'_150', 150, 150, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
    }

    public function getAbsolutePathMenuItem($id) {
        $uploadDirectory = $this->getCoreParameter('upload_directory');
        return $this->getWebPath() .  $uploadDirectory . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'menu'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }
    
    public function getAbsolutePathProfile($id) {
        $uploadDirectory = $this->getCoreParameter('upload_directory');
        return $this->getWebPath() . $uploadDirectory . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'profile'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }
    
    public function getAbsolutePathWeb($id) {
        $uploadDirectory = $this->getCoreParameter('upload_directory');
        return $this->getWebPath() .  $uploadDirectory . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'web'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }
    
    public function getAbsolutePathPost($id) {
        $uploadDirectory = $this->getCoreParameter('upload_directory');
        return $this->getWebPath() .  $uploadDirectory . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'post'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }

    public function getWebPath() {
        return __DIR__ . '/../../../../../web/';
    }
    
    public function getCoreParameter($parameter)
    {
        $em = $this->container->get('doctrine')->getManager();
        $uploadDirectory = $em->getRepository('CoreBundle:Parameter')->findOneByParameter($parameter);
        return $uploadDirectory->getValue();
    }


    public function uploadProfileImage($entity)
    {
        $uploadDirectory = $this->getCoreParameter('upload_directory');
        $absPathImage = $this->getWebPath() .  $uploadDirectory  . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR .$entity->getImage()->getPath();
        
        $extension = pathinfo($entity->getImage()->getPath(), PATHINFO_EXTENSION);
        $name = pathinfo($entity->getImage()->getPath(), PATHINFO_FILENAME);
        $imageName = $name . '.' . $extension;

        $dir = $this->getWebPath().$uploadDirectory.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR.$entity->getId();
        if(!is_dir($dir)) {
            $this->createPath($this->getWebPath().$uploadDirectory.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR.$entity->getId());
        }
        if (copy($absPathImage, $this->getAbsolutePathProfile($entity->getId()).$imageName)) {
            
            $thumPath = $this->getWebPath().$uploadDirectory.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR.$entity->getId().DIRECTORY_SEPARATOR.'thumbnail';
            if(!is_dir($thumPath)) {
                $this->createPath($thumPath);
            }
            
            $this->resizeImage($absPathImage, $name.'_380', 380, 180, $this->getAbsolutePathProfile($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_260', 260, 123, $this->getAbsolutePathProfile($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_142', 142, 88, $this->getAbsolutePathProfile($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_150', 150, 150, $this->getAbsolutePathProfile($entity->getId()));
            unlink($absPathImage);
            return $imageName;
        }
        else {
            return null;
        }
    }
    
    public function uploadProfileImagePost($image, $entity)
    {
        $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = sha1(uniqid(mt_rand(), true));
        $imageName = $name . '.' . $extension;

        if ($image->move($this->getAbsolutePathProfile($entity->getId()), $imageName)) {
            $absPathImage = $this->getAbsolutePathProfile($entity->getId()).$imageName;
            
            $thumPath = $this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR.$entity->getId().DIRECTORY_SEPARATOR.'thumbnail';
            if(!is_dir($thumPath)) {
                $fs = new Filesystem();
                $fs->mkdir($thumPath, 0777);
                $fs->chown($thumPath, 'www-data', true);
                $fs->chgrp($thumPath, 'www-data', true);
                $fs->chmod($thumPath, 0777, 0000, true);
            }
            
            $this->resizeImage($absPathImage, $name.'_260', 260, 123, $this->getAbsolutePathProfile($entity->getId()));
            $this->resizeImage($absPathImage, $name.'_142', 142, 88, $this->getAbsolutePathProfile($entity->getId()));
            return $imageName;
        }
        else {
            return null;
        }
    }
   
    /**
    * Returns the image path of user actor
    *
    */
    public function getProfileImage($actor=null)
    {

        if (is_null($actor)) {
                $actor = $this->container->get('security.token_storage')->getToken()->getUser();
        }

        if ($actor instanceof Actor && $actor->getImage() instanceof Image) {
            $profileImage = '/uploads/images/profile/'.$actor->getId().'/'.$actor->getImage()->getPath();
        } else {
            $profileImage = $this->getDefaultImageProfile();
        }
 
        return  $profileImage;
    }
    
    public function getDefaultImageProfile()
    {
        return '/bundles/admin/img/default_profile.png';
    }
    
    public function resizeImage($pathSource, $name, $newImageWidth, $newImageHeight, $dstPath=null) 
    {
        $fileName = explode('/', $pathSource);
        $extension = pathinfo(end($fileName), PATHINFO_EXTENSION);

        if($extension=='jpg' || $extension=='jpeg') {
            $source = @imagecreatefromjpeg($pathSource);
        }elseif($extension=='gif') {
            $source = @imagecreatefromgif($pathSource);
        }elseif($extension=='png') {
            $source = @imagecreatefrompng($pathSource);
        }

        //imagen vertical u horizontal
        $width  = imagesx($source);
        $height = imagesy($source);    
        
        if($newImageWidth==null){
          $ratio = $newImageHeight / $height;
          $newImageWidth = round($width * $ratio);
        }

        if($newImageHeight==null){
          $ratio = $newImageWidth / $width;
          $newImageHeight = round($height * $ratio);
        }

        $source_ratio=$width/$height;
        $new_ratio=$newImageWidth/$newImageHeight;

        //imagen horizontal ajustar al alto   
        if($new_ratio<$source_ratio){
          $ratio = $newImageHeight / $height;
          $width_aux = round($width * $ratio);
          $height_aux = $newImageHeight;
        }else{//imagen vertical ajustar al ancho
          $ratio = $newImageWidth / $width;
          $height_aux = round($height * $ratio);
          $width_aux = $newImageWidth;
        }  
        
        $newImage = imagecreatetruecolor($width_aux,$height_aux);       
        imagecopyresampled($newImage,$source,0,0,0,0,$width_aux,$height_aux,$width,$height);
        //imagedestroy($source);

        //recortar al centro
        if($width_aux==$newImageWidth && $height_aux==$newImageHeight){
          $newImage2=$newImage;
        }else{
          
          $centreX = ceil($width_aux / 2);
          $centreY = ceil($height_aux / 2);

          $cropWidth  = $newImageWidth;
          $cropHeight = $newImageHeight;
          $cropWidthHalf  = ceil($cropWidth / 2); // could hard-code this but I'm keeping it flexible
          $cropHeightHalf = ceil($cropHeight / 2);

          $x1 = max(0, $centreX - $cropWidthHalf);
          $y1 = max(0, $centreY - $cropHeightHalf);

          $x2 = min($width, $centreX + $cropWidthHalf);
          $y2 = min($height, $centreY + $cropHeightHalf);

          $newImage2 = imagecreatetruecolor($cropWidth,$cropHeight);
          //echo 'recorta '.$cropWidth.' '.$cropHeight.' '.$x1.' '.$y1.' '.$x2.' '.$y2.' '.$newImageWidth.' '.$newImageHeight;
          imagecopy($newImage2, $newImage, 0, 0, $x1, $y1, $newImageWidth, $newImageHeight); 

        }
        //save image
        if(!is_null($dstPath)) {
            if($extension=='jpg' || $extension=='jpeg') {
                imagejpeg($newImage2, $dstPath.'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='gif') {
                imagegif($newImage2, $dstPath.'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='png') {
                $quality = round(abs((90 - 100) / 11.111111));
                imagepng($newImage2, $dstPath.'thumbnail/'.$name.'.'.$extension,$quality);
            }
        }else {
            if($extension=='jpg') {
                imagejpeg($newImage2, $this->options['upload_dir'].'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='gif') {
                imagegif($newImage2, $this->options['upload_dir'].'thumbnail/'.$name.'.'.$extension,90);
            }elseif($extension=='png') {
                imagepng($newImage2, $this->options['upload_dir'].'thumbnail/'.$name.'.'.$extension,90);
            } 
        }
        return $newImage2;
       
    }
    
    public static function createPath($path)
    {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createPath($prev_path);

        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
    }
    
    public function recurseRemove($directory, $empty=FALSE)
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
    
    public function checkRemoteFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(curl_exec($ch)!==FALSE)
        {
            return true;
        }
        else
        {
            return false;
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
    
    
    public function getSubscriptorFromType(NewsletterShipping $entity)
    {
        $emailArray  = array();
        $em = $this->container->get('doctrine')->getManager();
        $query =  null;
        if($entity->getType() == NewsletterShipping::TYPE_SUBSCRIPTS){
            $query = ' SELECT a'
                . ' FROM CoreBundle:NewsletterSubscription a'
                ;
        }elseif($entity->getType() == NewsletterShipping::TYPE_USER){
            $query = ' SELECT a'
                . ' FROM CoreBundle:Actor a'
                . " WHERE a.newsletter =  true "
                ;
        }
        
        if(is_null($query)) throw $this->createNotFoundException('No type found');
       
        $q = $em->createQuery($query);
        $entities = $q->getResult();
        foreach ($entities as $value) {
            ///////////////////////////////////////////////////
            ///////////////// test case ///////////////////////
            ///////////////////////////////////////////////////
//            $core = $this->getParameter('core');
//            if(preg_match($core['xpath_email'], $value->getEmail())){
                 $emailArray[] = $value->getEmail();
//            }
            
        }
         
        return $emailArray;
    }
    
    public function getRefererPath(Request $request=null)
    {
        $referer = $request->headers->get('referer');

        $baseUrl = $request->getSchemeAndHttpHost();

        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));

        return $lastPath;
    }
    
    public function getLocales(){
        return $this->container->getParameter('a2lix_translation_form.locales');
    }
    
    public function getActorClass()
    {
        $mapping = $this->container->getParameter('core.dynamic_discriminator_map.mapping');
        if($mapping['baseactor']['map']['Actor'] != 'CoreBundle\Entity\Actor'){
            $actorClass = $mapping['baseactor']['map']['Actor'];
        }else{
            $actorClass = 'CoreBundle\Entity\Actor';
        }
        return $actorClass;
    }
    
    public function getProductClass()
    {
        $mapping = $this->container->getParameter('core.dynamic_discriminator_map.mapping');
        if($mapping['product']['entity'] != 'CoreBundle\Entity\Product'){
            $actorClass = $mapping['product']['entity'];
        }else{
            $actorClass = 'CatalogueBundle\Entity\Product';
        }
        return $actorClass;
    }
            
    public function getActorBundleName() {
        $mapping = $this->container->getParameter('core.dynamic_discriminator_map.mapping');
        if($mapping['baseactor']['map']['Actor'] != 'CoreBundle\Entity\Actor'){
            $actorClass = $mapping['baseactor']['map']['Actor'];
            $bundleName = explode('\\', $actorClass);
            return $bundleName[0];
        }else{
            $bundleName = 'CoreBundle';
        }
        return $bundleName;
    }
    
    public function getActorFormClass()
    {
        $mapping = $this->container->getParameter('core.dynamic_discriminator_map.mapping');
        if($mapping['baseactor']['map']['Actor'] != 'CoreBundle\Entity\Actor'){
            $actorClass = $mapping['baseactor']['map']['Actor'];
        }else{
            $actorClass = 'CoreBundle\Entity\Actor';
        }
        return $actorClass;
    }
    
    public function getSuperAdmin()
    {
        $em = $this->container->get('doctrine')->getManager();
        $superAdmin = $em->getRepository('CoreBundle:BaseActor')->findOneByRole(Role::SUPER_ADMIN);
        
        return $superAdmin;
    }
}
