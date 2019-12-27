#!/bin/sh

BASEDIR=$(dirname $0)
cd $BASEDIR


# copy the cofig once from primary
##/etc/pve/nodes/pve01/qemu-server/100.conf 


php make-clone-for-dr-testing.php vm-100-disk-0
php make-clone-for-dr-testing.php vm-100-disk-1



