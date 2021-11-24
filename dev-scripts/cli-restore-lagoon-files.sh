#!/bin/bash

BACKUP_FILES_DIR="$1"

if [ -z "$BACKUP_FILES_DIR" ]; then
	echo "usage: $0 <BACKUP_FILES_DIR> " >&2
	exit 1
fi

BACKUP_FILES_DIR="/app/${1}"
BACKUP_PACKAGE=$(cd ${BACKUP_FILES_DIR} && ls -t *.tar.gz)
BACKUP_PACKAGE_PATH="${BACKUP_FILES_DIR}/${BACKUP_PACKAGE}"
FILES_DIRECTORY="/app/web/sites/default/files"


echo "Verifying file" \
&& test -s $BACKUP_PACKAGE_PATH \
    || (echo "package file is missing or empty" && exit 1) \
&& tar ztf $BACKUP_PACKAGE_PATH data/nginx &> /dev/null \
    || (echo "could not verify the tar.gz file: ${BACKUP_PACKAGE}" && exit 1) \
&& test -d $FILES_DIRECTORY \
    || (echo Could not find destination $FILES_DIRECTORY \
        && exit 1) \
&& echo Removing existing sites/default/files \
&& rm -fr $FILES_DIRECTORY \
&& echo Unpacking backup \
&& mkdir -p $FILES_DIRECTORY \
&& tar --strip 2 --gzip --extract --file $BACKUP_PACKAGE_PATH \
        --directory $FILES_DIRECTORY data/nginx \
&& echo Fixing permissions \
&& chmod -R 777 $FILES_DIRECTORY