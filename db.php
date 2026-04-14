<?php

// ── Credentials: env vars (Vercel/FreeDDB) with local fallbacks (XAMPP) ──
$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname   = getenv('DB_NAME')     ?: 'evaluation_system';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed");
}

// ── Database-backed session handler (required for Vercel serverless) ──
// On traditional hosts (XAMPP, InfinityFree) this also works fine.
if (!class_exists('DbSessionHandler')) {
    class DbSessionHandler implements SessionHandlerInterface {
        private mysqli $db;
        public function __construct(mysqli $db) { $this->db = $db; }
        public function open(string $path, string $name): bool { return true; }
        public function close(): bool { return true; }
        public function read(string $id): string|false {
            $stmt = @$this->db->prepare(
                "SELECT data FROM sessions WHERE id = ? AND expires > NOW()"
            );
            if (!$stmt) return '';
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? $row['data'] : '';
        }
        public function write(string $id, string $data): bool {
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $stmt = @$this->db->prepare(
                "REPLACE INTO sessions (id, data, expires) VALUES (?, ?, ?)"
            );
            if (!$stmt) return false;
            $stmt->bind_param('sss', $id, $data, $expires);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        public function destroy(string $id): bool {
            $stmt = @$this->db->prepare("DELETE FROM sessions WHERE id = ?");
            if (!$stmt) return false;
            $stmt->bind_param('s', $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        }
        public function gc(int $maxlifetime): int|false {
            $this->db->query("DELETE FROM sessions WHERE expires < NOW()");
            return max(0, $this->db->affected_rows);
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    $handler = new DbSessionHandler($conn);
    session_set_save_handler($handler, true);
    session_start();
}
?>