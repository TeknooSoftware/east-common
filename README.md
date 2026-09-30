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
#These operations are not required with teknoo/east-common-symfony

#config/packages/di_bridge.yaml:
di_bridge:
    compilation_path: '%kernel.project_dir%/var/cache/phpdi'
    definitions:
      - '%kernel.project_dir%/config/di.php'

#config/packages/east_foundation.yaml:
di_bridge:
    definitions:
        - '%kernel.project_dir%/vendor/teknoo/east-foundation/src/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-foundation/infrastructures/symfony/config/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-foundation/infrastructures/symfony/config/laminas_di.php'
    import:
        Psr\Log\LoggerInterface: 'logger'

#config/packages/east_common_di.yaml:
di_bridge:
    definitions:
        - '%kernel.project_dir%/vendor/teknoo/east-common/src/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/doctrine/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/symfony/config/di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/symfony/config/laminas_di.php'
        - '%kernel.project_dir%/vendor/teknoo/east-common/infrastructures/di.php'
    import:
        Doctrine\Persistence\ObjectManager: 'doctrine_mongodb.odm.default_document_manager'

#bundles.php
...
Teknoo\DI\SymfonyBridge\DIBridgeBundle::class => ['all' => true],
Teknoo\East\FoundationBundle\EastFoundationBundle::class => ['all' => true],
Teknoo\East\CommonBundle\TeknooEastCommonBundle::class => ['all' => true],

#In doctrine config (east_common_doctrine_mongodb.yaml)
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

#In security.yaml
security:
    #...
    providers:
        with_password:
            id: 'Teknoo\East\CommonBundle\Provider\PasswordAuthenticatedUserProvider'

    password_hashers:
        Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser:
            algorithm: '%teknoo.east.common.bundle.password_authenticated_user_provider.default_algo%'


#In routes/common.yaml
admin_common:
    resource: '@TeknooEastCommonBundle/config/admin_routing.yaml'
    prefix: '/admin'

common:
    resource: '@TeknooEastCommonBundle/config/routing.yaml'
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

Render JSON API responses
-------------------------

East Common's endpoints support JSON APIs. Add `api: 'json'` to a route's defaults, and use `.json.twig` templates
for `template` and `errorTemplate`. CSRF protection is disabled in this mode, and bodies can be sent as JSON (with the
header `Content-Type: application/json`), urlencoded or multipart. With a JSON body, the key `publish` publishes a
publishable object.

When `symfony/serializer` (enabled in `framework.serializer`) and `symfony/twig-bundle` (7.3 or later, the extensions
use Twig attributes) are installed, these Twig filters render JSON with the envelope `{"meta": {...}, "data": ...}`.
Objects are normalized by the East Foundation normalizer according to the groups passed in the context:

* `east_api_object_serialization(context, format, meta, parentObject)`: serializes an object or an array. The id and
  the root class of the identified object (or of the parent object) are added to `meta`.
* `east_api_collection_serialization(page, pageCount, context, format, meta)`: serializes a paginated collection,
  with `totalPages`, `page` and `count` in `meta`.
* `east_api_object_with_form_serialization(formView, context, format, meta, parentObject)`: serializes an object
  edited by a form, or, when the root form has errors (including errors bubbled from its fields), the form's errors,
  as `{"meta": {"errors": true}, "data": {".field": "message"}}`.
* `east_api_error_serialization(format, meta)`: serializes an error, as
  `{"meta": {"error": true}, "data": {"code": 404, "message": "..."}}`. Previous errors are listed in
  `data.previous`, with their code and their message. The class, the file, the line and the trace of errors are never
  exported. Messages of server errors (5xx), previous errors included, are hidden unless the parameter
  `teknoo.east.common.rendering.api.expose_server_error_message` is set to `true`.
* The function `east_api_form_errors(formView)` returns all errors of a form, indexed by the field's path.

The bundle ships these templates, which applications can override in `templates/bundles/TeknooEastCommonBundle/`:

* `@TeknooEastCommon/Error/default.json.twig`
* `@TeknooEastCommon/api/AdminUser/{list,item,deleted}.json.twig`
* `@TeknooEastCommon/api/AdminMedia/{list,item,deleted}.json.twig`

```yaml
#In routes/api.yaml
my_api_user_list:
    path: '/api/v1/admin/users'
    methods: ['GET']
    defaults:
        _controller: 'teknoo.east.common.endpoint.crud.list'
        api: 'json'
        defaultOrderDirection: 'ASC'
        errorTemplate: '@@TeknooEastCommon/Error/default.json.twig'
        itemsPerPage: 20
        loader: '@Teknoo\East\Common\Loader\UserLoader'
        template: '@@TeknooEastCommon/api/AdminUser/list.json.twig'
```

Authenticate API clients with API keys and JWT tokens
-----------------------------------------------------

