package utils

import (
	"math/rand"
	"time"
)

const charset = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789" // Dihilangkan O, 0, 1, I agar tidak ambigu saat dicetak

// GenerateVoucherCode membuat string acak sepanjang n karakter
func GenerateVoucherCode(length int) string {
	// Seed randomizer agar benar-benar acak setiap kali dieksekusi
	var seededRand *rand.Rand = rand.New(rand.NewSource(time.Now().UnixNano()))
	
	b := make([]byte, length)
	for i := range b {
		b[i] = charset[seededRand.Intn(len(charset))]
	}
	return string(b)
}