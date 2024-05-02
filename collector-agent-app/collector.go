package main

import (
	"context"
	"encoding/json"
	"log"
	"os"
	"time"

	"github.com/segmentio/kafka-go"
	"github.com/shirou/gopsutil/disk"
)

type DiskUsage struct {
	Host   string         `json:"hostname"`
	HostID string         `json: "hostID"`
	Disk   map[string]int `json:"disk"` // Map for named disk usage
}

func updateUsage() {
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
		HostID: "b2626db1-879f-478c-9e0b-56060e5d6912", // hardcoded, this will be set by installation bash script
		Disk:   map[string]int{"total": int(usg.Total), "free": int(usg.Free), "used": int(usg.Used)},
	}

	usgData, err := json.Marshal(disk)
	if err != nil {
		log.Fatal("Unable to process usage statistics!", err)
	}

	topic := "deviceMetrics_someuuid-213123123-asdfasdvkj" // will also be set by bash script.
	partition := 0

	conn, err := kafka.DialLeader(context.Background(), "tcp", "localhost:9092", topic, partition)
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
	ticker := time.NewTicker(time.Minute)
	defer ticker.Stop()

	for {
		select {
		case <-ticker.C:
			go updateUsage()
		}
	}
}
