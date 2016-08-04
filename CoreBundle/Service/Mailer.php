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
           
        $templateName = 'CoreBundle:Email:validate.email.html.twig';
        
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
    
    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendRecoveryPasswordMessage($user)
    {
        $templateName = 'CoreBundle:Email:recovery.password.email.html.twig';

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
        
        $templateName = 'CoreBundle:Email:validate.new.password.html.twig';

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
    
    /*
     * ECOMMERCE
     */
    public function sendActorNewProduct($product){
        
        $actor = $product->getActor();
        //send message to admin
        $fromEmail =  $this->parameters['company']['email'];
        $toEmail = $this->parameters['company']['email'];
        $templateName = 'CoreBundle:Email:new_product.html.twig';
        $route = $this->router->generate('ecommerce_product_edit',array('id'=>$product->getId()));
        $context = array(
            'texto' => 'El usuario "'.$actor->getName().'" ha creado un nuevo producto que espera validación, por favor visita el sguiente enlace para validarlo <a href="'.$this->parameters['server_base_url'].$route.'">Activar producto</a>',
        );
        $this->sendMessage($templateName, $context, $fromEmail, $toEmail);
        
        //send email to optic, product will be active after moderation
        $fromEmail =  $this->parameters['company']['email'];
        $toEmail = $actor->getEmail();
        $templateName = 'Ecommerce:Email:new_product.html.twig';
        $context = array(
            'texto' => 'Hemos recibido su solicitud de alta de una nueva oferta, en las proximas 24hs. nuestro equipo la examinará y si es correcto la activará, te avisaremos cuando esto sucesa.<br><br>Gracias por confiar en Optisoop.',
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
     * Notify invest to the site admin
     *
     * @param Invoice $invoice
     */
    public function sendAdvertPurchaseNotification(Invoice $invoice)
    {
        $templateName = 'EcommerceBundle:Email:advert.notification.admin.html.twig';

        if($invoice->getTransaction()->getActor() instanceof Actor){
            $email = $invoice->getTransaction()->getActor()->getEmail();
        }elseif($invoice->getTransaction()->getOptic() instanceof Optic){
            $email = $invoice->getTransaction()->getOptic()->getEmail();
        }
        $context = array(
            'order_number'    => $invoice->getTransaction()->getTransactionKey(),
            'invoice_date'      => $invoice->getCreated(),
            'user_email'        => $email,
            'order_details_url' => $this->router->generate('core_actor_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $this->parameters['company']['sales_email']);
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
        $templateName = 'EcommerceBundle:Email:advert.confirmation.html.twig';
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
            'user' => $user,
            'token' => $token
        );
        
        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);
        
    }
    
    /**
     * Notify invest to the site admin
     *
     * @param Invoice $invoice
     */
    public function sendPurchaseNotification(Invoice $invoice)
    {
        $templateName = 'EcommerceBundle:Email:sale.notification.admin.html.twig';

        if($invoice->getTransaction()->getItems()->first()->getProduct() instanceof Product){
            $product = $invoice->getTransaction()->getItems()->first()->getProduct();
        }
        
        $sellerEmails = array();
        if($invoice->getTransaction()->getItems()->first()->getProduct()->getActor() instanceof Actor){
            $sellerEmails[] = $invoice->getTransaction()->getItems()->first()->getProduct()->getActor()->getEmail();
        } 
            
        $context = array(
            'order_number'    => $invoice->getTransaction()->getTransactionKey(),
            'invoice_date'      => $invoice->getCreated(),
            'user_email'        => $invoice->getTransaction()->getActor()->getEmail(),
            'seller_email'        => implode(',', $sellerEmails),
            'order_details_url' => $this->router->generate('core_actor_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'], $this->parameters['company']['sales_email']);
    }
    
    /**
     * Send invest confirmation
     *
     * @param Invoice $invoice
     * @param float   $amount
     */
    public function sendPurchaseConfirmationMessage(Invoice $invoice, $amount)
    {
        $templateName = 'EcommerceBundle:Email:sale.confirmation.html.twig';
        $toEmail = $invoice->getTransaction()->getActor()->getEmail();

        switch ($invoice->getTransaction()->getPaymentMethod()) {
            case Transaction::PAYMENT_METHOD_BRAINTREE_CREDIT_CARD:
                $paymentType = 'invoice.payment.by.braintree';
                break;
            case Transaction::PAYMENT_METHOD_BANK_TRANSFER:
                $paymentType = 'invoice.payment.by.bank.transfer';
                break;
            case Transaction::PAYMENT_METHOD_CREDIT_CARD:
                $paymentType = 'invoice.payment.by.credit.card';
                break;
            case Transaction::PAYMENT_METHOD_PAYPAL:
                $paymentType = 'invoice.payment.by.paypal';
        }

        $orderUrl = $this->router->generate('core_actor_showinvoice', array('number' => $invoice->getInvoiceNumber()), UrlGeneratorInterface::ABSOLUTE_URL);

        $context = array(
            'order_number' => $invoice->getTransaction()->getTransactionKey(),
            'amount'         => $amount,
            'payment_type'   => $paymentType,
            'order_url'      => $orderUrl,
        );

        $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);
        
        //send email to optic to confirm purchase
        //check empty bank number account
        $templateName = 'EcommerceBundle:Email:sale.confirmation.actor.html.twig';
        $productItems = $invoice->getTransaction()->getItems();
        $actor = $productItems->first()->getProduct()->getActor();
        if($actor instanceof Actor) {
            $toEmail = $actor->getEmail();
            $token = null;
            if($actor->getBankAccountNumber() == ''){
                $token = sha1(uniqid());
                $date = new \DateTime();
                $actor->setBankAccountToken($token);
                $actor->setBankAccountTime($date->getTimestamp());
                $this->manager->persist($actor);
                $this->manager->flush();
            }
            $context = array(
                'order_number' => $invoice->getTransaction()->getTransactionKey(),
                'products' => $productItems,
                'token' => $token,
                'actor' => $actor
            );

            $this->sendMessage($templateName, $context, $this->parameters['company']['sales_email'] , $toEmail);
        }
        
    }
    
    /**
     * Send the tracking code
     *
     * @param Transaction $transaction
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
    
}
