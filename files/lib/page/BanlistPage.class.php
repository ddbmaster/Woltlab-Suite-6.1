<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;
use Throwable;

class BanlistPage extends AbstractPage {
    public $templateName = 'banlist';
    public array $bans = [];
    public string $error = '';

    public function readData() {
        parent::readData();

        // ✅ Rechteprüfung (WSC 6.1)
        if (!WCF::getSession()->getPermission('user.cs2bannliste.canView')) {
            throw new PermissionDeniedException();
        }

        try {
            $pdo = $this->connect();
            $cfg = $this->getConfig();
            $table = ($cfg['prefix'] ?? '') . 'sa_bans';

            $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY created DESC LIMIT 200");
            $this->bans = $stmt->fetchAll();
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'bans' => $this->bans,
            'error' => $this->error,

            // 🔐 Rechte an Template übergeben
            'canAdd'  => WCF::getSession()->getPermission('user.cs2bannliste.canAdd'),
            'canEdit' => WCF::getSession()->getPermission('user.cs2bannliste.canEdit'),
            'canUnban'=> WCF::getSession()->getPermission('user.cs2bannliste.canUnban'),

        ]);
    }

    protected function getConfig(): array {
        $file = WCF_DIR . 'config/cs2bannliste.php';
        if (!file_exists($file)) {
            throw new \RuntimeException('Konfigurationsdatei fehlt: ' . $file);
        }
        return require $file;
    }

    protected function connect(): PDO {
        $cfg = $this->getConfig();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['hostname'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset'] ?? 'utf8mb4'
        );

        return new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}

