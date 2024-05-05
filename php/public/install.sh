#!/bin/bash

install_go () {
        echo "Go is not detected, attempting to install..."
        if [ "$(arch)" == "x86_64" ]
        then
                cd /tmp
                echo "Downloading Go 1.22.2..."
                wget -q https://go.dev/dl/go1.22.2.linux-amd64.tar.gz
                echo "Installing Go 1.22.2..."
                sudo tar -C /usr/local -xzf go1.22.2.linux-amd64.tar.gz
                echo "Go is installed!"
                # cleanup
                rm -f go1.22.2.linux-amd64.tar.gz
        else
                echo "Unsupported architecture: $(arch)"
                echo "Please install go manually for your system https://go.dev/dl/"
                exit
        fi
}

# Check if connstring is passed
if [ -z "$1" ]
then
        echo "Connstring not passed!"
        echo "Usage: ./install.sh <connstring>"
        echo "Exiting..."
        exit
fi

cd /tmp

# Check if Golang is installed
if [ -d "/usr/local/go/bin" ]
then
        echo "Go is installed, continuing..."
else 
        install_go
fi

# Download release
# For now, hardcoded versioning. may move into dynamic versioning in possible future.

echo "Downloading collector-agent source..."
wget -q https://github.com/arffsaad/vm-observer/releases/download/v0.1.0/collector-agent.tar.gz
echo "Unpacking service..."
tar -xzf collector-agent.tar.gz
cd collector-agent-app
/usr/local/go/bin/go mod tidy && /usr/local/go/bin/go build
sudo mv collector-agent /usr/local/bin/

# creating systemd service
echo "Installing service..."
echo ""

sudo touch /etc/systemd/system/collector-agent.service
service_content="[Unit]
Description=vm-observer Collector Agent
After=network.target
StartLimitIntervalSec=0
[Service]
Type=simple
Restart=always
RestartSec=1
User=$(whoami)
ExecStart=/usr/local/bin/collector-agent $1

[Install]
WantedBy=multi-user.target"

sudo echo "$service_content" | sudo tee -a /etc/systemd/system/collector-agent.service
echo ""

sudo systemctl daemon-reload
sudo systemctl enable collector-agent
sudo systemctl start collector-agent

echo "Collector Agent succesfully installed! "
echo "Please check your dashboard and wait for 1-2 minutes for connection to be established"