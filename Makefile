### Variables

# Applications
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= lastest
PHP ?= /usr/bin/env php

### Helpers
all: clean depend

.PHONY: all

### Dependencies
depend:
ifeq ($(DEPENDENCIES), lowest)
	${COMPOSER} update --prefer-lowest --prefer-dist --no-interaction;
else
	${COMPOSER} update --prefer-dist --no-interaction;
endif

.PHONY: depend

### QA
qa: lint phpstan phpcs audit
qa-offline: lint phpstan phpcs

lint:
	find ./src -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0
	find ./infrastructures -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

phpstan:
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse

phpcs:
	${PHP} vendor/bin/phpcs --standard=PSR12 --extensions=php src/ infrastructures/

audit:
	${COMPOSER} audit

.PHONY: qa qa-offline lint phpstan phpcs audit

### Testing
# Isolated tests run in dedicated PHP processes, which do not inherit the `-d` options of the command line: when Xdebug
# is not already enabled in the PHP configuration, it is loaded from an ini file, also scanned by these processes.
ifeq ($(shell ${PHP} -r 'echo (int) extension_loaded("xdebug");'),1)
PHP_WITH_XDEBUG = XDEBUG_MODE=coverage ${PHP}
else
PHP_WITH_XDEBUG = XDEBUG_MODE=coverage PHP_INI_SCAN_DIR=":$(CURDIR)/tests/support/xdebug" ${PHP}
endif

test:
	${PHP_WITH_XDEBUG} -dxdebug.mode=coverage vendor/bin/phpunit -c phpunit.xml --colors --coverage-text
	${PHP} vendor/bin/behat
	rm -rf tests/var/cache/

.PHONY: test

### Cleaning
clean:
	rm -rf vendor
	rm -rf tests/var/cache/*

.PHONY: clean
