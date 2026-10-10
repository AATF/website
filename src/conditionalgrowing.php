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
//  Generated On: Fri, 26 Nov 2021 05:11:01 -0500
//  For Domain: aatf.us
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

define('__REQUEST_HOST', 'hpr9.projecthoneypot.org');
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
            'head2'   => '<title>http://aatf.us</title></head>',
            'top'     => '<body><div align="center">',
            'bottom'  => '</div></body></html>',
        ]);
        $factory->setCompressedData([
            'robots'    => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VSrKT8ovKVZSSM7PK0nNK7FVystPLErOyCxL1cnLz8xLSa3QScvPyckvV7IDANCDo6c3AAAA',
            'nocollect' => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VcrL103NTczM0U3Oz8lJTS7JzM9TUkjOzytJzSuxVdJXsgMAKsBXli0AAAA',
            'legal'     => 'H4sIAAAAAAAAA6VbbXPbuK7-fn8Fb3qnp51J06Tbps1VT2e8idP6zm7aid3t7EdKom02kuhDUvbx_voLAqBFOYnt7PngKpItvgAPgAcA-9HLvFIiN7ZU9p9Hp0eiUFW1kGWpm9nm3i1kwfefPnr76b8--vLTx6lpvJjKQv3zqDCt1coefXre5G6RiY_5p4_wUiOcX1f4fWXs_z67vr7Ojj7dfXwdvtvxCx1_8RrGyS2PGv6azJUIV__82dnZWSbW4caEm_NsrsLND5W7cLVKluEq4cvTXzIVfnOKY_h5eHSW1eGmMc2r9LVuOrHZS7qA8UIV4XpjmufP3r7DZ26hcaqFNbM45dn7jFZl4qjDGpdCr4y80JvVfchME27m0uFszhsbrktZtTg7DPQuDoffyJlVh6x2KIswwduMXpI454uj58_ev83aKEf4Wj2ui0XURVhYW8xpQRe0vdOsPOxNMxVTXEOzxiWOlV2muhD93TwJRvdA8ieBo8VBa0kzyrxFBf-hXacG2syH82w0HoW_vz1_dv4uu4VHJLLxd3hwno3xt68D4OEC2H_cAHYuuUyWvLXRuJazs4tM5aRxFFwrXPzmbSY8iYufwC78x_9-9Uo4JWct2KnQTWkqBcvSjde19ArWYeEyW4tXrz4lsubpE8PxPOne1SOiT8QEoeAtgqFJVqROHodm9zN8rXp8NpegB_D6JiOjqmV4oKw7RiOXtSMlex2HP2BM8gxOkBGpE_EnIkQsJdmJwsGVMIhZ8Hy4dlUnTiRF3A6NF_c0fjBA-rMsJCEAfZbzuKBSFx2ULQIhBxRMdVPMlRMLq_9Sojatn3uDl4ABNdPdqJVcoeNSNDhuV5U4kge_H3auat0EQBVmFi4zI-fgWXGkbkWyIPnrgpQvc7OM6z_YiB-0iMKQzN3Gsb7JFlFtE0SgsRvPPQGkXKD9_kKeQHy9RT8IT86y78PD7fhh6GpP4YewmP_EhdiaLAe_WRt2O6JAME0F2xghSmLwcFNlO_E8ZidWOY_qFuiiErWB7UDM8N2DUrGTuLjIZFXFEUpLz0F3DTmT_TEXV121OyJCzzJxetmgQT3-iudXPLuaucZ1-cffkPxG510eF5RfC0nDknY4qIoByprkTwJnRUm8MTb8DtwtvlPRGKYR-e69RMhylCEosHaJa-xeLVjRAf4iLMmKLg7EODlKpD6z_ZkGn293L33Ko48hql1kN-HHNxPx639qFVNLokBCVZC8BaJzZpYk3aIgU1HTlkRFjquJb8DWHJk40NH7prHTh6iHfOwYnZicTgHK6L2aAq4QCjVouG2A7a6AGOSAXNk0Mriz1OdaXeD6nfamw80xcTY9I5eE61xvgp0DW9vv2MP7fwXPeUpxpLijwXCahpksSva14ZhPgxM8o_1_eB8YLXiBGFBPM6H-vRt4wBk79mBQPkvjlRULXempsXWQAlso-HNDwYCsCKZfoEcRCnXWlCktMRvCy-inmQuitVapeo8BM5tHJ2lqhUGpDIYSpsaVmtyBAhXGHbHxB8PhflhgkLga3XwO19vh4PI_hTvdrog5IyKM8B3Ntk2C7YUnQ6jIJa0hZzjHX5V6mTh0q2epdgMJ1D41A0D0t-HlKMUoAJkSHXciSCUEgFJ4k75Ksm1EEi78HMG0qhT67UqR7ZWanSXeEA1wK6CUFYAEBG87-DjNbhU3yWHmcQWz3_WoYJsaf3P0krgM-WJNUqs0xQlN-KIboLHoCT2ZgmN80KIav4d1yiWNPVfE-vSU5jBedJsOny-DSY8hnweu-v5Dhhzxi0CGcf1UDO10YIt7DqyjP4QrynPJa74hXzFjLjFXJFy12_XPeI5Bg0OVraxImDuclkqc1hz44xYgB7_1PO5v90kruFrS0PuQ4mKezaSR6DvHMaQtRTo68ANK8xkys7D1Cwqz-8Mbw9vqHIONcwSRLpj-wwkUlwtm-SbJPneqSe7jqpK9d-Kp2QW6Oac7AXiQkSVem4w1LmSVvMpZLO5fEnjZfI3YUsV-5tXD1oGb3AQOsTaJUjkJOijvt4KYnhHtxkV8oST7j27I34fj7mby5ck0vdvYJF03UHEZJf0hqwwJN4CNHd7je5hHKBF0bLe8G9LoC0d-a2Zlqpm52tLM6HJ4k65J7ud-EBgwAbOyVLpBahKk1tYJrqzJN9b0jpF9nE7NRY6kXqCc4jhBUmnEd5ynMKX42UIuqYEDLyrpTaML8PtK-TD3mBwx7fpEjPBaS12R5zfzg2AYs4sBUJfTDP9JpQJOaa9YNnwjMK-w8CD6sjYgc1uthdPNHeRcNuAirJsIXCVyIqBeL1OnDsZI7FlDLoJAJduNBTiiIqoi8RVbSr2FfPL8ffYVl_GvVhd3kKoJZ-qc5k631tr9KENSH5MiWmJLO9Xk9_1GysPxBH89HnxGMxkPKQs-uDo1pDqBktYJWecaVNu6wI895fmyKoC0h13c_j5ON1Kw5USLescVwNm9ehK5p5gVI9heYQmnpSAfFXmRUXSj33vgmt1I6t-LLaFT0rIfJ7fpoiOBb8QfXamEuO9CBgODC9Af2LuGYa1pZ3Ph2roGPc7lYoFVM58GK0vYW6mq8rqGJS-sNlbMKumc8KpeGAtmhLW2GK96IVEmJEqlFeEZFXDAopbK5sEWp5VcoWKszMPogfruifIRUP_z7jRx2FOWb5NQQEnhB_mfzlOyGcL1eTa6HPRQ3EFdqAqyJxFSugV1D6T32rclgkb74w4gMf5HbEB2wLKf68o4s5ivRWGNuRN3Si3IdpaE-EtKP0jajdKzeQ4gDv6p0KWogD03wIDaWes8yAJdgYYRwwi1Io2T0aw0XXVN2Zu3ptlC1u3VOGUy7JK8Srz-glxGePCvVlFAoWBO-A6fGxDcGSSJGB7GFB6GwYieGM0Ctbq56rGp6OUBOGQkAIuSnEZXkAJtCvRUkitG_EPULXCGdfzh203862QwxJrZpGc89h87yKFJ-wIcbBKWUFiUS3kses6zx4oqISnJsEo6iDqg0UrmYEDe2HWMe72M5UT8aXjbHZuD2ER8j3fFcjpJdodV_N-G97WcMlCfwkXZhmDkiWY3u4iyTGShxICMilRCaVezvzJQ4MZC5N3SzNfrHs3-oxc3FVWCAz1-e0o7WAsiCmXZ9Yg-DybogMPfVCOlqEGjfr0WfwOjlzhOj9xACEvUwiWEH1So2ITvs9g5omJKxE4HTlMRZdxRrHSb6mAsvnFSbvWW9EaXk17WXGmuRDlyB5AD225l9aL3Pq-txDdK9txUuRQKhdyKaWzRheocNRYqQcpUPEeynN8GP-6DUPf8JEQTnE7kazSOWi4ENmBDnHahjA_W4qN57KglxnToBco2Vx75bx2BGZe9s_sXrRyLQE1DvU_O4Zb3ZD0e9fj3N0s1avBRR7jWIs4XZOuoI4heEpzPm2x0E_7-MqQa53hIGcLBxOZqxHQWmA2Gor7iezXTxmEhRhBOqWfhY8xC8FligscQY_CFkmDimVBvh5CvNw-EEO5cMO0uqg6t7pgIJgTPXq3GMgdyiqYv5pSoWk4ssKCPZRmITtieqYEZaOBrIAnXJQuc8ZC6nEiqKMmaBxCwzj9k99cNCETyJa3ekyaZNE3iGaK2SVCKoD32GEARDmVNyWxMKootIQRx3vZ83vdeVgcrCNcRMZikJxcrX8aKHvv6fCwwCF8NbzadXcjLTp4Erwc1rBSRjclcJdz9V-o4H1AOfhFj88b9nWdzckUJ-WDldxT7TdbvH8HyrvsiY39siJ2UilgJks-zD6QnIK0yHQSYJ2md0cLdc0r0gO_NUmD6LgM466r48Pds2zL-7_stGWa7bCsJtFqXSobae2hvGS7Iq0bPaikavJOVZv-2RYHeMnt0BXB2qgWTM9cNkcvEi3Jnc0AgqfW9G3USSNJFEjzGPkmwV7qrCk6BlW_tatzjEp8GD3iakgiapppdR_Tp67UhvwJy6LYI2Px6Sx2eZ-9_ya5Hw82UB8P0-nHLIUvhItULFEJ6oGCpnU4NGK4_yH_QoZP9cJ50hTayslrfI5o3RBteoSZ181MViICpbkAWgEG3CE8oA-2ln0VC-fLWc6KeNKFDIaDnTLXyDOzt3J90YxWB3LUdQwHHjCjQf1FtPFk5MuSr0WW6KlWSL5taU3dzGNI5-X2B73End4xX0xIrhsiDv5mLnLI_VeVmBSuogmprXWlpo0s3sU22Lc-rx5EYGTEL6eglmTKCC5etJMl0439vPpOnuBWXX5-MvfEDBPuInZetcYNToC56psPBlGnVurkoZA5hS1eVmkHK61rOAt0mqznPmLCg_5koi3KOR2Q6s9rfsDXE7GNdgnxJLv0qxFGWcqRiiYC_fP-9J1SXdvSlZQgdizT56aeY6HZjmVmXnJ-kCuL4KSkcyKbzPXaZqJ09i9te4mT0tcfDZyLvzKIXQzYtcq-O-HrA4Yycz9gFMhNu_uSGH-Uekg6FxeM0ybp-_U6Q6HtGS7RkP7vX08PST-BCHQloNnnf5HaAjPKGS2ZPwXK63h8qT1VND1-Q3Ts-IxQOWqEw7EpTyECrVxHFZ9lUbPdPbvp5XY-tguul9DPtCO-oJMbiFxWjY6knfnTjEwYxNY9xC77R1KyoNsh8lzXUnwzPRZ_3b1FHarvnpm3CCVKAxLRiD19Y1QS7zyXm9i1n_gWYAAb98HA274acJKV358k0XoqpIbvZNv-GG62980JAVqhXJm25Y82WErxV0CGmEMCsNXAMUesaEnK_glgOTL3S3lcqegrFivmWDjxmJxeIDA3gwvlZ2aiQvNU6jJNbcPE_YSxl12IpZ62icuH702yI5zcm3ydPjvvsmrRnPtQR4HhCh1xQ92RMwXLOx4W4iSjqeBPyO24w3qPn4xFxlRSxeZrIV5rbueFmLdKjShAp09G4MU4ugX4xT8vEZIEEhTz1sDUNTVWEY-ayppJbVsZEOCTTwfczILCTGoxyj1ndbTqK4YfxREKv3R9q5mTxXGfk04Lbn_KETlK0QHAI-qDDAC_GE-TKPcHc8omF8QE93Zu_B5ejl1Sf4FKwTrRUkyNZ6qLjh_RNzg_2dxyDzyRW08zkDDKl0F1ovWnCYUQsZ99R1qT4QNaqYckop6SF3HdlwxGd4q5dAE9onayS7L4LcAkwsY5Ap_8OPjfMK1hE4J9nnIQDneSjfH6ezuPbrpkLcyxTy1voCoFY66bzTfGQ86bt2yVOBx2y2_6QIlxbEO7GiQe3XDICLl3IWlUOkiVjF9roEh6A5FZK3VXogKPs6TARboIP9-zTcGzw_sQUJmavsb8Yz4JufyDLowNinqoLIY0_qKLa3_bkaxKFB5fYiPub3hLWgX1Es2qCyACjNvRddC1nmlJRPPWCVs28sGFNviT7OYnF-LRz51QixM1j7sJsfxbk_6hi4M0x896Li8zUC1LQjwcOxC3Ii2oKWE1ooWDzJfWQRAV0kqM7LlOAj8IujblHSugz36_7BZXcOUZATs_ICXlUEgh8mhFSIkDfupJMjMwn0nrTPO-O0m5_pK7wORcBfIix2J9LSkCyJGfWSwQHYvIlXIfYd54M_h5czJL-l0csqjBlSipf37hdvDl0EErHCdfitHCjyeTw_faHYsGKmGfLrSjOeZLKxAZFylJkoqPQno-L0ImAA6Jbr74UDtySdrR7aG3W02kkhL6KAVXSETJBM-vCd_5PNS37FyLL4fjlgwouSyqyajqcBMzwJP1-dHsVLjeX2AnuMPIkNVJaT6egcFFJP5a6UaWahcoAx-VDP70dJadC08evjd03zKYmzqXMptMMSRY18OACtkstiQHcXD0srNf4n7leYxsX_oDv_x8dL1uMETYAAA',
            'style'     => 'H4sIAAAAAAAAAyXMwQnDMAwAwFUM_SZN-rVDn95DsZUiEJKxFHAo2b2P3gC3mV-Mb3g2Knxa04of-hZl7fGRc06HisdduYbX2kaATsCTgdhs2OlIjsPnikU7OKlEUcF0b8v__QEe3lsxXwAAAA',
            'vanity'    => 'H4sIAAAAAAAAA22SUU_CMBDHv8qlvApDVBLKWIwEY0wUgvrgY7eWrVp7zfVg8u3tJr6oaS65S3u__7_X5qxKZ6AyzsWgKuvrhRiLrgxK61NZImlDXRb56MxClKp6rwn3XsvBbDabt1ZzIycX4_A5F0XOlELDQTlb-4VgDD-NJ6iE8_AJkxRXKS5T17fEkGzdsIzorO6PDJbLZUdM3jycGDv0LEt0Gjo9UGSVO4vKx2E0ZHfzCh2SHEyn03lSlp2ngNGyRS_JOMX2YBLzOs86apFnrP_YhVPuzI4F_DJ_kVTHaV1-31ZBQ2a3EA1zkFnWtu0oEL6Zihv05hiQR0h1JqByKsaEsZXbx4Da1FYUD6uHm9UW1rew2a7vV8tnuFs_rl5hs37OM1XkJf2rsPfJ_Meowg_xL_Yp7cKdooOJbAg2hJwMpRHAo-EW6b0DJ5sHq42G8ggvPbCX7AeSdY-Y9b-j-AJXrBPaJQIAAA',
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
                            $id = 'ph54olechepr';
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
            'tag2' => 'af8d5dda543933acb2a778ece1c00ace',
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

