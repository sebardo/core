<?php

namespace CoreBundle\Twig;

use Twig_SimpleFunction;
use CoreBundle\Entity\Actor;
use CoreBundle\Form\ActorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CoreExtension
 */
class CoreExtension extends \Twig_Extension
{

    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('addSufix', array($this, 'addSufix')),
            new Twig_SimpleFunction('changePage', array($this, 'changePage')),
            new Twig_SimpleFunction('userForm', array($this, 'userForm'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('get_profile_image', array($this, 'getProfileImage')),
            new Twig_SimpleFunction('json_encode', array($this, 'jsonEncode')),
            new Twig_SimpleFunction('get_menu_items', array($this, 'getMenuItems')),
            new Twig_SimpleFunction('get_carousel_items', array($this, 'getCarouselItems')),
            new Twig_SimpleFunction('get_random_header', array($this, 'getRandomHeader')),
            new Twig_SimpleFunction('created_ago', array($this, 'createdAgo')),
            new Twig_SimpleFunction('get_notification', array($this, 'getNotification')),
            new Twig_SimpleFunction('get_notification_url', array($this, 'getNotificationUrl')),
            new Twig_SimpleFunction('get_max_size_file', array($this, 'getMaxFileSize')),
            new Twig_SimpleFunction('get_thumb_image', array($this, 'getThumbImage')),
            new Twig_SimpleFunction('check_slider_image', array($this, 'checkSliderImage')),
            new Twig_SimpleFunction('get_address_form', array($this, 'getAddressForm')),
            new Twig_SimpleFunction('get_first_image', array($this, 'getFirstImage')),
            new Twig_SimpleFunction('get_admin_menu', array($this, 'getAdminMenu')),
            new Twig_SimpleFunction('get_referer', array($this, 'getRefererPath')),
            new Twig_SimpleFunction('get_parameter', array($this, 'getParameter')),
            new Twig_SimpleFunction('get_locales', array($this, 'getLocales')),
            new Twig_SimpleFunction('get_fonts', array($this, 'getFonts')),
            new Twig_SimpleFunction('get_languages', array($this, 'getLanguages'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('login_form', array($this, 'loginForm'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('get_profile_form', array($this, 'getProfileForm')),
            new Twig_SimpleFunction('get_password_form', array($this, 'getPasswordForm')),
            
            
        );
    }
    
    public function jsonEncode($array)
    {
        return json_encode($array);
//       if(isset($array['page'])){
//           $array['page'] = $value;
//       }
//       return $array;
    }
    
  
    public function changePage($array, $value)
    {
       if(isset($array['page'])){
           $array['page'] = $value;
       }
       return $array;
    }
    
    public function addSufix($string)
    {
        $pos = strpos($string, '_pager');

        if ($pos === false) {
            return $string.'_pager';
        } else {
            return $string;
        }
    }
    
    /**
    * Returns the part of a feedID
    *
    * @param string $feedID  ID of the feed to load
    */
    public function userForm($uri)
    {
        
        $entity  = new Actor();

        $form =$this->container->get('form.factory')->create(new ActorType(), $entity, array(
            'action' => $this->container->get('router')->generate('itnube_core_actor_create').'?referer='.$uri,
            'method' => 'POST',
            'attr' => array('class' => 'form-horizontal form-row-seperated')
        ));
         
        $twig = $this->container->get('twig');
        
        $content = $twig->render('CoreBundle:Actor:_form.popup.html.twig', array(
                    'form' => $form->createView()
                    ));

        return $content;
            
    }
    
  
    
    public function getFirstImage($imageName) {
        $arr = explode(',', $imageName);
        if(isset($arr[0])) return $arr[0];
        else return null;
    }
    
    public function getThumbImage($imageName, $size) {
        if($imageName == '') return null;
        $arr = explode('.', $imageName);
        $arr2 = explode('/', $arr[0]);
        if(is_array($arr2) && count($arr2)>1){
            $name = end($arr2);
            array_pop($arr2);
            $path = implode('/', $arr2);
            $returnPath =  $path.'/thumbnail/'.$name.'_'.$size.'.'.$arr[1];
            
            $coreManager =  $this->container->get('core_manager');
            $core = $this->container->getParameter('core');
            if(!$coreManager->checkRemoteFile($core['server_base_url'].$returnPath)){
                $returnPath =  $path.'/thumbnail/'.$name.'_'.$size.'.jpg';
            }
            
            return $returnPath;
        }
        return $arr[0].'_'.$size.'.'.$arr[1];
    }
    
    public function checkSliderImage($imageName) {
        if($imageName == '') return null;
        $arr = explode('.', $imageName);
        $arr2 = explode('/', $arr[0]);
        if(is_array($arr2) && count($arr2)>1){
            $name = end($arr2);
            array_pop($arr2);
            $path = implode('/', $arr2);
            $returnPath =  $path.'/'.$name.'.'.$arr[1];
            
            $frontManager =  $this->container->get('core_manager');
            $core = $this->container->getParameter('core');
            if(!$frontManager->checkRemoteFile($core['server_base_url'].$returnPath)){
                return  $path.'/'.$name.'.'.$arr[1];
            }
            
            return $returnPath;
        }
        //default image
        return $arr[0].'.'.$arr[1];
    }
    
    public function getMaxFileSize(){
        return ini_get("upload_max_filesize")*1024*1024;
    }
    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('search_wrap', array($this, 'searchWrap'), array(
                            'is_safe' => array('html')
                        )),
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
        );
    }
   
    function createdAgo($ptime)
    {
        $etime = time() - $ptime;
        $translator = $this->container->get('translator');
        if ($etime < 1)
        {
            return '0 seconds';
        }

        $a = array( 365 * 24 * 60 * 60  =>  $translator->trans('year'),
                     30 * 24 * 60 * 60  =>  $translator->trans('month'),
                          24 * 60 * 60  =>  $translator->trans('day'),
                               60 * 60  =>  $translator->trans('hour'),
                                    60  =>  $translator->trans('minute'),
                                     1  =>  $translator->trans('second')
                    );
        $a_plural = array( $translator->trans('year')   => $translator->trans('years'),
                           $translator->trans('month')  => $translator->trans('months'),
                           $translator->trans('day')    => $translator->trans('days'),
                           $translator->trans('hour')   => $translator->trans('hours'),
                           $translator->trans('minute') => $translator->trans('minutes'),
                           $translator->trans('second') => $translator->trans('seconds')
                    );

        foreach ($a as $secs => $str)
        {
            $d = $etime / $secs;
            if ($d >= 1)
            {
                $r = round($d);
                //if locale is not spanish move ago to finnish
                return $translator->trans('ago'). ' '. $r . ' ' . ($r > 1 ? $a_plural[$str] : $str);
            }
        }
    }
    
    /**
    * Returns the image path of user actor
    *
    */
    public function getProfileImage($actor=null, $size=null)
    {

         /** @var CoreManager $coreManager */
        $coreManager =  $this->container->get('core_manager');
        $profileImage = $coreManager->getProfileImage($actor, $size);
        
        return  $profileImage;
    }
    
    /**
     * Price filter
     *
     * @param int    $number
     * @param bool   $applyOtherPercentageCharge
     * @param bool   $applyVat
     * @param bool   $round
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     *
     * @return string
     */
    public function priceFilter($number, $applyOtherPercentageCharge = false, $applyVat = false, $round = false, $decimals = 2, $decPoint = ',', $thousandsSep = '.', $applyNewVat = 0)
    {
//        currency_symbol: app.currency_symbol 
//        vat: app.vat 
//        special_percentage_charge: app.special_percentage_charge

        // apply VAT
        if($applyVat){
            // apply new vat inline
            if($applyNewVat==0){
                $price = $applyVat ? $number * (1 + $this->parameters['company']['vat'] / 100) : $number;
            }else{
                $price = $number * (1 + $applyNewVat / 100);
            }   
        }else{
            $price = $number;
        }
        
        

        // apply other percentage charge
        if ($applyOtherPercentageCharge) {
            $price = $price * (1 + $this->parameters['company']['special_percentage_charge'] / 100);
        }

        
        
        // round
        if ($round) {
            $price = round($price);
        }

        // apply format
        $price = number_format($price, $decimals, $decPoint, $thousandsSep);

        // remove decimals when they are ,00
        if ('00' === substr($price, -2)) {
            $price = substr($price, 0, -3);
        }

        $price = $price.' '.$this->parameters['company']['currency_symbol'];

        return $price;
    }
    
    
    /**
     * Returns all items.
     *
     * @return ArrayCollection
     */
    public function getCarouselItems()
    {
        $em = $this->container->get('doctrine')->getManager();
        $headers = $em->getRepository("CoreBundle:Slider")->findBy(array(), array('order' => 'ASC'));
        return $headers;
    }
   

    /**
     * Returns all items.
     *
     * @return ArrayCollection
     */
    public function getRandomHeader()
    {
        $em = $this->container->get('doctrine')->getManager();
        $headers = $em->getRepository("CoreBundle:Slider")->findBy(array());
        $arr = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($headers as $header) {
            $arr->add($header);
        }
        $header = $arr->get(array_rand($arr->toArray()));
        
        return $header;
    }

    
     /**
     * Search filter
     *
     * @param string $text
     *
     * @return string
     */
    public function searchWrap($text, $search)
    {
        if (ctype_upper($text)) {
            return str_ireplace($search, '<span class="yellow">'.strtoupper($search).'</span>', $text);
        }
        return str_ireplace($search, '<span class="yellow">'.$search.'</span>', $text);
    }
    
    function starts_with_upper($str) {
        $chr = mb_substr ($str, 0, 1, "UTF-8");
        return mb_strtolower($chr, "UTF-8") != $chr;
    }
    
    public function getNotification($type, $count=true)
    {
        $notificationManager = $this->container->get('notification_manager');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        
        $notif = $notificationManager->getNotification($user, $type, $count);
        
        return $notif;
    }
    
    public function getNotificationUrl(Notification $notification)
    {

        return $this->container->get('router')->generate('notification_show', array(
                'slug' => $notification->getProject()->getSlug(),
                $notification->getType() => true
            ));

    }
    
    /**
     * Returns all menuitems.
     *
     * @return ArrayCollection
     */
    public function getMenuItems($visible=null)
    {
        $em = $this->container->get('doctrine')->getManager();
        if(is_null($visible)){ //null
            $entities = $em->getRepository("CoreBundle:MenuItem")->findBy(array('active'=>true), array('order' => 'ASC'));
        }elseif(!is_null($visible) && $visible){ //true
            $qb =  $em->getRepository("CoreBundle:MenuItem")->createQueryBuilder('m');
            $qb
                ->where($qb->expr()->andx(
                    $qb->expr()->isNull('m.parentMenuItem'),
                    $qb->expr()->eq('m.active', ':active'),
                    $qb->expr()->eq('m.visible', ':visible')
                ))
                ->setParameters(array('active'=> true, 'visible' => true));

            $entities = $qb->getQuery()->getResult();
    
//            $entities = $em->getRepository("CoreBundle:MenuItem")->findBy(array('active'=>true, 'visible' => true), array('order' => 'ASC'));
        }else{ //false
            $entities = $em->getRepository("CoreBundle:MenuItem")->findBy(array('active'=>true, 'visible' => false), array('order' => 'ASC'));
        }
        
        return $entities;
    }
    
    public function getAdminMenu()
    {
        $adminMenu = $this->container->getParameter('core.admin_menus');
        return $adminMenu['admin_menus'];
    }
    
    /**
    * Returns treferer path
    *
    */
    public function getRefererPath(Request $request)
    {

         /** @var FrontManager $frontManager */
        $adminManager =  $this->container->get('core_manager');
        $path = $adminManager->getRefererPath($request);

        return  $path;
    }
    
    /**
    * Returns parameter config
    *
    */
    public function getParameter($parameter)
    {
        return  $this->container->getParameter($parameter);
    }
    
    /**
    * Returns array locaes
    *
    */
    public function getLocales()
    {

         /** @var FrontManager $coreManager */
        $coreManager =  $this->container->get('core_manager');

        return  $coreManager->getLocales();
    }
    
     /**
     * Returns all fonts.
     *
     */
    public function getFonts()
    {
        $em = $this->container->get('doctrine')->getManager();
         
        $entities = $em->getRepository("CoreBundle:Font")->findBy(array('active'=>true), array('id' => 'ASC'));
        $returnValues = array();
        foreach ($entities as $entity) {
            $returnValues[] = str_replace(' ', '+', $entity->getName());
        }
        return implode('|', $returnValues);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getLanguages($params)
    {
        $twig = $this->container->get('twig');
        $content = $twig->render('CoreBundle:Base:language.html.twig', array('params' => $params));

        return $content;
    }
    
    /**
     * {@inheritDoc}
     */
    public function loginForm($params=array())
    {
        $twig = $this->container->get('twig');
        $content = $twig->render('CoreBundle:Security:login.form.html.twig', array('params' => $params));

        return $content;
    }
    
    
    public function getProfileForm() {

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new \Exception('This user does not have access to this section.');
        }
        
        $form = $this->container->get('form.factory')->create('CoreBundle\Form\ProfileUserType', $user);
        
        return $form->createView();
        
    }
    
    public function getPasswordForm() {

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new \Exception('This user does not have access to this section.');
        }
        
        $form = $this->container->get('form.factory')->create('CoreBundle\Form\PasswordType', $user);
        
        return $form->createView();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'core_extension';
    }
}