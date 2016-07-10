<?php

namespace CoreBundle\Service;

use CoreBundle\Entity\Actor;
use CoreBundle\Entity\Optic;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EcommerceBundle\Entity\Invoice;
use EcommerceBundle\Entity\Transaction;
use CoreBundle\Entity\Notification;
use stdClass;

/**
 * Class Mailer
 */
class Mailer
{
    private $mailer;
    private $twig;
    private $parameters;
    private $router;
    private $templating;
    private $html2pdf_factory;
    private $kernel;
    private $notificationManager;
    private $manager;

    /**
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     * @param array             $parameters
     */
    public function __construct(
            \Swift_Mailer $mailer, 
            \Twig_Environment $twig, 
            array $parameters, 
            $router,
            $templating,
            $html2pdf_factory,
            $kernel,
            $notificationManager,
            $manager
            )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->parameters = $parameters['parameters'];
        $this->router = $router;
        $this->templating = $templating;
        $this->html2pdf_factory = $html2pdf_factory;
        $this->kernel = $kernel;
        $this->notificationManager = $notificationManager;
        $this->manager = $manager;
    }

    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendRegisteredEmailMessage($user)
    {
        $templateName = 'CoreBundle:Email:registration.email.html.twig';
        $contractPath = null;
        $context = array(
            'user' => $user
        );
        
        if($user instanceof Optic){
            
            $templateName = 'CoreBundle:Email:registration.email.optic.html.twig'; 
//            $contractPath = $this->getContract($user);
            //add notification
            $admin = $this->manager->getRepository('CoreBundle:Actor')->findOneByUsername('admin');
            $detail = new stdClass();
            $detail->optic = $user->getId();
            $this->notificationManager->setNotification(
                    $admin,
                    $admin,
                    Notification::TYPE_NEW_OPTIC,
                    $detail
                    );
        }
        
         $this->sendMessage(
                    $templateName, 
                    $context, 
                    $this->parameters['company']['email'], 
                    $user->getEmail()
//                    $contractPath
                    );
    }
  
    
    public function sendValidateEmailMessage($user)
    {
        if($user instanceof Actor){
           $templateName = 'CoreBundle:Email:validate.email.html.twig';
        }elseif($user instanceof Optic){
           $templateName = 'CoreBundle:Email:validate.email.optic.html.twig'; 
        }
        $context = array(
            'name' => $user->getName(),
        );
        
        $this->sendMessage(
                $templateName, 
                $context, 
                $this->parameters['company']['email'], 
                $user->getEmail()
                );
            
    }
    
    
    public function getContract($optic) {
        //Presencia en la plataforma
        if($optic->getPack()->getId() == 2){
            $html = $this->templating->render(
                'CoreBundle:Email/Contract:plataforma.html.twig', 
                array(
                    'optic' => $optic,
                ));
            $html2pdf = $this->html2pdf_factory->create();
            $html2pdf->WriteHTML($html);
            @mkdir($this->kernel->getRootDir().'/../web/uploads/documents');
            $filename = 'contrato_presencia-plataforma_'.$optic->getId().'-'.$optic->getPack()->getId().'.pdf';
            $fileDir = $this->kernel->getRootDir().'/../web/uploads/documents/'.$filename;
            $html2pdf->Output($fileDir, 'F');
            return $fileDir;
        }
        //Pack Internet Plus
        if($optic->getPack()->getId() == 3){
            $html = $this->templating->render(
                'CoreBundle:Email/Contract:plus.html.twig', 
                array(
                    'optic' => $optic,
                ));

            $html2pdf = $this->html2pdf_factory->create();
            $html2pdf->WriteHTML($html);

            $filename = 'contrato_internet-plus_'.$optic->getId().'-'.$optic->getPack()->getId().'.pdf';
            $fileDir = $this->kernel->getRootDir().'/../web/uploads/documents/'.$filename;
            $html2pdf->Output($fileDir, 'F');
            return $fileDir;

        }
        //Pack Internet Total
        if($optic->getPack()->getId() == 4){
            $html = $this->templating->render(
                'CoreBundle:Email/Contract:total.html.twig', 
                array(
                    'optic' => $optic,
                ));

            $html2pdf = $this->html2pdf_factory->create();
            $html2pdf->WriteHTML($html);

            $filename = 'contrato_internet-total_'.$optic->getId().'-'.$optic->getPack()->getId().'.pdf';
            $fileDir = $this->kernel->getRootDir().'/../web/uploads/documents/'.$filename;
            $html2pdf->Output($fileDir, 'F');
            return $fileDir;
        }
    }
    
    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendRecoveryPasswordMessage($user)
    {
        $templateName = 'CoreBundle:RecoveryPassword:recovery.password.email.html.twig';

        $context = array(
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'hash' => $user->getSalt(),
        );

        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $user->getEmail()
                );

    }
    
    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendRecoveryPasswordConfirmation($user){
        
        $templateName = 'CoreBundle:RecoveryPassword:validate.new.password.html.twig';

        $context = array(
            'name' => $user->getName(),
        );

        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $user->getEmail()
                );
        
    }
    
    public function sendOpticNewProduct($product){
        
        $optic = $product->getOptic();
        //send message to admin
        $fromEmail =  $this->parameters['company']['email'];
        $toEmail = $this->parameters['company']['email'];
        $templateName = 'CoreBundle:Email:new_product.html.twig';
        $route = $this->router->generate('ecommerce_product_edit',array('id'=>$product->getId()));
        $context = array(
            'texto' => 'La optica "'.$optic->getName().'" ha creado un nuevo producto que espera validación, por favor visita el sguiente enlace para validarlo <a href="'.$this->parameters['server_base_url'].$route.'">Activar producto</a>',
        );
        $this->sendMessage($templateName, $context, $fromEmail, $toEmail);
        
        //send email to optic, product will be active after moderation
        $fromEmail =  $this->parameters['company']['email'];
        $toEmail = $optic->getEmail();
        $templateName = 'CoreBundle:Email:new_product.html.twig';
        $context = array(
            'texto' => 'Hemos recibido su solicitud de alta de una nueva oferta, en las proximas 24hs. nuestro equipo la examinará y si es correcto la activará, te avisaremos cuando esto sucesa.<br><br>Gracias por confiar en Website.',
        );
        $this->sendMessage($templateName, $context, $fromEmail, $toEmail);
       
         //add notification
        $user = $this->manager->getRepository('CoreBundle:Actor')->findOneByUsername('admin');
        $detail = new stdClass();
        $detail->product = $product->getId();
        $this->notificationManager->setNotification(
                $user,
                $user,
                Notification::TYPE_NEW_PRODUCT,
                $detail
                );
        
    }
    
    
    /**
     * Send invest confirmation
     *
     * @param Invoice $invoice
     * @param float   $amount
     */
    public function sendPurchaseConfirmationMessage(Invoice $invoice, $amount)
    {
        $templateName = 'CoreBundle:Email:sale_confirmation.html.twig';
        $toEmail = $invoice->getTransaction()->getActor()->getEmail();

        switch ($invoice->getTransaction()->getPaymentMethod()) {
            case Transaction::PAYMENT_METHOD_BANK_TRANSFER:
                $paymentType = 'invoice.payment.by.bank.transfer';
                break;
            case Transaction::PAYMENT_METHOD_CREDIT_CARD:
                $paymentType = 'invoice.payment.by.credit.card';
                break;
            case Transaction::PAYMENT_METHOD_PAYPAL:
                $paymentType = 'invoice.payment.by.paypal';
        }

        $orderUrl = $this->router->generate('front_profile_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL);

        $context = array(
            'order_number' => $invoice->getTransaction()->getTransactionKey(),
            'amount'         => $amount,
            'payment_type'   => $paymentType,
            'order_url'      => $orderUrl,
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);
        
        //send email to optic to confirm purchase
        //check empty bank number account
        $templateName = 'CoreBundle:Email:sale_confirmation_optic.html.twig';
        $productItems = $invoice->getTransaction()->getItems();
        $optic = $productItems->first()->getProduct()->getOptic();
        $toEmail = $optic->getEmail();
        $token = null;
        if($optic->getBankAccountNumber() == ''){
            $token = sha1(uniqid());
            $date = new \DateTime();
            $optic->setBankAccountToken($token);
            $optic->setBankAccountTime($date->getTimestamp());
            $this->manager->persist($optic);
            $this->manager->flush();
        }
         $context = array(
            'order_number' => $invoice->getTransaction()->getTransactionKey(),
            'products' => $productItems,
            'token' => $token,
            'optic' => $optic
        );
        
        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);

        
    }
    
    /**
     * Send plan purchase confirmation
     *
     * @param Invoice $invoice
     * @param float   $amount
     */
    public function sendPlanPurchaseConfirmationMessage(Invoice $invoice, $amount)
    {
        //send email to optic to confirm plan purchase
        //check empty bank number account
        $templateName = 'CoreBundle:Email:plan.confirmation.html.twig';
        $plan = $invoice->getTransaction()->getItems()->first()->getPlan();
        $optic = $invoice->getTransaction()->getOptic();
        $toEmail = $optic->getEmail();
        $token = null;
        if($optic->getBankAccountNumber() == ''){
            $token = sha1(uniqid());
            $date = new \DateTime();
            $optic->setBankAccountToken($token);
            $optic->setBankAccountTime($date->getTimestamp());
            $this->manager->persist($optic);
            $this->manager->flush();
        }
         $context = array(
            'order_number' => $invoice->getTransaction()->getTransactionKey(),
            'plan' => $plan,
            'optic' => $optic,
            'token' => $token
        );
        
        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);

        
    }

    /**
     * Send plan purchase confirmation
     *
     * @param Invoice $invoice
     * @param float   $amount
     */
    public function sendAdvertPurchaseConfirmationMessage(Invoice $invoice, $amount)
    {
        //send email to optic to confirm plan purchase
        //check empty bank number account
        $templateName = 'CoreBundle:Email:advert.confirmation.html.twig';
        $advert = $invoice->getTransaction()->getItems()->first()->getAdvert();
        
        if($invoice->getTransaction()->getActor() instanceof Actor){
            $user = $invoice->getTransaction()->getActor();
        }elseif($invoice->getTransaction()->getOptic() instanceof Optic){
            $user = $invoice->getTransaction()->getOptic();
        }
            
        $toEmail = $user->getEmail();
        $token = null;
            
         $context = array(
            'order_number' => $invoice->getTransaction()->getTransactionKey(),
            'advert' => $advert,
            'actor' => $user,
            'token' => $token
        );
        
        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);

        
    }
    
    /**
     * Notify invest to the site admin
     *
     * @param Invoice $invoice
     */
    public function sendPlanPurchaseNotification(Invoice $invoice)
    {
        $templateName = 'CoreBundle:Email:plan.notification.admin.html.twig';

        $context = array(
            'order_number'    => $invoice->getTransaction()->getTransactionKey(),
            'invoice_date'      => $invoice->getCreated(),
            'buyer_email'        => $invoice->getTransaction()->getOptic()->getEmail(),
            'plan_name'        => $invoice->getTransaction()->getItems()->first()->getPlan()->getName(),
            'order_details_url' => $this->router->generate('front_profile_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $this->parameters['company']['sales_email']);
    }
    
    /**
     * Notify invest to the site admin
     *
     * @param Invoice $invoice
     */
    public function sendPurchaseNotification(Invoice $invoice)
    {
        $templateName = 'CoreBundle:Email:sale.notification.admin.html.twig';

        if($invoice->getTransaction()->getItems()->first()->getProduct() instanceof Product){
            $product = $invoice->getTransaction()->getItems()->first()->getProduct();
        }else{
            
        }
        $context = array(
            'order_number'    => $invoice->getTransaction()->getTransactionKey(),
            'invoice_date'      => $invoice->getCreated(),
            'user_email'        => $invoice->getTransaction()->getActor()->getEmail(),
            'optic_email'        => $invoice->getTransaction()->getItems()->first()->getProduct()->getOptic()->getEmail(),
            'order_details_url' => $this->router->generate('front_profile_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $this->parameters['company']['sales_email']);
    }

    /**
     * Notify invest to the site admin
     *
     * @param Invoice $invoice
     */
    public function sendAdvertPurchaseNotification(Invoice $invoice)
    {
        $templateName = 'CoreBundle:Email:advert.notification.admin.html.twig';

        if($invoice->getTransaction()->getActor() instanceof Actor){
            $email = $invoice->getTransaction()->getActor()->getEmail();
        }elseif($invoice->getTransaction()->getOptic() instanceof Optic){
            $email = $invoice->getTransaction()->getOptic()->getEmail();
        }
        $context = array(
            'order_number'    => $invoice->getTransaction()->getTransactionKey(),
            'invoice_date'      => $invoice->getCreated(),
            'user_email'        => $email,
            'order_details_url' => $this->router->generate('front_profile_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $this->parameters['company']['sales_email']);
    }
    
    /**
     * Send the tracking code
     *
     * @param Order $order
     */
    public function sendTrackingCodeEmailMessage(Transaction $transaction)
    {
        $templateName = 'EcommerceBundle:Email:trackingCode.html.twig';
        $toEmail = $transaction->getActor()->getEmail();

        $context = array(
            'order_number'  => $transaction->getTransactionKey(),
            'tracking_code' => $transaction->getDelivery()->getTrackingCode(),
            'carrier_name'  => 'Transporte'//$transaction->getDelivery()->getCarrier()->getName()
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $toEmail);
    }

    /**
     * Send the tracking code
     *
     * @param Order $order
     */
    public function sendCuponCodeEmailMessage(Transaction $transaction)
    {
        $templateName = 'EcommerceBundle:Email:cuponCode.html.twig';
        $toEmail = $this->parameters['company']['sales_email'];

        $context = array(
            'product' => $transaction->getItems()->first()->getProduct(),
            'order_number'  => $transaction->getTransactionKey(),
            'cupon_code' => $transaction->getStorePickupCode(),
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $toEmail);
    }
    
    /**
     * Send bank transfer confirmation
     *
     * @param Transaction $transaction
     */
    public function sendBankTransferConfirmation(Transaction $transaction)
    {
        $templateName = 'CoreBundle:Email:bankTransferConfirmation.html.twig';
        $toEmail = $transaction->getActor()->getEmail();

        $context = array(
            'user_name'     => $transaction->getActor()->getName(),
            'order_number'  => $transaction->getTransactionKey()
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);
    }
    
    public function sendTransport($fileDir, $transaction) {
        
        
        $templateName = 'EcommerceBundle:Email:transportAttachment.html.twig';

        $context = array(
            'order_number' => $transaction->getId()
        );
        // 'recogidas.automaticas@servihenares.es',
        // 'informatica@servihenares.es',
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $this->parameters['company']['transport_email'],
                $fileDir
                );
               
    }
    
    public function sendCupon($user, $pass, $fileDir, $product) {
        
        //to user
        $templateName = 'EcommerceBundle:Email:cupon.html.twig';
        $context = array(
            'user' => $user,
            'password' => $pass,
            'product' => $product
        );
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $user->getEmail(),
                $fileDir
                );
        
        //to optic
        $templateName = 'EcommerceBundle:Email:cupon.optic.html.twig';

        $productUrl = $this->router->generate('front_product_show', array('slug' => $product->getSlug()), UrlGeneratorInterface::ABSOLUTE_URL);
        
        $context = array(
            'product' => $product,
            'product_url' => $productUrl,
            'optic' => $product->getOptic(),
            'user' => $user
        );
        // 'recogidas.automaticas@servihenares.es',
        // 'informatica@servihenares.es',
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $product->getOptic()->getEmail(),
                $fileDir
                );
        
               
    }
    
    
      
    /**
     * Send an email to a user to confirm the subscription
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendSubscribeToNewsletterMessage(Actor $user)
    {
        $templateName = 'CoreBundle:Email:subscription.email.html.twig';

        $context = array(
            'user' => $user
        );

        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $user->getEmail()
                );
    }
    
    /**
     * Send bank newsletter email
     * 
     * @param Transaction $transaction
     */
    public function sendShipping($emails, $type, $body)
    {
        $templateName = 'CoreBundle:Email:newsletter.html.twig';
  
        $context = array(
            'type'     => $type,
            'body' => $body
        );
        
         $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $emails
                );

    }
    
    /**
     * Send bank newsletter email
     * 
     * @param Transaction $transaction
     */
    public function sendActorEmail($email, $title, $body)
    {
        $templateName = 'CoreBundle:Email:actor.html.twig';
  
        $context = array(
            'title'     => $title,
            'body' => $body
        );
        
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $email
                );
        
    }
    
    /**
     * Send bank newsletter email
     * 
     * @param Transaction $transaction
     */
    public function sendOpticStats($opticEntity, $stats, $products, $prodEstadisticas)
    {
        $templateName = 'CoreBundle:Email:optic.stats.email.html.twig';
  
        $context = array(
            'optic' => $opticEntity,
            'stats' => $stats,
            'products' => $products,
            'prodStats' => $prodEstadisticas
        );
        
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $opticEntity->getEmail()
                );
        
    }
    
    /**
     * Send an email to a user and admin
     *
     * @param array $params (name, email, message)
     *
     * @return void
     */
    public function sendContactMessage(array $params)
    {
        $templateName = 'CoreBundle:Email:base.email.html.twig';

        $context = array(
            'params' => $params
        );
        
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $params['email']
                );
        
        $this->sendMessage(
                $templateName, 
                $context,  
                $params['email'], 
                $this->parameters['company']['email']
                );

    }
    
    /**
     * Send an email to a user and admin
     *
     * @param array $params (name, email, message)
     *
     * @return void
     */
    public function sendContactWebMessage(array $params)
    {
        $templateName = 'CoreBundle:Email:web.contact.html.twig';

        $context = array(
            'params' => $params
        );
        
        $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $params['email']
                );

    }

    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $optic
     *
     * @return void
     */
    public function sendOpticNotificationProduct($optic){
        
        $templateName = 'CoreBundle:Email:optic.notification.product.html.twig';

        $context = array(
            'name' => $optic->getName(),
        );
        
        
        if(preg_match("/(^sebastian\.sasturain.+|^erponcio.+|^rpuentef.+)/", $optic->getEmail())){
             //add notification
            $user = $this->manager->getRepository('CoreBundle:Actor')->findOneByUsername('admin');
            $detail = new stdClass();
            $detail->optic = $optic->getId();
            $this->notificationManager->setNotification(
                                $user,
                                $optic,
                                Notification::TYPE_ADD_PRODUCT,
                                $detail
                                );      
            $this->sendMessage(
                    $templateName, 
                    $context,  
                    $this->parameters['company']['email'], 
                    $optic->getEmail()
                    );
        }
       
        
    }

    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $optic
     *
     * @return void
     */
    public function sendOpticNotificationWeb($optic){
        
        $templateName = 'CoreBundle:Email:optic.notification.web.html.twig';

        $context = array(
            'name' => $optic->getName(),
        );

        if(preg_match("/(^sebastian\.sasturain.+|^erponcio.+|^rpuentef.+)/", $optic->getEmail())){
            //add notification
            $user = $this->manager->getRepository('CoreBundle:Actor')->findOneByUsername('admin');
            $detail = new stdClass();
            $detail->optic = $optic->getId();
            $this->notificationManager->setNotification(
                                $user,
                                $optic,
                                Notification::TYPE_ADD_WEB,
                                $detail
                                );
            $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $optic->getEmail()
                );
        }        
    }
    
    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $optic
     *
     * @return void
     */
    public function sendOpticNotificationMostVisited($optic){
        
        $templateName = 'CoreBundle:Email:optic.notification.mostvisited.html.twig';

        $context = array(
            'name' => $optic->getName(),
        );

        //Test propouse
        //Avoid send email to real optic
        if(preg_match("/(^sebastian\.sasturain.+|^erponcio.+|^rpuentef.+)/", $optic->getEmail())){
            //add notification
            $user = $this->manager->getRepository('CoreBundle:Actor')->findOneByUsername('admin');
            $detail = new stdClass();
            $detail->optic = $optic->getId();
            $this->notificationManager->setNotification(
                                $user,
                                $optic,
                                Notification::TYPE_MOST_VISITED,
                                $detail
                                );
            $this->sendMessage(
                $templateName, 
                $context,  
                $this->parameters['company']['email'], 
                $optic->getEmail()
                );
        }        
    }
    
    
    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    private function sendMessage($templateName, $context, $fromEmail, $toEmail, $attach=null)
    {
        $context    = $this->twig->mergeGlobals($context);
        $template   = $this->twig->loadTemplate($templateName);
        $subject    = $template->renderBlock('subject', $context);
        $textBody   = $template->renderBlock('body_text', $context);
        $htmlBody   = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
//            ->setFrom(array($fromEmail =>  $this->parameters['company']['name']))
            ->setFrom(array('user@local.com' => 'Avisos'))
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }
        if(!is_null($attach)) $message->attach(\Swift_Attachment::fromPath($attach));
        
        
        $this->mailer->send($message);
        
        

    }
    
    
   
}
