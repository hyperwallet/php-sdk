#!/bin/bash

if [ ! -f ./sami.phar ]; then
    curl -O http://get.sensiolabs.org/sami.phar
fi

php ./sami.phar update ./sami.cfg.php

echo "
    <!DOCTYPE HTML>
    <html lang="en-US">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="1;url=/master">
            <script type="text/javascript">
                window.location.href = "/master";
            </script>
            <title>Page Redirection</title>
        </head>
        <body>
            <!-- Note: don't tell people to `click` the link, just tell them that it is a link. -->
            If you are not redirected automatically, follow the <a href='/master'>link to SDK documentation</a>
        </body>
    </html>
" >> ./docs/index.html
