<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */

namespace src\core\Helpers;

use Simplon\Feed\FeedReader;
use Simplon\Feed\Vo\Atom;

class GitLabHelper
{
    protected $gl;
    protected $config;

    /**
     * GitLabHelper constructor.
     * @param $config
     * @param $PDO
     */
    public function __construct($config, $PDO, $output)
    {
        $this->config    = $config->get('gitlab');
        $this->gl['url'] = $this->config['url'] . $this->config['token'];
        $feed            = new FeedReader();
        $feedVo          = $feed->atom($this->gl['url']);

        $PDO->writeData('Checking GitLab Updates', '.', $output);

        $this->prepareData($feedVo, $PDO, $output);
    }

    /**
     * @param $feedVo
     * @param $PDO
     * @return mixed
     */
    public function prepareData($feedVo, $PDO, $output)
    {
        foreach ($feedVo->getEntries() as $item) {

            $date    = $item->getUpdated();
            $summary = $item->getSummary();

            $PDO->persistData([
                'timestamp' => $date->getTimestamp(),
                'title'     => $item->getTitle(),
                'user_name' => !empty($summary['div']['p']['strong']) ? $summary['div']['p']['strong'] : 'empty_value',
                'type'      => 'gitlab',
                'link'      => !empty($item->getLink()['attrs']['href']) ? $item->getLink()['attrs']['href'] : 'empty_value',
                'message'   => !empty($summary['div']['blockquote']['p']) ? $summary['div']['blockquote']['p'] : 'empty_value'
            ], $output);

        }
    }
}