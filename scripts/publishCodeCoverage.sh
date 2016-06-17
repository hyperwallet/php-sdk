#!/bin/bash
mkdir -p ./build/cov

./vendor/bin/phpunit --coverage-php build/cov/coverage.cov
./vendor/bin/coveralls
