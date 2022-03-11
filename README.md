Teknoo Software - Website library
=================================

[![Latest Stable Version](https://poser.pugx.org/teknoo/east-website/v/stable)](https://packagist.org/packages/teknoo/east-website)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/east-website/v/unstable)](https://packagist.org/packages/teknoo/east-website)
[![Total Downloads](https://poser.pugx.org/teknoo/east-website/downloads)](https://packagist.org/packages/teknoo/east-website)
[![License](https://poser.pugx.org/teknoo/east-website/license)](https://packagist.org/packages/teknoo/east-website)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

Universal package, following the #East programming philosophy, build on Teknoo/East-Foundation (and Teknoo/Recipe),
and implementing a basic CMS to display dynamics pages with different types and templates.

Example with Symfony 
--------------------

    //These operations are not reauired with teknoo/east-website-symfony

    //config/packages/di_bridge.yaml:
    di_bridge:
        compilation_path: '%kernel.project_dir%/var/cache/phpdi'
        definitions:
          - '%kernel.project_dir%/config/di.php'
    
    //config/packages/east_foundation.yaml:
    di_bridge:
        definitions:
            - '%kernel.project_dir%/vendor/teknoo/east-foundation/src/di.php'
            - '%kernel.project_dir%/vendor/teknoo/east-foundation/infrastructures/symfony/Resources/config/di.php'
            - '%kernel.project_dir%/vendor/teknoo/east-foundation/infrastructures/symfony/Resources/config/laminas_di.php'
        import:
            Psr\Log\LoggerInterface: 'logger'

    //config/packages/east_website_di.yaml:
    di_bridge:
        definitions:
            - '%kernel.project_dir%/vendor/teknoo/east-website/src/di.php'
            - '%kernel.project_dir%/vendor/teknoo/east-website/infrastructures/doctrine/di.php'
            - '%kernel.project_dir%/vendor/teknoo/east-website/infrastructures/symfony/Resources/config/di.php'
            - '%kernel.project_dir%/vendor/teknoo/east-website/infrastructures/symfony/Resources/config/laminas_di.php'
            - '%kernel.project_dir%/vendor/teknoo/east-website/infrastructures/di.php'
        import:
            Doctrine\Persistence\ObjectManager: 'doctrine_mongodb.odm.default_document_manager'
    
    //bundles.php
    ...
    Teknoo\DI\SymfonyBridge\DIBridgeBundle::class => ['all' => true],
    Teknoo\East\FoundationBundle\EastFoundationBundle::class => ['all' => true],
    Teknoo\East\WebsiteBundle\TeknooEastWebsiteBundle::class => ['all' => true],

    //In doctrine config (east_website_doctrine_mongodb.yaml)
    doctrine_mongodb:
        document_managers:
            default:
                auto_mapping: true
                mappings:
                    TeknooEastWebsite:
                        type: 'xml'
                        dir: '%kernel.project_dir%/vendor/teknoo/east-website/infrastructures/doctrine/config/universal'
                        is_bundle: false
                        prefix: 'Teknoo\East\Website\Object'
                    TeknooEastWebsiteDoctrine:
                        type: 'xml'
                        dir: '%kernel.project_dir%/vendor/teknoo/east-website/infrastructures/doctrine/config/doctrine'
                        is_bundle: false
                        prefix: 'Teknoo\East\Website\Doctrine\Object'

    //In security.yml
    security:
        //...
        enable_authenticator_manager: true

        providers:
            with_password:
                id: 'Teknoo\East\WebsiteBundle\Provider\PasswordAuthenticatedUserProvider'
    
        password_hashers:
            Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser:
                algorithm: '%teknoo.east.website.bundle.password_authenticated_user_provider.default_algo%'


    //In routes/website.yml
    admin_website:
        resource: '@TeknooEastWebsiteBundle/Resources/config/admin_routing.yml'
        prefix: '/admin'
    
    website:
        resource: '@TeknooEastWebsiteBundle/Resources/config/routing.yml'

Enable third party authentication with an OAuth2 Provider (example with Gitlab)
-------------------------------------------------------------------------------

    //In security.yml
    security:
        providers:
            //...
            # Third party user provider
            from_third_party:
                id: 'Teknoo\East\WebsiteBundle\Provider\ThirdPartyAuthenticatedUserProvider'
        firewalls:
            # disables authentication for assets and the profiler, adapt it according to your needs
            //...
            admin_gitlab_login:
                pattern: '^/oauth2/gitlab/login$'
                security: false
    
            #require admin role for all others pages
            restricted_area:
                //...
                # Enable oauth2 authenticator for this form
                custom_authenticators:
                    - '%teknoo.east.website.bundle.security.authenticator.oauth2.class%'

    //In knpu_oauth2_client.yaml
    knpu_oauth2_client:
        clients:
            # will create service: "knpu.oauth2.client.gitlab"
            # an instance of: KnpU\OAuth2ClientBundle\Client\Provider\GitlabClient
            # composer require omines/oauth2-gitlab
            gitlab:
                # must be "gitlab" - it activates that type!
                type: gitlab
                # add and set these environment variables in your .env files
                client_id: '%env(OAUTH_GITLAB_CLIENT_ID)%'
                client_secret: '%env(OAUTH_GITLAB_CLIENT_SECRET)%'
                # a route name you'll create
                redirect_route: admin_connect_gitlab_check
                redirect_params: {}
                # Base installation URL, modify this for self-hosted instances
                domain: '%env(OAUTH_GITLAB_URL)%'

    //In service.yaml
    services:
        Teknoo\East\WebsiteBundle\EndPoint\ConnectEndPoint:
            class: 'Teknoo\East\WebsiteBundle\EndPoint\ConnectEndPoint'
            arguments:
              - '@KnpU\OAuth2ClientBundle\Client\ClientRegistry'
              - 'gitlab'
              - ['read_user']
            calls:
              - ['setResponseFactory', ['@Psr\Http\Message\ResponseFactoryInterface']]
              - ['setRouter', ['@router']]
            public: true
    
        Teknoo\East\WebsiteBundle\Contracts\Security\Authenticator\UserConverterInterface:
            class: 'App\Security\Authenticator\UserConverter'

    //In src/Security\Authenticator\UserConverter.php
    <?php
    
    declare(strict_types=1);
    
    namespace App\Security\Authenticator;
    
    use DomainException;
    use League\OAuth2\Client\Provider\ResourceOwnerInterface;
    use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
    use Teknoo\East\Website\Object\User;
    use Teknoo\East\WebsiteBundle\Contracts\Security\Authenticator\UserConverterInterface;
    use Teknoo\Recipe\Promise\PromiseInterface;
    
    class UserConverter implements UserConverterInterface
    {
        public function extractEmail(ResourceOwnerInterface $owner, PromiseInterface $promise): UserConverterInterface
        {
            if (!$owner instanceof GitlabResourceOwner) {
                $promise->fail(new DomainException('Resource not manager'));
    
                return $this;
            }
    
            $promise->success($owner->getEmail());
    
            return $this;
        }
    
        public function convertToUser(ResourceOwnerInterface $owner, PromiseInterface $promise): UserConverterInterface
        {
            if (!$owner instanceof GitlabResourceOwner) {
                $promise->fail(new DomainException('Resource not manager'));
    
                return $this;
            }
    
            $promise->success(
                (new User())->setEmail($owner->getEmail())
                    ->setLastName($owner->getName())
                    ->setFirstName($owner->getUsername())
            );
    
            return $this;
        }
    }

    //In routes/gitlab.yaml
    admin_connect_gitlab_login:
        path: '/oauth2/gitlab/login'
        defaults:
            _controller: 'Teknoo\East\WebsiteBundle\EndPoint\ConnectEndPoint'
    
    admin_connect_gitlab_check:
        path: '/oauth2/gitlab/check'
        defaults:
            _controller: 'teknoo.east.website.endpoint.static'
            template: '@@TeknooEastWebsite/Admin/index.html.twig'
            errorTemplate: '@@TeknooEastWebsite/Error/404.html.twig'
            _oauth_client_key: gitlab


    //In your template, create a link with {{ path('admin_connect_gitlab_login') }}

Support this project
---------------------

This project is free and will remain free, but it is developed on my personal time. 
If you like it and help me maintain it and evolve it, don't hesitate to support me on [Patreon](https://patreon.com/teknoo_software).
Thanks :) Richard. 

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/east-website
    
To start a project with Symfony :

    symfony new your_project_name new
    composer require teknoo/composer-install
    composer require teknoo/east-website-symfony    

This library requires :

    * PHP 8.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Teknoo/East-Foundation.
    * Optional: Symfony 6.0+ (for administration)

News from Teknoo Website 7.0
----------------------------

This library requires PHP 8.1 or newer and it's only compatible with Symfony 6.0 or newer.

- Support Recipe 4.1.1+
- Support East Foundation 6.0.1+
- Public constant are final
- Block's types are Enums
- Direction are Enums
- Use readonly properties behaviors on Immutables
- Remove support of deprecated features removed in `Symfony 6.0` (`Salt`, `LegacyUser`)
- Use `(...)` notation instead array notation for callable
- Enable fiber support in front endpoint
- `QueryInterface` has been splitted to `QueryElementInterface` and `QueryCollectionInterface` to differentiate
  queries fetching only one element, or a scalar value, and queries for collections of objects.
- `LoaderInterface::query` method is only dedicated for `QueryCollectionInterface` queries.
- a new method `LoaderInterface::fetch` is dedicated for `QueryElementInterface` queries.

* Warning * : All legacy user are not supported from this version. User's salt are also
  not supported, all users' passwords must be converted before switching to this version.

News from Teknoo Website 6.0
----------------------------

This library requires PHP 8.0 or newer and it's only compatible with Symfony 5.3 or newer
- Add `UserInterface` to represent and User in a Eastt Website / WebApp.
- Add `AuthDataInterface` to represent any data/credentials, able to authenticate an user
- Update `User` class to following the previeous interface
- Split authentications data from `User` class to a dedicated class `StoredPassword`
- Support password already hashed into `StoredPassword`
- Update Doctrine ODM mappingg about `User` ans add `StoredPassword`
- Support third-party authentication.
- Add `ThirdPartyAuth` to store ids data from thrid party needed to authenticate an user.
- Add `AbstractPassordAuthUser` to wrap password logic in Symfony User for `LegacyUser` and `PasswordAuthenticatedUser`.
- `AbstractUser` can be also used for non password authenticated user.
- Create `PasswordAuthenticatedUser` to implements new Symfony's interface `PasswordAuthenticatedUserInterface`
- Update `SymfonyUserWriter` implementation in Symfony to hash password only when its needed.
- Rework `UserProvider` to `PasswordAuthenticatedUserProvider` to return a `LegacyUser` if the user use the legacy Symfony behavior with a slug
  or a `PasswordAuthenticatedUser`. It is able to migrate logged user to the new behavior, update the hashed ppassword passed by Symfony and
  remove salt.
- Some QA fixes on PHPDoc
- Remove deprecated `ViewParameterInterface`
- Remove deprecated Symfony `User` class
- Create `StoredPasswordType` to manage new user in a Symfony Form.
- Fix some bug in admin routes.
- Update annd fix some minor bug in Doctrinemapping
- Create `OAuth2Authenticator`, built on KNPU OAuth2 client bundle to authenticate user thanks to a OAuth2 provider.

News from Teknoo Website 5.0
----------------------------

This library requires PHP 8.0 or newer and it's only compatible with Symfony 5.2 or newer
- Migrate to PHP 8.0
- Writers services, Deleting services, and interfaces use also `Teknoo\East\Website\Contracts\ObjectInterface`.
- Create `Teknoo\East\Website\Contracts\ObjectInterface`, `Teknoo\East\Website\Object\ObjectInterface` extends it
  dedicated to non persisted object, manipulable by other components
- Update steps and forms interface to use this new interface
- Replace ServerRequestInterface to MessageInterface for ListObjectAccessControlInterface and ObjectAccessControlInterface
- Switch Render steps to MessageInterface
- Add `ExprConversionTrait::addExprMappingConversion` to allow your custom evaluation of expression
- Add `ObjectReference` expression to filter on reference
- CreateObject step has a new parameter `$workPlanKey` to custom the key to use to store the
  new object in the workplan
- CreateObject, DeleteObject, LoadObject, SaveObject and SlugPreparation use `Teknoo\East\Website\Contracts\ObjectInterface`
  instead `Teknoo\East\Website\Object\ObjectInterface`. SaveObject pass the id only if the object implements
  this last object
  
News from Teknoo Website 4.0
----------------------------

This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer
- Migrate to Recipe 2.3+ and Tekno 3.3
- Migrate all classics services endpoints to Cookbook and Recipe.
- Remove all traits in main namespace with implementation in infrastructures namespaces.
- All cookbooks and recipes, and majors of step are defined in the main namespace, only specialized steps are defined in infrastructures namespace.
- Remove AdminEditEndPoint, AdminListEndPoint, AdminNewEndPoint, ContentEndPointTrait and MediaEndPointTrait.
- Update Symfony configuration to manage this new architecture. Remove all services dedicated for each objects in Website, replaced by only agnostic endpoint. All configuration is pass in route.

News from Teknoo Website 3.0
----------------------------

This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer
- Migrate to Doctrine ODM 2
- Migrate to new GridFS Repository
- Migrate Gedmo's Timestamp to intern function and service
- Migrate Gedmo's Slug to intern function and service
- Migrate to Doctrine XML Mapping
- Reworking Translation : Fork Gedmo Translation, clean, simplify, rework, in East philosophy
- Remove Gedmo
- Create new Translation configuration
- Pagination Query support countable
- ContentType and ItemType are not hardcoded to use DocumentType, but a Type passed in options via the EndPoint
- Optimize menu to limit requests
- Expr In Agnostic support
- Change Doctrine Repository behavior to create classes dedicated to ODM
- Create Common repository for non ODM with fallback feature
- Autoselect Good Repository in DI
- Migrate MediaEndPoint into ODM namespace
- Add ProxyDetectorInterface and a snippet into DI to detect if an object is behind a proxy agnosticaly
- Require to East Foundation 3.0.0
- Fix errors in services definitions
- Change exception management into MediaEndPoint

News from Teknoo Website 2.0
----------------------------

This library requires PHP 7.4 or newer and it's only compatible with Symfony 4.4 or newer, Some change causes bc breaks :
- PHP 7.4 is the minimum required
- Replace array_merge by "..." operators
- Remove some PHP useless DockBlocks
- Switch to typed properties
- Most methods have been updated to include type hints where applicable. Please check your extension points to make sure the function signatures are correct.
_ All files use strict typing. Please make sure to not rely on type coercion.
- Set default values for Objects.  
- Set dependencies defined into PHP-DI used in Symfony as synthetic
  services into Symfony's services definitions to avoid compilation error with Symfony 4.4
- Enable PHPStan in QA Tools and disable PHPMd
- Enable PHPStan extension dedicated to support Stated classes

Credits
-------
Richard Déloge - <richarddeloge@gmail.com> - Lead developer.
Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
East Website is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
