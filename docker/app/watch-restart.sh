#!/usr/bin/env bash
# watch-restart <command...>: runs a long-lived command and restarts it when PHP code changes.
# Used by the queue and scheduler services of the dev stack, which load the code once at start.
# Polls instead of using inotify: file events do not cross Docker Desktop's bind mounts reliably.
set -u

interval=${WATCH_INTERVAL:-2}
dirs=()
for dir in app config routes database contracts /gllhub-packages; do
    [ -d "$dir" ] && dirs+=("$dir")
done

stamp=$(mktemp)
child=0

start() {
    touch "$stamp"
    "$@" &
    child=$!
}

stop() {
    if kill -0 "$child" 2>/dev/null; then
        kill -TERM "$child"
        wait "$child" 2>/dev/null
    fi
}

changed() {
    find "${dirs[@]}" \( -name vendor -o -name node_modules -o -name .git \) -prune \
        -o -name '*.php' -newer "$stamp" -print -quit
}

trap 'stop; rm -f "$stamp"; exit 0' TERM INT

start "$@"
while true; do
    sleep "$interval" &
    wait $!
    file=$(changed)
    if [ -n "$file" ]; then
        echo "watch-restart: $file changed, restarting"
        stop
        start "$@"
    elif ! kill -0 "$child" 2>/dev/null; then
        echo "watch-restart: the command exited, restarting"
        start "$@"
    fi
done
