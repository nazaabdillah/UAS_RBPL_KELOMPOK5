package routes

import (
	"wifi-voucher-management/internal/handlers"
	"github.com/gofiber/fiber/v2"
)

func SetupRoutes(app *fiber.App) {
	api := app.Group("/api/v1")

	// Endpoint Auth
	auth := api.Group("/auth")
	auth.Post("/login", handlers.AdminLogin)

	portal := api.Group("/portal")
	portal.Post("/login", handlers.PortalLogin)

	admin := api.Group("/admin")
	admin.Get("/mikrotik/active", handlers.GetMikrotikActiveUsers)

	// Endpoint Manajemen Voucher
	admin.Post("/vouchers/generate", handlers.GenerateBatchVouchers)

	// Endpoint sementara untuk inject admin (hapus saat rilis)
	api.Post("/setup-admin", handlers.SetupFirstAdmin)
}