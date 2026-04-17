package main

import (
	"log"
	"os"

	"wifi-voucher-management/internal/database"

	"github.com/gofiber/fiber/v2"
	"github.com/gofiber/fiber/v2/middleware/cors"
	"github.com/gofiber/fiber/v2/middleware/logger"
	"github.com/joho/godotenv"
	"wifi-voucher-management/internal/routes"
)

func main() {
	// 1. Muat variabel environment
	if err := godotenv.Load(); err != nil {
		log.Println("Peringatan: File .env tidak ditemukan, menggunakan environment system")
	}

	// 2. Inisialisasi Koneksi Database
	database.ConnectDB()

	// 3. Inisialisasi Fiber App
	app := fiber.New()

	// 4. Middleware Global
	app.Use(logger.New()) // Mencatat setiap request ke terminal (berguna saat debug)
	app.Use(cors.New(cors.Config{
		AllowOrigins: "*", // Izinkan frontend lokal mengakses API
		AllowHeaders: "Origin, Content-Type, Accept, Authorization",
	}))

	

	// Endpoint uji coba awal
	app.Get("/api/health", func(c *fiber.Ctx) error {
		return c.Status(200).JSON(fiber.Map{
			"status":  "success",
			"message": "Sistem API Backend aktif dan berjalan",
		})
	})

	// 5. Jalankan Server
	port := os.Getenv("PORT")
	if port == "" {
		port = "3000"
	}
	routes.SetupRoutes(app)
	log.Fatal(app.Listen(":" + port))
}