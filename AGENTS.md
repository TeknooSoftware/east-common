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

- An `ApiKeyToken` keeps the hash of the value of a key (`token_hash`, a slow hash for the JWT flow) and, since 4.9,
  its SHA-256 digest (`token_digest`, with a unique sparse index in the mapping of `User`) and its scope. A key created
  before 4.9 has no digest and no scope. The value is never persisted.
- `Teknoo\East\Common\Contracts\User\ApiKeyFactoryInterface::create(User $owner, ApiKeyToken $apiKey, $promise)` is
  the only code that completes a new key: value, digest, hash, creation date. The form `ApiKeysAuthType` uses it. Its
  implementation is `Teknoo\East\CommonBundle\Security\ApiKeyFactory`. The caller saves the owner.
- `Teknoo\East\Common\Query\User\UserByApiKeyDigestQuery` loads the active user of a key from its digest.
- `Teknoo\East\CommonBundle\Security\Authenticator\ApiKeyAuthenticator` authenticates each request of a stateless
  firewall with the key `api_key` (options `second_header`, `accepted_scopes`, `failure_handler`), registered by
  `ApiKeyAuthenticatorFactory` in `TeknooEastCommonBundle::build()`. It supports each request of its firewall, uses
  no cache and puts `api_key_name` and `api_key_scope` in the attributes of the token. Its date is the real date by
  default (`teknoo.east.common.bundle.api_keys.authenticator.prefer_real_date`). A failure of the database gives an
  `AuthenticationServiceException`; a key that is not found (`DomainException` of the repository) gives a refusal.
- Parameters: `teknoo.east.common.bundle.api_keys.token_prefix`, `teknoo.east.common.bundle.api_keys.scopes` (empty:
  keys without scope) and `teknoo.east.common.bundle.api_keys.prefer_real_date` (`false` by default).
