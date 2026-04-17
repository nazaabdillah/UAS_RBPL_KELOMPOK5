package services

import (
	"fmt"
	"log"
	"os"

	"gopkg.in/routeros.v2"
)

// ConnectMikrotik membuka koneksi TCP ke API RouterOS
func ConnectMikrotik() (*routeros.Client, error) {
	address := os.Getenv("MIKROTIK_HOST")
	user := os.Getenv("MIKROTIK_USER")
	password := os.Getenv("MIKROTIK_PASSWORD")

	client, err := routeros.Dial(address, user, password)
	if err != nil {
		log.Printf("[ERROR] Gagal terhubung ke Mikrotik di %s: %v", address, err)
		return nil, err
	}

	return client, nil
}

// GetActiveHotspotUsers mengambil daftar pengguna yang sedang terkoneksi ke WiFi
func GetActiveHotspotUsers() ([]map[string]string, error) {
	// [ARSITEKTUR MOCKING] 
	// Memotong koneksi fisik jika Tech Lead sedang berada di luar jaringan Router Fahar
	if os.Getenv("USE_MOCK_MIKROTIK") == "true" {
		fmt.Println("[MOCK MODE ACTIVE] Mengembalikan data dummy lokal. Mengabaikan koneksi fisik Mikrotik...")
		
		// Mengembalikan data statis untuk kebutuhan development Yoga (Frontend)
		return []map[string]string{
			{
				"user":    "yoga_tester",
				"address": "192.168.10.5",
				"mac":     "00:11:22:33:44:55",
				"uptime":  "1h20m",
			},
			{
				"user":    "agi_designer",
				"address": "192.168.10.6",
				"mac":     "AA:BB:CC:DD:EE:FF",
				"uptime":  "45m",
			},
		}, nil
	}

	// [KODE PRODUKSI] 
	// Dieksekusi jika USE_MOCK_MIKROTIK = false (Saat UAT atau di-deploy di warung Fahar)
	client, err := ConnectMikrotik()
	if err != nil {
		return nil, err
	}
	defer client.Close()

	// Eksekusi command winbox secara programatis
	reply, err := client.Run("/ip/hotspot/active/print")
	if err != nil {
		log.Printf("[ERROR] Gagal mengeksekusi command Mikrotik: %v", err)
		return nil, err
	}

	var users []map[string]string
	for _, re := range reply.Re {
		users = append(users, map[string]string{
			"user":    re.Map["user"],
			"address": re.Map["address"],
			"mac":     re.Map["mac-address"],
			"uptime":  re.Map["uptime"],
		})
	}

	return users, nil
}