<?php

namespace Zls\Saiyan;

use Z;

class RPC
{
    private $addr = '';
    private $conn = null;

    private function __construct($addr, $timeout = 0)
    {
        $this->addr = ("tcp://{$$addr}");
        $this->connect();
        if ($timeout) {
            stream_set_timeout($this->conn, $timeout);
        }
    }

    public function __destruct()
    {
        if ($this->conn) {
            @fclose($this->conn);
        }
    }

    public function connect($re = false)
    {
        if ($re && $this->conn) {
            $this->__destruct();
        }
        $this->conn = @stream_socket_client($this->addr, $err, $errmsg, 10, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);
        if (!$this->conn) {
            return $errmsg;
        }
        return null;
    }

    public static function fastCall($method, $params = null)
    {
        static $instance;
        if (!$instance) {
            $addr = Z::server('ZLSPHP_JSONRPC_ADDR');
            if (!$addr) {
                return [null, 'rpc addr cannot be empty'];
            }
            $instance = new self($addr);
        }
        $result = $instance->call($method, $params);
        return [$result['result'] ?? null, $result['error']];
    }

    public function call($method, $params)
    {
        $len = false;
        $errmsg = null;
        $retry  = 1;
        do {
            if (!$this->conn) {
                $errmsg = $this->connect();
            }
            if ($errmsg) {
                $retry--;
                continue;
            }
            $len = @fwrite($this->conn, json_encode([
                'method' => $method,
                'params' => [$params],
                'id'     => 0,
            ]));
            if ($len === false) {
                $errmsg = $this->connect(true);
            }
        } while ($len === false && $retry >= 0);
        if ($len === false) {
            return ['error' => $errmsg];
        }
        $result = @fgets($this->conn);
        if ($result === false) {
            return ['error' => 'read failure'];
        }
        return json_decode($result, true);
    }
}
