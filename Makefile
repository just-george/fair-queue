all: composer test
test: generate style mess lint unit integration

composer:
	composer install
generate:
	vendor/bin/codecept build
style:
	env PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer -v check
mess:
	vendor/bin/phpmd --exclude=test/Support/_generated src,test text phpmd.xml
lint:
	vendor/bin/phpstan
unit:
	vendor/bin/phpunit
integration:
	vendor/bin/codecept run
