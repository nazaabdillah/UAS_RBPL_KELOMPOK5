package handlers

import (
	"time"
	"wifi-voucher-management/internal/database"
	"wifi-voucher-management/internal/models"

	"github.com/gofiber/fiber/v2"
	"github.com/golang-jwt/jwt/v5"
	"golang.org/x/crypto/bcrypt"
)

// Struct untuk memvalidasi input JSON dari Frontend
type LoginRequest struct {
	Username string `json:"username"`
	Password string `json:"password"`
}

func AdminLogin(c *fiber.Ctx) error {
	req := new(LoginRequest)

	// Validasi parsing JSON
	if err := c.BodyParser(req); err != nil {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{
			"status":  "error",
			"message": "Format request tidak valid",
		})
	}

	// Cari admin berdasarkan username di database
	var admin models.Admin
	if err := database.DB.Where("username = ?", req.Username).First(&admin).Error; err != nil {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{
			"status":  "error",
			"message": "Username atau password salah",
		})
	}

	// Verifikasi hash password
	if err := bcrypt.CompareHashAndPassword([]byte(admin.PasswordHash), []byte(req.Password)); err != nil {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{
			"status":  "error",
			"message": "Username atau password salah",
		})
	}

	// Generate JWT Token
	claims := jwt.MapClaims{
		"admin_id": admin.AdminID,
		"role":     admin.Role,
		"exp":      time.Now().Add(time.Hour * 24).Unix(), // Token mati dalam 24 jam
	}
	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	
	// TODO: Pindahkan secret key ini ke .env nanti
	t, err := token.SignedString([]byte("rahasia_negara_123"))
	if err != nil {
		return c.Status(fiber.StatusInternalServerError).JSON(fiber.Map{
			"status":  "error",
			"message": "Gagal membuat token sesi",
		})
	}

	return c.Status(fiber.StatusOK).JSON(fiber.Map{
		"status":  "success",
		"message": "Login berhasil",
		"data": fiber.Map{
			"token":    t,
			"username": admin.Username,
			"role":     admin.Role,
		},
	})
}

// Fungsi sementara untuk membuat akun admin pertama kali (Seeder)
func SetupFirstAdmin(c *fiber.Ctx) error {
	hash, _ := bcrypt.GenerateFromPassword([]byte("admin123"), 10)
	admin := models.Admin{
		Name:         "Super Admin",
		Username:     "admin",
		PasswordHash: string(hash),
		Role:         "admin",
	}
	
	database.DB.Create(&admin)
	return c.JSON(fiber.Map{"message": "Akun admin berhasil dibuat (username: admin, pass: admin123)"})
}