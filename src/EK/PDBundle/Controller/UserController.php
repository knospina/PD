<?php

namespace EK\PDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EK\PDBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @Route("/user/{id}", name="view_user")
     * @Template()
     */
    public function viewAction($id)
    {
        $logger = $this->get('logger');
        /* @var $logger \Symfony\Bridge\Monolog\Logger */
        
        $em = $this->get('doctrine.orm.entity_manager');
        /* @var $em \Doctrine\ORM\EntityManager */
        
        $user = $em->getRepository('EKPDBundle:User')->findOneBy(array( 'id' => $id ));
        if ($user === null) {
            $logger = $this->get('logger');
            /* @var $logger \Symfony\Bridge\Monolog\Logger */

            $logger->addError('Requested user with id does not exist.', array( 'id' => $id ));
        
            return $this->redirect($this->generateUrl('index'));
        }
        
        $logger->addInfo('User just logged in', array( 'id' => $user->getId(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName() ) );
                        
        return array(
            'user' => $user
        );
    }
}
