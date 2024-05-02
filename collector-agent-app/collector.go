package main

import (
	"context"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"strings"
	"time"

	"github.com/segmentio/kafka-go"
	"github.com/shirou/gopsutil/disk"
)

type DiskUsage struct {
	Host string            `json:"hostname"`
	Disk map[string]uint64 `json:"disk"`
}

func updateUsage(server string, hostid string) {
	host, err := os.Hostname()
	if err != nil {
		log.Fatal("Error retrieving hostname!", err)
	}

	usg, err := disk.Usage("/")
	if err != nil {
		log.Fatal("Error retrieving usage!", err)
	}

	disk := &DiskUsage{
		Host: host,
		Disk: map[string]uint64{"total": usg.Total, "free": usg.Free, "used": usg.Used},
	}

	usgData, err := json.Marshal(disk)
	if err != nil {
		log.Fatal("Unable to process usage statistics!", err)
	}

	topic := "deviceMetrics_" + hostid // will also be set by bash script.
	partition := 0

	conn, err := kafka.DialLeader(context.Background(), "tcp", server, topic, partition)
	if err != nil {
		log.Fatal("Failed to dial leader:", err)
	}

	conn.SetWriteDeadline(time.Now().Add(10 * time.Second))
	_, err = conn.WriteMessages(
		kafka.Message{Value: []byte(usgData)},
	)
	if err != nil {
		log.Fatal("failed to write messages:", err)
	}

	if err := conn.Close(); err != nil {
		log.Fatal("failed to close writer:", err)
	}
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

	// DEBUG
	fmt.Printf("server: %s\nhostid: %s\n", server, hostid)

	// first contact
	go updateUsage(server, hostid)

	ticker := time.NewTicker(2 * time.Minute) // Update every 2mins
	defer ticker.Stop()

	for {
		select {
		case <-ticker.C:
			go updateUsage(server, hostid)
		}
	}
}
