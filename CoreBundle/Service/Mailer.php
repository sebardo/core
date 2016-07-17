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
    
    
   
}
