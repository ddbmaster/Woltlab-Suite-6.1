<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;
use Throwable;

class BanEditPage extends AbstractPage {
    public $templateName = 'banedit';
    public int $banID = 0;
    public array $ban = [];
    public string $error = '';
    public string $success = '';

    public function readParameters() {
        parent::readParameters();
        if (isset($_GET['id'])) $this->banID = (int)$_GET['id'];
    }

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canEdit')) {
            throw new PermissionDeniedException();
        }

        try {
            $pdo = $this->connect();
            $cfg = $this->getConfig();
            $table = ($cfg['prefix'] ?? '') . 'sa_bans';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $reason   = trim($_POST['reason'] ?? '');
                $duration = trim($_POST['duration'] ?? '');

                $stmt = $pdo->prepare("UPDATE {$table} SET reason = ?, duration = ? WHERE id = ?");
                $stmt->execute([$reason, $duration, $this->banID]);

                $this->success = 'Der Bann wurde aktualisiert.';
            }

            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
            $stmt->execute([$this->banID]);
            $this->ban = $stmt->fetch() ?: [];
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'ban' => $this->ban,
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
