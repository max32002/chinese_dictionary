<?php
// 開啟 SQLite3 資料庫
$db = new SQLite3('kanji.db');

// 準備 SQL 語句
$sql = "SELECT * FROM characters WHERE text = :text";
$stmt = $db->prepare($sql);
$stmt->bindValue(':text', '姚', SQLITE3_TEXT);

// 執行查詢
$result_set = $stmt->execute();

// 取得第一列資料，使用 SQLITE3_ASSOC 讓結果變成以欄位名為 key 的陣列
$row = $result_set->fetchArray(SQLITE3_ASSOC);

if ($row) {
    // 將 JSON 字串還原為 PHP 陣列
    // 第二個參數 true 代表轉成關聯陣列
    $result = [
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

    echo "---\n";
    print_r($result);
}

// 關閉資料庫連線
$db->close();
?>