#!/bin/bash

if [ ! -f ./sami.phar ]; then
    curl -O http://get.sensiolabs.org/sami.phar
fi

php ./sami.phar update ./sami.cfg.php
