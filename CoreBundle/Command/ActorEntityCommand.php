<?php
namespace CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ActorEntityCommand extends Command
{
    private $output;

    protected function configure()
    {
        $this
        ->setName('core:actor')
        ->setDescription('Command to create or remove Actor entity from CoreBundle')
        ->addArgument('type', InputArgument::REQUIRED, 'Do you want a create or remove CoreBundle:Actor entity?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument("type");
        $this->container = $this->getApplication()->getKernel()->getContainer();

        $this->output = $output;
        if($type == 'create'){
            $this->create();
        }elseif($type == 'remove'){
            $this->remove();
        }
    }
    
    private function create()
    {
        $filename = __DIR__.'/../Entity/Actor.php';
        if (file_exists($filename)) {
            $this->output->writeln(sprintf('  <fg=yellow>File already exist</> %s', self::relativizePath($filename)));
        } else {
            $fromFile = __DIR__.'/../Entity/Actor.txt';
            $content = file_get_contents($fromFile);
            $this->output->writeln(sprintf('  <fg=green>Actor entity has been created successfully</> %s', self::relativizePath($filename)));
            return file_put_contents($filename, $content);
        }

    }
    
    
    private function remove()
    {
        $filename = __DIR__.'/../Entity/Actor.php';
        if (!file_exists($filename)) {
            $this->output->writeln(sprintf('  <fg=yellow>File does not exist</> %s', self::relativizePath($filename)));
        } else {
            $fs = new Filesystem();
            $fs->remove($filename);
            $this->output->writeln(sprintf('  <fg=red>Actor entity has been removed successfully</> %s', self::relativizePath($filename)));
            
        }
    }
    
    private static function relativizePath($absolutePath)
    {
        $relativePath = str_replace(getcwd(), '.', $absolutePath);

        return is_dir($absolutePath) ? rtrim($relativePath, '/').'/' : $relativePath;
    }
  
}
