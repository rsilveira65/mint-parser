<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */
namespace src\core\Helpers;

use HipChat\HipChat;

class HipChatHelper
{
    protected $config;
    protected $hc;
    protected $roomHistory;

    public function __construct($config, $PDO, $output)
    {
        $this->config      = $config->get('hipchat');
        $this->hc          = new HipChat($this->config['token']);
        $apiInfo           = $PDO->getConfig();
        $this->roomHistory = $this->getHistory($apiInfo['hipchat_id']);

        $PDO->writeData('Checking HipChat Updates', '.', $output);

        $this->prepareData($this->roomHistory, $PDO, $output);
    }

    /**
     * Method to get history from a hipchat room.
     * @param $room_id
     * @param int $max
     * @return array|string
     */
    private function getHistory($room_id, $max = 10)
    {
        $history = array_reverse($this->hc->get_rooms_history($room_id));

        if (empty($history)) {
            return 'no history';
        }

        foreach ($history as $key => $room) {

            if ($key == $max) {
                break;
            }

            if (!empty($room->from->user_id) && is_int($room->from->user_id)) {
                $room->from->image_url = $this->hc->get_user($room->from->user_id);
            }
            $data[] = $room;
        }
        return $data;
    }

    /**
     * Metho to prepare data to persist.
     * @param $items
     * @param $PDO
     */
    private function prepareData($items, $PDO, $output)
    {
        foreach ($items as $item) {

            $PDO->persistData([
                'timestamp' => strtotime($item->date),
                'user_name' => $item->from->name,
                'type'      => 'hipchat',
                'user_id'   => $item->from->user_id,
                'image_url' => (isset($item->from->image_url->photo_url)) ? $item->from->image_url->photo_url : 'empty_value',
                'message'   => str_replace('\n', '', strip_tags($item->message))
            ], $output);

        }
    }

    /**
     * Method to get all hipchat rooms.
     * @return array
     */
    public function gelAllRooms()
    {
        return $this->hc->get_rooms();
    }

    /**
     * Method to get all users (online/offline).
     * @param $type
     * @return array
     */
    public function getUsers($type)
    {
        $users = $this->hc->get_users();

        foreach ($users as $user) {

            if (strpos($user->photo_url, 'houette') || $user->status != $type) {
                continue;
            }

            $data[] = (array) $user;
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRoomNameById($id)
    {
        return $this->hc->get_room($id);
    }

}