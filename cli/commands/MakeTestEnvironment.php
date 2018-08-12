<?php

namespace WPDev\CLI\Commands;

use GuzzleHttp\Client;
use PHPUnit\Runner\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class MakeTestEnvironment extends Command
{
    protected $client;
    /** @var  \Symfony\Component\Filesystem\Filesystem */
    protected $fs;
    /** @var  InputInterface */
    protected $input;
    /** @var  OutputInterface */
    protected $output;
    protected $options = [
        'dbname'    => 'wpdevtest',
        'dbuser'    => 'root',
        'dbpass'    => '',
        'dbhost'    => 'localhost',
        'wpversion' => 'latest',
    ];
    protected $testDir;
    protected $wpTestsDir;
    protected $wpCoreDir;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->client = new Client();
        $this->testDir = dirname(dirname(__DIR__)) . '/test-env';
        $this->wpTestsDir = $this->testDir.'/wordpress-tests-lib';
        $this->wpCoreDir = $this->testDir.'/wordpress';
        $this->fs = new Filesystem();
    }

    protected function configure()
    {
        // todo db-name = wpdevtest
        // todo db-user = root
        // todo db-pass = ''
        // todo db-host = localhost
        // todo wp-version = latest

        // echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
        $this->setName('make:testEnvironment')
             ->setDescription('Scaffolds the testing environment.')
             ->setHelp('This commands scaffolds the testing environment...')
             ->addOption('dbname', null, InputOption::VALUE_OPTIONAL, 'The database name')
             ->addOption('dbuser', null, InputOption::VALUE_OPTIONAL, 'The database user')
             ->addOption('dbpass', null, InputOption::VALUE_OPTIONAL, 'The database pass')
             ->addOption('dbhost', null, InputOption::VALUE_OPTIONAL, 'The database host')
             ->addOption('wpversion', null, InputOption::VALUE_OPTIONAL, 'WordPress version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->getOptionsInteractively();

        // fresh start
        $this->fs->remove($this->testDir);

        $this->output->writeln('Downloading WordPress...');
        $this->installWordPress();
        $this->output->writeln('Downloading Test Suite...');
        $this->installTestSuite();
        $this->output->writeln('Creating Database...');
        $this->installDatabase();
        $this->output->writeln('Done!');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    protected function askUserForOption($option, $default)
    {
        $helper   = $this->getHelper('question');
        $question = new Question("What to use for $option? ($default) ", $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    protected function confirmOptions()
    {
        $this->output->writeln('---------------------');
        foreach (array_keys($this->options) as $option) {
            $this->output->writeln("$option: {$this->input->getOption($option)}");
        }
        $this->output->writeln('---------------------');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion("Continue with the above options? (y)");
        $continue = $helper->ask($this->input, $this->output, $question);

        if ( ! $continue) {
            $this->output->writeln('');
            $this->resetOptions();
            $this->getOptionsInteractively();
        }

        return true;
    }

    protected function getDownloadUrl()
    {
        $version = $this->getWpVersion();

        if ($version === 'nightly' || $version === 'trunk') {
            return 'https://wordpress.org/nightly-builds/wordpress-latest.zip';
        }

        if ($version === 'latest') {
            return "https://wordpress.org/latest.zip";
        }

        $archive_name = preg_replace('/\.0$/', '', $version);
        return "https://wordpress.org/wordpress-{$archive_name}.zip";
    }

    protected function getOptionsInteractively()
    {
        foreach ($this->options as $option => $default) {
            if ($this->input->getOption($option)) {
                continue;
            }
            $user_input = $this->askUserForOption($option, $default);
            $this->input->setOption($option, $user_input);
        }

        $this->confirmOptions();
    }

    protected function installDatabase()
    {
        $dbname = $this->input->getOption('dbname');
        $dbuser = $this->input->getOption('dbuser');
        $dbpass = $this->input->getOption('dbpass');
        $dbhost = $this->input->getOption('dbhost');

        exec("mysqladmin create $dbname --user=$dbuser --password=$dbpass --host=$dbhost --protocol=tcp");
    }

    protected function installTestSuite()
    {
        $includes_dir = $this->wpTestsDir.'/includes';
        $data_dir = $this->wpTestsDir.'/data';
        $this->fs->mkdir($includes_dir);
        $this->fs->mkdir($data_dir);

        $tag = $this->getTagOrBranchEndpoint();

        exec("svn checkout https://develop.svn.wordpress.org/{$tag}/tests/phpunit/includes/ $includes_dir");
        exec("svn checkout https://develop.svn.wordpress.org/{$tag}/tests/phpunit/data/ $data_dir");

        $wp_tests_config = $this->wpTestsDir.'/wp-tests-config.php';

        $this->client->get("https://develop.svn.wordpress.org/{$tag}/wp-tests-config-sample.php", [
            'sink' => $wp_tests_config,
        ]);

        $file_contents = file_get_contents($wp_tests_config);
        $file_contents = str_replace("dirname( __FILE__ ) . '/src/'", "'$this->wpCoreDir/'", $file_contents);
        $file_contents = str_replace('youremptytestdbnamehere', $this->input->getOption('dbname'), $file_contents);
        $file_contents = str_replace('yourusernamehere', $this->input->getOption('dbuser'), $file_contents);
        $file_contents = str_replace('yourpasswordhere', $this->input->getOption('dbpass'), $file_contents);
        $file_contents = str_replace('localhost', $this->input->getOption('dbhost'), $file_contents);
        file_put_contents($wp_tests_config,$file_contents);
    }

    protected function installWordPress()
    {
        $this->fs->mkdir($this->wpTestsDir, 0775);

        $this->fs->mkdir($this->testDir.'/wordpress-download', 0775);

        // download zip and unzip
        $zip_path = $this->testDir.'/wordpress-download/wordpress.zip';
        $this->client->get($this->getDownloadUrl(), [
            'sink' => $zip_path,
        ]);
        $this->unzip($zip_path, dirname($zip_path));

        // move WP to final destination
        $this->fs->rename(dirname($zip_path).'/wordpress', $this->wpCoreDir);

        $this->client->get('https://raw.github.com/markoheijnen/wp-mysqli/master/db.php', [
           'sink' => $this->wpCoreDir.'/wp-content/db.php',
        ]);
    }

    protected function getTagOrBranchEndpoint()
    {
        $version = $this->getWpVersion();

        // i.e 4.5
        if (preg_match('/^[0-9]+\.[0-9]+$/', $version)) {
            return "branches/$version";
        }

        // i.e 4.5.6
        if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $version)) {
            // remove ending .0 if present
            return 'tags/'.preg_replace('/\.0$/', '', $version);
        }

        if (strtolower($version) === 'nightly' || strtolower($version) === 'trunk') {
            return 'trunk';
        }

        try {
            ini_set("allow_url_fopen", 1);
            $wp_api_response = file_get_contents('http://api.wordpress.org/core/version-check/1.7/');
            $wp_version = json_decode($wp_api_response);
            $latest = $wp_version->offers[0]->version;
            return 'tags/'.$latest;
        } catch (Exception $e) {
            $this->output->writeln('Something went wrong getting latest info version from WordPress. Message below.');
            $this->output->writeln($e->getMessage());
            exit;
        }

    }

    protected function getWpVersion()
    {
        return strtolower($this->input->getOption('wpversion'));
    }

    protected function resetOptions()
    {
        foreach (array_keys($this->options) as $option) {
            $this->input->setOption($option, '');
        }
    }

    protected function unzip($file, $path)
    {
        $zip = new \ZipArchive;
        $file = $zip->open($file);
        if ($file) {
            $zip->extractTo($path);
        }
        $zip->close();
    }
}