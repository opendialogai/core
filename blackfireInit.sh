#!/usr/bin/env bash

# Configure Blackfire Repository
wget -q -O - https://packagecloud.io/gpg.key | apt-key add -
echo 'deb http://packages.blackfire.io/debian any main' | tee /etc/apt/sources.list.d/blackfire.list
apt-get update

# Install Blackfire Agent
apt-get install blackfire-agent
printf "%s\n" $BLACKFIRE_CLIENT_ID $BLACKFIRE_CLIENT_TOKEN | blackfire config

# Start blackfire on-boot
/etc/init.d/blackfire-agent restart

# Install Blackfire Probe
version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;")
curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version
tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp
mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so

# Enable Blackfire Probe
docker-php-ext-enable blackfire