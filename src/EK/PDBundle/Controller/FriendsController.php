<?php

namespace EK\PDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EK\PDBundle\Entity\User;
use EK\PDBundle\Entity\Wish;
use EK\PDBundle\Entity\FulfillWish;
use \Facebook;

class FriendsController extends Controller {
    
    public $appId = '225275277639484';
    public $secret = '4777721b7ff46fbe1cf90fa9ca54088e';

    /**
     * @Route("/friend/{id}", name="view_friend")
     * @Template()
     */
    public function viewAction($id) {


        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */
        
        $infologger = $this->get('my_info');
        /* @var $infologger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('fbId' => $id));
        if ($user === null) {

            $errorlogger->addError('Requested user with id does not exist.', array('fbId' => $id));
            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));

        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));
        $userId = $this->get('session')->get('userId');
              
        $friendsWishList = $em->getRepository('EKPDBundle:Wish')->findBy(
                array('ownerId' => $user->getId())
        );

        if (!$friendsWishList) {
            $infologger->addInfo('Wish list for user with this id is empty.', array('id' => $id));
        }

        if (!empty($userId)) {
            return array(
                'user' => $user,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'userId' => $userId,
                'friends_wish_list' => $friendsWishList
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }
    
     /**
     * @Route("/wish/{id}", name="fulfill_wish")
     * @Template()
     */
    public function fulfillAction($id, Request $request) {


        $errorlogger = $this->get('my_error');
        /* @var $errorlogger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $wish = $em->getRepository('EKPDBundle:Wish')->findOneBy(array('id' => $id));
        if ($wish === null) {

            $errorlogger->addError('Requested wish with id does not exist.', array('id' => $id));
            return $this->redirect($this->generateUrl('index'));
        }

        $facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret,
        ));
        
        $userId = $this->get('session')->get('userId');
        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('id' => $userId));

        $fbPic = $facebook->api($user->getFbProfilePicRequest($user->getFbId()));
        $fbBirthDay = $facebook->api($user->getFbProfileBirthdayRequest($user->getFbId()));
        $fbFirstName = $facebook->api($user->getFbProfileFirstNameRequest($user->getFbId()));
        $fbLastName = $facebook->api($user->getFbProfileLastNameRequest($user->getFbId()));
          
        $fulfilledWish = $em->getRepository('EKPDBundle:FulfillWish')->findBy(array('wishId' => $id), array('id' => 'DESC'));
        
        foreach ($fulfilledWish as $item) {
            $item->setFbFirstName($facebook->api($user->getFbProfileFirstNameRequest($item->getOwnerId()->getFbId())));
            $item->setFbLastName($facebook->api($user->getFbProfileLastNameRequest($item->getOwnerId()->getFbId())));
            $item->setFbImage($facebook->api($user->getFbProfilePicRequest($item->getOwnerId()->getFbId())));
        }
        
        $fulfillWish = new FulfillWish();
        $form = $this->createFormBuilder($fulfillWish)
                ->add('price', 'text')
                ->add('save', 'submit')
                ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $fulfillWish->setOwnerId($user);
            $fulfillWish->setWishId($wish);
            $em->persist($fulfillWish);
            $em->flush();
            return $this->redirect(
                            $this->generateUrl('view_user', array('id' => $user->getId()))
            );
        }     

        if (!empty($userId)) {
            return array(
                'wish' => $wish,
                'user' => $user,
                'userPic' => $fbPic['picture']['data']['url'],
                'userBirthDay' => (\DateTime::createFromFormat('m/d/Y', $fbBirthDay['birthday'])),
                'userFirstName' => $fbFirstName['first_name'],
                'userLastName' => $fbLastName['last_name'],
                'userId' => $userId,
                'fulfill_form' => $form->createView(),
                'fulfilled_section' => $fulfilledWish
            );
        } else {
            return $this->redirect($this->generateUrl('index'));
        }
    }

}
