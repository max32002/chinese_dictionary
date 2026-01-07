import sqlite3
import json

conn = sqlite3.connect('dictionary.db')
# 設定 row_factory 可以讓我們用欄位名稱存取資料
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

cursor.execute("SELECT * FROM characters WHERE text = '姚'")
row = cursor.fetchone()

if row:
    # 將 Row 物件轉換為 dict 並還原 JSON 欄位
    result = {
        'text': row['text'],
        'unicode': row['unicode'],
        'unicode_hex': row['unicode_hex'],
        'radical': row['radical'],
        'radical_count': row['radical_count'],
        'strokes_count': row['strokes_count'],
        'strokes_total': row['strokes_total'],
        'alternate': json.loads(row['alternate']),
        'semantic_variant': json.loads(row['semantic_variant']),
        'pronunciation_mandarin_pinyin': json.loads(row['pinyin']),
        'pronunciation_mandarin_zhuyin': json.loads(row['zhuyin']),
        'pronunciation_cantonese': json.loads(row['cantonese']),
        'pronunciation_southern_min': json.loads(row['southern_min']),
        'pronunciation_hakka': json.loads(row['hakka']),
        'component': json.loads(row['component'])
    }
    
    print("---")
    print(result)

conn.close()