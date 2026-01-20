#!/bin/sh -e
cd plugins/hphp && \
  apk add make minify zip && \
  make CI=1
