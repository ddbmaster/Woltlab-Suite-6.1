<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;
use Throwable;

class BanAddPage extends AbstractPage {
    public $templateName = 'banadd';
    public string $error = '';
    public string $success = '';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canAdd')) {
            throw new PermissionDeniedException();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = $this->connect();
                $cfg = $this->getConfig();
                $table = ($cfg['prefix'] ?? '') . 'sa_bans';

                $playerName = trim($_POST['player_name'] ?? '');
                $steamID    = trim($_POST['player_steamid'] ?? '');
                $reason     = trim($_POST['reason'] ?? '');
                $duration   = trim($_POST['duration'] ?? '');
                $adminName  = WCF::getUser()->username ?: 'System';

                if ($playerName === '' || $steamID === '' || $reason === '') {
                    $this->error = 'Bitte füllen Sie alle Pflichtfelder aus.';
                    return;
                }

                $stmt = $pdo->prepare("
                    INSERT INTO {$table} (player_name, player_steamid, reason, admin_name, created, duration, status)
                    VALUES (?, ?, ?, ?, NOW(), ?, 'Active')
                ");
                $stmt->execute([$playerName, $steamID, $reason, $adminName, $duration]);

                $this->success = 'Der Bann wurde erfolgreich hinzugefügt.';
            } catch (Throwable $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'error' => $this->error,
            'success' => $this->success,
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
