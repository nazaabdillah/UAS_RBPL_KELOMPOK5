package handlers

import (
	"wifi-voucher-management/internal/services"

	"github.com/gofiber/fiber/v2"
)

// GetMikrotikActiveUsers adalah endpoint admin untuk mengecek status router
func GetMikrotikActiveUsers(c *fiber.Ctx) error {
	users, err := services.GetActiveHotspotUsers()
	if err != nil {
		return c.Status(fiber.StatusInternalServerError).JSON(fiber.Map{
			"status":  "error",
			"message": "Gagal menghubungi RouterOS Mikrotik",
			"error":   err.Error(),
		})
	}

	return c.Status(fiber.StatusOK).JSON(fiber.Map{
		"status":  "success",
		"message": "Data berhasil diambil dari Mikrotik",
		"data":    users,
	})
}