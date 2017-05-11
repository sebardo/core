# core
#Intalation Core tools

This file explain how can install all tools for Sandbox.

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
        
        "sebardo/admin": "dev-master",
        "sebardo/blog": "dev-master",
        "sebardo/ecommerce": "dev-master",
        "sebardo/elearning": "dev-master"
    },

2- Edit config.yml

	imports:
	    - { resource: parameters.yml }
	    - { resource: "@CoreBundle/Resources/config/security.yml" }
	    - { resource: "@CoreBundle/Resources/config/services.yml" }
	    - { resource: "@AdminBundle/Resources/config/services.yml" }
            - { resource: "@EcommerceBundle/Resources/config/services.yml" }
            - { resource: "@ElearningBundle/Resources/config/services.yml" }

        #Uncomment
        translator:      { fallbacks: ["%locale%"] }

        # Session Configuration
        session:
            # handler_id set to null will use default session handler from php.ini
            handler_id:  ~

        # Comment this line
        #    assets: ~

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
                
        # Assetic Configuration
        assetic:
            debug:          "%kernel.debug%"
            use_controller: '%kernel.debug%'
            bundles:
                [ CoreBundle, AdminBundle, BlogBundle, EcommerceBundle, ElearningBundle ]
            node: "%node_path%"
            filters:
                cssrewrite:
                    apply_to: ".css$"
                less:
                    node: "%node_path%"
                    node_paths: ["%node_modules_path%"]
                    apply_to: ".less$"

        # OAuth login social networks  
        hwi_oauth:
             #name of the firewall in which this bundle is active, this setting MUST be set
            firewall_names: [secured_area]
            target_path_parameter: /
            resource_owners:
                twitter:
                    type:                twitter
                    client_id:           MIltvA3m2QpM5vp388DsYTThl
                    client_secret:       7KJoY105ka0TvHGmmy1bFPNoAnDlTOWcRpUV1pCnZurf3z8N3B
                google:
                    type:                google
                    client_id:           922040783377-rbn2lvna5qpogv95pm4aa1vc9010lr4l.apps.googleusercontent.com
                    client_secret:       bDLGMfYprPcJnozGu4kFQwlt
                    scope:               "email profile"
                    options:
                        access_type:     offline
                        approval_prompt: force
                        display:         popup
                        login_hint:      sub
                facebook:
                    type:                facebook
                    client_id:           1521991217899046
                    client_secret:       2cca8eb109a4a37ebac22231683af257
                    scope:               "email"
                    options:
                        display: popup 
                instagram:
                    type:                instagram
                    client_id:           dbd834deb2eb4a6e8bccd3d56c37c9cb
                    client_secret:       f38e792e45ad4f7ebdf3be71572c18e8
                    scope:               basic

        a2lix_i18n_doctrine:
            manager_registry: doctrine       # [1]
        a2lix_translation_form:
            locale_provider: default       # [1]
            locales: [es, en, de]      # [1-a]
            default_locale: es             # [1-b]
            required_locales: [es, en, de]         # [1-c]
            manager_registry: doctrine     # [2]
            templating: "CoreBundle:Base:default.tabs.html.twig"      # [3]

        #core:
        #    extended_layout: 'FrontBundle:Base:layout.html.twig'
        #    extended_layout_admin: 'FrontBundle:Admin:layout.html.twig'
        #    authentication_handler_class: 'CoreBundle\Handler\AuthenticationHandler'
        #    server_base_url: 'http://ebikes.dev'
        core: ~

        # If you want add item in admin menu use this example
        # dashboard:
        #    icon_class: 'fa fa-dashboard'
        #    label: 'dashboard'
        #    options:
        #        menuitems: core_menuitem_index
        #        sliders: core_slider_index
        admin:
            admin_menus:  ~
            apis:
                google_analytics:
                    options:
                        application_name: Analitycs integración
                        oauth2_client_id: 43533348693-s4rafifpr1o07gja2kgnfbhf4tjq2g0f.apps.googleusercontent.com
                        oauth2_client_secret: lo04F5hvUi_gPaAxyucY70jy
                        oauth2_redirect_uri: 'http://sasturain.dev/admin/analytics'
                        developer_key: AIzaSyCda_bsJ-kEa1M1DJenwKfUfyLVlVKuC6I
        
        dcs_dynamic_discriminator_map:
            mapping:
                baseactor:
                    entity: CoreBundle\Entity\BaseActor
                    map:
                        Actor: CoreBundle\Entity\Actor 

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
    elearning:
        resource: "@ElearningBundle/Resources/config/routing.yml"
        prefix:   /

4- Add this line on AppKernel.php

    new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
    new Symfony\Bundle\AsseticBundle\AsseticBundle(),
    new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
    new A2lix\I18nDoctrineBundle\A2lixI18nDoctrineBundle(),
    new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
    new Asm\TranslationLoaderBundle\AsmTranslationLoaderBundle(),
    new DCS\DynamicDiscriminatorMapBundle\DCSDynamicDiscriminatorMapBundle(),

    new CoreBundle\CoreBundle(),
    new AdminBundle\AdminBundle(),

5- Create data base and edit parameters.yml from next file

    @CoreBundle/Resources/config/parameters.yml.dist

6- And run

    composer update

7- Create schema and load fixtures

    php bin/console doctrine:schema:create
    php bin/console doctrine:fixtures:load --append



===============================================================================

Dinamyc discriminator map

If we want use a different Actor entity for users and we want this entity live in own new bundle we just edir config.yml 

    dcs_dynamic_discriminator_map:
        mapping:
            baseactor:
                entity: CoreBundle\Entity\BaseActor
                map:
                    Actor: MyBundle\Entity\Actor #or just leave CoreBundle\Entity\Actor

IMPORTANT: run command php "app/console core:actor remove" if we edit map with a new own class (avoid exception when create schema)

And to create this Actor entity again just run "app/console core:actor create"