<?php

$out_filename = filter_input(INPUT_GET, 'out_filename', FILTER_SANITIZE_STRING);

try {
    if (is_null($out_filename) || $out_filename === false) {
        $status_code = 400;
        throw new Exception('Missing required input fields', 100);
    }

    http_response_code(200);
    if (file_exists($out_filename)) {
        // minor CSS adjustments
        $html = trim(file_get_contents($out_filename));
        $html = preg_replace('/(.*)(<\/title>)(.*)/', '\\1\\2' . "\n\t" . '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">' . '\\3', $html);
        $html = preg_replace('/(.*)(<body)(.*)/', '\\1\\2' . ' style="font-size: 12px;"' . '\\3', $html);
        file_put_contents($out_filename, $html);

        echo json_encode([
            'data' => [
                'finish' => true,
            ],
            'code' => 201,
            'message' => 'Success diffing files',
        ]);
    } else {
        echo json_encode([
            'data' => [
                'finish' => false,
            ],
            'code' => 200,
            'message' => 'Running diff-tool, please wait...',
        ]);
    }
} catch (Exception $e) {
    // return error message
    http_response_code($status_code);
    echo json_encode([
        'error' => [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ],
    ]);
}
