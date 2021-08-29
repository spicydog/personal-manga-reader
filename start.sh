#!/usr/bin/env bash

set -e
role=${ROLE:-app}

if [ "$role" = "downloader" ]; then
    php downloader.php
else
    apache2-foreground
fi
