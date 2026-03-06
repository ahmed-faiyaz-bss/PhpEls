if ! sudo DEBIAN_FRONTEND=noninteractive apt-get install -y alt-php{{ $version }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi

# Install MySQL extensions
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y alt-php{{ $version }}-mysql80 alt-php{{ $version }}-mysqlnd

# Enable MySQL modules
PHP_ETC="/opt/alt/php{{ $version }}/etc"
if [ -f "$PHP_ETC/php.d.all/mysqlnd.ini" ]; then
    sudo cp "$PHP_ETC/php.d.all/mysqlnd.ini" "$PHP_ETC/php.d/mysqlnd.ini"
fi
if [ -f "$PHP_ETC/php.d.all/mysqli.ini" ]; then
    sudo cp "$PHP_ETC/php.d.all/mysqli.ini" "$PHP_ETC/php.d/mysqli.ini"
fi
if [ -f "$PHP_ETC/php.d.all/pdo_mysql.ini" ]; then
    sudo cp "$PHP_ETC/php.d.all/pdo_mysql.ini" "$PHP_ETC/php.d/pdo_mysql.ini"
fi

# Create FPM pool config if none exists
POOL_DIR="/opt/alt/php{{ $version }}/etc/php-fpm.d"
if [ -z "$(ls -A $POOL_DIR/*.conf 2>/dev/null)" ]; then
    sudo mkdir -p "$POOL_DIR"
    sudo bash -c "cat > $POOL_DIR/www.conf" <<'POOLEOF'
[www]
user = {{ $user }}
group = {{ $user }}

listen = /run/alt-php{{ $version }}-fpm/php-fpm.sock
listen.owner = vito
listen.group = vito
listen.mode = 0660

pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500
POOLEOF
fi

# Ensure socket directory exists
sudo mkdir -p /run/alt-php{{ $version }}-fpm

sudo systemctl enable alt-php{{ $version }}-fpm
sudo systemctl start alt-php{{ $version }}-fpm
