<?php

namespace Hp;

//  PROJECT HONEY POT ADDRESS DISTRIBUTION SCRIPT
//  For more information visit: http://www.projecthoneypot.org/
//  Copyright (C) 2004-2021, Unspam Technologies, Inc.
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
//  02111-1307  USA
//
//  If you choose to modify or redistribute the software, you must
//  completely disconnect it from the Project Honey Pot Service, as
//  specified under the Terms of Service Use. These terms are available
//  here:
//
//  http://www.projecthoneypot.org/terms_of_service_use.php
//
//  The required modification to disconnect the software from the
//  Project Honey Pot Service is explained in the comments below. To find the
//  instructions, search for:  *** DISCONNECT INSTRUCTIONS ***
//
//  Generated On: Fri, 26 Nov 2021 05:12:33 -0500
//  For Domain: www.aatf.us
//
//

//  *** DISCONNECT INSTRUCTIONS ***
//
//  You are free to modify or redistribute this software. However, if
//  you do so you must disconnect it from the Project Honey Pot Service.
//  To do this, you must delete the lines of code below located between the
//  *** START CUT HERE *** and *** FINISH CUT HERE *** comments. Under the
//  Terms of Service Use that you agreed to before downloading this software,
//  you may not recreate the deleted lines or modify this software to access
//  or otherwise connect to any Project Honey Pot server.
//
//  *** START CUT HERE ***

define('__REQUEST_HOST', 'hpr6.projecthoneypot.org');
define('__REQUEST_PORT', '80');
define('__REQUEST_SCRIPT', '/cgi/serve.php');

//  *** FINISH CUT HERE ***

interface Response
{
    public function getBody();
    public function getLines(): array;
}

class TextResponse implements Response
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getBody()
    {
        return $this->content;
    }

    public function getLines(): array
    {
        return explode("\n", $this->content);
    }
}

interface HttpClient
{
    public function request(string $method, string $url, array $headers = [], array $data = []): Response;
}

class ScriptClient implements HttpClient
{
    private $proxy;
    private $credentials;

    public function __construct(string $settings)
    {
        $this->readSettings($settings);
    }

    private function getAuthorityComponent(string $authority = null, string $tag = null)
    {
        if(is_null($authority)){
            return null;
        }
        if(!is_null($tag)){
            $authority .= ":$tag";
        }
        return $authority;
    }

    private function readSettings(string $file)
    {
        if(!is_file($file) || !is_readable($file)){
            return;
        }

        $stmts = file($file);

        $settings = array_reduce($stmts, function($c, $stmt){
            list($key, $val) = \array_pad(array_map('trim', explode(':', $stmt)), 2, null);
            $c[$key] = $val;
            return $c;
        }, []);

        $this->proxy       = $this->getAuthorityComponent($settings['proxy_host'], $settings['proxy_port']);
        $this->credentials = $this->getAuthorityComponent($settings['proxy_user'], $settings['proxy_pass']);
    }

