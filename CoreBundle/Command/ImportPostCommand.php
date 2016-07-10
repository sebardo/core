<?php
namespace CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CoreBundle\Form\Model\ImportPost;

class ImportPostCommand extends Command
{

    CONST SERVER = '127.0.0.1';
    CONST DB_NAME = 'wordpress_database';
    CONST DB_USER = 'user';
    CONST DB_PASS = 'pass';

    protected function configure()
    {
        $this
        ->setName('core:post:import')
        ->setDescription('Import wordpress post')
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
        $entity = new ImportPost();
        $entity->setServer(self::SERVER);
        $entity->setDbname(self::DB_NAME);
        $entity->setUsername(self::DB_USER);
        $entity->setPassword(self::DB_PASS);
        $entity->setLimitStart($start);
        $entity->setLimitEnd($end);
        
        
        $frontManager = $this->container->get('frontManager');
        $frontManager->importWPUsers($entity);
        $frontManager->importWPCategory($entity);
        $frontManager->importWPBlog($entity, true);

        $time2 = new \DateTime();
        $seconds2 = $time2->getTimestamp();
        $time = $seconds2 - $seconds1;
        $output->writeln("<info>Import success in: ".$time." seconds</info>");
    }

}
