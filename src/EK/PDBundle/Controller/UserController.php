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

    public $appId = '225275277639484';
    public $secret = '4777721b7ff46fbe1cf90fa9ca54088e';
    
    /**
     * @Route("/user/{id}", name="view_user")
     * @Template()
     */
    public function viewAction($id) {


        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */
        
        $infologger = $this->get('my_info');
        /* @var $infologger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $id));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('id' => $id));
            return $this->redirect($this->generateUrl('index'));
            
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $allWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
                array(), array('id' => 'DESC')
        );

        foreach ($allWishList as $wish) {
            $wish->setFbFirstName($facebook->api($user->getFbProfileFirstNameRequest($wish->getOwnerId()->getFbId())));
            $wish->setFbLastName($facebook->api($user->getFbProfileLastNameRequest($wish->getOwnerId()->getFbId())));
            $wish->setFbImage($facebook->api($user->getFbProfilePicRequest($wish->getOwnerId()->getFbId())));
        }

        $infologger->addInfo('User just logged in', array('id' => $user->getId(), 'first_name' => $fbFirstName['first_name'], 'last_name' => $fbLastName['last_name']));

        $userId = $this->get('session')->get('userId');

        if ($userId == $id) {
            return array(
                'user' => $user,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'wishes' => $allWishList,
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

        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */
        
        $infologger = $this->get('my_info');
        /* @var $infologger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $id));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('id' => $id));
            return $this->redirect($this->generateUrl('index'));
            
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $userId = $this->get('session')->get('userId');

        $myWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
                array('ownerId' => $userId)
        );

        if (!$myWishList) {
            $infologger->addInfo('Wishlist for user with this id is empty', array('id' => $id));
        }

        $wish = new Wish();
        $form = $this->createFormBuilder($wish)
                ->add('name', 'text')
                ->add('url', 'text')
                ->add('price', 'text')
                ->add('status', 'hidden', array('data' => 'active'))
                ->add('save', 'submit')
                ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $wish->setOwnerId($user);
            $em->persist($wish);
            $em->flush();
            return $this->redirect(
                            $this->generateUrl('view_wish', array('id' => $user->getId()))
            );
        }

        if ($userId == $id) {
            return array(
                'user' => $user,
                'friends' => $fbFriends['friends']['data'],
                'userId' => $userId,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'wish_form' => $form->createView(),
                'wish_list' => $myWishList
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

    /**
     * @Route("/user/{userid}/edit_wish/{wishid}", name="edit_wish")
     * @Template()
     */
    public function editwishAction($userid, $wishid, Request $request) {

        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $userid));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('id' => $userid));

            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $userId = $this->get('session')->get('userId');

        $wish = new Wish();
        $wish = $em->getRepository('EKPDBundle:Wish')->find($wishid);

        if (!$wish) {
            $errorlogger->addError('Requested wish with this id does not exist', array('id' => $wishid));
        }
        
        $wish->setName($wish->getName());
        $wish->setUrl($wish->getUrl());
        $wish->setPrice($wish->getPrice());
        $wish->setStatus($wish->getStatus());
        $form = $this->createFormBuilder($wish)
                ->add('name', 'text')
                ->add('url', 'text')
                ->add('price', 'text')
                ->add('status', 'hidden', array('data' => 'active'))
                ->add('save', 'submit')
                ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $wish->setOwnerId($user);
            $em->persist($wish);
            $em->flush();
            return $this->redirect(
                            $this->generateUrl('view_wish', array('id' => $user->getId()))
            );
        }

        if ($userId == $userid) {
            return array(
                'user' => $user,
                'friends' => $fbFriends['friends']['data'],
                'userId' => $userId,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'wish_form' => $form->createView()
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

    /**
     * @Route("/user/{userid}/delete_wish/{wishid}", name="delete_wish")
     * @Template()
     */
    public function deletewishAction($userid, $wishid, Request $request) {

        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */
        
        $infologger = $this->get('my_info');
        /* @var $infologger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $userid));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('id' => $userid));
            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $userId = $this->get('session')->get('userId');

        $wish = $em->getRepository('EKPDBundle:Wish')->find($wishid);

        if (!$wish) {
            $errorlogger->addError('Requested wish with this id does not exist', array('id' => $wishid));
            return $this->redirect(
                $this->generateUrl('view_wish', array('id' => $user->getId()))
            );
        } else {
            $em->remove($wish);
            $em->flush();
            return $this->redirect(
                $this->generateUrl('view_wish', array('id' => $user->getId()))
            );
        }

        $myWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
                array('ownerId' => $userId)
        );

        if (!$myWishList) {
            $infologger->addInfo('Wish list for user with this id is empty', array('id' => $userid));
        }

        if ($userId == $userid) {
            return array(
                'user' => $user,
                'friends' => $fbFriends['friends']['data'],
                'userId' => $userId,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'wish_form' => $form->createView(),
                'wish_list' => $myWishList
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

    /**
     * @Route("/user/{id}/friendswish", name="view_friends_wish")
     * @Template()
     */
    public function friendswishAction($id) {

        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */
        
        $infologger = $this->get('my_info');
        /* @var $infologger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $id));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('id' => $id));
            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));
        
        $userId = $this->get('session')->get('userId');
        
        $allWishList = $em->getRepository('EKPDBundle:Wish')->createQueryBuilder('p')
                            ->where('p.ownerId != :ownerId')
                            ->setParameter('ownerId', $userId)
                            ->orderBy('p.id', 'DESC')
                            ->getQuery()
                            ->getResult();

        foreach ($allWishList as $wish) {
            $wish->setFbFirstName($facebook->api($user->getFbProfileFirstNameRequest($wish->getOwnerId()->getFbId())));
            $wish->setFbLastName($facebook->api($user->getFbProfileLastNameRequest($wish->getOwnerId()->getFbId())));
            $wish->setFbImage($facebook->api($user->getFbProfilePicRequest($wish->getOwnerId()->getFbId())));
        }

        if (!$allWishList) {
            $infologger->addInfo('Friends wish list for user with this id is empty.', array('id' => $id));
        }        

        if ($userId == $id) {
            return array(
                'user' => $user,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'wishes' => $allWishList,
                'userId' => $userId
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

    /**
     * @Route("/user/{id}/friends", name="view_friends")
     * @Template()
     */
    public function friendsAction($id) {

        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $id));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('id' => $id));
            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $userId = $this->get('session')->get('userId');

        if ($userId == $id) {
            return array(
                'user' => $user,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'friends' => $fbFriends['friends']['data'],
                'userId' => $userId
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

    /**
     * @Route("/log_out", name="log_out")
     * @Template()
     */
    public function logoutAction() {
        $logger = $this->get('my_info');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */
        $logger->addInfo('User with this id just logged out', array($this->get('session')->get('userId')));
        $this->get('session')->set('userId', '');
        return array();
    }

}
