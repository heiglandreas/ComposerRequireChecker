<?php


class Parser 
{

    public $result = array();
    
    function getLinesFromSource($expression, $path) :array
    {
        exec(sprintf(
            'grep -r "%1$s" "%2$s"',
            $expression,
            $path
            ), $result, $retVal);
    
        return $result;
    }

    function parseLines(array $lines) 
    {
        foreach($lines as $line) {
            $result = $this->parseLine($line);
            if (! $result) {
                    continue;
            }
            $this->result[$result['symbol']] = $result['package'];
        }
    }
    
    protected function parseLine($line)
    {
        $line = explode(':', $line);
        $package = explode(DIRECTORY_SEPARATOR, $line[0]);
        
        $package = $package[1];
        
        $symbol = explode('(', $line[1]);
        
        switch (trim($symbol[0])) {
            case 'INIT_CLASS_ENTRY':
                $f = explode(',', $symbol[1]);
                $symbol = str_Replace('"', '', trim($f[1]));
                break;
            case 'static PHP_FUNCTION':
            case 'PHP_FUNCTION':
                $symbol = str_replace(')', '', $symbol[1]);
                $symbol = str_Replace(';', '', $symbol);
                break;
        }
        if (is_Array($symbol)) {
            return array();
        }
        
        return ['symbol' => $symbol, 'package' => $package];
    }
    
    
}

$parser = new Parser();

$parser->parseLines($parser->getLinesFromSource('INIT_CLASS_ENTRY', 'ext'));
$parser->parseLines($parser->getLinesFromSource('PHP_FUNCTION', 'ext'));

echo '<?php' . "\n" . 'return [' . "\n";
foreach ($parser->result as $key => $value) {
    echo '    "' . $key . '" => "' . $value . '"' . "\n";
}

echo '];' . "\n";

