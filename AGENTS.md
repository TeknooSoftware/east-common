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
