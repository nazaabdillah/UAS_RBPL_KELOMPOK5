package handlers

import (
	"time"
	"wifi-voucher-management/internal/database"
	"wifi-voucher-management/internal/models"

	"github.com/gofiber/fiber/v2"
)

// Struct sesuai dengan API Contract yang kita berikan ke Yoga
type PortalLoginReq struct {
	VoucherCode string `json:"voucher_code"`
	MACAddress  string `json:"mac_address"`
}

func PortalLogin(c *fiber.Ctx) error {
	req := new(PortalLoginReq)

	if err := c.BodyParser(req); err != nil {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{
			"status":  "error",
			"message": "Format request tidak valid",
		})
	}

	// 1. Cari voucher di database
	var voucher models.Voucher
	if err := database.DB.Where("code = ?", req.VoucherCode).First(&voucher).Error; err != nil {
		return c.Status(fiber.StatusNotFound).JSON(fiber.Map{
			"status":  "error",
			"message": "Kode voucher tidak valid atau tidak ditemukan.",
		})
	}

	now := time.Now()

	// 2. Logika Status Voucher
	if voucher.Status == "expired" {
		return c.Status(fiber.StatusForbidden).JSON(fiber.Map{
			"status":  "error",
			"message": "Kode voucher sudah kedaluwarsa.",
		})
	}

	// Jika voucher belum pernah dipakai, aktifkan sekarang
	if voucher.Status == "unused" {
		expiredTime := now.Add(time.Duration(voucher.DurationMinutes) * time.Minute)
		voucher.Status = "active"
		voucher.ActivatedAt = &now
		voucher.ExpiredAt = &expiredTime

		// Update database
		database.DB.Save(&voucher)
	} else if voucher.Status == "active" {
		// Jika statusnya active, cek apakah sudah melewati batas waktu
		if now.After(*voucher.ExpiredAt) {
			voucher.Status = "expired"
			database.DB.Save(&voucher)
			return c.Status(fiber.StatusForbidden).JSON(fiber.Map{
				"status":  "error",
				"message": "Sesi voucher Anda telah habis.",
			})
		}
		
		// TODO: Tambahkan logika validasi MAC Address di sini untuk mencegah multi-login
		// Jika req.MACAddress != MAC Address di sesi sebelumnya -> Tolak.
	}

	// 3. Catat Sesi (Session) Pengguna
	session := models.Session{
		VoucherID: voucher.VoucherID,
		UserMAC:   req.MACAddress,
		UserIP:    c.IP(), // Ambil IP dari request
		StartTime: now,
		Status:    "active",
	}
	database.DB.Create(&session)

	// 4. Kembalikan response sesuai API Contract
	return c.Status(fiber.StatusOK).JSON(fiber.Map{
		"status":  "success",
		"message": "Autentikasi berhasil, sesi dimulai.",
		"data": fiber.Map{
			"session_id":       session.SessionID,
			"duration_minutes": voucher.DurationMinutes,
			"expired_at":       voucher.ExpiredAt,
		},
	})
}