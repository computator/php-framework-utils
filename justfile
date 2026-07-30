_default:
	@just --list

php *ARGS:
	podman run --rm -it -v .:/app -w /app \
		--mount type=image,src=docker.io/library/composer,dst=/composer \
		docker.io/library/php:8.1-cli-alpine {{ARGS}}

composer *ARGS: (php "/composer/usr/bin/composer" ARGS)
deps: (composer 'install')
test *ARGS='tests --testdox': (php "/app/vendor/bin/phpunit" ARGS)

coverage-ctr-name := file_name(justfile_directory()) + '_php-coverage'
coverage-ctr-containerfile := '''
	FROM docker.io/library/php:8.1-cli-alpine
	RUN apk add $PHPIZE_DEPS && pecl install pcov && docker-php-ext-enable pcov
	STOPSIGNAL SIGKILL
'''

coverage-start:
	podman run --rm --name '{{ coverage-ctr-name }}' -d -v .:/app -w /app \
		$( \
			echo '{{ coverage-ctr-containerfile }}' \
			| podman build --quiet=false --iidfile /dev/fd/3 -f - 3>&1 1>&2 \
			| cut -d : -f 2 \
		) \
		sleep inf

coverage-stop:
	podman rm -f '{{ coverage-ctr-name }}'
coverage *ARGS='tests --coverage-text --show-uncovered-for-coverage-text':
	podman exec -it '{{ coverage-ctr-name }}' /app/vendor/bin/phpunit \
	--coverage-filter /app/src {{ARGS}}
