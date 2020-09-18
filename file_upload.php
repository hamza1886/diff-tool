<?php
header('Content-Type: application/json');

define('MAX_FILE_SIZE', 100 * 1024);
define('ALLOWED_MIMETYPES', ['text/plain']);
define('UPLOAD_DIR', 'upload');
define('OUTPUT_DIR', 'output');

$status_code = 200;

try {
    if (!isset($_FILES['old_file']) || $_FILES['old_file']['name'] === '' ||
        !isset($_FILES['new_file']) || $_FILES['new_file']['name'] === '') {
        $status_code = 400;
        throw new Exception('Missing required input fields', 100);
    }

    // check error while uploading file
    if ($_FILES['old_file']['error'] !== 0 || $_FILES['new_file']['error'] !== 0) {
        $status_code = 400;
        throw new Exception('Error uploading files, please try again', 101);
    }

    $old_file_name = $_FILES['old_file']['name'];
    $old_file_tmp_name = $_FILES['old_file']['tmp_name'];
    $old_file_size = $_FILES['old_file']['size'];
    $old_file_type = $_FILES['old_file']['type'];

    $new_file_name = $_FILES['new_file']['name'];
    $new_file_tmp_name = $_FILES['new_file']['tmp_name'];
    $new_file_size = $_FILES['new_file']['size'];
    $new_file_type = $_FILES['new_file']['type'];

    // check if empty file is uploaded
    if ($old_file_size === 0 || $new_file_size === 0) {
        $status_code = 400;
        throw new Exception('Cannot upload empty file', 103);
    }

    // check filesize does not exceed MAX_FILE_SIZE
    if ($old_file_size > MAX_FILE_SIZE || $new_file_size > MAX_FILE_SIZE) {
        $status_code = 413;
        throw new Exception('Max. filesize exceeded, allowed size is ' . MAX_FILE_SIZE / 1024 . ' kB', 104);
    }

    // check allowed files types are uploaded
    if (!in_array($old_file_type, ALLOWED_MIMETYPES) || !in_array($new_file_type, ALLOWED_MIMETYPES)) {
        $status_code = 400;
        throw new Exception('Only text files (.txt) are allowed, please try again', 105);
    }

    // upload and move file if not done already
    if (!is_uploaded_file($old_file_tmp_name)) {
        if (!move_uploaded_file($old_file_tmp_name, UPLOAD_DIR . '/' . $old_file_name)) {
            $status_code = 500;
            throw new Exception("Internal server error, cannot upload file: $old_file_name", 106);
        }
    }
    if (!is_uploaded_file($new_file_name)) {
        if (!move_uploaded_file($new_file_tmp_name, UPLOAD_DIR . '/' . $new_file_name)) {
            $status_code = 500;
            throw new Exception("Internal server error, cannot upload file: $new_file_name", 106);
        }
    }

    // create name of output file
    $old_file = pathinfo($old_file_name)['filename'];
    $new_file = pathinfo($new_file_name)['filename'];
    $out_file_name = OUTPUT_DIR . '/' . $old_file . '___' . $new_file . '.html';

    // delete old output file, if exists
    if (file_exists($out_file_name)) {
        @unlink($out_file_name);
    }

    // run diff-tool in background
    $cmd = 'diff_tool.py ' . UPLOAD_DIR . '/' . $old_file_name . ' ' . UPLOAD_DIR . '/' . $new_file_name . ' --html ' . $out_file_name;
    if (substr(php_uname(), 0, 7) === 'Windows') {
        pclose(popen('start /B python ' . $cmd, 'r'));
    } else {
        exec('python3 ' . $cmd . ' > /dev/null &');
    }

    // return success message
    http_response_code(200);
    echo json_encode([
        'data' => [
            'out_filename' => $out_file_name,
        ],
        'code' => 200,
        'message' => 'Success uploading files',
    ]);
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
