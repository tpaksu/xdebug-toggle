<?php

namespace Tpaksu\XdebugToggle\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class XdebugToggle extends Command
{
    /**
     * The complete line containing "*_extension=*xdebug*".
     *
     * @var string
     */
    protected $extensionLine;

    /**
     * Extension active status.
     *
     * @var bool
     */
    protected $extensionStatus;

    /**
     * The configuration written in php.ini for XDebug.
     *
     * @var array
     */
    protected $extensionSettings;

    /**
     * Path of the Loaded INI file.
     *
     * @var string
     */
    protected $iniPath;

    /**
     * Debug mode active flag.
     *
     * @var bool
     */
    protected $debug;

    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'xdebug {status : "on" or "off" to enable/disable XDebug}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Enables or disables XDebug extension';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->debug = false;
    }

    /**
     * initialization routines.
     */
    public function initialize()
    {
        // Define custom format for bold text
        $style = new OutputFormatterStyle('default', 'default', ['bold']);
        $this->output->getFormatter()->setStyle('bold', $style);

        // Get the verbosity level to set debug mode flag
        $verbosityLevel = $this->getOutput()->getVerbosity();
        if ($verbosityLevel > OutputInterface::VERBOSITY_DEBUG) {
            $this->debug = true;
        }
    }

    /**
     * The method that handles the command.
     */
    public function handle()
    {
        // Get XDebug desired status from the command line arguments
        $desiredStatus = strval($this->argument('status'));

        // do the validation
        if ($this->validateDesiredStatus($desiredStatus) === false) {
            return false;
        }

        // Retrieve the INI path to the global variable
        if ($this->getIniPath() === false) {
            return false;
        }

        // Get the XDebug extension information from the INI file
        $this->getXDebugStatus();

        // do the validation
        if ($this->validateXDebugStatus($desiredStatus) === false) {
            return false;
        }

        // we need to alter the status to the new one. Do it!
        $this->setXDebugStatus($desiredStatus);
    }

    /**
     * Validates the desired status argument received from console.
     *
     * @param   string  $desiredStatus  Should be "on" or "off"
     *
     * @return  bool                 Whether it is a valid input
     */
    public function validateDesiredStatus(string $desiredStatus)
    {
        if ($this->debug) {
            echo 'Desired Status: '.($desiredStatus)."\n";
        }

        // validate desired XDebug status
        if (! in_array($desiredStatus, ['on', 'off'])) {
            $this->line('Status should be "on" or "off". Other values are not accepted.', 'fg=red;bold');

            return false;
        }

        return true;
    }

    /**
     * Gets the XDebug status and related configuration from the loaded php.ini file.
     */
    private function getXDebugStatus()
    {
        // get the extension status
        $this->getExtensionStatus();

        // get extemsion settings
        $this->getExtensionSettings();
    }

    /**
     * Retrieves the INI path from php.ini file.
     *
     * @return bool
     */
    private function getIniPath()
    {
        $this->iniPath = php_ini_loaded_file() ?? '';

        // If we can't retrieve the loaded INI path, bail out
        if ($this->iniPath === '') {
            $this->line("Can't get php.ini file path from phpinfo() output.
            Make sure that the function is allowed inside your php.ini configuration.", 'bold');

            return false;
        }

        return true;
    }

    /**
     * Validates the XDebug status received.
     *
     * @param   string  $desiredStatus  The desired status
     *
     * @return  bool                 Whether we should continue to modify the status or not
     */
    public function validateXDebugStatus(string $desiredStatus)
    {
        // prepare variables for comparison and output
        $currentStatus = $this->extensionStatus ? 'on' : 'off';
        $styledStatus = $this->extensionStatus ? '<fg=green;bold>on' : '<fg=red;bold>off';

        // print current status to the user
        $this->line("<fg=yellow>Current XDebug Status: $styledStatus</>");

        // if the desired status and current status are the same, we don't need to alter anything
        // inform the user and exit
        if ($currentStatus === $desiredStatus) {
            $this->line('<fg=green>Already at the desired state. No action has been taken.</>');

            return false;
        }

        return true;
    }

    /**
     * Sets the new XDebug extension status.
     *
     * @param   string  $status  Whether the extension should be active or not
     *
     * @return  void
     */
    private function setXDebugStatus($status)
    {
        // inform the user about the current operation
        $this->line("<bold>Setting status to \"$status\"</bold>");

        // read the ini file
        $contents = file_get_contents($this->iniPath);

        if ($this->debug) {
            echo "status: $status\n";
            echo 'line: '.$this->extensionLine."\n";
            echo 'new: '.trim($this->extensionLine, ';')."\n";
        }

        // replace the "zend_extension=*xdebug.*" line with the active/passive equivalent
        switch ($status) {
            case 'on':
                $contents = str_replace($this->extensionLine, trim($this->extensionLine, ';'), $contents);
                break;

            case 'off':
                $contents = str_replace($this->extensionLine, ';'.$this->extensionLine, $contents);
                break;
        }

        // rewrite the php.ini file
        file_put_contents($this->iniPath, $contents);

        // restart the service to put the changes in effect
        $this->restartServices();
    }

    /**
     * Reads the extension status from PHP ini file.
     *
     * @return void
     */
    private function getExtensionStatus()
    {
        // read the extension line from file,
        // can't use parse_ini_file here because the keyed array overwrites "extension" lines and keeps the last one
        $this->extensionLine = collect(file_get_contents($this->iniPath))
            ->explode("\n")
            ->filter(function ($line) {
                return Str::contains($line, 'extension=') && Str::contains($line, 'xdebug');
            })
            ->first();

        $this->extensionLine = trim($this->extensionLine ?? '');

        if (strlen($this->extensionLine) > 0) {
            $this->extensionStatus = $this->extensionLine[0] === ';';
        } else {
            $this->extensionStatus = false;
        }

        if ($this->debug) {
            echo 'line: '.$this->extensionLine."\n";
            echo 'ext.status: '.$this->extensionStatus."\n";
        }
    }

    /**
     * Reads the extension settings from PHP ini file.
     *
     * @return void
     */
    private function getExtensionSettings()
    {
        $settings = collect(parse_ini_file($this->iniPath))->filter(function ($setting, $key) {
            return Str::startsWith($key, 'xdebug.');
        });
        $this->extensionSettings = $settings->toArray();
    }

    /**
     * Restarts the services that takes the modification into effect.
     *
     * @return void
     */
    private function restartServices()
    {
        /**
         * Define a global outputter to display command output to user.
         *
         * @param   string  $type  The type of the output
         * @param   string  $data  The output
         *
         * @return  void
         */
        $output = function ($type, $data) {
            $this->info($data);
        };
        // run the command(s) needed to restart the service
        (new Process([env('XDEBUG_SERVICE_RESTART_COMMAND', 'valet restart nginx')]))->run($output);
        // display the new extension status
        (new Process(['php --ri xdebug']))->run($output);
    }
}
