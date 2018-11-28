#!/usr/bin/env bash
REM rm "${0%/*}/../data/cache/schema/cqrs.inc.php"
REM php -S 0.0.0.0:4775 -t "${0%/*}/../" "${0%/*}/../index.php"
php -S 0.0.0.0:4775 -t ".." "..\index.php"

