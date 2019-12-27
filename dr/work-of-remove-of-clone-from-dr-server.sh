#!/bin/sh
cd /usr/local/src/ceph-dr-sync-tool-for-proxmox

## to clean the Clone images used for testing data is ok

php remove-clone-from-dr-testing.php vm-100-disk-0
php remove-clone-from-dr-testing.php vm-100-disk-1



