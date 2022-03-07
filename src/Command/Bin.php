<?php

namespace Zls\Saiyan\Command;

use Exception;
use Z;
use Zls\Action\Http;
use Zls\Command\Utils;
use Zls\Install\Install as In;
use Zls\Install\Util;

class Bin
{
    use Utils;

    private $file;
    private $filepath;
    private $http;
    private $isChina;
    private $chinaCDN = 'https://github.73zls.com/';

    public function __construct()
    {
        $this->file = Z::isWin() ? 'saiyan.exe' : 'saiyan';
        $this->http = new Http();
        $this->filepath = Z::realPath($this->file, false, false);
        $a = $this->http->get('https://docs.73zls.com');
    }

    public function path()
    {
        if (!file_exists($this->filepath)) {
            $this->download();
        }
        return $this->filepath;
    }

    public function download()
    {
        // 'curl/7.58.0'
        $version = Util::getGithubReleasesVersion("sohaha/saiyan-go");
        $os = Util::goOS();
        $arch = Util::getArch();
        if (!$version || !$os || !$arch) {
            $this->error("Failed to get version, please check the network");
            return;
        }
        $file = "saiyan_{$version}_{$os}_{$arch}.tar.gz";
        $url = ($this->isChina ? $this->chinaCDN : '') . "https://github.com/sohaha/saiyan-go/releases/download/v{$version}/{$file}";
        $data = [
            'url' => $url,
            'md5' => '',
            'path' => Z::realPath('./', true, false, true),
            'ignore' => ['README.md'],
            'moveRule' => [],
            'KeepOldFile' => false,
        ];
        $d = new In(Z::arrayGet($data, 'url'));
        $d->silentRun($data);
        $d->setProcessTip(function ($v) {
            $this->printStrN('[ Process ]: ' . $v);
        });
        try {
            $res = $d->run();
        } catch (Exception $e) {
            $res = $e->getMessage();
        }
        if (is_string($res)) {
            $this->error($res);
        } else {
            $this->success("Download success");
        }
    }
}