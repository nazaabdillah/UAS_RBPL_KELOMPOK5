package handlers

import (
	"fmt"
	"os"
	
	"wifi-voucher-management/internal/database"
	"wifi-voucher-management/internal/models"
	"wifi-voucher-management/pkg/utils"

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