#!/bin/bash
mkdir -p ./build/logs

./vendor/bin/phpunit --coverage-clover build/logs/clover.xml
./vendor/bin/coveralls
