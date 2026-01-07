import argparse
import os
import sqlite3
import json
from ChineseDictionary import ChineseDictionary

if __name__ == "__main__":
    dictionary = ChineseDictionary()

    data = {
        'text': '姚', 
        'unicode': 23002, 
        'unicode_hex': '59DA', 
        'radical': '女', 
        'radical_count': 3, 
        'strokes_count': 6, 
        'strokes_total': 9, 
        'alternate': [], 
        'semantic_variant': [], 
        'pronunciation_mandarin_pinyin': ['yáo'], 
        'pronunciation_mandarin_zhuyin': ['ㄧㄠˊ'], 
        'pronunciation_cantonese': [], 
        'pronunciation_southern_min': [], 
        'pronunciation_hakka': [], 
        'component': {'左': '女', '右': '兆'}
    }

    # 建立資料庫連線
    conn = sqlite3.connect('dictionary.db')
    cursor = conn.cursor()

    # 建立資料表
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS characters (
        text TEXT,
        unicode INTEGER,
        unicode_hex TEXT,
        radical TEXT,
        radical_count INTEGER,
        strokes_count INTEGER,
        strokes_total INTEGER,
        alternate TEXT,
        semantic_variant TEXT,
        pinyin TEXT,
        zhuyin TEXT,
        cantonese TEXT,
        southern_min TEXT,
        hakka TEXT,
        component TEXT,
        component_values TEXT
    )
    ''')

    # 使用 UNIQUE INDEX 可以確保同一個字不會重複存入，也能加快查詢速度
    cursor.execute('CREATE UNIQUE INDEX IF NOT EXISTS idx_text ON characters (text)')

    for char in dictionary.data.keys():
        data = dictionary.info(char)
        print(f"『{char}』的dict: {data}")

        components = data['component']
        component_values = ""
        if isinstance(components, dict):
            for part in components.values():
                component_values += part

        # 處理 JSON 格式的欄位，將 list 或 dict 轉為字串
        sql = '''INSERT INTO characters VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'''
        values = (
            data['text'],
            data['unicode'],
            data['unicode_hex'],
            data['radical'],
            data['radical_count'],
            data['strokes_count'],
            data['strokes_total'],
            json.dumps(data['alternate']),
            json.dumps(data['semantic_variant']),
            json.dumps(data['pronunciation_mandarin_pinyin']),
            json.dumps(data['pronunciation_mandarin_zhuyin']),
            json.dumps(data['pronunciation_cantonese']),
            json.dumps(data['pronunciation_southern_min']),
            json.dumps(data['pronunciation_hakka']),
            json.dumps(data['component'], ensure_ascii=False),
            component_values
        )
        cursor.execute(sql, values)

    conn.commit()
    conn.close()

    print("資料已成功寫入")