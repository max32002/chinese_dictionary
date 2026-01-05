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
$index_file = __DIR__ . '/search_index.json';

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
    $stmt = $db->prepare("SELECT char, data FROM dictionary WHERE char IN ($placeholders)");

    foreach ($chars as $i => $char) {
        $stmt->bindValue($i + 1, $char, SQLITE3_TEXT);
    }

    $result = $stmt->execute();
    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[$row['char']] = json_decode($row['data'], true);
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

            // Load Index
            $index = load_json($index_file);
            $matched_chars = [];

            if ($index) {
                // Use Index
                $search_parts = mb_str_split($keyword);

                foreach ($search_parts as $part) {
                    if (isset($index[$part])) {
                        $matched_chars = array_merge($matched_chars, $index[$part]);
                    }
                }
                $matched_chars = array_unique($matched_chars);
            } else {
                // Fallback (Not recommended with DB, scanning whole DB is slow but possible)
                // For now, let's just return empty or error if index is missing, 
                // but checking DB row by row is too heavy for standard PHP script time limits usually.
                // We'll trust optimize_data.py ran correctly.
                send_error('Search index missing. Please run optimize_data.py');
            }

            // Limit to 500
            $matched_chars = array_slice($matched_chars, 0, 500);

            // Fetch data from DB
            $db_results = get_chars_data($db, $matched_chars);

            // Format response (array of {char, data})
            $results = [];
            foreach ($matched_chars as $char) {
                if (isset($db_results[$char])) {
                    $results[] = ['char' => $char, 'data' => $db_results[$char]];
                }
            }
            $response = $results;
            break;

        case 'decompose':
            $text = $_GET['text'] ?? '';
            if (!$text) {
                send_error('No text specified');
            }

            $chars = mb_str_split($text);
            $decomposed_parts = [];
            $has_undecomposable = false;

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
