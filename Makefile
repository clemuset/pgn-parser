IMAGE ?= pgn-parser:latest
DOCKER ?= docker

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo "Available targets:"
	@echo "  help                 Show this help message"
	@echo "  build                Build the Docker image ($(IMAGE))"
	@echo "  fix                  Automatically fix code style in the container"
	@echo "  test                 Run PHPUnit in the container"
	@echo "  analyse              Run PHPStan static analysis in the container"
	@echo "  shell                Open a shell in the container"
	@echo "  check                Run fix, analyse, and test targets"

.PHONY: build
build:
	$(DOCKER) build -t $(IMAGE) .

.PHONY: fix
fix:
	$(DOCKER) run --rm -v $(PWD):/app $(IMAGE) php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

.PHONY: test
test:
	$(DOCKER) run --rm -v $(PWD):/app $(IMAGE) php vendor/bin/phpunit --display-warnings --display-deprecations --display-notices --colors=always

.PHONY: analyse
analyse:
	$(DOCKER) run --rm -v $(PWD):/app $(IMAGE) php vendor/bin/phpstan analyse

.PHONY: shell
shell:
	$(DOCKER) run --rm -it -v $(PWD):/app $(IMAGE) sh

.PHONY: check
check: fix analyse test
