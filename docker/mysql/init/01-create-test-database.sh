#!/bin/sh

set -eu

case "${MYSQL_DATABASE}" in
    ''|*[!a-zA-Z0-9_]*)
        echo "MYSQL_DATABASE must contain only letters, numbers and underscores." >&2
        exit 1
        ;;
esac

case "${MYSQL_USER}" in
    ''|*[!a-zA-Z0-9_]*)
        echo "MYSQL_USER must contain only letters, numbers and underscores." >&2
        exit 1
        ;;
esac

test_database="${MYSQL_DATABASE}_test"
grant_database=$(printf '%s' "${test_database}" | sed 's/_/\\_/g')

mysql --protocol=socket --user=root --password="${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${test_database}\`;
GRANT ALL PRIVILEGES ON \`${grant_database}\`.* TO '${MYSQL_USER}'@'%';
SQL
