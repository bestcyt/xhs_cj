#!/bin/bash
#删除日志
LOG_DIR=$(cd "$(dirname "$0")";cd ../logs;pwd)
rm -rf ${LOG_DIR}/*-bak