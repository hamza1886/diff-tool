<?php

$out_filename = filter_input(INPUT_GET, 'out_filename', FILTER_SANITIZE_STRING);

try {
    if (is_null($out_filename) || $out_filename === false) {
        $status_code = 400;
        throw new Exception('Missing required input fields', 100);
    }

    http_response_code(200);
    if (file_exists($out_filename)) {
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