East Common can authenticate the clients of a JSON API with JWT tokens, thanks to
[lexik/jwt-authentication-bundle](https://github.com/lexik/LexikJWTAuthenticationBundle). This bundle is not
required by East Common: install it (`composer require lexik/jwt-authentication-bundle`) only to use this feature.
A JWT token can be obtained in two ways:

* From an API key: a signed in user creates keys from a web page. An API client then logs in with a key, without the
  user's password and without 2FA, on a `json_login` route: the username is `key name:email` and the token is the
  secret of the key. The secret is displayed only once, when the key is created: only its hash is stored, in an
  `ApiKeyToken` owned by the `ApiKeysAuth` of the user. A key has an expiration date.
* From a web form, for a signed in user, or from the API, with a valid JWT token, to get a new one.

The `json_login` authenticator of Symfony ignores requests which are not in JSON, and the JWT authenticator then
answers `401 JWT Token not found`. The login route shipped by East Common declares the format `json`, so the body of
the login request is read as JSON even when the client does not send the header `Content-Type: application/json`.

Authentication failures are rendered like other errors of a JSON API, as
`{"meta": {"error": true}, "data": {"code": 401, "message": "..."}}`: East Common listens to the failures dispatched by
lexik/jwt-authentication-bundle (JWT token not found, invalid or expired). To get the same response when a login
fails, set the failure handler of this bundle on the `json_login` authenticator, as below.

The lifetime of a JWT token is the expiration date asked in the form, limited to
`teknoo.east.common.bundle.jwt.max_days_to_live` days.

```yaml
#In security.yaml
security:
    providers:
        //...
        # API key user provider, the identifier is `key name:email`
        with_api_key:
            id: 'Teknoo\East\CommonBundle\Provider\ApiKeysAuthenticatedUserProvider'

    password_hashers:
        //...
        # Secrets of API keys are hashed like passwords
        Teknoo\East\CommonBundle\Object\ApiKeysAuthUser:
            algorithm: '%teknoo.east.common.bundle.password_authenticated_user_provider.default_algo%'

    firewalls:
        //...
        api_area:
            pattern: '^/api'
            stateless: true
            # Provider used to load the user of a JWT token, from its email
            provider: 'with_password'
            jwt: ~
            json_login:
                provider: 'with_api_key'
                check_path: '_teknoo_common_api_jwt_login'
                username_path: 'username'
                password_path: 'token'
                # Render login failures like other errors of the API
                failure_handler: 'lexik_jwt_authentication.handler.authentication_failure'

    access_control:
        //...
        - { path: '^/api', roles: [IS_AUTHENTICATED_FULLY] }

#In lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'

#In routes/common.yaml
# Web pages to manage API keys and to generate a JWT token, in a firewall with a session
api_keys_common:
    resource: '@TeknooEastCommonBundle/config/api_keys_routing.yaml'
    prefix: '/my-settings'

jwt_common:
    resource: '@TeknooEastCommonBundle/config/jwt_routing.yaml'
    prefix: '/my-settings'

# JSON API: `POST /api/v1/login`, the `check_path` of the `json_login` authenticator
jwt_api_login_common:
    resource: '@TeknooEastCommonBundle/config/jwt_api_login_routing.yaml'
    prefix: '/api/v1'

# JSON API: `POST /api/v1/jwt/create-token`, with a valid JWT token
jwt_api_common:
    resource: '@TeknooEastCommonBundle/config/jwt_api_routing.yaml'
    prefix: '/api/v1'

#In services.yaml
parameters:
    # Prefix of generated secrets, empty by default
    teknoo.east.common.bundle.api_keys.token_prefix: 'my_'
    # Maximum lifetime of a JWT token, 1 day by default
    teknoo.east.common.bundle.jwt.max_days_to_live: 30
```

The JSON templates `@TeknooEastCommon/api/Jwt/{token,form}.json.twig` are shipped by the bundle: the token is returned
as `{"meta": {"error": false}, "data": {"token": "..."}}`. HTML templates of the web pages are provided by the
application, in `templates/bundles/TeknooEastCommonBundle/User/`:

* `api_keys.html.twig`: form to create a key (`formView`, with the fields `name` and `expiresAt`) and list of keys.
  `objectInstance.token` is the secret of the key just created. Keys are available from the user, for example with
  `app.user.wrappedUser.getOneAuthData('Teknoo\\East\\Common\\Object\\ApiKeysAuth').tokens`.
* `jwt_form.html.twig`: form to generate a JWT token (`formView`, with the field `expirationDate`).
* `jwt_token.html.twig`: the generated token, in `jwtToken`.

The application must also translate these keys: `teknoo.east.common.api_keys.form.name`,
`teknoo.east.common.api_keys.form.name.regex_error`, `teknoo.east.common.api_keys.form.expiration`,
`teknoo.east.common.api_keys.error.already_exists`, `teknoo.east.common.api_keys.error.list_not_accessible` and
`teknoo.east.common.jwt.form.expiration`.

With Doctrine ODM, the mapping of `ApiKeysAuth` and `ApiKeyToken` is shipped in
`infrastructures/doctrine/config/universal`, with the mapping of `User`.

Support this project
---------------------
This project is free and will remain free. It is fully supported by commercial activities of SASU Teknoo Software
and EIRL Richard DELOGE. If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).

Thanks :) Richard.

Credits
-------
EIRL Richard Déloge - <https://deloge.io> - Lead developer.
SASU Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge, as part of EIRL Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
sharing knowledge and skills.

License
-------
East Common is licensed under the 3-Clause BSD License - see the licenses folder for details.

Installation & Requirements
---------------------------
To install this library with composer, run this command :

```sh
composer require teknoo/east-common
```
 
To start a project with Symfony :

```
symfony new your_project_name new
composer require teknoo/east-common-symfony    
```

This library requires :

    * PHP 8.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.
    * Teknoo/Recipe.
    * Teknoo/East-Foundation.
    * Optional: Symfony 6.3+ (for administration)

News from Teknoo Common
-----------------------
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

Contribute :)
-------------
You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
