<?php


namespace App\Service;

use App\Service\Src\Scanner;
use App\Service\Src\Parser;
use App\Service\Src\Document;
use App\Service\Src\Node\Node;
use App\Service\Src\Node\BlockNode;
use App\Service\Src\Node\CharNode;
use App\Service\Src\Node\CtrlWordNode;
use App\Service\Src\Node\ParNode;
use App\Service\Src\Node\TextNode;

class RtfParser
{

    //https://github.com/tyru/php-rtf-parser

    public function extractText(string $filename, array $config)
    {
        // Read the data from the input file.
        $text = file_get_contents($filename);
        if (empty($text)) {
            return '';
        }

        $scanner = new Scanner($text);
        $parser  = new Parser($scanner);
        $text    = '';
        $doc     = $parser->parse();
        foreach ($doc->childNodes() as $node) {
            $text .= $node->text();
        }

        if ($config['input_encoding'] === 'guess') {
            $config['input_encoding'] = $doc->getEncoding();
            if (is_null($config['input_encoding'])) {
                $config['input_encoding'] = 'utf-8';
            }
        }
        if ($config['input_encoding'] !== $config['output_encoding']) {
            $text = mb_convert_encoding($text, $config['output_encoding'], $config['input_encoding']);
        }
        return $text;
    }

    public function getConfig()
    {
        if (preg_match('/^Windows/', php_uname('s'))) {
            // TODO: Get current code page of output_encoding.
            // 'cp932' is default code page of Japanese version.
            return [
                'input_encoding'  => 'guess',
                'output_encoding' => 'cp932',
            ];
        }
        // Detect encoding from LANG environment variable
        $lang    = getenv('LANG');
        $matches = null;
        if (is_string($lang) && preg_match('/\.(.+)$/', $lang, $matches)) {
            $out = $matches[1];
        } else {
            $out = 'utf-8';
        }
        return [
            'input_encoding'  => 'guess',
            'output_encoding' => $out,
        ];
    }

    public function parseArgs(array $argv)
    {

        $opts = getopt('i:o:f:', []);
        if (!isset($opts['f']) || !is_string($opts['f'])) {
            return [$argv[0], null, []];
        }
        $config = $this->getConfig();
        if (isset($opts['i']) && is_string($opts['i'])) {
            $config['input_encoding'] = $opts['i'];
        }
        if (isset($opts['o']) && is_string($opts['o'])) {
            $config['output_encoding'] = $opts['o'];
        }
        return [$argv[0], $opts['f'], $config];
    }

    /**
     * @param $filename
     * @param $config
     */
    public function main($filename, $config)
    {
        /*
        [$script, $filename, $config] = $this->parseArgs($argv);
        if (is_null($filename)) {
            echo "Usage: $script [-i <input encoding>] [-o <output encoding>] -f <file.rtf>\n";
            return;
        }*/
        return $this->extractText($filename, $config);
    }

}
