#!/bin/sh
cd /usr/local/src/ceph-dr-sync-tool-for-proxmox

# copy the cofig once
#scp /etc/pve/nodes/pve01/qemu-server/100.conf root@172.16.201.74:/etc/pve/nodes/pve02/qemu-server/

php sync-primary-to-dr-site.php vm-100-disk-0
php sync-primary-to-dr-site.php vm-100-disk-1

php sync-primary-to-dr-site.php vm-101-disk-0


