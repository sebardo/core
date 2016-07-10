<?php
namespace CoreBundle\DataFixtures\ORM\test;

use CoreBundle\DataFixtures\SqlScriptFixture;
use CoreBundle\Entity\Role;
use CoreBundle\Entity\Actor;

class LoadCoreTestData extends SqlScriptFixture
{
    
    public function createTestFixtures()
    {
        $core = $this->container->getParameter('core');
        if(isset($core['fixtures_test']) && $core['fixtures_test']){
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
            $user->setSurnames('Surnames Admin');
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
