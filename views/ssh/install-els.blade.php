if ! ls /etc/apt/sources.list.d/*alt-php*.list 1>/dev/null 2>&1; then
    wget -O /tmp/install-els-alt-php-deb-repo.sh https://repo.alt.tuxcare.com/alt-php-els/install-els-alt-php-deb-repo.sh

    if ! sudo bash /tmp/install-els-alt-php-deb-repo.sh --license-key {{ $licenseKey }}; then
        rm -f /tmp/install-els-alt-php-deb-repo.sh
        echo 'VITO_SSH_ERROR' && exit 1
    fi

    rm -f /tmp/install-els-alt-php-deb-repo.sh
    sudo apt-get update
fi

if ! sudo DEBIAN_FRONTEND=noninteractive apt-get install -y alt-php{{ $version }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi
