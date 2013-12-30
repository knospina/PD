<?php

namespace EK\PDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EK\PDBundle\Entity\User;
use EK\PDBundle\Entity\Wish;
use \Facebook;

class FriendsController extends Controller {

    /**
     * @Route("/friend/{id}", name="view_friend")
     * @Template()
     */
    public function viewAction($id) {


        $logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $id));
        if ($user === null) {
            $logger = $this->get('logger');
            /* @var $logger \Symfony\Bridge\Monolog\Logger */

            $logger->addError('Requested user with id does not exist.', array('id' => $id));

            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => '225275277639484',
            'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest());
        //var_dump($fbFriends['friends']['data']);
        //die();

        $logger->addInfo('User just logged in', array('id' => $user->getId(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName()));

        //$session = new Session();
        //$session->start();
        // set and get session attributes
        //$session->set('name', 'Drak');
        //$session->get('name');
        $userId = $this->get('session')->get('userId');
        //var_dump($this->get('session')->all());
        //die();

        if ($userId == $id) {
            return array(
                'user' => $user,
                'friends' => $fbFriends['friends']['data'],
                'userId' => $userId
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

}
