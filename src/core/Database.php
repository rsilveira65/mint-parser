<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */
namespace src\core;
use Spatie\Emoji\Emoji;


class Database
{
    protected $config;
    protected $pdo;
    protected $queries;
    protected $urls;
    protected $newData = false;
    protected $teamwork;


    /**
     * Database constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config  = $config->get('database');
        $database      = $this->config['db'];
        $databaseUser  = $this->config['db_user'];
        $databasePass  = $this->config['db_pass'];
        $databaseHost  = $this->config['db_host'];
        $dataCharset   = $this->config['db_charset'];
        $databaseInfo  = "mysql:host=$databaseHost;";
        $databaseInfo .= "dbname=$database;";
        $databaseInfo .= "charset=$dataCharset;";

        $this->teamwork = $config->get('teamwork');

        try {
            $this->pdo = new \PDO($databaseInfo, $databaseUser, $databasePass);
        } catch (\PDOException $e) {
            print $e->getMessage();
            die();
        }
    }

    /**
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * @return bool
     */
    public function getNewData()
    {
        return $this->newData;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        $conf = $this->pdo->prepare("SELECT * FROM config ORDER by id DESC LIMIT 1");

        if ($conf->execute()) {
            return $conf->fetch(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * Method to persist the last updated timestamp.
     * @param $now
     */
    public function saveLastUpdated($now)
    {
        $stmt = $this->pdo->prepare("UPDATE ping SET last_updated = :last_updated WHERE id = 1");
        $stmt->bindParam(':last_updated', $now, \PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * @param $data
     */
    public function persistData($data, $output)
    {
        $conf = $this->pdo->prepare("SELECT id FROM data WHERE timestamp = {$data['timestamp']}");

        if ($conf->execute() && empty($conf->fetch(\PDO::FETCH_ASSOC)['id'])) {
            $this->newData = true;
            $output->writeln('');
            echo Emoji::warningSign();
            $output->writeln(sprintf("  <question>News on %s:</question>", $data['type']));
            $output->writeln(sprintf("<info>%s - %s</info>",$data['user_name'], $data['message']));

            if ($data['type'] == 'teamwork') {
                $output->writeln(sprintf("<question> %s/%s</question>", $this->teamwork['url'], $data['link']));
            }
            $output->writeln(" ");

            $sql = "INSERT IGNORE INTO data(timestamp,type,link,title,user_name,user_id,image_url,message) VALUES (:timestamp,:type,:link,:title,:user_name,:user_id,:image_url,:message)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':timestamp', $data['timestamp'], \PDO::PARAM_STR);
            $stmt->bindParam(':type', $data['type'], \PDO::PARAM_STR);
            (isset($data['link'])) ? $stmt->bindParam(':link', $data['link'], \PDO::PARAM_STR) : $stmt->bindParam(':link', $data['image_url']);
            (isset($data['title'])) ? $stmt->bindParam(':title', $data['title'], \PDO::PARAM_STR) : $stmt->bindParam(':title', $data['user_name']);
            $stmt->bindParam(':user_name', $data['user_name'], \PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $data['user_id'], \PDO::PARAM_STR);
            $stmt->bindParam(':image_url', $data['image_url'], \PDO::PARAM_STR);
            $stmt->bindParam(':message', $data['message'], \PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * @param $data
     * @param $output
     */
    public function persistTWData($data, $output)
    {
        $conf = $this->pdo->prepare("SELECT task_id FROM tasks WHERE task_id = {$data['task_id']}");

        if ($conf->execute() && empty($conf->fetch(\PDO::FETCH_ASSOC)['task_id'])) {
            $this->newData = true;

            $output->writeln('');
            echo Emoji::warningSign();
            $output->writeln(sprintf("<question>  New Task from %s. %s:</question>", $data['project_name'], $data['from']));
            $output->writeln(sprintf("<info>%s</info>", $data['description']));
            $output->writeln(sprintf("<info>%s/tasks/%s</info>", $this->teamwork['url'], $data['task_id']));
            $output->writeln('');

            if (strpos($data['responsible'], $this->teamwork['username'])) {
                exec(sprintf("say New Task from %s. %s:", $data['project_name'], $data['from']));
                exec(sprintf("mintopen \"conradcaine.teamwork.com/tasks/%s\"", $data['task_id']));
            }

            $sql = "INSERT INTO tasks(task_id) VALUES (:task_id)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':task_id', $data['task_id'], \PDO::PARAM_STR);

            $stmt->execute();
        }

    }

    /**
     * @param $name
     * @param $separator
     * @param $output
     */
    public function writeData($name, $separator, $output)
    {
        $output->write(sprintf("\033\143"));
        echo Emoji::pigFace();
        $output->write(sprintf('<comment>  %s</comment>', $name));

        for ($i = 0; $i<3; $i++) {
            sleep(1);
            echo Emoji::heavyExclamationMarkSymbol();
        }

        $output->writeln('');
    }
}
