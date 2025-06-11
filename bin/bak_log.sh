#!/bin/bash
#备份日志
LOG_DIR=$(cd "$(dirname "$0")";cd ../logs;pwd)
for file in `ls ${LOG_DIR}`
    do
        if [[ "${file}" != "." ]] && [[ "${file}" != ".." ]]
        then
          echo ${file}
          mv -f "${LOG_DIR}/$file" "${LOG_DIR}/$file-bak"
        fi
    done