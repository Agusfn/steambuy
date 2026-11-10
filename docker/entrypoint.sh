#!/bin/sh
set -e
ROOT=/var/www/public_html
DATADIR=/var/lib/mysql
SOCK=/var/run/mysqld/mysqld.sock
INITDIR=/docker-entrypoint-initdb.d

# ---- Application runtime files ----
# Create runtime config only if absent
[ -f "$ROOT/global_scripts/config.php" ] || cp /usr/local/share/config.example.php "$ROOT/global_scripts/config.php"

# Writable runtime dirs / files
for d in \
  data/img/game_imgs/small data/img/game_imgs/224x105 data/img/game_imgs/240x112 data/img/game_imgs/320x149 \
  data/img/payment_receipts \
  global_scripts/AfipWs/debug_data global_scripts/AfipWs/resources/TA; do
  mkdir -p "$ROOT/$d"
done
[ -f "$ROOT/sitemap.xml" ] || touch "$ROOT/sitemap.xml"

# Seed demo game images when the (volume-backed) image dir has no full-size images yet
if [ -d /opt/seed-assets/game_imgs ] && ! ls "$ROOT"/data/img/game_imgs/*.jpg >/dev/null 2>&1; then
  echo "[entrypoint] Seeding demo game images"
  cp -a /opt/seed-assets/game_imgs/. "$ROOT/data/img/game_imgs/"
fi

chown -R www-data:www-data "$ROOT/data" "$ROOT/global_scripts/AfipWs/debug_data" \
  "$ROOT/global_scripts/AfipWs/resources/TA" "$ROOT/global_scripts/config.php" "$ROOT/sitemap.xml"
# Log/state txt files live in php/ (created on demand by the app)
chown www-data:www-data "$ROOT/global_scripts/php" "$ROOT"/global_scripts/php/*.txt 2>/dev/null || true

# ---- MySQL first-run initialisation ----
mkdir -p /var/run/mysqld
chown mysql:mysql /var/run/mysqld "$DATADIR"

# Escape backslashes and single quotes for SQL string literals
sqlesc() {
  printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/\\\\'/g"
}

if [ ! -d "$DATADIR/mysql" ]; then
  echo "[entrypoint] Initialising MySQL data directory"
  : "${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD is required}"
  : "${DB_NAME:?DB_NAME is required}"
  : "${DB_USER:?DB_USER is required}"
  : "${DB_PASSWORD:?DB_PASSWORD is required}"
  case "$DB_NAME" in
    *[!A-Za-z0-9_]*) echo "[entrypoint] DB_NAME must match [A-Za-z0-9_]+" >&2; exit 1 ;;
  esac

  cd /usr/local/mysql
  ./scripts/mysql_install_db --defaults-file=/etc/my.cnf --user=mysql \
    --basedir=/usr/local/mysql --datadir="$DATADIR" >/dev/null

  mysqld --defaults-file=/etc/my.cnf --skip-networking --socket="$SOCK" &
  MYSQLD_PID=$!

  i=0
  until mysqladmin --socket="$SOCK" -uroot ping >/dev/null 2>&1; do
    i=$((i+1))
    if [ "$i" -gt 90 ]; then echo "[entrypoint] mysqld did not start" >&2; exit 1; fi
    kill -0 "$MYSQLD_PID" 2>/dev/null || { echo "[entrypoint] mysqld died" >&2; exit 1; }
    sleep 1
  done

  R=$(sqlesc "$MYSQL_ROOT_PASSWORD")
  P=$(sqlesc "$DB_PASSWORD")
  U=$(sqlesc "$DB_USER")

  mysql --socket="$SOCK" -uroot <<SQL
DELETE FROM mysql.user WHERE NOT (User='root' AND Host='localhost');
DROP DATABASE IF EXISTS test;
UPDATE mysql.user SET Password=PASSWORD('$R') WHERE User='root' AND Host='localhost';
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER '$U'@'127.0.0.1' IDENTIFIED BY '$P';
CREATE USER '$U'@'localhost' IDENTIFIED BY '$P';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$U'@'127.0.0.1';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$U'@'localhost';
FLUSH PRIVILEGES;
SQL

  export MYSQL_PWD="$MYSQL_ROOT_PASSWORD"
  for f in "$INITDIR"/*.sql "$INITDIR"/*.sql.gz; do
    [ -f "$f" ] || continue
    echo "[entrypoint] Importing $f"
    case "$f" in
      *.gz) gunzip -c "$f" | mysql --socket="$SOCK" -uroot "$DB_NAME" ;;
      *)    mysql --socket="$SOCK" -uroot "$DB_NAME" < "$f" ;;
    esac
  done

  mysqladmin --socket="$SOCK" -uroot shutdown
  wait "$MYSQLD_PID" || true
  unset MYSQL_PWD
  echo "[entrypoint] MySQL initialised"
fi

exec "$@"
