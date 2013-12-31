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

        if (!$allWishList) {
            throw $this->createNotFoundException(
                    'No product found for id ' . $userId
            );
        }

        $logger->addInfo('User just logged in', array('id' => $user->getId(), 'first_name' => $fbFirstName['first_name'], 'last_name' => $fbLastName['last_name']));

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

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

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

        $logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $userid));
        if ($user === null) {
            $logger = $this->get('logger');
            /* @var $logger \Symfony\Bridge\Monolog\Logger */

            $logger->addError('Requested user with id does not exist.', array('id' => $userid));

            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => '225275277639484',
            'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $userId = $this->get('session')->get('userId');

        //$myWishList = $em->getRepository('EKPDBundle:Wish')->findByOwnerId($userId);
        //$myWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
        //        array('ownerId' => $userId)
        //);
        //if (!$myWishList) {
        //    throw $this->createNotFoundException(
        //             'No product found for id ' . $userId
        //     );
        //}
        //$product->setName('New product name!');
        //$em->flush();
        //return $this->redirect($this->generateUrl('homepage'));
        // public function newAction(Request $request)
        //{
        // create a task and give it some dummy data for this example
        //$task = new Task();
        //$task->setTask('Write a blog post');
        //$task->setDueDate(new \DateTime('tomorrow'));
        //$form = $this->createFormBuilder($task)
        //    ->add('task', 'text')
        //    ->add('dueDate', 'date')
        //    ->add('save', 'submit')
        //    ->getForm();
        // return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
        //     'form' => $form->createView(),
        // ));
        //}


        $wish = new Wish();
        $wish = $em->getRepository('EKPDBundle:Wish')->find($wishid);

        if (!$wish) {
            throw $this->createNotFoundException(
                    'No wish found for id ' . $wishid
            );
        }
        $wish->setName($wish->getName());
        $wish->setUrl($wish->getUrl());
        $wish->setPrice($wish->getPrice());
        $wish->setStatus($wish->getStatus());
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
                    //'wish_list' => $myWishList
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

        $logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $userid));
        if ($user === null) {
            $logger = $this->get('logger');
            /* @var $logger \Symfony\Bridge\Monolog\Logger */

            $logger->addError('Requested user with id does not exist.', array('id' => $userid));

            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => '225275277639484',
            'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        ));

        $fbFriends = $facebook->api($user->getFbFriendListRequest($user->getFbId()));
        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));

        $userId = $this->get('session')->get('userId');

        $wish = $em->getRepository('EKPDBundle:Wish')->find($wishid);

        if (!$wish) {
            throw $this->createNotFoundException(
                    'No product found for id ' . $wishid
            );
        } else {
            $em->remove($wish);
            $em->flush();
            return $this->redirect(
                            $this->generateUrl('view_wish', array('id' => $user->getId()))
            );
        }



        //$myWishList = $em->getRepository('EKPDBundle:Wish')->findByOwnerId($userId);

        $myWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
                array('ownerId' => $userId)
        );

        if (!$myWishList) {
            throw $this->createNotFoundException(
                    'No product found for id ' . $userId
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

        if (!$allWishList) {
            throw $this->createNotFoundException(
                    'No product found for id ' . $userId
            );
        }

        $logger->addInfo('User just logged in', array('id' => $user->getId(), 'first_name' => $fbFirstName['first_name'], 'last_name' => $fbLastName['last_name']));

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
        $this->get('session')->set('userId', '');
        var_dump($this->get('session')->all());
        return array();
    }

}
