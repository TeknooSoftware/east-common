# AGENTS.md - Project Knowledge Base

## Project Overview

`teknoo/east-common` is a universal PHP library built following the **#East programming philosophy**. It provides
fundamental components for web applications, including:

- User management
- Object persistence
- Template rendering
- Infrastructure adapters (Symfony, Doctrine, Flysystem, etc.)

## Technical Stack

- **Language**: PHP 8.4+
- **Dependency Injection**: PHP-DI, Symfony Dependency Injection
- **Persistence**:
    - Doctrine (specifically MongoDB ODM)
    - League Flysystem (for file storage)
- **Frontend/Assets**:
    - Matthiasmullie Minify (for CSS/JS)
    - Symfony UX (LiveComponent, TwigComponent)
- **Core Principles**: Decoupled architecture separating domain logic (`src/`) from infrastructure implementations
  (`infrastructures/`).

## Project Structure

- `src/`: Core domain logic and service definitions.
- `infrastructures/`: Infrastructure adapters and concrete implementations.
    - `symfony/`: Symfony-specific bundles and bridges.
    - `doctrine/`: Doctrine-based implementations.
    - `flysystem/`: File system abstractions.
    - `minify/`: Asset minification logic.
- `tests/`: Comprehensive test suites:
    - `universal/`: Core logic tests.
    - `infrastructures/`: Infrastructure-specific tests (symfony, doctrine, flysystem, minify).
    - `behat/`: Behavioral testing.

## Testing and Validation

All verification must be performed via the provided `Makefile`.

| Command           | Action                                                                      |
|-------------------|-----------------------------------------------------------------------------|
| `make depend`     | Install/update dependencies using Composer.                                 |
| `make test`       | Run unit tests (PHPUnit) and behavior tests (Behat).                        |
| `make qa`         | Run full quality assurance suite (Linting, PHPStan, PHPCS, Composer Audit). |
| `make qa-offline` | Run QA suite excluding security audit.                                      |
| `make clean`      | Remove `vendor/` and test cache directories.                                |

### Validation Details

- **Static Analysis**: PHPStan is used for type checking and static analysis.
- **Code Style**: PHPCS is used (PSR-12 standard).
- **Unit Testing**: PHPUnit.
- **Behavioral Testing**: Behat.

## Development Conventions

- **Namespaces**:
    - Core: `Teknoo\East\Common\`
    - Infrastructure: `Teknoo\East\CommonBundle\` (Symfony), `Teknoo\East\Common\Doctrine\`, etc.
- **Coding Standard**: PSR-12.
- **PHP Version**: Minimum requirement is PHP 8.4.

## API keys

- An `ApiKeyToken`, in `ApiKeysAuth`, is an API key of the JWT flow: it keeps the hash of its value (`token_hash`, a
  slow hash). The value is never persisted. The form `ApiKeysAuthType` and, since 4.9, the method `create()` of
  `Teknoo\East\Common\Contracts\User\ApiKeyTokenFactoryInterface` (implementation
  `Teknoo\East\CommonBundle\Security\ApiKeyTokenFactory`, without form and signed in user) create it.
- Since 4.9, a `BearerToken`, in `BearerTokensAuth`, is an API key sent with each request: it keeps only the SHA-256
  digest of its value (`token_digest`, with a unique sparse index in the mapping of `User`). It is another behavior,
  so another class: `ApiKeyToken` does not change. The method `create()` of
  `Teknoo\East\Common\Contracts\User\BearerTokenFactoryInterface` (implementation
  `Teknoo\East\Common\User\BearerTokenFactory`, in PHP-DI) creates it. The caller saves the owner.
- `Teknoo\East\Common\Query\User\UserByBearerTokenDigestQuery` loads the active user of a bearer token from its
  digest.
- `Teknoo\East\CommonBundle\Security\Authenticator\BearerTokenAuthenticator` authenticates each request of a
  stateless firewall with the key `bearer_token` (options `second_header`, `failure_handler`), registered by
  `BearerTokenAuthenticatorFactory` in `TeknooEastCommonBundle::build()`. It supports each request of its firewall,
  uses no cache, gives a `BearerTokenAuthenticatedUser` and puts `bearer_token_name` in the attributes of the token.
  Its date is the real date by default (`teknoo.east.common.bundle.bearer_tokens.authenticator.prefer_real_date`). A
  failure of the database gives an `AuthenticationServiceException`; a key that is not found (`DomainException` of
  the repository) gives a refusal.
- Parameters: `teknoo.east.common.bundle.api_keys.token_prefix` and
  `teknoo.east.common.bundle.api_keys.prefer_real_date` (`false` by default) for the API keys; the PHP-DI values
  `teknoo.east.common.user.bearer_tokens.token_prefix` and
  `teknoo.east.common.user.bearer_tokens.prefer_real_date` (`false` by default) for the bearer tokens.
