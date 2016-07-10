<?php
namespace CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CronCommand extends ContainerAwareCommand
{

    /**
     * @see ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        
        $this->setName('crontasks:run')
            ->setDescription('Runs Cron Tasks')
             ;
    }

    /**
     * @see ContainerAwareCommand::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //check if exist file execute this command if not out
        $cronFilePath = realpath($this->getContainer()->getParameter('kernel.root_dir').'/..').'/web/uploads/cronrun.txt';
        if(file_exists($cronFilePath)){
            $content = file_get_contents($cronFilePath);
            if($content == 'yes'){

                $pack1Group = $this->getOpticaPack('2');//2 res
                $pack2Group = $this->getOpticaPack('3');
                $pack3Group = $this->getOpticaPack('4');//3 res

                //procces
                $this->proccessReport($pack1Group, '6', $output);
                $this->proccessReport($pack2Group, '3', $output);
                $this->proccessReport($pack3Group, '1', $output);

                $output->writeln('<info>Cron executed successfully</info>');

            }else{
                $output->writeln('<error>Cron NO executed</error>');
            }
        }else{
            $output->writeln('<error>Cron NO executed</error>');
        }
        
    }

    private function getOpticaPack($pack) 
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $packEntity = $em->getRepository('CoreBundle:Pack')->findOneById($pack);
        $entities = $em->getRepository('CoreBundle:Optic')->findBy(array('pack' => $packEntity, 'isActive' => true));
        return $entities;
    }
    
    public function proccessReport($optics, $months, $output)
    {
        $now = new \DateTime();
        foreach ($optics as $optic) {
            //send report only to the active optics
            if($optic->getIsActive()){
                $dateInit = $optic->getCreated();
                $day = $dateInit->format('d');
                $month = $dateInit->format('m');
                $dayCurrent = $now->format('d');
                $monthCurrent = $now->format('m');

                if($months == '1'){
                    $this->checkDayAndSendReport($day, $month, $dayCurrent, $monthCurrent, $optic->getId(), $output);
                }elseif($months == '3'){
                    $multipleMonths = $this->getMultipleMonths($month, '3');
                    foreach ($multipleMonths as $multipleMonth) {
                        if($month == $multipleMonth){
                             $this->checkDayAndSendReport($day, $month, $dayCurrent, $monthCurrent, $optic->getId(), $output);
                        }
                    }
                }elseif($months == '6'){
                    $multipleMonths = $this->getMultipleMonths($month, '6');
                    foreach ($multipleMonths as $multipleMonth) {
                        if($month == $multipleMonth){
                             $this->checkDayAndSendReport($day, $month, $dayCurrent, $monthCurrent, $optic->getId(), $output);
                        }
                    }
                }
            }
        }

    }

    public function getMultipleMonths($month, $multiple)
    {
        if($multiple=='3'){
            if($month=='1' || $month=='4' || $month=='7' || $month=='10') return array('1','4','7','10');
            if($month=='2' || $month=='5' || $month=='8' || $month=='11') return array('2','5','8','11');
            if($month=='3' || $month=='6' || $month=='9' || $month=='12') return array('3','6','9','12');
        }
        
        elseif($multiple=='6'){
            if($month=='1' || $month=='7') return array('1','7');
            if($month=='2' || $month=='8') return array('2','8');
            if($month=='3' || $month=='9') return array('3','9');
            if($month=='4' || $month=='10') return array('4','10');
            if($month=='5' || $month=='11') return array('5','11');
            if($month=='6' || $month=='12') return array('6','12');
        }
        else{
            throw new \Exception('Invalid multiple month only accept 3 or 6.');
        }
    }
    
    public function checkDayAndSendReport($day, $month, $dayCurrent, $monthCurrent, $opticId, $output)
    {

        if($day == $dayCurrent){
            return $this->sendReport($opticId, $output);
        }elseif($monthCurrent=='02' && ($day=='30' || $day == '31')){
            return $this->sendReport($opticId, $output);
        }elseif( ($monthCurrent == '04' ||
                $monthCurrent == '06' ||
                $monthCurrent == '09' ||
                $monthCurrent == '11' ) && 
                $day == '31'
                ){
            return $this->sendReport($opticId, $output);
        }
    }
    
    public function sendReport($idOptic, $output)
    {
        
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $qb = $em->createQueryBuilder()
                ->select(array('s'))
                ->from('CoreBundle:OpticStats', 's')
                ->leftJoin('s.optic', 'o')
                ->andWhere('o.id = :idOptic')
                ->setParameter('idOptic',$idOptic)
                ->orderBy('s.id', 'ASC');
        $stats = $qb->getQuery()->getResult();
        
        //get all products from optic
        $qb_prod = $em->createQueryBuilder()
                ->select('p')
                ->from('EcommerceBundle:Product', 'p')
                ->leftJoin('p.category', 'c')
                ->leftJoin('p.optic', 'o')
                ->where("p.active = 1 and c.id != 3")
                ->andWhere ("o.id = :idOptic ")
                ->setParameter('idOptic',$idOptic);
        $products =  $qb_prod->getQuery()->getResult();
        
        $prodEstadisticas = array();
        foreach ($products as $product) {
            $qb = $em->createQueryBuilder()
                    ->select(array('s'))
                    ->from('EcommerceBundle:ProductStats', 's')
                    ->leftJoin('s.product', 'p')
                    ->where('p.id = :idProduct')
                    ->setParameter('idProduct',$product->getId())
                    ->orderBy('s.id', 'ASC');
            
            $prodEstadisticas[$product->getId()] =  $qb->getQuery()->getResult();
            
        }
        
        $opticEntity = $em->getRepository('CoreBundle:Optic')->find($idOptic);
        $container->get('core.mailer')->sendOpticStats($opticEntity, $stats, $products, $prodEstadisticas);
        
        $output->writeln('<info>Send report optic: '.$idOptic.'</info>');

    }
}
