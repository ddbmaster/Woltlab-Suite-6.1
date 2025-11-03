<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\data\user\UserProfile;
use PDO;

class BanAddPage extends AbstractPage {
    public $templateName = 'banadd';
    public string $error = '';
    public string $success = '';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canAdd')) {
            throw new \wcf\system\exception\PermissionDeniedException();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $admin_name = WCF::getUser()->username;
            $admin_steamid = $this->resolveAdminSteamId();

            if (!$admin_steamid) {
                $this->error = "⚠ Deine Steam-ID fehlt im Profil (Profilfeld ID 30).";
                return;
            }

            try {
                $pdo = $this->connect();
                $cfg = $this->getConfig();
                $table = ($cfg['prefix'] ?? '') . 'sa_bans';

                $stmt = $pdo->prepare("
                    INSERT INTO {$table} 
                    (player_name, player_steamid, reason, duration, admin_name, admin_steamid, created) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $_POST['player_name'],
                    $_POST['player_steamid'],
                    $_POST['reason'],
                    $_POST['duration'],
                    $admin_name,
                    $admin_steamid
                ]);

                $this->success = "✅ Bann wurde gespeichert.";
            } catch (\Throwable $e) {
                $this->error = $e->getMessage();
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

    private function resolveAdminSteamId(): ?string {
        $user = WCF::getUser();
        $userID = (int)$user->userID;

        try {
            $profile = new UserProfile($user);
            $val = $profile->getUserOptionValue('steamID64');
            if (!empty($val)) return (string)$val;
        } catch (\Throwable $e) {}

        try {
            $stmt = WCF::getDB()->prepare("SELECT userOption30 FROM wcf1_user_option_value WHERE userID = ?");
            $stmt->execute([$userID]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($row['userOption30'])) return (string)$row['userOption30'];
        } catch (\Throwable $e) {}

        return null;
    }

    private function getConfig(): array {
        return require WCF_DIR . 'config/cs2bannliste.php';
    }

    private function connect(): PDO {
        $cfg = $this->getConfig();
        return new PDO(
            "mysql:host={$cfg['hostname']};dbname={$cfg['database']};port={$cfg['port']};charset=utf8mb4",
            $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
