<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\data\user\UserProfile;
use PDO;

class BanEditPage extends AbstractPage {
    public $templateName = 'banedit';
    public string $error = '';
    public string $success = '';
    public array $ban = [];

    // Deine CS2 Datenbank
    private string $host = 'localhost';
    private string $db   = 'xxxxx';
    private string $user = 'xxxxxx';
    private string $pass = 'xxxxxxxxxx';
    private string $table = 'sa_bans';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canEdit')) {
            throw new \wcf\system\exception\PermissionDeniedException();
        }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->error = "Ungültige Bann-ID.";
            return;
        }

        try {
            $pdo = $this->connect();

            // Bann laden
            $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $this->ban = $stmt->fetch();

            if (!$this->ban) {
                $this->error = "Bann nicht gefunden.";
                return;
            }

            // Speichern
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $admin_name = WCF::getUser()->username;
                $admin_steamid = $this->resolveAdminSteamId();

                if (!$admin_steamid) {
                    $this->error = "⚠ Keine Steam-ID im Profil eingetragen (Profilfeld 30).";
                    return;
                }

                // Wenn vorher UNBANNED → zurück auf ACTIVE
                $newStatus = ($this->ban['status'] === 'UNBANNED') ? 'ACTIVE' : $this->ban['status'];

                $stmt = $pdo->prepare("
                    UPDATE {$this->table}
                    SET player_name=?, player_steamid=?, reason=?, duration=?,
                        admin_name=?, admin_steamid=?, status=?
                    WHERE id=?
                ");

                $stmt->execute([
                    $_POST['player_name'],
                    $_POST['player_steamid'],
                    $_POST['reason'],
                    $_POST['duration'],
                    $admin_name,
                    $admin_steamid,
                    $newStatus,
                    $id
                ]);

                $this->success = "✅ Bann aktualisiert.";

                // Neu laden
                $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
                $stmt->execute([$id]);
                $this->ban = $stmt->fetch();
            }

        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'ban'     => $this->ban,
            'error'   => $this->error,
            'success' => $this->success
        ]);
    }

    private function resolveAdminSteamId(): ?string {
        $user = WCF::getUser();
        $userID = (int)$user->userID;

        // Profilfeld 30
        try {
            $stmt = WCF::getDB()->prepareStatement("
                SELECT userOption30 FROM wcf1_user_option_value WHERE userID = ?
            ");
            $stmt->execute([$userID]);
            $row = $stmt->fetchArray();

            if (!empty($row['userOption30'])) {
                return (string)$row['userOption30'];
            }
        } catch (\Throwable $e) {}

        return null;
    }


    private function connect(): PDO {
        return new PDO(
            "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
            $this->user, $this->pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
