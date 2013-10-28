<?php

namespace EK\PDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Facebook;
use EK\PDBundle\Entity\User;

class AuthController extends Controller
{
    /**
     * @Route("/fb-login", name="fb_login")
     */
    public function indexAction()
    {
        $logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */
        
        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */
        
        $facebook = new Facebook(array(
            'appId'  => '225275277639484',
            'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        ));
        
        if ($facebook->getUser() == 0) {
            $url = $facebook->getLoginUrl(array(
                'scope' => array(
                        'email', 'user_activities', 'user_education_history', 'user_birthday', 
                        'user_interests', 'user_likes', 'user_location', 'user_hometown', 'publish_actions', 'publish_stream',
                        'picture'
                    ),
                //'display' => 'popup',
            ));

            
            return $this->redirect($url);
        }
        
        $fbUser = $facebook->api('/me');
        $fbPicture = $facebook->api('/me?fields=picture.type(large)');
//        $fbFriends = $facebook->api('/me?fields=friends.fields(name,picture.type(small))');
        
//        var_dump($fbPicture['picture']['data']['url']);
//        die();
        
        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array( 'fbId' => $fbUser['id'] ));
        if ($user === null ) {
            $user = new User();
            $user
                ->setFbId($fbUser['id'])
                ->setName($fbUser['name'])
                ->setEmail($fbUser['email'])
                ->setBirthDate(\DateTime::createFromFormat('d/m/Y', $fbUser['birthday']))
                ->setProfilePic($fbPicture['picture']['data']['url']);
            ;

            $em->persist($user);
            $em->flush();
        }
        
        
        return $this->redirect(
            $this->generateUrl('view_user', array('id' => $user->getId()))
        );
    }
}
