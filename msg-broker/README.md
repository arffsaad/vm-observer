# Message Broker setup

For message broker, Apache Kafka is used.
The server is setup with 3 users, superadmin, phpConsumer and collectorAgent.

phpConsumer user will be used by PHP application to consume the deviceMetrics topic.
collectorAgent user is only used by the golang app as a client to produce updates into Kafka.

## Setup

- Install Kafka and copy the configs from this directory.

    ```
    sudo mkdir /opt/kafka
    wget https://dlcdn.apache.org/kafka/3.7.0/kafka_2.13-3.7.0.tgz
    tar -xzf kafka_2.13-3.7.0.tgz
    sudo mv kafka_2.13-3.7.0/* /opt/kafka
    rm -rf kafka_2.13-3.7.0.tgz kafka_2.13-3.7.0
    sudo cp * /opt/kafka/config
    ```

- Start the Zookeper server.

    ```
    sudo /opt/kafka/bin/zookeeper-server-start.sh /opt/kafka/config/zookeeper.properties
    ```

- On another terminal, pass the JAAS config to the JVM by running 
    
    ```
    export KAFKA_OPTS=-Djava.security.auth.login.config=/opt/kafka/config/kafka_server_jaas.conf
    ```

- Next, Create the users by running these commands

    ```
    sudo /opt/kafka/bin/kafka-configs.sh --zookeeper localhost:2181 --alter --add-config 'SCRAM-SHA-512=[password='superadmin']' --entity-type users --entity-name superadmin
    sudo /opt/kafka/bin/kafka-configs.sh --zookeeper localhost:2181 --alter --add-config 'SCRAM-SHA-512=[password=consume123!]' --entity-type users --entity-name phpConsumer
    sudo /opt/kafka/bin/kafka-configs.sh --zookeeper localhost:2181 --alter --add-config 'SCRAM-SHA-512=[password=collect123!]' --entity-type users --entity-name collectorAgent
    ```

- Start Kafka server using 
    
    ```
    sudo /opt/kafka/bin/kafka-server-start.sh /opt/kafka/config/server.properties
    ```

- Lastly, finish with setting up ACLs for the users and create the topic

    ```
    sudo /opt/kafka/bin/kafka-topics.sh --command-config /opt/kafka/config/superuser.properties --create --topic deviceMetrics --bootstrap-server localhost:9092
    sudo /opt/kafka/bin/kafka-acls.sh --command-config /opt/kafka/config/superuser.properties --bootstrap-server localhost:9092 --add --allow-principal User:phpConsumer --operation Read --topic deviceMetrics --group '*'
    sudo /opt/kafka/bin/kafka-acls.sh --command-config /opt/kafka/config/superuser.properties --bootstrap-server localhost:9092 --add --allow-principal User:collectorAgent --operation Write --topic deviceMetrics --group '*'
    ```