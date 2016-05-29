<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */
namespace src\core\Helpers;

use GuzzleHttp\Client as Guzzle;
use Rossedman\Teamwork\Client as TeamClient;
use Rossedman\Teamwork\Factory as Teamwork;

class TeamWorkHelper
{
    protected $config;
    protected $tw;
    protected $twHistory;
    protected $twTasks;

    public function __construct($config, $PDO, $output)
    {
        $this->config = $config->get('teamwork');

        $this->tw['client']  = new TeamClient(new Guzzle, $this->config['token'], $this->config['url']);
        $this->tw['object']  = new Teamwork($this->tw['client']);

        $apiInfo = $PDO->getConfig();

        $this->twHistory = $this->getLatestActivity($apiInfo['teamwork_id']);

        $PDO->writeData('Checking TeamWork Updates', '.', $output);

        $this->prepareData($this->twHistory, $PDO, $output);

        $this->twTasks = $this->getAllTasks();
        $this->prepareData($this->twTasks, $PDO, $output, true);
    }

    /**
     * get the latest activity on a project
     * @return mixed
     */
    public function getLatestActivity($tw_id)
    {
        return  $this->tw['object']->project((int)$tw_id)->activity();
    }

    /**
     * Method to prepare data to persist.
     * @param $items
     * @param $PDO
     */
    public function prepareData($items, $PDO, $output, $type = false)
    {
        if ($type) {
            foreach ($items['todo-items'] as $item) {

                $PDO->persistTWData([
                    'responsible' => isset($item['responsible-party-names']) ? $item['responsible-party-names'] : 'noname',
                    'task_id'     => $item['id'],
                    'description' => $item['description'],
                    'from'        => $item['creator-firstname'],
                    'project_name'=> $item['project-name'],
                    'project_id'  => $item['project-id']
                ], $output);

            }
            return;
        }
        foreach ($items['activity'] as $item) {

            $PDO->persistData([
                'timestamp'        => strtotime($item['datetime']),
                'user_name'        => $item['fromusername'],
                'type'             => 'teamwork',
                'user_id'          => $item['userid'],
                'image_url'        => $item['from-user-avatar-url'],
                'message'          => $item['description'],
                'link'             => $item['link'],
                'title'            => $item['activitytype'],
                'extradescription' => $item['extradescription'],
                'activitytype'     => $item['activitytype']
            ], $output);

        }
    }

    /**
     * get all teamWork projects.
     */
    public function getAllProjects()
    {
        return $this->tw['object']->project()->all();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProjectNameById($id)
    {
        return $this->tw['object']->project((int)$id)->find();
    }

    /**
     * @return mixed
     */
    public function getAllTasks()
    {
       return $this->tw['object']->task()->all();

    }

}