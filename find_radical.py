import argparse
import os
from ChineseDictionary import ChineseDictionary

def find_characters_by_radical(input_file, keyword, output_file):
    dictionary = ChineseDictionary()
    filtered_chars = []
    search_parts = []

    if input_file and os.path.exists(input_file):
        with open(input_file, "r", encoding="utf-8") as f:
            search_parts.extend(list(f.read().strip()))
    if keyword:
        search_parts.extend(list(keyword))

    if search_parts:
        for char in dictionary.data.keys():
            for part in search_parts:
                if part == dictionary.radical(char):
                    filtered_chars.append(char)
        with open(output_file, "w", encoding="utf-8") as f:
            f.write("".join(filtered_chars))
        print(f"找到 {len(filtered_chars)} 個包含部首 {search_parts} 的字，已輸出到 {output_file}")
    else:
        print("沒有輸入部首，請輸入部首")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="根據部首關鍵字篩選中文字")
    parser.add_argument("--input", "-i", help="輸入的文字檔 (可選)")
    parser.add_argument("--keyword", "-k", help="要查詢的部首關鍵字 (可選)")
    parser.add_argument("--output", "-o", default="output.txt", help="輸出的文字檔 (預設為 output.txt)")

    args = parser.parse_args()
    find_characters_by_radical(args.input, args.keyword, args.output)