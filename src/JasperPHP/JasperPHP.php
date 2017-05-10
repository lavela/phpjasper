<?php

namespace JasperPHP;

class JasperPHP
{
    private $executable; //executable jasperstarter
    private $path_executable;
    private $the_command;
    private $resource;
    private $formats;
    private $input_file;
    private $output_file;
    private $db_connection;

    function __construct()
    {
        $this->setExecutable('jasperstarter');
        $this->setPathExecutable(); //Path to executable
        $this->formats = [
            'pdf', 'rtf', 'xls', 'xlsx', 'docx',
            'odt', 'ods', 'pptx', 'csv', 'html',
            'xhtml', 'xml', 'jrprint'
        ];
        $this->setResource();
    }

    public static function __callStatic($method, $parameters)
    {
        // Create a new instance of the called class, in this case it is Post
        $model = get_called_class();

        // Call the requested method on the newly created object
        return call_user_func_array(array(new $model, $method), $parameters);
    }

    public function compile($input_file, $output_file = false)
    {
        $this->setInputFile($input_file);
        $this->output_file = $output_file;

        $this->the_command = $this->generateCommand('compile');

        return $this;
    }

    public function process($input_file, $output_file = false, $format = array('pdf'), $parameters = array(), $db_connection = array())
    {
        $this->setInputFile($input_file);
        $this->output_file = $output_file;
        $this->db_connection = $db_connection;

        $this->the_command = $this->generateCommand('process', $format, $parameters);

        return $this;
    }

    public function list_parameters($input_file)
    {
        $this->setInputFile($input_file);

        $this->the_command = $this->generateCommand('list_parameters');

        return $this;
    }

    public function output()
    {
        return $this->the_command;
    }

    public function execute($run_as_user = false)
    {
        $output = array();
        $return_var = 0;

        if (is_dir($this->path_executable)){
            chdir($this->path_executable);
            exec($this->the_command, $output, $return_var);

            if($return_var)
                throw new \Exception("{$output[0]}", 1);
        } else {
            throw new \Exception('Invalid resource directory.', 1);
        }



        return $output;
    }

    public function generateCommand($type, $array_format = false, $array_parameter = false, $run_as_user = false){
        $formats = function($array_format){
            if( is_array($array_format)) {
                foreach ($array_format as $key) {
                    if (!in_array($key, $this->formats))
                        throw new \Exception('Invalid format!', 1);
                }
            }
            else {
                if (!in_array($array_format, $this->formats))
                    throw new \Exception('Invalid format!', 1);
            }

            return is_array($array_format) ? implode(' ', $array_format) : $array_format;
        };

        $parameter = function($array_parameter){
            $params = null;

            if(is_array($array_parameter))
            {
                foreach ($array_parameter as $key => $value)
                {
                    $param = $key . '="' . $value . '" ';
                    $params .= " " .$param. " ";
                }

            }

            return !isset($params) ?: $params;
        };

        $command = [
            'process' => [
                'executable' => $this->executable,
                'input_file' => "process \"{$this->input_file}\"",
                'output_file' => (!empty($this->output_file)) ? "-o \"{$this->output_file}\"" : NULL,
                'parameter' => (count($array_parameter) > 0) ?  "-P {$parameter($array_parameter)}" : NULL,
                'format' => "-f {$formats($array_format)}",
                'resource' => (!empty($this->resource)) ? "-r {$this->resource}" : NULL,
                'connection' => (!empty($this->db_connection)) ? $this->generateCommandConnection() : NULL,
                'run_as_user' => ($run_as_user && !$this->isWindows()) ? "su -u  {$run_as_user}  -c \"{$this->the_command}\"" : NULL,
                'output_shell' => '2>&1'
            ],
            'compile' => [
                'executable' => $this->executable,
                'input_file' => "compile \"{$this->input_file}\"",
                'output_file' => (!empty($this->output_file)) ? " -o \"{$this->output_file}\"" : NULL,
                'output_console' => '2>&1'
            ],
            'list_parameters' => [
                'list_parameters' => $this->executable,
                'input_file' => "list_parameters \"{$this->input_file}\"",
                'output_shell' => '2>&1'
            ]
        ];

        return implode(' ', array_filter($command[$type]));

    }

    public function setPathExecutable($path_executable = false){
        if($path_executable) {
            if (!file_exists($path_executable))
                throw new \Exception('Invalid executable directory.', 1);

            $this->path_executable = $path_executable;
        } else {
            $path_executable = __DIR__ . '/../JasperStarter/bin';
            $this->setPathExecutable($path_executable);
        }

    }

    public function setResource($resource = false){
        //resource dir or file
        if ($resource) {
            if (!file_exists($resource))
                throw new \Exception('Invalid resource directory.', 1);

            $this->resource = $resource;
        } else {
            $resource = __DIR__ . '/../JasperStarter/lib';
            $this->setResource($resource);
        }
    }

    public function setExecutable($executable){
        $this->executable = ($this->isWindows()) ? "{$executable}" : "./{$executable}";
    }

    public function setInputFile($input_file){
        if(is_null($input_file) || empty($input_file))
            throw new \Exception('No input file', 1);

        $this->input_file = $input_file;
    }

    public function generateCommandConnection(){
        if (count($this->db_connection) > 0) {
            if(!isset($this->db_connection['driver']))
                throw new \Exception('Define drive the connection.', 1);

            $connection = [
                'driver' => " -t {$this->db_connection['driver']}",
                'username' => isset($this->db_connection['username']) ? " -u \"{$this->db_connection['username']}\"" : NULL,
                'password' => isset($this->db_connection['password']) ? " -p \"{$this->db_connection['password']}\"" : NULL,
                'host' => isset($this->db_connection['host']) ? " -H \"{$this->db_connection['host']}\"" : NULL,
                'database' => isset($this->db_connection['database']) ? " -n \"{$this->db_connection['database']}\"" : NULL,
                'port' => isset($this->db_connection['port']) ? " --db-port \"{$this->db_connection['port']}\"" : NULL,
                'jdbc_driver' => isset($this->db_connection['jdbc_driver']) ? " --db-driver \"{$this->db_connection['jdbc_driver']}\"" : NULL,
                'jdbc_url' => isset($this->db_connection['jdbc_url']) ? " --db-url \"{$this->db_connection['jdbc_url']}\"" : NULL,
                'jdbc_dir' => isset($this->db_connection['jdbc_dir']) ? " --jdbc-dir \"{$this->db_connection['jdbc_dir']}\"" : NULL,
                'db_sid' => isset($this->db_connection['db_sid']) ? " --db-sid \"{$this->db_connection['db_sid']}\"" : NULL,
                'xml_xpath' => isset($this->db_connection['xml_xpath']) ? " --xml-xpath \"{$this->db_connection['xml_xpath']}\"" : NULL,
                'data_file' => isset($this->db_connection['data_file']) ? " --data-file \"{$this->db_connection['data_file']}\"" : NULL,
                'json_query' => isset($this->db_connection['json_query']) ? " --json-query \"{$this->db_connection['json_query']}\"" : NULL
            ];

            return implode(' ', $connection);
        }

        return false;

    }

    public function isWindows(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            return true;
        return false;
    }

}
