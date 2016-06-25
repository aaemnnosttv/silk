#!/usr/bin/env bash

# Use the Phar rather than require it because we don't deploy the docs on every build, and it has a lot of dependencies.
wget https://phpdoc.org/phpDocumentor.phar
chmod +x phpDocumentor.phar
# Always build docs for the branch
echo "Building docs for branch '$TRAVIS_BRANCH'";
php phpDocumentor.phar -d src -t "$TRAVIS_BUILD_DIR/api-docs/branch/$TRAVIS_BRANCH" --cache-folder="$TRAVIS_BUILD_DIR/api-docs-cache"
# If the build is for a tagged release, build docs for that as well.
if [ "$TRAVIS_TAG" ]; then
    echo "Building docs for tag '$TRAVIS_TAG'";
    php phpDocumentor.phar -d src -t "$TRAVIS_BUILD_DIR/api-docs/tag/$TRAVIS_TAG" --cache-folder="$TRAVIS_BUILD_DIR/api-docs-cache"
fi
