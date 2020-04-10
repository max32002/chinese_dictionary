# 簡易中文字典
一個簡易的字典。能查文字「部首」、「筆畫」的文字數有 81,052 筆，好多哦。資料主是從別人的資料庫搬出來的。

有「異體字」、「同義字」和「文字組件」的資料有：12,914筆，應該可以應付日常大多數的情況。


使用範例：

```
import json

with open('Dictionary.json', 'r') as read_file:
    dict_data = json.load(read_file)
   
char = '姚'
if char in dict_data:
    print(char, ":", dict_data[char])
```

執行結果：
```
姚 : {'text': '姚', 'unicode': 23002, 'unicode_hex': '59DA', 'radical': '女', 'radical_count': 3, 'strokes_count': 6, 'strokes_total': 9, 'alternate': [], 'semantic_variant': [], 'pronunciation_mandarin': ['yáo', 'ㄧㄠˊ'], 'pronunciation_cantonese': [], 'pronunciation_southern_min': [], 'component': {'左': '女', '右': '兆'}}
```
![執行結果](https://github.com/max32002/chinese-dictionary/raw/master/preview/runtime-query-dict.png)


執行說明：
查詢 "姚"，結果是「女」部，女部有3畫，非部首有6畫，總筆數9畫。漢語發音和注音：'yáo', 'ㄧㄠˊ'。文字組成：'左': '女', '右': '兆'


目前可以使用的元件位置(component position)有以下這幾種：
* '上', '下', '具有相關結構', '周圍', '中心', '左', '右', '左上', '右下', '左下', '右上', '左、右、上', '中間', '合併', '加', '上左下', '中和右', '左右', '中'

## 相關網頁：
* 查「部首」、「筆畫」、「異體字」、「同義字」和「文字組件」的字典
https://max-everyday.com/2020/04/chinese-dictionary-radical/


## 贊助Max

很高興可以替中華民國美學盡一分心力，如果你覺得這篇文章寫的很好，想打賞Max，贊助方式如下：
https://max-everyday.com/about/#donate
