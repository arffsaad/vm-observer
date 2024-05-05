package main

import (
	"context"
	"crypto/tls"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"strings"
	"time"

	"github.com/segmentio/kafka-go"
	"github.com/segmentio/kafka-go/sasl/scram"
	"github.com/shirou/gopsutil/disk"
)

type DiskUsage struct {
	Host   string            `json:"hostname"`
	Hostid string            `json:hostId`
	Disk   map[string]uint64 `json:"disk"`
}

func updateUsage(server string, hostid string, username string, password string) { // albeit having credentials here, we will make sure the credentials have limited permissions.
	host, err := os.Hostname()
	if err != nil {
		log.Fatal("Error retrieving hostname!", err)
	}

	usg, err := disk.Usage("/")
	if err != nil {
		log.Fatal("Error retrieving usage!", err)
	}

	disk := &DiskUsage{
		Host:   host,
		Hostid: hostid,
		Disk:   map[string]uint64{"total": usg.Total, "free": usg.Free, "used": usg.Used},
	}

	usgData, err := json.Marshal(disk)
	if err != nil {
		log.Fatal("Unable to process usage statistics!", err)
	}

	topic := "deviceMetrics"

	mechanism, _ := scram.Mechanism(scram.SHA256, username, password)
	w := kafka.Writer{
		Addr:  kafka.TCP(server),
		Topic: topic,
		Transport: &kafka.Transport{
			SASL: mechanism,
			TLS:  &tls.Config{},
		},
	}
	w.WriteMessages(context.Background(), kafka.Message{Value: []byte(usgData)})
	w.Close()
}

func main() {
	if len(os.Args) < 2 {
		panic("Not enough arguments! Usage: ./collector-agent <connectionString>")
	}
	connectionString := os.Args[1]
	decodedBytes, err := base64.StdEncoding.DecodeString(connectionString)
	if err != nil {
		log.Fatal("Error decoding connection string!", err)
	}
	connectionString = string(decodedBytes)
	if !strings.Contains(connectionString, "|") {
		log.Fatal("Invalid connection string!")
	}
	server := strings.Split(connectionString, "|")[0]
	hostid := strings.Split(connectionString, "|")[1]
	username := strings.Split(connectionString, "|")[2]
	password := strings.Split(connectionString, "|")[3]

	// DEBUG
	fmt.Printf("server: %s\nhostid: %s\n", server, hostid)

	// first contact
	go updateUsage(server, hostid, username, password)

	ticker := time.NewTicker(2 * time.Minute) // Update every 2mins
	defer ticker.Stop()

	for {
		select {
		case <-ticker.C:
			go updateUsage(server, hostid, username, password)
		}
	}
}
