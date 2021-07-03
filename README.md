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
        //..
        providers:
            main:
                id: 'teknoo.east.website.bundle.user_provider'

    //In routes/website.yml
    admin_website:
        resource: '@TeknooEastWebsiteBundle/Resources/config/admin_routing.yml'
        prefix: '/admin'
    
    website:
        resource: '@TeknooEastWebsiteBundle/Resources/config/routing.yml'

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

    * PHP 8.0+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Teknoo/East-Foundation.
    * Optional: Symfony 5.2+ (for administration)

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
