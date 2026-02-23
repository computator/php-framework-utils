_default:
	@just --list

deps: (composer 'install')

composer *ARGS:
	podman run --rm -it -v .:/app docker.io/library/composer {{ARGS}}

test *ARGS='tests --testdox':
	podman run --rm -it -v .:/app -w /app docker.io/library/php:8.1-cli-alpine \
		/app/vendor/bin/phpunit {{ARGS}}
