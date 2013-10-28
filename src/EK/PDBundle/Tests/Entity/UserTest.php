<?php

namespace EK\PDBundle\Tests\Entity;

use EK\PDBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase {

    public function testAge() {
        $user = new User();
        $user->setBirthDate(new \DateTime('10/05/1990'));
        $result = $user->getAge();

        $this->assertEquals(23, $result);
    }

    public function testDisplayName() {
        $user = new User();
        $user->setFirstName('Evita');
        $user->setLastName('Knospiņa');
        $result = $user->getDisplayName();

        $this->assertEquals('Evita K.', $result);
    }

}