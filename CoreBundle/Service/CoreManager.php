<?php
namespace CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use CoreBundle\Entity\Image;
use Symfony\Component\Filesystem\Filesystem;

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
    
    public function uploadMenuImage($image, $entity)
    {
        $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = sha1(uniqid(mt_rand(), true));
        $imageName = $name . '.' . $extension;

        if(!is_dir($this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.$entity->getId())) {
                $this->createPath($this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.$entity->getId());
            }
        if ($image->move($this->getAbsolutePathMenuItem($entity->getId()), $imageName)) {
            $absPathImage = $this->getAbsolutePathMenuItem($entity->getId()).$imageName;
            
            $thumPath = $this->getWebPath().$this->parameters['upload_directory'].DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'menu'.DIRECTORY_SEPARATOR.$entity->getId().DIRECTORY_SEPARATOR.'thumbnail';
            if(!is_dir($thumPath)) {
                $this->createPath($thumPath);
            }
            
            $this->container->get('admin_manager')->resizeImage($absPathImage, $name.'_380', 380, 180, $this->getAbsolutePathMenuItem($entity->getId()));
            $this->container->get('admin_manager')->resizeImage($absPathImage, $name.'_260', 260, 123, $this->getAbsolutePathMenuItem($entity->getId()));
            $this->container->get('admin_manager')->resizeImage($absPathImage, $name.'_142', 142, 88, $this->getAbsolutePathMenuItem($entity->getId()));
            $this->container->get('admin_manager')->resizeImage($absPathImage, $name.'_150', 150, 150, $this->getAbsolutePathMenuItem($entity->getId()));
            
            return $imageName;
        }
        else {
            return null;
        }
    }
    
     public function getAbsolutePathMenuItem($id) {
        return $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'menu'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }

    public function getWebPath() {
        return __DIR__ . '/../../../../../web/';
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
        $this->container->get('admin_manager')->resizeImage($absolutePath, $img_name.'_380', 380, 180, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
        $this->container->get('admin_manager')->resizeImage($absolutePath, $img_name.'_260', 260, 123, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
        $this->container->get('admin_manager')->resizeImage($absolutePath, $img_name.'_142', 142, 88, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
        $this->container->get('admin_manager')->resizeImage($absolutePath, $img_name.'_150', 150, 150, __DIR__ . '/../../../../../web/uploads/images/menu/'.$menuItem->getId().'/');
    }
     
    public static function createPath($path)
    {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = self::createPath($prev_path);

        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
    }
}
