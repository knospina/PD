<?php

namespace EK\PDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Facebook;
use EK\PDBundle\Entity\User;
use EK\PDBundle\Entity\Wish;


class AuthController extends Controller {

    /**
     * @Route("/fb-login", name="fb_login")
     */
    public function indexAction() {
        $logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */

        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */

        $facebook = new Facebook(array(
            'appId' => '225275277639484',
            'secret' => '4777721b7ff46fbe1cf90fa9ca54088e',
        ));

        if ($facebook->getUser() == 0) {
            $url = $facebook->getLoginUrl(array(
                'scope' => array(
                    'user_birthday'
                ),
            ));


            return $this->redirect($url);
        }

        $fbUser = $facebook->api('/me');

        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array('fbId' => $fbUser['id']));
        if ($user === null) {
            $user = new User();
            $user
                    ->setFbId($fbUser['id'])
                    //->setFirstName($fbUser['first_name'])
                    //->setLastName($fbUser['last_name'])
                    //->setEmail($fbUser['email'])
                    //->setBirthDate(\DateTime::createFromFormat('d/m/Y', $fbUser['birthday']));
                    //->setProfilePic($fbPicture['picture']['data']['url']);
            ;

            $wish = new Wish();
            $wish->setName('tests');
            $wish->setUrl('tests');
            $wish->setPrice(19.99);
            $wish->setStatus('active');
            $wish->setOwnerId($user);

            $em->persist($user);
            $em->persist($wish);
            $em->flush();
            $logger->addInfo('New user registered', array('id' => $fbUser['id']));
        }

        $session = new Session();
        $session->set('userId', $user->getId());
        $session->get('userId');

        return $this->redirect(
                        $this->generateUrl('view_user', array('id' => $user->getId()))
        );
    }

}
