#!/bin/bash
./vendor/bin/phpunit --coverage-php build/cov/coverage.cov
./vendor/bin/coveralls
