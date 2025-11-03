<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;
use Throwable;

class BanUnbanPage extends AbstractPage {
    public $templateName = 'banunban';
    public int $banID = 0;
    public string $error = '';
    public string $success = '';

    public function readParameters() {
        parent::readParameters();
        if (isset($_GET['id'])) $this->banID = (int)$_GET['id'];
    }

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canUnban')) {
            throw new PermissionDeniedException();
        }

        try {
            $pdo = $this->connect();
            $cfg = $this->getConfig();
            $table = ($cfg['prefix'] ?? '') . 'sa_bans';

            $stmt = $pdo->prepare("UPDATE {$table} SET status = 'Unbanned' WHERE id = ?");
            $stmt->execute([$this->banID]);

            $this->success = 'Der Bann wurde erfolgreich aufgehoben.';
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'banID' => $this->banID,
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
