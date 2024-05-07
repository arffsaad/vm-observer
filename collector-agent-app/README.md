# Collector Agent - Golang

This will be the collector component used to retrieve data from the client devices to be fed back to the server.

## Details

This golang app accepts a "Connection String" which is a Base64 string consisting of 4 variables (in this specific order), separated by the pipe ("|") symbol.
- Kafka server (with port)
- Host ID (this must match the device ID retrieved from the PHP App dashboard)
- Kafka SASL username
- Kafka SASL password

This application is currently locked to use SASL_SSL and the SCRAM_SHA_256 mechanisms.

## Instructions

To run, simply build/install and pass the connection string as an argument.

```
go build
./collector-agent <connectionstring>
```
