<?php
namespace CoreBundle\DataFixtures\ORM;

use CoreBundle\DataFixtures\SqlScriptFixture;
use CoreBundle\Entity\Slider;
use CoreBundle\Entity\Image;
use CoreBundle\Entity\Role;
use CoreBundle\Entity\BaseActor as BaseUser;

/*
 * php app/console doctrine:fixtures:load --fixtures=vendor/sebardo/core/CoreBundle/DataFixtures/ORM/LoadCoreData.php
 */
class LoadCoreData extends SqlScriptFixture
{

    /**
     * There two kind of fixtures
     * Bundle fixtures: all info bundle needed
     * Dev fixtures: info for testing porpouse
     */
    public function createFixtures()
    {
        /**
         * Bundle fixtures
         */
        if($this->container->getParameter('core.fixture_bundle')){
            $this->runSqlScript('Country.sql');
            $this->runSqlScript('State.sql');
            //$this->runSqlScript('PostalCode.sql');
            $this->runSqlScript('Translation.sql');

            //get dinamic actor class
            $actorClass = $this->container->get('core_manager')->getActorClass();

            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder(new $actorClass());

            //Roles
            $userRole = new Role();
            $userRole->setName('user');
            $userRole->setRole(Role::USER);
            $this->getManager()->persist($userRole);

            $managerRole = new Role();
            $managerRole->setName('manager');
            $managerRole->setRole(Role::MANAGER);
            $this->getManager()->persist($managerRole);

            $companyRole = new Role();
            $companyRole->setName('company');
            $companyRole->setRole(Role::COMPANY);
            $this->getManager()->persist($companyRole);

            $adminRole = new Role();
            $adminRole->setName('admin');
            $adminRole->setRole(Role::ADMIN);
            $this->getManager()->persist($adminRole);

            $superRole = new Role();
            $superRole->setName('root');
            $superRole->setRole(Role::SUPER_ADMIN);
            $this->getManager()->persist($superRole);

            $this->getManager()->flush();

             //User root
            $password = 'root';
            $root = new BaseUser();
            $root->setUsername('root');
            $root->setEmail('root@latinotype.com');
            $root->addRole($superRole);
            $encodePassword = $encoder->encodePassword($password, $root->getSalt());
            $root->setPassword($encodePassword);
            $root->setName('Root');
            $root->setActive(true);
            $this->getManager()->persist($root);
            
            $this->getManager()->flush();
        }
        
        /**
         * Dev fixtures
         */
        if($this->container->getParameter('core.fixture_dev')){
            //User admin
            $password = 'admin';
            $admin = new $actorClass();
            $admin->setUsername('admin');
            $admin->setEmail('admin@admin.com');
            $admin->addRole($adminRole);
            $encodePassword = $encoder->encodePassword($password, $admin->getSalt());
            $admin->setPassword($encodePassword);
            $admin->setName('Admin');
            $admin->setLastname('Lastname');
            $admin->setActive(true);
            $this->getManager()->persist($admin);

            $password2 = 'company';
            $company = new $actorClass();
            $company->setUsername('company');
            $company->setEmail('company@latinotype.com');
            $company->addRole($companyRole);
            $encodePassword2 = $encoder->encodePassword($password2, $company->getSalt());
            $company->setPassword($encodePassword2);
            $company->setName('Company');
            $company->setLastname('Lastname');
            $company->setActive(true);
            $this->getManager()->persist($company);

            $password = 'user';
            $user = new $actorClass();
            $user->setUsername('user');
            $user->setEmail('user@user.com');
            $user->addRole($userRole);
            $encodePassword = $encoder->encodePassword($password, $user->getSalt());
            $user->setPassword($encodePassword);
            $user->setName('User');
            $user->setLastname('Lastname');
            $user->setActive(true);
            $this->getManager()->persist($user);

            $password2 = 'user2';
            $user2 = new $actorClass();
            $user2->setUsername('user2');
            $user2->setEmail('user2@user2.com');
            $user2->addRole($userRole);
            $encodePassword2 = $encoder->encodePassword($password2, $user2->getSalt());
            $user2->setPassword($encodePassword2);
            $user2->setName('User2');
            $user2->setLastname('Lastname2');
            $user2->setActive(true);
            $this->getManager()->persist($user2);

            $this->getManager()->flush();
        }
        
    }

    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
