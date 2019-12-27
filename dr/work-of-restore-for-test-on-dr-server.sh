#!/bin/sh
cd /usr/local/src/ceph-dr-sync-tool-for-proxmox

# copy the cofig once from primary
##/etc/pve/nodes/pve01/qemu-server/100.conf 


php make-clone-for-dr-testing.php vm-100-disk-0
php make-clone-for-dr-testing.php vm-100-disk-1



