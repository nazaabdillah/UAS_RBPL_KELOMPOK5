package routes

import (
	"wifi-voucher-management/internal/handlers"
	"wifi-voucher-management/internal/middleware"

	"github.com/gofiber/fiber/v2"
)

func SetupRoutes(app *fiber.App) {
	// Root Group untuk API versi 1
	api := app.Group("/api/v1")

	// ==========================================
	// 1. PUBLIC ROUTES (Bisa diakses tanpa Token)
	// ==========================================

	// Grup Autentikasi Admin (Untuk mendapatkan Token JWT)
	auth := api.Group("/auth")
	auth.Post("/login", handlers.AdminLogin)

	// Grup Captive Portal (Untuk pelanggan WiFi/End-User)
	portal := api.Group("/portal")
	portal.Post("/login", handlers.PortalLogin)

	// Endpoint Setup Awal (Hanya untuk inisialisasi akun pertama kali)
	api.Post("/setup-admin", handlers.SetupFirstAdmin)

	// ==========================================
	// 2. PROTECTED ROUTES (Wajib bawa Token JWT)
	// ==========================================

	// Middleware Protected() dipasang di sini sebagai 'Gatekeeper'.
	// Semua rute di dalam grup 'admin' akan otomatis diperiksa Tokennya.
	admin := api.Group("/admin", middleware.Protected())

	// Sub-Grup: Manajemen Mikrotik
	admin.Get("/mikrotik/active", handlers.GetMikrotikActiveUsers)

	// Sub-Grup: Manajemen Voucher
	admin.Get("/vouchers", handlers.GetVouchers)                     // Lihat list voucher
	admin.Post("/vouchers/generate", handlers.GenerateBatchVouchers) // Buat voucher baru
	// Tambahkan di dalam grup admin:
	admin.Get("/stats", handlers.GetDashboardStats)          // Endpoint Statistik Dashboard
	admin.Delete("/vouchers/:id", handlers.DeleteVoucher)    // Endpoint Hapus Voucher

	admin.Get("/vouchers/export-pdf", handlers.ExportVouchersPDF)
}