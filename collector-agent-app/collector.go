package main

import (
	"fmt"
	"os"

	"github.com/shirou/gopsutil/disk"
)

func main() {
	host, err := os.Hostname()
	if err != nil {
		fmt.Println("Error retrieving hostname!")
		panic(err)
	}
	fmt.Println("Usage statistics for", host)
	parts, err := disk.Partitions(false)
	if err != nil {
		fmt.Println("Error retrieving partitions!")
		panic(err)
	}

	for _, partition := range parts {
		fmt.Println("Partition: ", partition)
	}

	usg, err := disk.Usage("/")
	if err != nil {
		fmt.Println("Error retrieving usage!")
		panic(err)
	}
	fmt.Println("Main disk usage (~/):", usg)
}
