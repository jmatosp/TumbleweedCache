# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  config.vm.box = "oakensoul/ansible-php-cli-trusty64"

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "192.168.33.10"

  # config.vm.network "public_network"

  # config.vm.synced_folder "../data", "/vagrant_data"

  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  config.vm.provision "shell", inline: <<-SHELL
    sudo apt-get update
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    sudo apt-get install php5-curl php5-apcu php5-redis redis-server memcached php5-dev -y
    sudo pecl install runkit
    sudo sh -c "echo 'apc.enable_cli=1' >> /etc/php5/cli/conf.d/20-apcu.ini"
    sudo sh -c "echo 'extension=runkit.so=1' >> /etc/php5/cli/php.ini"
    sudo sh -c "echo 'runkit.internal_override=1' >> /etc/php5/cli/php.ini"
  SHELL
end
