# VM-Observer PHP Application

### This application serves as a front-facing interface for the application, displaying the metrics and helping users to onboard their devices onto the platform.
### The application uses the TALL stack.

### Pre-requisites
This application requires PHP8.2, PHP-rdkafka extension, sqlite3 extension and basic php extensions needed to run laravel-based applications.

### Installation
- #### First extract the application files into a proper directory (preferably /var/www/html for hosting)
- #### Run these commands
    - `cp .env.example .env`
    - `composer install`
    - `npm install`
    - `npm run build`
    - `php artisan key:generate`
- #### Modify .env parameters. These parameters listed below are important and must be changed to suit your environment. Further explanation on the use of these parameters are explained in the .env file where necessary.
    - APP_URL
    - KAFKA_BROKERS
    - KAFKA_CONSUMER_USER
    - KAFKA_CONSUMER_PASSWORD
    - KAFKA_SECURITY_MECHANISM
    - KAFKA_SECURITY_PROTOCOL
    - KAFKA_COLLECTOR_SERVER
    - KAFKA_COLLECTOR_USER
    - KAFKA_COLLECTOR_PASSWORD

- #### Setup supervisord worker or run the consumer command manually
    - To allow the application to be subscribed to the broker, the `php artisan app:subscribe-topic` command must be run. 
    - This command is a long-running process, and is preferable to be run under supervisord to allow it to auto-start and restart
    - To use supervisord, first install it and create a config file
    ```
    sudo apt-get update
    sudo apt-get install supervisor
    sudo vim /etc/supervisor/conf.d/laravel-kafka-consumer.conf
    ```

    - You may use the below example config for supervisord

    ```[program:laravel-kafka-consumer]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/html/artisan queue:work
    autostart=true
    autorestart=true
    user=your-user
    numprocs=1
    redirect_stderr=true
    stdout_logfile=/var/log/laravel-kafka-consumer.log
    ```

    - Lastly, run the supervisor config using these commands
    ```sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start laravel-kafka-consumer:*
    ```