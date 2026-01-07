<?php
// api.php - Backend for Chinese Component Search
// Handles search, decompose, and detail requests using SQLite for efficiency

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // For development
header('Cache-Control: no-cache');

// Enable Gzip compression if supported by client
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

$start_time = microtime(true);

// Configuration
$db_file = __DIR__ . '/dictionary.db';

// Helper function to send error
function send_error($message)
{
    echo json_encode(['error' => $message]);
    exit;
}

// Helper function to load JSON
function load_json($file)
{
    if (!file_exists($file)) {
        return null;
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

// Helper to get DB connection
function get_db()
{
    global $db_file;
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found. Please run optimize_data.py.");
    }
    $db = new SQLite3($db_file);
    $db->busyTimeout(5000); // 5 seconds
    return $db;
}

// Helper to get info for multiple chars
function get_chars_data($db, $chars)
{
    if (empty($chars))
        return [];

    // Prepare statement with placeholders
    $placeholders = implode(',', array_fill(0, count($chars), '?'));
    $stmt = $db->prepare("SELECT * FROM characters WHERE text IN ($placeholders)");

    foreach ($chars as $i => $char) {
        $stmt->bindValue($i + 1, $char, SQLITE3_TEXT);
    }

    $result = $stmt->execute();
    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[$row['text']] = [
            'text' => $row['text'],
            'unicode' => $row['unicode'],
            'unicode_hex' => $row['unicode_hex'],
            'radical' => $row['radical'],
            'radical_count' => $row['radical_count'],
            'strokes_count' => $row['strokes_count'],
            'strokes_total' => $row['strokes_total'],
            'alternate' => json_decode($row['alternate'], true),
            'semantic_variant' => json_decode($row['semantic_variant'], true),
            'pronunciation_mandarin_pinyin' => json_decode($row['pinyin'], true),
            'pronunciation_mandarin_zhuyin' => json_decode($row['zhuyin'], true),
            'pronunciation_cantonese' => json_decode($row['cantonese'], true),
            'pronunciation_southern_min' => json_decode($row['southern_min'], true),
            'pronunciation_hakka' => json_decode($row['hakka'], true),
            'component' => json_decode($row['component'], true)
        ];
    }
    return $data;
}

// Helper to get info for one char
function get_char_data($db, $char)
{
    $stmt = $db->prepare("SELECT data FROM dictionary WHERE char = :char");
    $stmt->bindValue(':char', $char, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        return json_decode($row['data'], true);
    }
    return null;
}

// Get parameters
$action = $_GET['action'] ?? '';

if (!$action) {
    send_error('No action specified');
}

$response = [];

try {
    $db = get_db();

    switch ($action) {
        case 'search':
            $keyword = $_GET['keyword'] ?? '';
            if (!$keyword) {
                send_error('No keyword specified');
            }
            //print_r("\nkeyword:".$keyword);

            $enable_advance_search = false;
            $advance_search = $_GET['advance_search'] ?? '';
            if ($advance_search) {
                $enable_advance_search = true;
            }
            //print_r("\nenable_advance_search:".$enable_advance_search);

            // initial
            $matched_chars = [];
            
            // Format response (array of {char, data})
            $results = [];

            // 第一階段：從 characters table 搜尋
            $stmt = $db->prepare("SELECT * FROM characters WHERE component_values LIKE :part");
            $stmt->bindValue(':part', '%' . $keyword . '%', SQLITE3_TEXT);
            $result = $stmt->execute();

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['text'])) {
                    $matched_chars[] = $row['text'];
                }
            }
            //print_r("\nmatched_chars count:".count($matched_chars));

            // 第二階段：從 component table 搜尋（例如搜尋 paired1 欄位）
            if(empty($matched_chars) || $enable_advance_search) {
                //print_r("\nstart to advanced search");

                $stmt = $db->prepare("SELECT * FROM component WHERE paired1 LIKE :part");
                $stmt->bindValue(':part', '%' . $keyword . '%', SQLITE3_TEXT);
                $result = $stmt->execute();

                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    //print_r("\nrow:".$row['char']);
                    if (!empty($row['char'])) {
                        $matched_chars[] = $row['char'];
                    }
                }
                //print_r("\nmatched_chars count:".count($matched_chars));
            }

            // 最終階段：合併、去重、重整索引
            $matched_chars = array_unique($matched_chars);
            $matched_chars = array_values($matched_chars);

            // Limit to 1000
            $matched_chars = array_slice($matched_chars, 0, 1000);

            // Fetch data from DB
            $db_results = get_chars_data($db, $matched_chars);

            foreach ($matched_chars as $char) {
                if (isset($db_results[$char])) {
                    $results[] = ['char' => $char, 'data' => $db_results[$char]];
                }
            }
            $response = $results;
            break;

        case 'decompose':
            $text = $_GET['text'] ?? '';
            // Compatibility: if text is empty but char is set
            if (!$text) {
                $text = $_GET['char'] ?? '';
            }

            if (!$text) {
                send_error('No text specified');
            }

            $chars = mb_str_split($text);
            $decomposed_parts = [];
            $has_undecomposable = false;

            // Prepare statement once
            $stmt = $db->prepare("SELECT paired1 FROM component WHERE char = :char");

            foreach ($chars as $char) {
                $stmt->bindValue(':char', $char, SQLITE3_TEXT);
                $result = $stmt->execute();
                $row = $result->fetchArray(SQLITE3_ASSOC);

                if ($row && !empty($row['paired1'])) {
                    $decomposed_parts[] = $row['paired1'];
                } else {
                    $decomposed_parts[] = $char;
                    $has_undecomposable = true;
                }
            }

            if ($has_undecomposable) {
                // plan B
                $has_undecomposable = false;
                $decomposed_parts = [];

                $component_keys = ['合併', '右', '右上', '左右', '左下', '中間', '周圍', '左上', '下', '左', '上左下', '上', '左、右、上'];

                // Optimize: fetch all chars in one query
                $char_data_map = get_chars_data($db, $chars);

                foreach ($chars as $char) {
                    $data = $char_data_map[$char] ?? null;
                    $pushed = false;

                    if ($data && isset($data['component']) && is_array($data['component'])) {
                        foreach ($component_keys as $key) {
                            if (isset($data['component'][$key])) {
                                $decomposed_parts[] = $data['component'][$key];
                                $pushed = true;
                            }
                        }
                    }

                    if (!$pushed) {
                        $decomposed_parts[] = $char;
                        $has_undecomposable = true;
                    }
                }
            }

            $unique_parts = array_unique($decomposed_parts);
            $result_str = implode('', $unique_parts);

            $response = [
                'input' => $text,
                'result' => $result_str,
                'is_same' => ($result_str === $text),
                'has_undecomposable' => $has_undecomposable
            ];
            break;

        case 'detail':
            $char = $_GET['char'] ?? '';
            if (!$char) {
                send_error('No char specified');
            }

            $data = get_char_data($db, $char);

            if ($data) {
                $response = ['char' => $char, 'data' => $data];
            } else {
                $response = null; // Not found
            }
            break;

        default:
            send_error('Invalid action');
    }
} catch (Exception $e) {
    send_error($e->getMessage());
}

echo json_encode($response);
exit;
