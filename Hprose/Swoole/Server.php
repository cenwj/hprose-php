<?php
/**********************************************************\
|                                                          |
|                          hprose                          |
|                                                          |
| Official WebSite: http://www.hprose.com/                 |
|                   http://www.hprose.org/                 |
|                                                          |
\**********************************************************/

/**********************************************************\
 *                                                        *
 * Hprose/Swoole/Service.php                              *
 *                                                        *
 * hprose swoole service library for php 5.3+             *
 *                                                        *
 * LastModified: Apr 17, 2015                             *
 * Author: Ma Bingyao <andot@hprose.com>                  *
 *                                                        *
\**********************************************************/

namespace Hprose\Swoole {
    class Server {
        private $real_server = null;
        private $type = null;
        private $mode = SWOOLE_PROCESS;
        private function init_server($url) {
            $result = new \stdClass();
            $p = parse_url($url);
            if ($p) {
                switch (strtolower($p['scheme'])) {
                    case 'http':
                    case 'https':
                        if ($this->real_server) {
                            throw new \Exception($p['scheme'] . " server didn't support addListener method.");
                        }
                        else {
                            $this->real_server = new \Hprose\Swoole\Http\Server($p['host'], $p['port']);
                            $this->type = $p['scheme'];
                        }
                        break;
                    case 'tcp':
                    case 'tcp4':
                    case 'tcp6':
                    case 'unix':
                        if ($this->real_server) {
                            if ($this->type == "socket") {
                                $this->real_server->addListener($url);
                            }
                            else {
                                throw new \Exception($this->type . " server didn't support addListener method.");
                            }
                        }
                        else {
                            $this->real_server = new \Hprose\Swoole\Socket\Server($url, $this->mode);
                            $this->type = "socket";
                        }
                        break;
                    default:
                        throw new \Exception("Only support http, https, tcp, tcp4, tcp6 or unix scheme");
                        break;
                }
            }
            else {
                throw new \Exception("Can't parse this url: " . $url);
            }
            return $result;
        }
        public function __construct($url, $mode = SWOOLE_PROCESS) {
            $this->mode = $mode;
            $this->init_server($url);
        }
        public function addListener($url) {
            $this->init_server($url);
        }
        public function __call($name, $args) {
            return call_user_func_array(array($this->real_server, $name), $args);
        }
        public function __set($name, $value) {
            $this->real_server->$name = $value;
        }
        public function __get($name) {
            return $this->real_server->$name;
        }
        public function __isset($name) {
            return isset($this->real_server->$name);
        }
        public function __unset($name) {
            unset($this->real_server->$name);
        }
    }
}
