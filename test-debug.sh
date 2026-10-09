#!/usr/bin/env bash
vendor/bin/phpunit --colors=never > phpunit.log 2>&1 && exit 0
msg=$(tail -c 8000 phpunit.log | tr '\n' '|')
echo "::error::${msg}"
exit 1
