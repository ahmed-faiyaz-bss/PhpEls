if ! sudo DEBIAN_FRONTEND=noninteractive apt-get install -y alt-php{{ $version }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi

if [ -f /opt/alt/php{{ $version }}/etc/php-fpm.d/www.conf ]; then
    sudo sed -i 's/apache/{{ $user }}/g' /opt/alt/php{{ $version }}/etc/php-fpm.d/www.conf
    sudo sed -i 's/nobody/{{ $user }}/g' /opt/alt/php{{ $version }}/etc/php-fpm.d/www.conf
fi

sudo systemctl enable alt-php{{ $version }}-fpm
sudo systemctl start alt-php{{ $version }}-fpm
