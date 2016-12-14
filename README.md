# core
#Intalation Core tools

Requeriments:
1- Need install nodejs and less
2- Composer
3- Virtual host for youur website (apache:@CoreBundle/Resources/config/vhost.txt)

#Instalation
1- Edit composer.json
    ...
    "minimum-stability": "dev", 
    "prefer-stable": true,
    "require": {
        "php": ">=5.5.9",
        "symfony/symfony": "3.2.*",
        "doctrine/orm": "^2.5",
        "doctrine/doctrine-bundle": "^1.6",
        "doctrine/doctrine-cache-bundle": "^1.2",
        "symfony/swiftmailer-bundle": "^2.3",
        "symfony/monolog-bundle": "^3.0",
        "symfony/polyfill-apcu": "^1.0",
        "sensio/distribution-bundle": "^5.0",
        "sensio/framework-extra-bundle": "^3.0.2",
        "incenteev/composer-parameter-handler": "^2.0",
        
        "sebardo/admin": "dev-master"
    },

2- Edit config.yml
	imports:
	    - { resource: parameters.yml }
	    - { resource: "@CoreBundle/Resources/config/security.yml" }
	    - { resource: "@CoreBundle/Resources/config/services.yml" }
	    - { resource: "@AdminBundle/Resources/config/services.yml" }

	# Doctrine Configuration
	doctrine:
	    orm:
		...
		entity_managers:
		    default:
		        auto_mapping: true
		        # New custom filter
		        filters:
		            oneLocale:
		                class: A2lix\I18nDoctrineBundle\Doctrine\ORM\Filter\OneLocaleFilter
		                enabled: true

	# Twig Configuration
	twig:
	    ...
	    globals:
		core: %core%

3- Add routes on routing.yml
	core:
	    resource: "@CoreBundle/Resources/config/routing.yml"
	    prefix:   /
	admin:
	    resource: "@AdminBundle/Resources/config/routing.yml"
	    prefix:   /
	blog:
	    resource: "@BlogBundle/Resources/config/routing.yml"
	    prefix:   /
	ecommerce:
	    resource: "@EcommerceBundle/Resources/config/routing.yml"
	    prefix:   /
	front:
	    resource: "@FrontBundle/Controller/"
	    type:     annotation
	    prefix:   /

4- Add this line on AppKernel.php
    new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
    new Symfony\Bundle\AsseticBundle\AsseticBundle(),
    new A2lix\I18nDoctrineBundle\A2lixI18nDoctrineBundle(),
    new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),

    new CoreBundle\CoreBundle(),
    new AdminBundle\AdminBundle(),

5- Create data base and edit parameters.yml from next file
@CoreBundle/Resources/config/parameters.yml.dist

6- And run
composer update

7- Create schema and load fixtures
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load --append



