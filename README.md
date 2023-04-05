Teknoo Software - Common library
=================================

[![Latest Stable Version](https://poser.pugx.org/teknoo/east-common/v/stable)](https://packagist.org/packages/teknoo/east-common)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/east-common/v/unstable)](https://packagist.org/packages/teknoo/east-common)
[![Total Downloads](https://poser.pugx.org/teknoo/east-common/downloads)](https://packagist.org/packages/teknoo/east-common)
[![License](https://poser.pugx.org/teknoo/east-common/license)](https://packagist.org/packages/teknoo/east-common)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

Universal package, following the #East programming philosophy, build on Teknoo/East-Foundation (and Teknoo/Recipe),
and providing components (user management, object persistence, template rendering, ..) for the creation of web 
application or website.

This project is a fork of `East Website` to separate the CMS (admin, front and translation) and all others base 
components helpful to build a website or a webapp (objet persistence and CRUD operations, template rendering, user
management and authentification).

Example with Symfony 
--------------------

```yaml
//These operations are not required with teknoo/east-common-symfony

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

//config/packages/east_common_di.yaml:
di_bridge:
    definitions:
        - '%kernel.project_dir%/vendor/teknoo/east-common/src/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/doctrine/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/symfony/Resources/config/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/symfony/Resources/config/laminas_di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/di.php'
    import:
        Doctrine\Persistence\ObjectManager: 'doctrine_mongodb.odm.default_document_manager'

//bundles.php
...
Teknoo\DI\SymfonyBridge\DIBridgeBundle::class => ['all' => true],
Teknoo\East\FoundationBundle\EastFoundationBundle::class => ['all' => true],
Teknoo\East\CommonBundle\TeknooEastCommonBundle::class => ['all' => true],

//In doctrine config (east_common_doctrine_mongodb.yaml)
doctrine_mongodb:
    document_managers:
        default:
            auto_mapping: true
            mappings:
                TeknooEastCommon:
                    type: 'xml'
                    dir: '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/doctrine/config/universal'
                    is_bundle: false
                    prefix: 'Teknoo\East\Common\Object'
                TeknooEastCommonDoctrine:
                    type: 'xml'
                    dir: '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/doctrine/config/doctrine'
                    is_bundle: false
                    prefix: 'Teknoo\East\Common\Doctrine\Object'

//In security.yaml
security:
    //...
    enable_authenticator_manager: true

    providers:
        with_password:
            id: 'Teknoo\East\CommonBundle\Provider\PasswordAuthenticatedUserProvider'

    password_hashers:
        Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser:
            algorithm: '%teknoo.east.common.bundle.password_authenticated_user_provider.default_algo%'


//In routes/common.yaml
admin_common:
    resource: '@TeknooEastCommonBundle/Resources/config/admin_routing.yaml'
    prefix: '/admin'

common:
    resource: '@TeknooEastCommonBundle/Resources/config/routing.yaml'
```

Enable third party authentication with an OAuth2 Provider (example with Gitlab)
-------------------------------------------------------------------------------

```yaml
//In security.yaml
security:
    providers:
        //...
        # Third party user provider
        from_third_party:
            id: 'Teknoo\East\CommonBundle\Provider\ThirdPartyAuthenticatedUserProvider'
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
                - '%teknoo.east.common.bundle.security.authenticator.oauth2.class%'

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
    Teknoo\East\CommonBundle\EndPoint\ConnectEndPoint:
        class: 'Teknoo\East\CommonBundle\EndPoint\ConnectEndPoint'
        arguments:
          - '@KnpU\OAuth2ClientBundle\Client\ClientRegistry'
          - 'gitlab'
          - ['read_user']
        calls:
          - ['setResponseFactory', ['@Psr\Http\Message\ResponseFactoryInterface']]
          - ['setRouter', ['@router']]
        public: true

    Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface:
        class: 'App\Security\Authenticator\UserConverter'
```

```php
//In src/Security\Authenticator\UserConverter.php
<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use DomainException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface;
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
```

```yaml
//In routes/gitlab.yaml
admin_connect_gitlab_login:
    path: '/oauth2/gitlab/login'
    defaults:
        _controller: 'Teknoo\East\CommonBundle\EndPoint\ConnectEndPoint'

admin_connect_gitlab_check:
    path: '/oauth2/gitlab/check'
    defaults:
        _controller: 'teknoo.east.common.endpoint.static'
        template: '@@TeknooEastCommon/Admin/index.html.twig'
        errorTemplate: '@@TeknooEastCommon/Error/404.html.twig'
        _oauth_client_key: gitlab
```

```
//In your template, create a link with {{ path('admin_connect_gitlab_login') }}
```

Support this project
---------------------

This project is free and will remain free, but it is developed on my personal time. 
If you like it and help me maintain it and evolve it, don't hesitate to support me on 
[Patreon](https://patreon.com/teknoo_software).
Thanks :) Richard.

Installation & Requirements
---------------------------

To install this library with composer, run this command :

```sh
composer require teknoo/east-common
```
 
To start a project with Symfony :

```
symfony new your_project_name new
composer require teknoo/composer-install
composer require teknoo/east-common-symfony    
```

This library requires :

    * PHP 8.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Teknoo/East-Foundation.
    * Optional: Symfony 6.0+ (for administration)

News from Teknoo Common 7.0
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

Credits
-------

Richard Déloge - <richard@teknoo.software> - Lead developer.
Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------

**Teknoo Software** is a PHP software editor, founded by Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------

East Common is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
