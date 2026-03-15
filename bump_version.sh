#!/usr/bin/env bash
set -e

cd "$(git rev-parse --show-toplevel)"

TYPE=${1:-patch}

VERSION=$(cat VERSION)
IFS='.' read -r MAJOR MINOR PATCH <<< "$VERSION"

case "$TYPE" in
patch)
PATCH=$((PATCH+1))
;;
minor)
MINOR=$((MINOR+1))
PATCH=0
;;
major)
MAJOR=$((MAJOR+1))
MINOR=0
PATCH=0
;;
*)
echo "invalid type"
exit 1
;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
BRANCH=$(git rev-parse --abbrev-ref HEAD)

echo "$NEW_VERSION" > VERSION

git add VERSION
git commit -m "bump version $NEW_VERSION"

git tag -a "v$NEW_VERSION" -m "release v$NEW_VERSION"

git push origin "$BRANCH"
git push origin "v$NEW_VERSION"
