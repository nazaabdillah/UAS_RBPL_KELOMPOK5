package models

import (
	"time"
)

// Admin merepresentasikan pengguna dashboard (KF-01)
type Admin struct {
	AdminID      uint      `gorm:"primaryKey;column:admin_id" json:"admin_id"`
	Name         string    `gorm:"type:varchar(255);not null" json:"name"`
	Username     string    `gorm:"type:varchar(100);uniqueIndex;not null" json:"username"`
	PasswordHash string    `gorm:"type:varchar(255);not null" json:"-"` // Disembunyikan dari JSON
	Role         string    `gorm:"type:varchar(20);default:'reseller'" json:"role"`
	CreatedAt    time.Time `json:"created_at"`
	Vouchers     []Voucher `gorm:"foreignKey:CreatedBy;references:AdminID"`
}

// Voucher merepresentasikan data voucher WiFi (KF-03, KF-05)
type Voucher struct {
	VoucherID       uint       `gorm:"primaryKey;column:voucher_id" json:"voucher_id"`
	Code            string     `gorm:"type:varchar(50);uniqueIndex;not null" json:"code"`
	DurationMinutes int        `gorm:"not null" json:"duration_minutes"`
	Status          string     `gorm:"type:varchar(20);default:'unused'" json:"status"`
	CreatedBy       uint       `json:"created_by"`
	CreatedAt       time.Time  `json:"created_at"`
	ActivatedAt     *time.Time `json:"activated_at"`
	ExpiredAt       *time.Time `json:"expired_at"`
	Sessions        []Session  `gorm:"foreignKey:VoucherID;references:VoucherID"`
}

// Session merepresentasikan sesi aktif pengguna di Mikrotik (KF-06)
type Session struct {
	SessionID       uint       `gorm:"primaryKey;column:session_id" json:"session_id"`
	VoucherID       uint       `json:"voucher_id"`
	UserMAC         string     `gorm:"type:varchar(17);not null" json:"user_mac"`
	UserIP          string     `gorm:"type:varchar(15)" json:"user_ip"`
	StartTime       time.Time  `json:"start_time"`
	StopTime        *time.Time `json:"stop_time"`
	Status          string     `gorm:"type:varchar(20);default:'active'" json:"status"`
	TerminateReason string     `gorm:"type:varchar(255)" json:"terminate_reason"`
}

// Log merepresentasikan riwayat sistem
type Log struct {
	LogID       uint      `gorm:"primaryKey;column:log_id" json:"log_id"`
	EventType   string    `gorm:"type:varchar(100);not null" json:"event_type"`
	VoucherID   *uint     `json:"voucher_id"`
	SessionID   *uint     `json:"session_id"`
	Description string    `gorm:"type:text" json:"description"`
	CreatedAt   time.Time `json:"created_at"`
}