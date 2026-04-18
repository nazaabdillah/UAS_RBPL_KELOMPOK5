package middleware

import (
	"strings"

	"github.com/gofiber/fiber/v2"
	"github.com/golang-jwt/jwt/v5"
)

// Protected adalah middleware untuk memvalidasi Token JWT
func Protected() fiber.Handler {
	return func(c *fiber.Ctx) error {
		authHeader := c.Get("Authorization")

		// Cek apakah header Authorization ada dan formatnya benar (Bearer <token>)
		if authHeader == "" || !strings.HasPrefix(authHeader, "Bearer ") {
			return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{
				"status":  "error",
				"message": "Akses ditolak. Token tidak ditemukan di header.",
			})
		}

		// Ekstrak token
		tokenString := strings.TrimPrefix(authHeader, "Bearer ")

		// Validasi token
		token, err := jwt.Parse(tokenString, func(token *jwt.Token) (interface{}, error) {
			// Ingat: Sesuaikan dengan secret key yang ada di auth.go (Nanti kita pindah ke .env)
			return []byte("rahasia_negara_123"), nil
		})

		if err != nil || !token.Valid {
			return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{
				"status":  "error",
				"message": "Sesi tidak valid atau telah kedaluwarsa. Silakan login kembali.",
			})
		}

		// Jika token valid, ekstrak data admin_id dan simpan ke context (opsional untuk audit trail)
		claims := token.Claims.(jwt.MapClaims)
		c.Locals("admin_id", claims["admin_id"])

		// Lanjutkan ke handler utama (misal: GenerateVoucher atau GetVouchers)
		return c.Next()
	}
}