    public function request(string $method, string $uri, array $headers = [], array $data = []): Response
    {
        $options = [
            'http' => [
                'method' => strtoupper($method),
                'header' => $headers + [$this->credentials ? 'Proxy-Authorization: Basic ' . base64_encode($this->credentials) : null],
                'proxy' => $this->proxy,
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $body = file_get_contents($uri, false, $context);

        if($body === false){
            trigger_error(
                "Unable to contact the Server. Are outbound connections disabled? " .
                "(If a proxy is required for outbound traffic, you may configure " .
                "the honey pot to use a proxy. For instructions, visit " .
                "http://www.projecthoneypot.org/settings_help.php)",
                E_USER_ERROR
            );
        }

        return new TextResponse($body);
    }
}

trait AliasingTrait
{
    private $aliases = [];

    public function searchAliases($search, array $aliases, array $collector = [], $parent = null): array
    {
        foreach($aliases as $alias => $value){
            if(is_array($value)){
                return $this->searchAliases($search, $value, $collector, $alias);
            }
            if($search === $value){
                $collector[] = $parent ?? $alias;
            }
        }

        return $collector;
    }

    public function getAliases($search): array
    {
        $aliases = $this->searchAliases($search, $this->aliases);
    
        return !empty($aliases) ? $aliases : [$search];
    }

    public function aliasMatch($alias, $key)
    {
        return $key === $alias;
    }

    public function setAlias($key, $alias)
    {
        $this->aliases[$alias] = $key;
    }

    public function setAliases(array $array)
    {
        array_walk($array, function($v, $k){
            $this->aliases[$k] = $v;
        });
    }
}

abstract class Data
{
    protected $key;
    protected $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function key()
    {
        return $this->key;
    }

    public function value()
    {
        return $this->value;
    }
}

class DataCollection
{
    use AliasingTrait;

    private $data;

    public function __construct(Data ...$data)
    {
        $this->data = $data;
    }

    public function set(Data ...$data)
    {
        array_map(function(Data $data){
            $index = $this->getIndexByKey($data->key());
            if(is_null($index)){
                $this->data[] = $data;
            } else {
                $this->data[$index] = $data;
            }
        }, $data);
    }

    public function getByKey($key)
    {
        $key = $this->getIndexByKey($key);
        return !is_null($key) ? $this->data[$key] : null;
    }

    public function getValueByKey($key)
    {
        $data = $this->getByKey($key);
        return !is_null($data) ? $data->value() : null;
    }

    private function getIndexByKey($key)
    {
        $result = [];
        array_walk($this->data, function(Data $data, $index) use ($key, &$result){
            if($data->key() == $key){
                $result[] = $index;
            }
        });

        return !empty($result) ? reset($result) : null;
    }
}

interface Transcriber
{
    public function transcribe(array $data): DataCollection;
    public function canTranscribe($value): bool;
}

class StringData extends Data
{
    public function __construct($key, string $value)
    {
        parent::__construct($key, $value);
    }
}

class CompressedData extends Data
{
    public function __construct($key, string $value)
    {
        parent::__construct($key, $value);
    }

    public function value()
    {
        $url_decoded = base64_decode(str_replace(['-','_'],['+','/'],$this->value));
        if(substr(bin2hex($url_decoded), 0, 6) === '1f8b08'){
            return gzdecode($url_decoded);
        } else {
            return $this->value;
        }
    }
}

class FlagData extends Data
{
    private $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function value()
    {
        return $this->value ? ($this->data ?? null) : null;
    }
}

class CallbackData extends Data
{
    private $arguments = [];

    public function __construct($key, callable $value)
    {
        parent::__construct($key, $value);
    }

    public function setArgument($pos, $param)
    {
        $this->arguments[$pos] = $param;
    }

    public function value()
    {
        ksort($this->arguments);
        return \call_user_func_array($this->value, $this->arguments);
    }
}

class DataFactory
{
    private $data;
    private $callbacks;

    private function setData(array $data, string $class, DataCollection $dc = null)
    {
        $dc = $dc ?? new DataCollection;
        array_walk($data, function($value, $key) use($dc, $class){
            $dc->set(new $class($key, $value));
        });
        return $dc;
    }

    public function setStaticData(array $data)
    {
        $this->data = $this->setData($data, StringData::class, $this->data);
    }

    public function setCompressedData(array $data)
    {
        $this->data = $this->setData($data, CompressedData::class, $this->data);
    }

    public function setCallbackData(array $data)
    {
        $this->callbacks = $this->setData($data, CallbackData::class, $this->callbacks);
    }

    public function fromSourceKey($sourceKey, $key, $value)
    {
        $keys = $this->data->getAliases($key);
        $key = reset($keys);
        $data = $this->data->getValueByKey($key);

        switch($sourceKey){
            case 'directives':
                $flag = new FlagData($key, $value);
                if(!is_null($data)){
                    $flag->setData($data);
                }
                return $flag;
            case 'email':
            case 'emailmethod':
                $callback = $this->callbacks->getByKey($key);
                if(!is_null($callback)){
                    $pos = array_search($sourceKey, ['email', 'emailmethod']);
                    $callback->setArgument($pos, $value);
                    $this->callbacks->set($callback);
                    return $callback;
                }
            default:
                return new StringData($key, $value);
        }
    }
}

class DataTranscriber implements Transcriber
{
    private $template;
    private $data;
    private $factory;

    private $transcribingMode = false;

    public function __construct(DataCollection $data, DataFactory $factory)
    {
        $this->data = $data;
        $this->factory = $factory;
    }

    public function canTranscribe($value): bool
    {
        if($value == '<BEGIN>'){
            $this->transcribingMode = true;
            return false;
        }

        if($value == '<END>'){
            $this->transcribingMode = false;
        }

        return $this->transcribingMode;
    }

    public function transcribe(array $body): DataCollection
    {
        $data = $this->collectData($this->data, $body);

        return $data;
    }

    public function collectData(DataCollection $collector, array $array, $parents = []): DataCollection
    {
        foreach($array as $key => $value){
            if($this->canTranscribe($value)){
                $value = $this->parse($key, $value, $parents);
                $parents[] = $key;
                if(is_array($value)){
                    $this->collectData($collector, $value, $parents);
                } else {
                    $data = $this->factory->fromSourceKey($parents[1], $key, $value);
                    if(!is_null($data->value())){
                        $collector->set($data);
                    }
                }
                array_pop($parents);
            }
        }
        return $collector;
    }

    public function parse($key, $value, $parents = [])
    {
        if(is_string($value)){
            if(key($parents) !== NULL){
                $keys = $this->data->getAliases($key);
                if(count($keys) > 1 || $keys[0] !== $key){
                    return \array_fill_keys($keys, $value);
                }
            }

            end($parents);
            if(key($parents) === NULL && false !== strpos($value, '=')){
                list($key, $value) = explode('=', $value, 2);
                return [$key => urldecode($value)];
            }

            if($key === 'directives'){
                return explode(',', $value);
            }

        }

        return $value;
    }
}

interface Template
{
    public function render(DataCollection $data): string;
}

class ArrayTemplate implements Template
{
    public $template;

    public function __construct(array $template = [])
    {
        $this->template = $template;
    }

    public function render(DataCollection $data): string
    {
        $output = array_reduce($this->template, function($output, $key) use($data){
            $output[] = $data->getValueByKey($key) ?? null;
            return $output;
        }, []);
        ksort($output);
        return implode("\n", array_filter($output));
    }
}

class Script
{
    private $client;
    private $transcriber;
    private $template;
    private $templateData;
    private $factory;

    public function __construct(HttpClient $client, Transcriber $transcriber, Template $template, DataCollection $templateData, DataFactory $factory)
    {
        $this->client = $client;
        $this->transcriber = $transcriber;
        $this->template = $template;
        $this->templateData = $templateData;
        $this->factory = $factory;
    }

    public static function run(string $host, int $port, string $script, string $settings = '')
    {
        $client = new ScriptClient($settings);

        $templateData = new DataCollection;
        $templateData->setAliases([
            'doctype'   => 0,
            'head1'     => 1,
            'robots'    => 8,
            'nocollect' => 9,
            'head2'     => 1,
            'top'       => 2,
            'legal'     => 3,
            'style'     => 5,
            'vanity'    => 6,
            'bottom'    => 7,
            'emailCallback' => ['email','emailmethod'],
        ]);

        $factory = new DataFactory;
        $factory->setStaticData([
            'doctype' => '<!DOCTYPE html>',
            'head1'   => '<html><head>',
            'head2'   => '<title>Goodnaturedrevengeheadon</title></head>',
            'top'     => '<body><div align="center">',
            'bottom'  => '</div></body></html>',
        ]);
        $factory->setCompressedData([
            'robots'    => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VSrKT8ovKVZSSM7PK0nNK7FVSsvPyckvV7KzwacoLz8zLyW1QicvP7EoOSOzLFXJDgBSH3-tVQAAAA',
            'nocollect' => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VcrL103NTczM0U3Oz8lJTS7JzM9TUkjOzytJzSuxVdJXsgMAKsBXli0AAAA',
            'legal'     => 'H4sIAAAAAAAAA61aXXPbNhZ931-BcXa8yUziOI4TO8vUM4qtJOq0dtZy0ukjSEISapJgAdKq9tfvuRcAJdkymnY2M5FtEQSB-3HuuQd838m8UqJQVeVaWehm_sPe4R7_3cqyjH_nxpbK0q9n_3jfWfoohetWlfphb2aa7sVM1rpa_VvUpjE0kcr2zt7nZ-_poihMZewPTz7yvzP9_iV9u_PaPF57mZ_tN7lrM-F_hD8emfLR20T6Pv3gvve53fjtZqHEUu0_eXeaOd0pMbP7T169epXVdLEzYmV64fr8N1V0oqPvDF1_m-Hz8DhTVnTK1k7MzZ2iq7-onKeh22RRqPewVBPNWGrXVhImbEyj9s7uZFWp1fuXNOSMbrZKlqJbqFoU0qpZT9_JuWo6euSbTJiZ8A9fqF1bLcJWNQ065N1hMlrou-xA3IQV87Kco6uNSS2vMH3T2ZXTpRLqD4OpbK2LsNzmhV-JlY2b0cyv8CvCbMMGB_T72tiPOUjucNB3evKxKWd_L8Qe3oaBU_prO2SmrSpg43cnWSWs37t3UGd1wTFyabwLXnzuUxaWtpCNEmVf58GstWx2rawMK_umYVhDT3At3GLdc6RtiI7nQtOF1pq5lbVLTVMqf4ueN-ww2Xemlp0u_Kaq1f6T47dZdN-4lhRRh6eZAFzYcK__VE7QkEkntBNWFbTvw9dZo_-rSp65wkYPxK-cDbcNL30hnZACwd8r4c1kOp4lGNFYmTJaq3_vJbZG3-Ur-hSzvhG6oURUpZbIvmCEYNQ5lnx8jNU35Utjh0edZrwEJBVCO5kIVql2RdtTuu3CpGH_QrI3sGjgKCZKuptWbVycQe_0UYzy_SenJ5nKPQRgAbXytupST3CLvuuA9QCjBovRjVhKRIpbxEVzDMMOKfigBx0dZvey9y__2M6ZsSwWopXWo8YqhfC0XZ9V3lMIybnUTYgO0UUc8zhNvsOwp3s_0t8nWUmX32SF_yFD4I4K_2BtPCr6-eHSuXYdWxYzlmLEzuwL_whRyWVAXl6FhbdbdkhTilbZmaGAE36uhmvDTMx47YeoDLKiiBNu2EWzQhJ0OuXA3gHvRW71fAHzyBpPCa4zjeAiZU0_ZxAKq74L-w1ZVpgm4L5fc27uwl7XZq1Trk1DpPt7yLr4XozvNgZuB5DfXMp2pa5UXUsxk5WjOq5UJZtCCUpOfKKkohyH6pWap-lrBSs7QUPySjsnCrhR9sPtLgkXMwksAD2iKZB-HQp5tQEalMIJbAblWNB-a-CabFslvZuFM7X33pIjMO8dPH6zmwi0Ya5JCe5Aw7_5IHHaZ4HxczofIoQuookx8W0y3X9y8jq7uQICHWVTMfqUNLrVbRs5zPV4jClPj0Pinh6iSrx9k13f_Cr8bGI6vryYXH7iAMY3b7IP0_F_vo4v-S7xgQfhnnfZ6JwX9LIrv4-Ifhcg7Qy52zXgHmfj62Tpqfo7jbQuVWtBoQjPAzUMBvh5YAvr4F0uNKCPMPVVZvpU0BMx83jDWMJlKZFMM1NVZkllh7ljauGL3nYCUV0ssOaYA3Qnc8HUnVS_EXrIp8oURV8p2nOH1gFlNmYDQzKgeKisRwTTC-UC8niGzLFGI8AvDsTIp8JKXJrmxWcP3YxLAb99gdZlHxD8KSY7xlzLheHHpMCJ0TMA99yHOratdha9AdDW_tLdwiRpmyNC0BDJKMDcyCIzXXWLFTCiAOvAFzImPNsB7NlbJQaG_3_2ZXw-Gf20hYlIPWotkH_nnAaUGK-z8daY680Y06ZxwteGk0zcDfzQuoMt00pvb_GNSWJTqj-YPYZmZ00i2QUCIMSREZllYLlycwOGGUYwM_c8AarU2pbdQnrEScVxbGlcMtoVb-HQ58ayUiXqJJVXGTys-LGVXyggG49uxNep-OebQ94T6rz1E-R9p_22m8AhZhxSxG2dCxl4ICYIGY-e2Jyf9l9OYBm6Ih6cCpDO9BbPc2LRg9DrbrPHY-SoVsJHhDULnftldf_PqoyBIwbgLz9NzkdboOQBJsE8YblORMLih_e648iwoNmp4lUEuzbUUL_LOkTnAAqH2d4z4RahxUjNkiu00x4ABkR4l3krNeRHtCp3SpxzoLeVVj5g5ymXKEcqCJgwpaewuuuB43kFdox2G9xekw4S3GTT3YBqHCp7aeUcu1tIm-NOZTGaOg_1R1sZh32HudR68ZHPMg_1XOwUAEh4E_ZG_QORN5oI3Xcgy2ARIUL46iI4Z5MTL3Ttuz8XuwSySHgEylNIcy7y2P93dubtdzfcaufI6_H51fXFdFt5YfYSSCssnphsBQtwFlretgKJM00UQLQLGeRtESyLOWc6EONUixPokEh3521r0FVSqOHr3mlS0dBWMqFjdyMWdeGGevp7rypGxm6oiGBphJJODND5eXQjPo-Y6HwbT28mAPvT7NPzjXZpdPmr-Hk8nY4-jVHzDrPps11bMRuF6zPRqxOg-83n8XScQtEbrirX4Cl_g10RqFxebIEMqtQJqtTF5GZydbnD0y50Q8EIRDXvJwQ6llITUiSZl1UlUhi8mjFFWisjqLqDFKcCQ3ahIUpJPkGyYt1DfFgJXyRFEXQcv0Annna-_RNeiPGBFkogEo6vRgzsbLJb0U1hbGsonITMZXErSrNsGETWzQKyF0WaQ7wPfa_hbIjxRTJjj2puRexpvY6IArgEyHVEU4I6d498IPauJ-cP3Hb28er6oRvtv4Y-mNakGd3Xytdhtrt2u3VPI0MhNdaJoE8WVV-q54-yuQgDoFQo90RWfO2wdymzzk2uKt0I03dmBqcv0UZpmBWATy4ciJmKiHEIzpPoCgpTt0H9AvxYMd_iQN5KiGASSQcVAcSQZYKdRS5OzFJaIFiBcyCs1QYOcB1WpJsw6aALB9RevfP9G-jeLI1f2nYGH1hAjWReKgmSGktcJF2W6qbPD0CVLL1JmAh5aY-NRnuoUEcib4pBhjFB95v72lIebITYB2az2-T1pxELW9tMBSz37en2dz9-vZ487KXkegnkjNab3BTr0B9KJV1HIx6EztAkKdsMUYttpuTofMXUvZLcb4PSFx3SH9_sjcpar0lNbDRy1S2pK4hXSX9B74u_djKmiE9TSvgD8SubOnB0esoo2ZQBoNFW8mkFqBkaDw3MllEqeuyZ83Wn-zrrOJAbEowa8ixQdRnkAV5Lsp3E4Lx3CxLBVN0iqVeiqAxJacBpcWeqvo4oVgin7J0uVDy9OHoARVcft1z_jfsfXmRQIrYQ6ezrdDukHtDbw0ztlFhiyfxiTWdwiWk8uSopcDAqWzWoyf65T0MCJYuWkrZSriDGaWAqCUMN8JM8eGnmtlde4y50ufXgPRZ19IyDbCVq2RULJsBpMTmoD7HNCUE7oyRJFt41RcbYmdLo_uVciplSokYEWGOiXUCioxM-jcAzTo6y8fXk8tNOeI18ZArnspODYnScXRGJEZOLoAlNPk7G6_y_-iimY9xw-jabnI8P_hqJeRhp9yLJL74hfg3atZcW812l486VYPrgI0L55GfNI12oWEiqQC-RPUtyI24iwZcqCAJHeyXBrra1-7TOY2qYeU7Ko67lPNClYZmoi5z1LBNw93D4OhNPI-7IxseJp9ti7yboNoS3NJb0u0DGA-AmKPYv_kw14C-gyi1kxUXMETTzceJSo-bUa_feBKpS_3mTamapq1EcDypSwYKqds4XB6AXH_oEecrcg6PLq8sXn7_-PLrcRqXJdHLDbHcTbvgoNR5JhPk2goGG5H3Hhb0xXaRPvkIQWoLVABMSZpTowc16MrJaimkBOjoWpOzWruwgqgXG9haBtOIzM6rr3EcnBQ1pOwTrTDHVNGiMWPDpGyobulxV1XAwHFrNO136p5UMMot-w82e-jjhDymGYy6SoZnzDJ4BcWFD9Tl4cSi3_uDVG7JaiZJNuRNjIoFEi34XGBaCfu7PAj2PIzcdHRFn82eS2lSyuxcO04vJuYeiK-7VHiKGYu0g0V3kdGiDvj1f0fBZYC31cJgvNjNtbScTko7s5ILXOqqj60KXKvNELVJOBUsAv6ETsMoLKtZQSIqqL25XIpfNbQSOvZ0d6FqHtjEDPKWYrtfqSw1CD0VrHcVoWriDAG-mYKXOyQsfwyFW7CoafzK8EPmQ0KRDouS5Qbvz9xrORKvm0pZRXRD3M3u7eR19-Brk1RROy0qKFSZF_OfgwjqW8B1dUogyvzS0AgW9UlP6pnLk-SdMmXjYUhe3CEbV21Vn2iGn_GP0TCwRCL1rNzo9EkgCajaqE0xt-EnSpTvqGVKa6P_MqgalB1SeVCvhWmNuY8kZyDyquD_YuR5dTj96DSF9KtTXNdCNVLIZ4h6pPbAYUj7CEVByDgmW83uvtkxwjm71ZpLkXFRIl8beinahK-MMyAvWkas5AgN4gZ9RsLn56tWQqQDUxyD5C2zC37H3jFogtHx3MdCZUyb1YYx2KPTM3JGkPRPoSudEET0RH6p-6bVnKlp_rqGjzW5Wa_zwSksAzkbZZyIclt8l2f1SAgs6S5KExOeiMRqMZLFyugBlqQH80YIFkOFAfKkQbOqxNj6iUZ5-pwM_KBj7hrCUYKNFU-LfW5Nt16N-1qo2Vq8b-DVK6P1w4h7eOblNH2nNweh9QyvnPebVzUDLB7uhu3YRaekdj9RbXFotWSgkEsdrWBfC2JSGrtPGl022Yel6h3BGrxq0ledjwqd0xzT_OUAgD4IoFci2DwJpvX4zghhI6IPXi4j_O7-9-GZEGwp2lIm6FWd9PC_yJ0rWBfmGrB6AXOrGxUJ0lOmKQ5dPCer9KNUTTIf9EtjAtTsrtRvIDr2gE1QHbyvtto21qQa4nqsIv1fSraKgc7SzKLYDM-Rm9LFojbQrQrlG3SqGQPgTBTHcEvoAUAo2Wmo1agBv-DbQ8IDfqQb2t94XGO3i2y5M83N-YXEgFXe-5IJe0akNzemYaXhYcCLQC_Z24fVNlhJ3GtxHQzj9SvZF8s6s0YuJHhNlevUpWAbALrkTCD04jTl46N7QDo7QEE6uL9bfj4DaJ9n5-AsPYPHoxL80QKfKYGgX99D8Jb_N-pLhBL_gwv8A8_m3JRMrAAA',
            'style'     => 'H4sIAAAAAAAAAyXMywmAMAwA0FUEr36vrXjsHlEjBGMiTQRF3N2Db4A3mN-MIzQTbac5GeP-zMqaQ5lSiquKh0l5KfruuArIBFwZiNWGmdboeHm94KwZnFSCqGB8h_ZvP8YqSXVeAAAA',
            'vanity'    => 'H4sIAAAAAAAAA22S3U7DMAyFX8XKbmEdf5OWdRViGkJIsGnABZdpk7VhWRw53srenrSMG0CRJVuJv3PiJGdVOgOVcS4GVVlfz8RIdGVQWp_KEkkb6rLIR2dmolTVtibcey0Hk8lk2lrNjby8GoXPqShyphQaDsrZ2s8EY_hpPEElXIRPuExxk-I6dX1LnJOtG5YRndX9kcF8Pu-IyZuHE2ODnmWJTkOnB4qscmdR-XgeDdnNtEKHJAfj8XialGXnKWC0bNFLMk6xPZjEvM2zjlrkGes_duGUO7NhAb_MXyXVUVrX37dV0JDZzETDHGSWtW07DIQfpuIGvTkG5CFSnQmonIoxDc9u95FtdGYniqfF091iDct7WK2Xj4v5KzwsnxfvsFq-5pkq8pL-Fdj75H03rHAn_qO-pE14UHQwkQ3BipCTnTQAeDbcIm07bjJ5sNpoKI_w1vN6xX4cWfeEWf83ii-2tdQcIwIAAA',
        ]);
        $factory->setCallbackData([
            'emailCallback' => function($email, $style = null){
                $value = $email;
                $display = 'style="display:' . ['none',' none'][random_int(0,1)] . '"';
                $style = $style ?? random_int(0,5);
                $props[] = "href=\"mailto:$email\"";
        
                $wrap = function($value, $style) use($display){
                    switch($style){
                        case 2: return "<!-- $value -->";
                        case 4: return "<span $display>$value</span>";
                        case 5:
                            $id = 'd3dr';
                            return "<div id=\"$id\">$value</div>\n<script>document.getElementById('$id').innerHTML = '';</script>";
                        default: return $value;
                    }
                };
        
                switch($style){
                    case 0: $value = ''; break;
                    case 3: $value = $wrap($email, 2); break;
                    case 1: $props[] = $display; break;
                }
        
                $props = implode(' ', $props);
                $link = "<a $props>$value</a>";
        
                return $wrap($link, $style);
            }
        ]);

        $transcriber = new DataTranscriber($templateData, $factory);

        $template = new ArrayTemplate([
            'doctype',
            'injDocType',
            'head1',
            'injHead1HTMLMsg',
            'robots',
            'injRobotHTMLMsg',
            'nocollect',
            'injNoCollectHTMLMsg',
            'head2',
            'injHead2HTMLMsg',
            'top',
            'injTopHTMLMsg',
            'actMsg',
            'errMsg',
            'customMsg',
            'legal',
            'injLegalHTMLMsg',
            'altLegalMsg',
            'emailCallback',
            'injEmailHTMLMsg',
            'style',
            'injStyleHTMLMsg',
            'vanity',
            'injVanityHTMLMsg',
            'altVanityMsg',
            'bottom',
            'injBottomHTMLMsg',
        ]);

        $hp = new Script($client, $transcriber, $template, $templateData, $factory);
        $hp->handle($host, $port, $script);
    }

    public function handle($host, $port, $script)
    {
        $data = [
            'tag1' => '9ca8449456de0d8818ce7b1b19f81df2',
            'tag2' => 'eac049e648f232f44ea53902140e86f6',
            'tag3' => '3649d4e9bcfd3422fb4f9d22ae0a2a91',
            'tag4' => md5_file(__FILE__),
            'version' => "php-".phpversion(),
            'ip'      => $_SERVER['REMOTE_ADDR'],
            'svrn'    => $_SERVER['SERVER_NAME'],
            'svp'     => $_SERVER['SERVER_PORT'],
            'sn'      => $_SERVER['SCRIPT_NAME']     ?? '',
            'svip'    => $_SERVER['SERVER_ADDR']     ?? '',
            'rquri'   => $_SERVER['REQUEST_URI']     ?? '',
            'phpself' => $_SERVER['PHP_SELF']        ?? '',
            'ref'     => $_SERVER['HTTP_REFERER']    ?? '',
            'uagnt'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        $headers = [
            "User-Agent: PHPot {$data['tag2']}",
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-store, no-cache",
            "Accept: */*",
            "Pragma: no-cache",
        ];

        $subResponse = $this->client->request("POST", "http://$host:$port/$script", $headers, $data);
        $data = $this->transcriber->transcribe($subResponse->getLines());
        $response = new TextResponse($this->template->render($data));

        $this->serve($response);
    }

    public function serve(Response $response)
    {
        header("Cache-Control: no-store, no-cache");
        header("Pragma: no-cache");

        print $response->getBody();
    }
}

Script::run(__REQUEST_HOST, __REQUEST_PORT, __REQUEST_SCRIPT, __DIR__ . '/phpot_settings.php');

