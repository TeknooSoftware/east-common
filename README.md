Teknoo Software - Website library
=================================

[![Build Status](https://travis-ci.org/TeknooSoftware/east-website.svg?branch=master)](https://travis-ci.org/TeknooSoftware/east-website) [![Build Status](https://travis-ci.org/TeknooSoftware/east-website.svg?branch=master)](https://travis-ci.org/TeknooSoftware/east-website)

Universal package, following the #East programming philosophy, build on Teknoo/East-Foundation (and Teknoo/Recipe),
and implementing a basic CMS to display dynamics pages with different types and templates.

Example with Symfony
--------------------

    <?php

    //In the AppKernel:
    use DI\Bridge\Symfony\Kernel;
    use DI\ContainerBuilder as DIContainerBuilder;
    use Doctrine\Common\Persistence\ObjectManager;

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = [
                //..
                new Teknoo\East\FoundationBundle\EastFoundationBundle(),
                new Teknoo\East\WebsiteBundle\TeknooEastWebsiteBundle(),
            ];

            //..

            return $bundles;
        }

        protected function buildPHPDIContainer(DIContainerBuilder $builder)
        {
            // Configure your container here
            $vendorPath = dirname(__DIR__).'/vendor';
            $builder->addDefinitions($vendorPath.'/teknoo/east-foundation/src/universal/di.php');
            $builder->addDefinitions($vendorPath.'/teknoo/east-foundation/src/symfony/Resources/config/di.php');
            $builder->addDefinitions($vendorPath.'/teknoo/east-website/src/universal/di.php');
            $builder->addDefinitions([
                ObjectManager::class => \DI\get('doctrine_mongodb.odm.default_document_manager')
            ]);

            return $builder->build();
        }
    }

    //In app/config.yml
    doctrine_mongodb:
      document_managers:
        default:
          auto_mapping: true
          mappings:
            TeknooEastWebsite:
              type: 'yml'
              dir: '%kernel.root_dir%/../vendor/teknoo/east-website/src/universal/config/doctrine'
              is_bundle: false
              prefix: 'Teknoo\East\Website\Object'

    //In app/security.yml
    security:
      //..
      providers:
        main:
          id: 'teknoo.east.website.bundle.user_provider'

    //In app/routing.yml
    website:
      resource: '@TeknooEastWebsiteBundle/Resources/config/routing.yml'

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/east-website

This library requires :

    * PHP 7.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Teknoo/East-Foundation.
    * Optional: Symfony 3.4+ (for administration)

API Documentation
-----------------
The API documentation is available at : [API](docs/howto/api/index.index).

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
