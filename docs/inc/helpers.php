<?php

use Symfony\Component\Yaml\Yaml;
use WPDev\Docs\ClassParser;

function wrap_function_calls_in_code_tags($string)
{
    return preg_replace('/[a-z_A-Z]+\([^\)]*\)(\.[^\)]*\))?/', '<code>$0</code>', $string);
}

function generate_yaml_file($model, $folder_to_save_into)
{
    $class = new ClassParser($model);

    $data = [
        'name'      => $class->getName(),
        'shortName' => $class->getShortName(),
        'namespace' => $class->getNamespaceName(),
        'methods'   => [],
    ];

    $methods = $class->getPublicMethods();
    foreach ($methods as $method) {
        $method_data = [
            'name'        => $method->getName(),
            'summary'     => wrap_function_calls_in_code_tags($method->getSummary()),
            'description' => wrap_function_calls_in_code_tags($method->getDescription()),
            'visibility'  => $method->getVisibility(),
        ];

        if ($method->isStatic()) {
            $method_data['static'] = true;
        }

        if ($method->hasReturnTag()) {
            $method_data['return'] = [
                'type'        => $method->getReturnType(),
                'description' => wrap_function_calls_in_code_tags($method->getReturnDescription()),
            ];
        }

        if ($method->getNumberOfParameters()) {
            $method_data['args'] = $method->getParameters();

            foreach ($method_data['args'] as $arg) {
                if ( ! empty($arg['description'])) {
                    $arg['description'] = wrap_function_calls_in_code_tags($arg['description']);
                }
            }
        }

        $data['methods'][] = $method_data;
    }

    $yaml = Yaml::dump($data, 5);

    $filename = $folder_to_save_into.basename($class->getFileName(), '.php').'.yaml';
    file_put_contents($filename, $yaml);
}