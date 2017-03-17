<?php
namespace CoreBundle\DataFixtures\ORM\test;

use CoreBundle\DataFixtures\SqlScriptFixture;
use CoreBundle\Entity\Role;
use CoreBundle\Entity\Actor;

/*
 * php app/console doctrine:fixtures:load --fixtures=vendor/sebardo/core/CoreBundle/DataFixtures/ORM/test/LoadCoreTestData.php --env=test
 */
class LoadCoreTestData extends SqlScriptFixture
{
    
    public function createTestFixtures()
    {
        $env = $this->container->getParameter("kernel.environment");
        if($env == 'test'){
            $this->runSqlScript('Country.sql');
            $this->runSqlScript('State.sql');
            $this->runSqlScript('PostalCode.sql');
            $this->runSqlScript('Role.sql');

            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder(new Actor());

            //User admin
            $password = 'admin';
            $user = new Actor();
            $user->setUsername('admin');
            $user->setEmail('admin@admin.com');
            $user->addRole($this->getManager()->getRepository('CoreBundle:Role')->findOneByRole(Role::ADMIN));
            $encodePassword = $encoder->encodePassword($password, $user->getSalt());
            $user->setPassword($encodePassword);
            $user->setName('Admin 1');
            $user->setLastname('Surnames Admin');
            $user->setNewsletter(true);
            $this->getManager()->persist($user);
            $this->getManager()->flush();

        }
       

    }

    public function getOrder()
    {
        return 1000; // the order in which fixtures will be loaded
    }
}
