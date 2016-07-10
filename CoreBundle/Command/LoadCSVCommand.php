<?php

namespace CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use CoreBundle\Command\Implementation\ActiveOpticImplementation;
use CoreBundle\Command\Implementation\SubscrptorsImplementation;
use CoreBundle\Command\Implementation\TransportImplementation;
use CoreBundle\Command\Implementation\ActorSubscriptorsImplementation;

class LoadCSVCommand extends ContainerAwareCommand
{    
     
    /**
     * @see ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        
        $this->setName('load:data')
            ->setDescription('Load csv data from file')
//            ->addArgument(
//                'implementation',
//                InputArgument::REQUIRED,
//                'Implementation class name.'
//            )
            //->addArgument(
            //    'end',
            //    InputArgument::REQUIRED,
            //    'End line import.'
            //)
             ;
    }

    /**
     * @see ContainerAwareCommand::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $implementation = $input->getArgument('implementation');
        //$endLine = $input->getArgument('end');
        $container = $this->getContainer();
        $manager = $container->get('doctrine')->getManager();
        //here implement diferent kind of behaviour
        $implementor = new ActiveOpticImplementation($manager, $input, $output);
        $dir = realpath($container->getParameter('kernel.root_dir').'/..');
        $path = $dir.DIRECTORY_SEPARATOR.$implementor->getFileLocation();
        $f = fopen($path, 'r');
        $lineNo = 0;
        while ($line = fgetcsv($f, 10000, ",")) {
            $lineNo++;
            
            //load transport
            //$implementor->load($line, $lineNo);
            
            //load subscriptors
            $implementor->load($line, $lineNo);
             
        }
   
        $output->writeln('<info>Proccesed rows: '.$lineNo.'</info>');
        $output->writeln('<info>Import executed successfully!</info>');

    }

   
}

