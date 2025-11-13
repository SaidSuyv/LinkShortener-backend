# Link Shortener by SaidSuyv

Version: 0.0.1

# Description

This is the API for the project Link Shortened developed by SaidSuyv.

# How to install in production

## Clone this repository

`git clone <link> api.link.<domain.com>`

## Install dependencies

`composer install`

## Run migrations

`php artisan migrate`

## Keep queues alive

### systemd / Unix Systems

1. Create linkshortener.service file

**IMPORTANT: CHANGE ALL PATHS**

```text
[Unit]
Description=LinkShortener - Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/tu_proyecto/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/tu_proyecto
StandardOutput=append:/var/www/tu_proyecto/storage/logs/queue.log
StandardError=append:/var/www/tu_proyecto/storage/logs/queue-error.log

[Install]
WantedBy=multi-user.target
```

2. Move the file to the correct systemd path

`sudo mv linkshortener.service /etc/systemd/system/`

3. Active the service

```shell
sudo systemctl daemon-reload
sudo systemctl enable linkshortener.service
sudo systemctl start linkshortner.service
```

3.1. Verify the status

`sudo systemctl status linkshortener.service`

3.2. (optional) Check the logs

`journalctl -u linkshortener -f`

3.3. Restart the service

`sudo systemctl restart linkshortener`

### Termux / pseudo-unix system

1. Create the service structure

```shell
mkdir -p $PREFIX/var/service/linkshortener-worker
mkdir -p $PREFIX/var/service/linkshortener-worker/log
mkdir -p $PREFIX/var/service/linkshortener-worker/supervisor
nano $PREFIX/var/service/linkshortener-worker/run
```

2. Write the following content

```bash
#!/data/data/com.termux/files/usr/bin/sh

cd /data/data/com.termux/files/home/tu_proyecto

# Manten el dispositivo despierto
termux-wake-lock

# Inicia el worker
exec php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

3. Give the right permissions

`chmod +x $PREFIX/var/service/linkshortener-worker/run`

4. Start the service

`sv up linkshortener-worker`

4.1. Check the status

`sv status linkshortener-worker`

4.2. Start automatically at termux start

`sv-enable linkshortener-worker`