php_ver_main := '8.5'
php_ver_min := '8.1'

_default:
	@just --list

[group('php')]
composer *ARGS: (_php-call php_ver_main "/composer/usr/bin/composer" ARGS)

deps: (composer 'install')

[group('testing')]
phpunit *ARGS: (_php-call php_ver_main "/app/vendor/bin/phpunit" ARGS)


[group('testing')]
test *ARGS='tests': \
	(_php-call php_ver_min "/app/vendor/bin/phpunit" ARGS) \
	(_php-call php_ver_main "/app/vendor/bin/phpunit" "--testdox" ARGS)

coverage-ctr-name := file_name(justfile_directory()) + '_php-coverage'
coverage-ctr-containerfile := f'''
	FROM docker.io/library/php:{{ php_ver_main }}-cli-alpine
	RUN apk add $PHPIZE_DEPS && pecl install pcov && docker-php-ext-enable pcov
	STOPSIGNAL SIGKILL
'''

[group('coverage')]
coverage-start:
	podman run --rm --name '{{ coverage-ctr-name }}' -d -v .:/app -w /app \
		$( \
			echo '{{ coverage-ctr-containerfile }}' \
			| podman build --quiet=false --iidfile /dev/fd/3 -f - 3>&1 1>&2 \
			| cut -d : -f 2 \
		) \
		sleep inf

[group('coverage')]
coverage-stop:
	podman rm -f '{{ coverage-ctr-name }}'

[group('coverage')]
coverage-call *ARGS:
	podman exec -it '{{ coverage-ctr-name }}' /app/vendor/bin/phpunit {{ARGS}}

[group('coverage')]
coverage *ARGS='tests': (coverage-call "--coverage-filter src/ --coverage-html ./.coverage_html --coverage-text" ARGS)


_php-call phpver=php_ver_main *ARGS:
	podman run --rm -it -v .:/app -w /app \
		--mount type=image,src=docker.io/library/composer,dst=/composer \
		docker.io/library/php:{{trim(phpver)}}-cli-alpine {{ARGS}}

[group('php')]
[arg('phpver', long)]
php phpver=php_ver_main *ARGS: (_php-call phpver ARGS)
