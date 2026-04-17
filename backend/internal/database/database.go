package database

import (
	"fmt"
	"log"
	"os"
	
	"wifi-voucher-management/internal/models"

	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

var DB *gorm.DB

func ConnectDB() {
	dsn := fmt.Sprintf("host=%s user=%s password=%s dbname=%s port=%s sslmode=disable TimeZone=Asia/Jakarta",
		os.Getenv("DB_HOST"), os.Getenv("DB_USER"), os.Getenv("DB_PASSWORD"), os.Getenv("DB_NAME"), os.Getenv("DB_PORT"))

	db, err := gorm.Open(postgres.Open(dsn), &gorm.Config{})
	if err != nil {
		log.Fatal("Gagal koneksi ke database: \n", err)
	}

	fmt.Println("Database PostgreSQL berhasil terkoneksi.")

	// Eksekusi AutoMigrate untuk membuat tabel secara otomatis
	err = db.AutoMigrate(
		&models.Admin{},
		&models.Voucher{},
		&models.Session{},
		&models.Log{},
	)
	
	if err != nil {
		log.Fatal("Gagal melakukan migrasi database: \n", err)
	}

	fmt.Println("Migrasi tabel selesai.")
	DB = db
}