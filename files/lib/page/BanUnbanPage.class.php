<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use PDO;

class BanUnbanPage extends AbstractPage {
    public $templateName = 'banunban';
    public string $error = '';
    public string $success = '';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canUnban')) {
            throw new \wcf\system\exception\PermissionDeniedException();
        }

        $banId = (int)($_GET['id'] ?? 0);
        $adminId = WCF::getUser()->userID;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = $this->connect();

                // ✅ Bannstatus ändern
                $stmt = $pdo->prepare("UPDATE sa_bans SET status = 'UNBANNED' WHERE id = ?");
                $stmt->execute([$banId]);

                // ✅ Eintrag in sa_unbans setzen
                $stmt = $pdo->prepare("
                    INSERT INTO sa_unbans (ban_id, admin_id, reason, date)
                    VALUES (?, ?, 'Entbannt über Forum', NOW())
                ");
                $stmt->execute([$banId, $adminId]);

                $this->success = "✅ Spieler erfolgreich entbannt!";
            } catch (\Throwable $e) {
                $this->error = "Fehler: ".$e->getMessage();
            }
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'error' => $this->error,
            'success' => $this->success
        ]);
    }

    private function connect(): PDO {
        $cfg = require WCF_DIR.'config/cs2bannliste.php';
        return new PDO(
            "mysql:host={$cfg['hostname']};dbname={$cfg['database']};charset=utf8mb4",
            $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
