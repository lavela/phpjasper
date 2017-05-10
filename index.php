<?PHP

require __DIR__ . '/vendor/autoload.php';

use JasperPHP\JasperPHP;
/*
$input = __DIR__ . '/examples/hello_world_xml.jrxml';
$output = __DIR__ . '/';

$jasper = new JasperPHP;

$jasper->process(
    $input,
    $output,
    array('pdf'),
    array(),
    array(
        'data_file' => __DIR__ . '/examples/xmlExample.xml',
        'driver' => 'xml',
        'xml_xpath' => '/CancelResponse/CancelResult/ID'
    )
)->execute();*/

$input = __DIR__ . '/examples/hello_world_json.jrxml';
$output = __DIR__ . '/';

$jasper = new JasperPHP;

$jasper->process(
    $input,
    $output,
    array('pdf'),
    array(),
    array(
        'driver' => 'json',
        'json_query' => 'contacts.person',
        'data_file' => __DIR__ . '/examples/jsonExample.json'
    )
)->execute();