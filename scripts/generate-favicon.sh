#!/usr/bin/env bash

mkdir -p public/favicon

convert public/favicon/favicon.svg -resize 16x16 public/favicon/favicon-16x16.png
convert public/favicon/favicon.svg -resize 32x32 public/favicon/favicon-32x32.png
convert public/favicon/favicon.svg -resize 48x48 public/favicon/favicon-48x48.png
convert public/favicon/favicon.svg -resize 64x64 public/favicon/favicon-64x64.png
convert public/favicon/favicon.svg -resize 128x128 public/favicon/favicon-128x128.png
convert public/favicon/favicon.svg -resize 256x256 public/favicon/favicon-256x256.png

convert public/favicon/favicon.svg -resize 64x64 public/favicon/favicon.ico
