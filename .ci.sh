#!/bin/sh
ENGINE=podman
[ -n "$CONTAINER_ENGINE" ] && ENGINE="$CONTAINER_ENGINE"

_ci() {
  "$ENGINE" run --rm -v "$PWD:/app" -w /app alpine:latest sh -c "cd plugins/hphp && apk add make minify zip && make $1"
}

_ci "CI=1"
