<?php
/**
 * mikrotik/RouterosAPI.php
 * 
 * PHP Class untuk komunikasi dengan MikroTik RouterOS API
 * Based on official MikroTik API protocol
 * 
 * Cara pakai:
 *   $api = new RouterosAPI('192.168.1.1', 'admin', 'password');
 *   $api->connect();
 *   $users = $api->query('/ip/hotspot/user/print');
 */

class RouterosAPI {
    private string $host;
    private string $username;
    private string $password;
    private int    $port;
    private int    $timeout;

    /** @var resource|false Socket koneksi */
    private $socket = false;

    private bool   $connected = false;
    private string $lastError = '';

    public function __construct(
        string $host,
        string $username,
        string $password,
        int    $port    = 8728,
        int    $timeout = 10
    ) {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port     = $port;
        $this->timeout  = $timeout;
    }

    // =========================================================
    // Koneksi & Login
    // =========================================================

    /**
     * Membuka koneksi socket dan login ke MikroTik
     */
    public function connect(): bool {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if ($this->socket === false) {
            $this->lastError = "Cannot connect to {$this->host}:{$this->port} — $errstr ($errno)";
            return false;
        }

        stream_set_timeout($this->socket, $this->timeout);
        return $this->login();
    }

    /**
     * Menutup koneksi socket
     */
    public function disconnect(): void {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket    = false;
            $this->connected = false;
        }
    }

    public function isConnected(): bool {
        return $this->connected;
    }

    public function getLastError(): string {
        return $this->lastError;
    }

    // =========================================================
    // Query & Command
    // =========================================================

    /**
     * Mengirim command ke MikroTik dan mengembalikan hasilnya
     *
     * @param  string $command  Path API, misal '/ip/hotspot/user/print'
     * @param  array  $params   Parameter tambahan, misal ['=name=voucher1']
     * @return array|false      Array hasil atau false jika gagal
     */
    public function query(string $command, array $params = []): array|false {
        if (!$this->connected) {
            $this->lastError = 'Not connected to router.';
            return false;
        }

        // Kirim command ke router
        $this->writeWord($command);
        foreach ($params as $param) {
            $this->writeWord($param);
        }
        $this->writeWord(''); // Empty word = end of sentence

        // Baca respons
        return $this->readResponse();
    }

    // =========================================================
    // Hotspot User Management (High-level helpers)
    // =========================================================

    /**
     * Menambahkan user hotspot baru ke MikroTik
     */
    public function addHotspotUser(string $username, string $password, string $profile, string $comment = ''): bool {
        $params = [
            "=name=$username",
            "=password=$password",
            "=profile=$profile",
        ];
        if ($comment) {
            $params[] = "=comment=$comment";
        }

        $result = $this->query('/ip/hotspot/user/add', $params);

        if ($result === false) return false;

        // Cek apakah ada error dalam respons
        foreach ($result as $row) {
            if (isset($row['type']) && $row['type'] === '!trap') {
                $this->lastError = $row['message'] ?? 'Unknown API error';
                return false;
            }
        }

        return true;
    }

    /**
     * Menghapus user hotspot dari MikroTik berdasarkan username
     */
    public function removeHotspotUser(string $username): bool {
        // Cari .id user terlebih dahulu
        $result = $this->query('/ip/hotspot/user/print', ["?name=$username"]);

        if ($result === false || empty($result)) {
            $this->lastError = "User '$username' not found in MikroTik.";
            return false;
        }

        // Filter hanya baris data (bukan !done atau !trap)
        $users = array_filter($result, fn($r) => isset($r['.id']));

        if (empty($users)) {
            $this->lastError = "User '$username' not found.";
            return false;
        }

        $user = reset($users);
        $id   = $user['.id'];

        $removeResult = $this->query('/ip/hotspot/user/remove', ["=.id=$id"]);

        if ($removeResult === false) return false;

        foreach ($removeResult as $row) {
            if (isset($row['type']) && $row['type'] === '!trap') {
                $this->lastError = $row['message'] ?? 'Remove failed';
                return false;
            }
        }

        return true;
    }

    /**
     * Mengambil semua user hotspot dari MikroTik
     */
    public function getHotspotUsers(): array {
        $result = $this->query('/ip/hotspot/user/print');
        if ($result === false) return [];

        return array_filter($result, fn($r) => isset($r['name']));
    }

    /**
     * Cek apakah user sedang aktif (terkoneksi) di MikroTik
     */
    public function isUserActive(string $username): bool {
        $result = $this->query('/ip/hotspot/active/print', ["?user=$username"]);
        if ($result === false) return false;

        $actives = array_filter($result, fn($r) => isset($r['user']));
        return !empty($actives);
    }

    /**
     * Mengambil daftar profile hotspot dari MikroTik
     */
    public function getProfiles(): array {
        $result = $this->query('/ip/hotspot/user/profile/print');
        if ($result === false) return [];

        return array_filter($result, fn($r) => isset($r['name']));
    }

    // =========================================================
    // Protocol Implementation (Low-level)
    // =========================================================

    private function login(): bool {
        // Kirim /login command
        $this->writeWord('/login');
        $this->writeWord("=name={$this->username}");
        $this->writeWord("=password={$this->password}");
        $this->writeWord('');

        $response = $this->readResponse();

        if ($response === false) {
            $this->lastError = 'Login failed: no response.';
            return false;
        }

        // Periksa hasil login
        foreach ($response as $row) {
            if (isset($row['type'])) {
                if ($row['type'] === '!done') {
                    $this->connected = true;
                    return true;
                }
                if ($row['type'] === '!trap') {
                    $this->lastError = 'Login failed: ' . ($row['message'] ?? 'Invalid credentials');
                    return false;
                }
            }
        }

        // Coba format login lama (RouterOS < 6.43)
        foreach ($response as $row) {
            if (isset($row['ret'])) {
                $challenge = pack('H*', $row['ret']);
                $encoded   = '00' . md5("\x00{$this->password}{$challenge}");

                $this->writeWord('/login');
                $this->writeWord("=name={$this->username}");
                $this->writeWord("=response=$encoded");
                $this->writeWord('');

                $resp2 = $this->readResponse();
                foreach ($resp2 as $r2) {
                    if (isset($r2['type']) && $r2['type'] === '!done') {
                        $this->connected = true;
                        return true;
                    }
                }
                $this->lastError = 'Login challenge failed.';
                return false;
            }
        }

        $this->connected = true; // RouterOS 6.43+ langsung done
        return true;
    }

    /**
     * Menulis satu word ke socket dengan length encoding
     */
    private function writeWord(string $word): void {
        $len = strlen($word);
        fwrite($this->socket, $this->encodeLength($len) . $word);
    }

    /**
     * Membaca semua respons hingga !done atau !trap
     * Fix: tambah guard EOF/timeout agar tidak infinite loop
     */
    private function readResponse(): array {
        $result  = [];
        $current = [];

        while (true) {
            // Guard: cek apakah socket masih valid sebelum baca
            if (!is_resource($this->socket) && !($this->socket instanceof \Socket)) {
                $this->lastError = 'Socket terputus saat membaca respons.';
                break;
            }

            $meta = stream_get_meta_data($this->socket);
            if ($meta['timed_out'] || $meta['eof']) {
                $this->lastError = $meta['timed_out']
                    ? 'Timeout membaca respons dari MikroTik.'
                    : 'Koneksi ditutup oleh router (EOF).';
                break;
            }

            $word = $this->readWord();

            // null = error saat baca (socket putus/timeout)
            if ($word === null) {
                break;
            }

            if ($word === '') {
                // Empty word = end of sentence dalam protokol MikroTik
                if (!empty($current)) {
                    $result[] = $current;
                    $current  = [];
                }
                continue;
            }

            if ($word === '!done') {
                if (!empty($current)) {
                    $result[] = $current;
                }
                $result[] = ['type' => '!done'];
                break;
            }

            if ($word === '!trap' || $word === '!fatal') {
                if (!empty($current)) {
                    $result[] = $current;
                    $current  = [];
                }
                $current['type'] = $word;
                continue;
            }

            if ($word === '!re') {
                if (!empty($current)) {
                    $result[] = $current;
                    $current  = [];
                }
                continue;
            }

            // Parse key=value pairs (format: =key=value atau =.id=*1)
            if (str_starts_with($word, '=')) {
                $parts = explode('=', substr($word, 1), 2);
                if (count($parts) === 2) {
                    $current[$parts[0]] = $parts[1];
                }
            }
        }

        return $result;
    }

    /**
     * Membaca satu word dari socket
     * Fix: return null jika socket error/EOF, bukan string kosong
     * (agar readResponse() bisa membedakan "end of sentence" vs "error")
     */
    private function readWord(): ?string {
        $len = $this->decodeLength();

        // null = error decode (socket putus/EOF)
        if ($len === null) return null;

        // 0 = end of sentence (valid dalam protokol)
        if ($len === 0) return '';

        $word  = '';
        $remaining = $len;

        while ($remaining > 0) {
            // Cek stream sebelum baca
            $meta = stream_get_meta_data($this->socket);
            if ($meta['timed_out'] || $meta['eof']) {
                $this->lastError = 'Stream timeout/EOF saat membaca data.';
                return null;
            }

            $chunk = fread($this->socket, $remaining);
            if ($chunk === false || $chunk === '') {
                $this->lastError = 'Gagal membaca data dari socket.';
                return null;
            }
            $word      .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $word;
    }

    /**
     * Encode panjang word sesuai protokol MikroTik
     */
    private function encodeLength(int $len): string {
        if ($len < 0x80) {
            return chr($len);
        } elseif ($len < 0x4000) {
            $len |= 0x8000;
            return chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        } elseif ($len < 0x200000) {
            $len |= 0xC00000;
            return chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
        } elseif ($len < 0x10000000) {
            $len |= 0xE0000000;
            return chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF)
                 . chr(($len >> 8) & 0xFF)  . chr($len & 0xFF);
        }
        return chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF)
                         . chr(($len >> 8) & 0xFF)  . chr($len & 0xFF);
    }

    /**
     * Decode panjang word sesuai protokol MikroTik
     * Fix: return null (bukan int) saat fread() gagal — mencegah TypeError PHP 8+
     *      karena ord(false) throw TypeError di PHP 8.1+
     */
    private function decodeLength(): ?int {
        $raw = fread($this->socket, 1);

        // fread() return false atau '' saat socket EOF/error
        if ($raw === false || $raw === '') {
            $this->lastError = 'Tidak dapat membaca panjang word dari socket (EOF/error).';
            return null;
        }

        $byte = ord($raw);

        if (($byte & 0x80) === 0x00) {
            return $byte;
        }

        if (($byte & 0xC0) === 0x80) {
            $b2 = fread($this->socket, 1);
            if ($b2 === false || $b2 === '') return null;
            $byte &= ~0x80;
            return ($byte << 8) | ord($b2);
        }

        if (($byte & 0xE0) === 0xC0) {
            $b2 = fread($this->socket, 1);
            $b3 = fread($this->socket, 1);
            if ($b2 === false || $b2 === '' || $b3 === false || $b3 === '') return null;
            $byte &= ~0xC0;
            return ($byte << 16) | (ord($b2) << 8) | ord($b3);
        }

        if (($byte & 0xF0) === 0xE0) {
            $b2 = fread($this->socket, 1);
            $b3 = fread($this->socket, 1);
            $b4 = fread($this->socket, 1);
            if ($b2 === false || $b2 === '' || $b3 === false || $b3 === '' || $b4 === false || $b4 === '') return null;
            $byte &= ~0xE0;
            return ($byte << 24) | (ord($b2) << 16) | (ord($b3) << 8) | ord($b4);
        }

        // 0xF0 — baca 4 byte berikutnya (word sangat panjang, jarang terjadi)
        $skip = fread($this->socket, 1); // skip extra byte
        $b1   = fread($this->socket, 1);
        $b2   = fread($this->socket, 1);
        $b3   = fread($this->socket, 1);
        $b4   = fread($this->socket, 1);
        if ($b1 === false || $b1 === '' || $b2 === false || $b2 === ''
            || $b3 === false || $b3 === '' || $b4 === false || $b4 === '') {
            return null;
        }
        return (ord($b1) << 24) | (ord($b2) << 16) | (ord($b3) << 8) | ord($b4);
    }

    /**
     * Destructor — pastikan socket selalu ditutup
     */
    public function __destruct() {
        $this->disconnect();
    }
}
