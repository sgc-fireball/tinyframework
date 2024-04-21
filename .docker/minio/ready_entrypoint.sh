#!/bin/sh

set -euo pipefail

until (/usr/bin/mc config host add minio-local ${AWS_DOMAIN} ${MINIO_ROOT_USER} ${MINIO_ROOT_PASSWORD}); do
    echo '...waiting...' && sleep 1
done

/usr/bin/mc alias set minio-local ${AWS_DOMAIN} ${MINIO_ROOT_USER} ${MINIO_ROOT_PASSWORD}
/usr/bin/mc admin user add minio-local ${AWS_ACCESS_KEY_ID} ${AWS_SECRET_ACCESS_KEY}

/usr/bin/mc mb minio-local/${AWS_BUCKET_PUBLIC} || true
/usr/bin/mc admin policy attach minio-local/${AWS_BUCKET_PUBLIC} readwrite --user ${AWS_ACCESS_KEY_ID} || true
/usr/bin/mc anonymous set download minio-local/${AWS_BUCKET_PUBLIC}

/usr/bin/mc mb minio-local/${AWS_BUCKET_PRIVATE} || true
/usr/bin/mc admin policy attach minio-local/${AWS_BUCKET_PRIVATE} readwrite --user ${AWS_ACCESS_KEY_ID} || true
/usr/bin/mc anonymous set none minio-local/${AWS_BUCKET_PRIVATE}

/usr/bin/mc mb minio-local/${FTP_BUCKET} || true
/usr/bin/mc admin policy attach minio-local/${FTP_BUCKET} readwrite --user ${AWS_ACCESS_KEY_ID} || true
/usr/bin/mc anonymous set none minio-local/${FTP_BUCKET}

echo 'Successful!'

exit 0
