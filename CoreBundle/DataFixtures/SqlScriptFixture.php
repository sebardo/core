<?php
namespace CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

abstract class SqlScriptFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    protected $container;
    protected $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->createFixtures();
        if ($this->get('kernel')->getEnvironment() == 'test') {
            $this->createTestFixtures();
        }
    }

    public function createFixtures() {}
    public function createTestFixtures() {}

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function get($service)
    {
        return $this->container->get($service);
    }

    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }

    protected function getEntityManager()
    {
        return $this->container->get('doctrine')->getManager();
    }

    protected function getRepository($repo)
    {
        return $this->getEntityManager()->getRepository($repo);
    }

    protected function runSqlScript($script)
    {
        $class_info = new \ReflectionClass($this);
        $dir = dirname($class_info->getFileName());

        $f = file($dir . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . $script);
        $request = "";
        $dba = $this->container->get('database_connection');

        foreach ($f as $num_line => $line) {
            $request = $request ." ". rtrim($line);

            if (substr(rtrim($line), -1) == ';') {
                $dba->query($request);
                $request = "";
            }
        }
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
    
}
