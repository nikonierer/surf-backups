#!/bin/bash

dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "Start parsing directory ${dir}/backups/"

for backup in ${dir}/backups/*.php
do
    echo "Backing up: ${backup}"

    file="${backup##*/}"
    ${dir}/bin/backup backup $(echo ${file} | cut -d'.' -f 1) --configurationPath=${dir}/backups >> ./log/backups.log
done