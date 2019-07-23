<?php

class custom_disguising extends \FileRun\Files\Plugin
{

    static $localeSection = 'Custom Actions: Disguising as Picture';

    function init()
    {
        $pathToPicture = self::getSetting('pathToPicture');
        $this->config = [
            'disguising_command' => 'cat [%picturePath%] [%filePath%] > [%targetPath%]'
        ];
        $this->settings = [
            [
                'key' => 'pathToPicture',
                'title' => self::t('Path to Picture'),
                'comment' => self::t('Enter a picture path from filerun. <br/>eg: /user-files/sample.png')
            ]
        ];
        $this->JSconfig = [
            'title' => self::t('Disguising as Picture'),
            'iconCls' => 'fa fa-fw fa-magic',
            "requiredUserPerms" => ["download", "upload"],
            'requires' => ['download', 'create', 'alter'],
            'fn' => 'FR.customActions.disguising.run()'
        ];
    }

    function isDisabled()
    {
        return (strlen(self::getSetting('pathToPicture')) == 0);
    }

    function run()
    {
        $readData = $this->prepareRead(['expect' => 'file']);
        $fileName = \FM::basename($readData['fullPath']);
        $extension = \FM::getExtension(self::getSetting('pathToPicture'));
        $deleteSrc = (S::fromHTML($_POST['deleteSrc']) == 1 ? true : false);

        $rs = $this->disguiseFile($readData, $extension);
        if (!$rs) {
            jsonFeedback(false, self::t("Failed to disguise the selected file!"));
        }

        if ($deleteSrc) {
            \FileRun\Files\Actions\Delete\File::run($readData, false);
        }
        $this->writeFile([
            'source' => 'external',
            'logging' => ['details' => ['method' => 'Disguising as Picture']]
        ]);
        jsonFeedback(true, self::t("The selected file was successfully disguised."));
    }

    function JSinclude()
    {
        include(gluePath($this->path, "include.js.php"));
    }

    private function disguiseFile($readData, $extension)
    {
        $cmd = $this->parseCmd($this->config['disguising_command'], $readData['fullPath'], $extension);
        return $this->runCmd($cmd);
    }

    private function parseCmd($cmd, $filePath, $extension)
    {
        return str_replace(
            array("[%picturePath%]", "[%filePath%]", "[%targetPath%]"),
            array(self::getSetting('pathToPicture'), $this->escapeshellarg($filePath), $this->escapeshellarg($filePath) . '.' . $extension),
            $cmd);
    }

    private function escapeshellarg($s)
    {
        return '"' . addslashes($s) . '"';
    }

    private function runCmd($cmd)
    {
        session_write_close();
        @exec($cmd, $return_text, $return_code);
        if ($return_code != 0) {
            return false;
        } else {
            return true;
        }
    }
}
