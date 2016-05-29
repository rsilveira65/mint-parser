<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */
namespace src\core;

use src\core\Database;
use src\core\Helpers\GitLabHelper;
use src\core\Helpers\HipChatHelper;
use src\core\Helpers\RandomJokes;
use src\core\Helpers\TeamWorkHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Spatie\Emoji\Emoji;


class MintCommand extends Command
{
    protected function configure()
    {
        $this->setName('run');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $PDO    = new Database($config);

        $output->write(sprintf("\033\143"));
        echo Emoji::pigFace();
        $output->write('<comment>  Let me tell u a joke: </comment>');
        $output->writeln('');
        echo RandomJokes::getJoke();
        echo Emoji::faceWithTearsOfJoy();
        echo Emoji::faceWithTearsOfJoy();
        sleep(6);
        $output->write(sprintf("\033\143"));


        $PDO->writeData('Now, let me find some Feed for u', '!', $output);

        new HipChatHelper($config, $PDO, $output);

        new GitLabHelper($config, $PDO, $output);

        new TeamWorkHelper($config, $PDO, $output);

        if ($PDO->getNewData()) {
            $PDO->saveLastUpdated((string)time());
        } else {
            $output->write(sprintf("\033\143"));
            echo Emoji::pigFace();
            $output->write("<info>  Sorry, no Feed! See you bro!</info>");
            sleep(2);
            echo Emoji::raisedHand();
            sleep(2);
            $output->write(sprintf("\033\143"));
        }

        $output->writeln('');
    }
}
