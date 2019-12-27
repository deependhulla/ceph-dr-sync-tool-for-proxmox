#!/bin/sh

BASEDIR=$(dirname $0)
cd $BASEDIR

## to clean the Clone images used for testing data is ok

php remove-clone-from-dr-testing.php vm-100-disk-0
php remove-clone-from-dr-testing.php vm-100-disk-1



