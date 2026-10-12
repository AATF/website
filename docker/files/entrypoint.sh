#!/bin/bash

set -x

php-fpm 2>&1 &

nginx -g "daemon off;"
