package handlers

import (
	"fmt"
	"os"
	
	"wifi-voucher-management/internal/database"
	"wifi-voucher-management/internal/models"
	"wifi-voucher-management/pkg/utils"
	"github.com/jung-kurt/gofpdf"

	"github.com/gofiber/fiber/v2"
)

// Struct untuk memvalidasi input JSON
type GenerateVoucherReq struct {
	Qty             int  `json:"qty"`
	DurationMinutes int  `json:"duration_minutes"`
	AdminID         uint `json:"admin_id"` // Nanti otomatis dari JWT, sementara kita minta dari JSON
}

func GenerateBatchVouchers(c *fiber.Ctx) error {
	req := new(GenerateVoucherReq)

	if err := c.BodyParser(req); err != nil {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{
			"status":  "error",
			"message": "Format request tidak valid",
		})
	}

	// Validasi minimal generate
	if req.Qty <= 0 || req.DurationMinutes <= 0 {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{
			"status":  "error",
			"message": "Kuantitas dan durasi harus lebih dari 0",
		})
	}

	var generatedVouchers []models.Voucher

	// Looping pembuatan voucher
	for i := 0; i < req.Qty; i++ {
		code := utils.GenerateVoucherCode(8) // Panjang kode 8 karakter

		voucher := models.Voucher{
			Code:            code,
			DurationMinutes: req.DurationMinutes,
			Status:          "unused",
			CreatedBy:       req.AdminID,
		}

		// 1. Simpan ke PostgreSQL
		if err := database.DB.Create(&voucher).Error; err != nil {
			return c.Status(fiber.StatusInternalServerError).JSON(fiber.Map{
				"status":  "error",
				"message": "Gagal menyimpan data ke database lokal",
			})
		}

		generatedVouchers = append(generatedVouchers, voucher)

		// 2. Injeksi ke Mikrotik (Mock atau Real)
		if os.Getenv("USE_MOCK_MIKROTIK") == "true" {
			fmt.Printf("[MOCK] Mengirim kode %s (Durasi: %dm) ke Virtual Mikrotik\n", code, req.DurationMinutes)
		} else {
			// TODO: Panggil services.ConnectMikrotik()
			// client.Run(fmt.Sprintf("/ip/hotspot/user/add =name=%s =password=%s =profile=default", code, code))
		}
	}

	// Catat ke Log Sistem
	database.DB.Create(&models.Log{
		EventType:   "GENERATE_VOUCHER",
		Description: fmt.Sprintf("Admin ID %d membuat %d voucher dengan durasi %d menit", req.AdminID, req.Qty, req.DurationMinutes),
	})

	return c.Status(fiber.StatusCreated).JSON(fiber.Map{
		"status":  "success",
		"message": fmt.Sprintf("%d voucher berhasil dibuat", req.Qty),
		"data":    generatedVouchers,
	})
}

// GetVouchers mengambil semua daftar voucher dari database
func GetVouchers(c *fiber.Ctx) error {
	var vouchers []models.Voucher

	// Ambil data dari database, urutkan dari yang paling baru dibuat
	// Dalam skala produksi nyata, kita akan menambahkan pagination (Limit & Offset) di sini
	if err := database.DB.Order("created_at desc").Find(&vouchers).Error; err != nil {
		return c.Status(fiber.StatusInternalServerError).JSON(fiber.Map{
			"status":  "error",
			"message": "Gagal mengambil data voucher dari database",
		})
	}

	return c.Status(fiber.StatusOK).JSON(fiber.Map{
		"status": "success",
		"data":   vouchers,
	})
}

// GetDashboardStats mengambil agregasi data untuk halaman utama Dashboard Admin
func GetDashboardStats(c *fiber.Ctx) error {
	var total, unused, active, expired int64

	// Menghitung agregasi menggunakan GORM
	database.DB.Model(&models.Voucher{}).Count(&total)
	database.DB.Model(&models.Voucher{}).Where("status = ?", "unused").Count(&unused)
	database.DB.Model(&models.Voucher{}).Where("status = ?", "active").Count(&active)
	database.DB.Model(&models.Voucher{}).Where("status = ?", "expired").Count(&expired)

	return c.Status(fiber.StatusOK).JSON(fiber.Map{
		"status": "success",
		"data": fiber.Map{
			"total_vouchers": total,
			"unused":         unused,
			"active":         active,
			"expired":        expired,
		},
	})
}

// DeleteVoucher menghapus voucher berdasarkan ID
func DeleteVoucher(c *fiber.Ctx) error {
	// Mengambil parameter ID dari URL (misal: /admin/vouchers/5)
	voucherID := c.Params("id")

	var voucher models.Voucher
	
	// Cek apakah data ada
	if err := database.DB.First(&voucher, voucherID).Error; err != nil {
		return c.Status(fiber.StatusNotFound).JSON(fiber.Map{
			"status":  "error",
			"message": "Voucher tidak ditemukan",
		})
	}

	// Lakukan penghapusan (Soft Delete atau Hard Delete tergantung setting GORM, 
	// namun default-nya jika tidak ada DeletedAt adalah Hard Delete).
	if err := database.DB.Delete(&voucher).Error; err != nil {
		return c.Status(fiber.StatusInternalServerError).JSON(fiber.Map{
			"status":  "error",
			"message": "Gagal menghapus voucher",
		})
	}

	return c.Status(fiber.StatusOK).JSON(fiber.Map{
		"status":  "success",
		"message": "Voucher berhasil dihapus",
	})
}

// ExportVouchersPDF menghasilkan file PDF biner langsung ke browser
func ExportVouchersPDF(c *fiber.Ctx) error {
	var vouchers []models.Voucher
	// Ambil data voucher yang belum terpakai
	database.DB.Where("status = ?", "unused").Limit(12).Find(&vouchers)

	// Inisialisasi PDF: Portrait, Unit Milimeter, Ukuran A4
	pdf := gofpdf.New("P", "mm", "A4", "")
	pdf.AddPage()
	pdf.SetFont("Arial", "B", 16)

	// Header Laporan
	pdf.Cell(190, 10, "DAFTAR VOUCHER WIFI - WARKOP FAHAR")
	pdf.Ln(12) // Line break

	// Setting Grid Tiket
	col := 0
	xBase := 10.0
	yBase := 30.0
	width := 60.0
	height := 30.0

	pdf.SetFont("Courier", "B", 12)

	for i, v := range vouchers {
		// Hitung posisi grid (3 kolom)
		x := xBase + float64(col)*width
		y := yBase + float64(i/3)*(height+5)

		// Gambar Kotak Voucher
		pdf.Rect(x, y, width, height, "D")
		
		// Isi Teks Voucher
		pdf.SetXY(x+2, y+5)
		pdf.SetFont("Arial", "", 8)
		pdf.Cell(width, 5, "KODE AKSES:")
		
		pdf.SetXY(x+2, y+12)
		pdf.SetFont("Courier", "B", 14)
		pdf.Cell(width, 10, v.Code)
		
		pdf.SetXY(x+2, y+22)
		pdf.SetFont("Arial", "I", 8)
		pdf.Cell(width, 5, fmt.Sprintf("Durasi: %d Menit", v.DurationMinutes))

		col++
		if col > 2 {
			col = 0
		}
	}

	// Output: Alirkan biner PDF langsung ke respons HTTP
	c.Set("Content-Type", "application/pdf")
	c.Set("Content-Disposition", "attachment; filename=vouchers.pdf")
	
	return pdf.Output(c.Response().BodyWriter())
}