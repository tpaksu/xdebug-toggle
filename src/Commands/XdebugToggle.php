<?php

namespace Tpaksu\XdebugToggle\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class XdebugToggle extends Command
{

    protected $extensionLine;

    protected $extensionStatus;

    protected $extensionSettings;

    protected $iniPath;

    protected $debug;

    protected $signature = 'xdebug {status : "on" or "off" to enable/disable XDebug}';

    protected $description = 'Enables or disables XDebug extension';

    public function __construct()
    {
        parent::__construct();
        $this->debug = false;
    }

    public function handle()
    {

        // Custom colors
        $style = new OutputFormatterStyle('default', 'default', array('bold'));
        $this->output->getFormatter()->setStyle('bold', $style);

        $verbosityLevel = $this->getOutput()->getVerbosity();
        if ($verbosityLevel > OutputInterface::VERBOSITY_DEBUG) {
            $this->debug = true;
        }

        $desiredStatus = $this->argument("status");

        if ($this->debug) {
            echo "Desired Status: $desiredStatus\n";
        }

        if (!in_array($desiredStatus, ["on", "off"])) {
            $this->line("Status should be \"on\" or \"off\". Other values are not accepted.", "fg=red;bold");
            return false;
        }

        $this->getIniPath();

        if ($this->iniPath == null) {
            $this->line("Can't get php.ini file path from phpinfo() output.
            Make sure that the function is allowed inside your php.ini configuration.", "bold");
            return false;
        }

        $this->getXDebugStatus();

        $currentStatus = $this->extensionStatus ? "on" : "off";
        $styledStatus = $this->extensionStatus ? "<fg=green;bold>on" : "<fg=red;bold>off";

        $this->line("<fg=yellow>Current XDebug Status: $styledStatus</>");

        if ($currentStatus === $desiredStatus) {
            $this->line("<fg=green>Already at the desired state. No action has been taken.</>");
            return false;
        }

        $this->setXDebugStatus($desiredStatus);
    }

    private function setXDebugStatus($status)
    {
        $this->line("<bold>Setting status to $status</bold>");

        $contents = file_get_contents($this->iniPath);
        if ($this->debug) {
            echo "status: $status\n";
            echo "line: " . $this->extensionLine . "\n";
            echo "new: " . trim($this->extensionLine, ";") . "\n";
        }
        switch ($status) {
            case 'on':
                $contents = str_replace($this->extensionLine, trim($this->extensionLine, ";"), $contents);
                break;

            default:
                $contents = str_replace($this->extensionLine, ";" . $this->extensionLine, $contents);
                break;
        }
        file_put_contents($this->iniPath, $contents);
        $this->restartServices();
    }

    private function getIniPath()
    {
        ob_start();
        phpinfo(INFO_GENERAL);
        $info = ob_get_contents();
        ob_end_clean();
        preg_match("/Loaded Configuration File => (.+)/i", $info, $matches);
        if (count($matches) == 2) {
            $this->iniPath = $matches[1];
            return;
        }
        $this->iniPath = null;
    }

    private function getXDebugStatus()
    {
        $this->line("<fg=yellow>INI file path:</> <bold>$this->iniPath</bold>");
        $lines = collect();
        $fp = @fopen($this->iniPath, "r");
        if ($fp) {
            while (!feof($fp)) {
                $line = fgets($fp);
                if ($line && stripos($line, "xdebug") !== false) {
                    $lines->push($line);
                }
            }
        }
        fclose($fp);

        $this->getExtensionStatus($lines);
        $this->getExtensionSettings();
    }

    private function getExtensionStatus(Collection $lines)
    {
        $this->extensionLine = $lines->filter(function ($line) {
            return Str::contains($line, "extension=");
        })->first();

        $this->extensionLine = trim($this->extensionLine);

        if ($this->debug) {
            echo "line: " . $this->extensionLine . "\n";
        }

        if (strlen($this->extensionLine) > 0) {
            $this->extensionStatus = $this->extensionLine[0] == ";" ? false : true;
        }
        if ($this->debug) {
            echo "ext.status: " . $this->extensionStatus . "\n";
        }
    }

    private function getExtensionSettings()
    {
        $settings = collect(parse_ini_file($this->iniPath))->filter(function ($setting, $key) {
            return Str::startsWith($key, "xdebug.");
        });
        $this->extensionSettings = $settings->toArray();
    }

    private function restartServices()
    {
        $output = function ($type, $data) {
            $this->info($data);
        };
        (new Process("valet restart nginx"))->run($output);
        (new Process("php --ri xdebug"))->run($output);
    }
}
