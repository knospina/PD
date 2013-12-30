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

class UserController extends Controller {

    /**
     * @Route("/user/{id}", name="view_user")
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

    /**
     * @Route("/user/{id}/wish", name="view_wish")
     * @Template()
     */
    public function wishAction($id, Request $request) {

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

        $userId = $this->get('session')->get('userId');

        //$myWishList = $em->getRepository('EKPDBundle:Wish')->findByOwnerId($userId);

        $myWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
                array('ownerId' => $userId)
        );

        if (!$myWishList) {
            throw $this->createNotFoundException(
                    'No product found for id ' . $userId
            );
        }

        $wish = new Wish();
        $form = $this->createFormBuilder($wish)
                ->add('name', 'text')
                ->add('url', 'text')
                ->add('price', 'text')
                ->add('status', 'text')
                ->add('save', 'submit')
                ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $wish->setOwnerId($user);
            $em->persist($wish);
            $em->flush();
            return $this->redirect(
                            $this->generateUrl('view_user', array('id' => $user->getId()))
            );
        }

        if ($userId == $id) {
            return array(
                'user' => $user,
                'friends' => $fbFriends['friends']['data'],
                'userId' => $userId,
                'wish_form' => $form->createView(),
                'wish_list' => $myWishList
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }



        /* Te buus forma */








        //$all = $this->all();
        var_dump($id);
        //$logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        //$user = $em->getRepository('EKPDBundle:User')->findOneBy(array( 'id' => $id ));
        //if ($user === null) {
        //$logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        //$logger->addError('Requested user with id does not exist.', array( 'id' => $id ));
        //return $this->redirect($this->generateUrl('index'));
        //}
        //$facebook = new Facebook(array(
        //'appId'  => '225275277639484',
        //'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        //));
        //$fbFriends = $facebook->api($user->getFbFriendListRequest());    
        //var_dump($fbFriends['friends']['data']);
        //die();
        //$logger->addInfo('User just logged in', array( 'id' => $user->getId(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName() ) );
        //$session = new Session();
        //$session->start();
        // set and get session attributes
        //$session->set('name', 'Drak');
        //$session->get('name');
        //return array(
        //'user' => $user,
        //'friends' => $fbFriends['friends']['data']
        //);

        /* return $this->redirect(
          $this->generateUrl('view_user', array('id' => $user->getId()))
          ); */
    }

    /**
     * @Route("/user/{id}/friendswish", name="view_friends_wish")
     * @Template()
     */
    public function friendswishAction($id) {

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
        //$all = $this->all();
        var_dump($id);
        //$logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        //$user = $em->getRepository('EKPDBundle:User')->findOneBy(array( 'id' => $id ));
        //if ($user === null) {
        //$logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        //$logger->addError('Requested user with id does not exist.', array( 'id' => $id ));
        //return $this->redirect($this->generateUrl('index'));
        //}
        //$facebook = new Facebook(array(
        //'appId'  => '225275277639484',
        //'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        //));
        //$fbFriends = $facebook->api($user->getFbFriendListRequest());    
        //var_dump($fbFriends['friends']['data']);
        //die();
        //$logger->addInfo('User just logged in', array( 'id' => $user->getId(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName() ) );
        //$session = new Session();
        //$session->start();
        // set and get session attributes
        //$session->set('name', 'Drak');
        //$session->get('name');
        //return array(
        //'user' => $user,
        //'friends' => $fbFriends['friends']['data']
        //);

        /* return $this->redirect(
          $this->generateUrl('view_user', array('id' => $user->getId()))
          ); */
    }

    /**
     * @Route("/user/{id}/friends", name="view_friends")
     * @Template()
     */
    public function friendsAction($id) {
        // Te vajag izveidot visus draugus kā URL, kas aizved uz tā cilvēka profilu

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
        //var_dump($fbFriends);
        //die();

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
        //$all = $this->all();
        var_dump($id);
        //$logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        //$user = $em->getRepository('EKPDBundle:User')->findOneBy(array( 'id' => $id ));
        //if ($user === null) {
        //$logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        //$logger->addError('Requested user with id does not exist.', array( 'id' => $id ));
        //return $this->redirect($this->generateUrl('index'));
        //}
        //$facebook = new Facebook(array(
        //'appId'  => '225275277639484',
        //'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        //));
        //$fbFriends = $facebook->api($user->getFbFriendListRequest());    
        //var_dump($fbFriends['friends']['data']);
        //die();
        //$logger->addInfo('User just logged in', array( 'id' => $user->getId(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName() ) );
        //$session = new Session();
        //$session->start();
        // set and get session attributes
        //$session->set('name', 'Drak');
        //$session->get('name');
        //return array(
        //'user' => $user,
        //'friends' => $fbFriends['friends']['data']
        //);

        /* return $this->redirect(
          $this->generateUrl('view_user', array('id' => $user->getId()))
          ); */
    }

    /**
     * @Route("/log_out", name="log_out")
     * @Template()
     */
    public function logoutAction() {
        $this->get('session')->set('userId', '');
        var_dump($this->get('session')->all());
        return array();
    }

}
