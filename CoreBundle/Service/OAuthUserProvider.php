<?php
namespace CoreBundle\Service;

use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use CoreBundle\Entity\Actor;
use Doctrine\Common\Persistence\ManagerRegistry;
use CoreBundle\Entity\Image;

class OAuthUserProvider extends EntityUserProvider
{

    protected $encoderFactory;
    protected $session;

    public function __construct(
            ManagerRegistry $registry, 
            $class, 
            array $properties, 
            $managerName = null, 
            $encoderFactory, 
            $session
            )
    {
        //this "_id" have relation with user field on entity and table 
        //move this to database configuration
        foreach ($properties as $key => $owner) {
            $properties[$key] = $owner.'_id';
        }

        parent::__construct($registry, $class, $properties, $managerName);
        $this->encoderFactory = $encoderFactory;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $sessionArr = $this->session->all();
        $resourceOwnerName = $response->getResourceOwner()->getName();
        $setter = 'set'.$this->dashesToCamelCase($resourceOwnerName, true);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        
        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }
        //hack for oauth test_connect
        if($resourceOwnerName == 'test_connect'){
            $response = (object) $response->getResponse();
            $username = $response->username;
            $email = $response->email;
            $realName = $response->username;
            $profilePicture = null;
        }else{
            $username = $response->getUsername();
            $email = $response->getEmail();
            $realName = $response->getRealName();
            $profilePicture = $response->getProfilePicture();
        }
        
        
        
        if (isset($sessionArr['_security_secured_area'])) {  
            //when user already logged and connect with other social network
            $instance = unserialize($sessionArr['_security_secured_area']);
            $user = $this->findUser(array('id' => $instance->getUser()->getId()));
            if (!$user instanceof Actor) {
                throw new \RuntimeException("_security_secured_area key exist but any user have been stored ");
            }
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            $this->em->flush();
            return $user;
        } else {
            //when user not logged and connect with other social network
            $user = $this->findUser(array($this->properties[$resourceOwnerName] => $username));
            if (null === $user && $resourceOwnerName != 'twitter') $user = $this->findUser(array('email' => $email));
             //when the user is registrating
            if (null === $user) {
                
                print_r($response);die;
                // create new user here
                $user = new Actor();
                $user->$setter_id($username);
                $user->$setter_token($response->getAccessToken());
                 //Encode pass
                $encoder = $this->encoderFactory->getEncoder($user);
                $password = $encoder->encodePassword($username, $user->getSalt());
                $user->setPassword($password);
                //Add ROLE
                $role = $this->em->getRepository('CoreBundle:Role')->findOneBy(array('role' => 'ROLE_USER'));
                $user->addRole($role);

                //I have set all requested data with the user's username
                $user->setUsername($username);
                if(isset($oauthData['name'])) {
                    $user->setName($oauthData['name']);
                }else{
                    $user->setName($username);
                }
                if($resourceOwnerName == 'twitter' || $resourceOwnerName == 'instagram'){
                    $user->setEmail($username);
                }else {
                    $user->setEmail($email);
                }
                $user->setActive(true);
                $this->em->persist($user);
                $this->em->flush();
                
            }

            //we need response data to update or fill: username, name, profile image
            $this->updateOAuthData($user, array(
                'owner' => $resourceOwnerName,
                'id' => $username,
                'name'=> $realName,
                'profileImage'=> $profilePicture
                ));
                
            return $user;
        }

//        return $user;
    }

    function dashesToCamelCase($string, $capitalizeFirstCharacter = false) 
    {

        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }


    public function updateOAuthData($user, $oauthData)
    {
        $profileImage = null;
        if (isset($oauthData['profileImage']) && $oauthData['owner']=='twitter' && strpos($oauthData['profileImage'], '_normal') > 0 ) {
            $profileImage = str_replace ('_normal', '', $oauthData['profileImage']);
        }
        if ($oauthData['owner']=='facebook') {
            $profileImage = $this->getFacebookImage($oauthData['id']);
        }
        if (isset($oauthData['profileImage']) && $oauthData['owner']=='google') {
            $profileImage = $oauthData['profileImage'];
        }
        if (isset($oauthData['profileImage']) && $oauthData['owner']=='instagram') {
            $profileImage = $oauthData['profileImage'];
        }
        if(!is_null($profileImage)) {
            
             //save image
            $arr = explode('/', $profileImage);
            $ext = explode('.', parse_url($arr[count($arr)-1], PHP_URL_PATH));
            @mkdir(__DIR__.'/../../../../../web/uploads/images/profile/'.$user->getId());
            $imageName = md5(uniqid()).'.'.$ext[1];
            $img = __DIR__.'/../../../../../web/uploads/images/profile/'.$user->getId().'/'.$imageName;
            file_put_contents($img, file_get_contents($profileImage));

            $image = new Image();
            $image->setPath($imageName);
            $user->setImage($image);
            $this->em->persist($image);
        }
        if(isset($oauthData['name'])) {
            $user->setName($oauthData['name']);
        }
        $this->em->flush();
        return true;
    }

    public function instagramToken($code)
    {     
        # Sandbox
        $clientId = 'dbd834deb2eb4a6e8bccd3d56c37c9cb';
        $secret = 'f38e792e45ad4f7ebdf3be71572c18e8';
        $redirectUrl = 'http://kundaliniwoman.dev/login/check-instagram';
        $url = 'https://api.instagram.com/oauth/access_token';
        $postdata = 'grant_type=authorization_code;redirect_uri='.$redirectUrl.';code='.$code;
        
        $curl = curl_init($url); 
        curl_setopt($curl, CURLOPT_POST, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $clientId. ":" . $secret);
        curl_setopt($curl, CURLOPT_HEADER, false); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 
        $response = curl_exec( $curl );
        if (empty($response)) {
            // some kind of an error happened
            die(curl_error($curl));
            curl_close($curl); // close cURL handler
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl); // close cURL handler
                if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                        echo "Received error: " . $info['http_code']. "\n";
                        echo "Raw response:".$response."\n";
                        die(curl_error($curl));
            }
        }
        print_r($response);die();
        // Convert the result from JSON format to a PHP array 
        $jsonResponse = json_decode( $response );
        $this->token = $jsonResponse->access_token;

    }
    
    public function getFacebookImage($facebookId)
    {
        $json = file_get_contents('https://graph.facebook.com/'.$facebookId.'/picture?width=140&height=140&redirect=false');
        $answer = json_decode($json, true);
        if (!isset($answer['data']['url'])) {
            throw new ExceptionBase('No profile image has been returned on facebook graph with userid {'.$facebookId.'}.');
        }

        return $answer['data']['url'];
    }
}
