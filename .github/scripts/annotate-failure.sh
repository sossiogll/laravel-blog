#!/usr/bin/env bash
# annotate-failure.sh <title> <command...>: runs the command and, when it fails, repeats the
# tail of its output as an annotation, because the Actions logs are not readable everywhere.
title=$1; shift
log=$(mktemp)
"$@" 2>&1 | tee "$log"
status=${PIPESTATUS[0]}
if [ "$status" -ne 0 ]; then
    echo "::error title=${title}::$(tail -c 3000 "$log" | sed ':a;N;$!ba;s/%/%25/g;s/\r/%0D/g;s/\n/%0A/g')"
fi
exit "$status"
