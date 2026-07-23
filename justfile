_default:
	@just --list

php *ARGS:
	podman run --rm -it -v .:/app -w /app \
		--mount type=image,src=docker.io/library/composer,dst=/composer \
		docker.io/library/php:8.1-cli-alpine {{ARGS}}

composer *ARGS: (php "/composer/usr/bin/composer" ARGS)
deps: (composer 'install')
test *ARGS='tests --testdox': (php "/app/vendor/bin/phpunit" ARGS)
