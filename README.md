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
![執行結果](https://github.com/max32002/chinese_dictionary/raw/master/preview/runtime-query-dict.png)


執行說明：
查詢 "姚"，結果是「女」部，女部有3畫，非部首有6畫，總筆數9畫。漢語發音和注音：'yáo', 'ㄧㄠˊ'。文字組成：'左': '女', '右': '兆'


目前可以使用的元件位置(component position)有以下這幾種：
* '上', '下', '具有相關結構', '周圍', '中心', '左', '右', '左上', '右下', '左下', '右上', '左、右、上', '中間', '合併', '加', '上左下', '中和右', '左右', '中'

比較特別是有2個，一個是：
「具有相關結構」，是指該字可以被重組在其他字裡，例如：「點」和「㸃」，收集到的不完整清單如下：丟主了二亼什仔仙仚仜仝仩仴仿企伂休伒伙伝佃佘佞佮佯佳佴佸侁侄侜依促俗俚俱們倓值偽傌僰儿兀兄劖加劼勀勛募勰化匙卼古另叨召叭叹叻吁吂吇吊吐吓吙君吝吞吟吥否吧吩吪含听吻吽吾呅呆呇呈呐㕸呣呤呰呲咄咇和咏㖅咠咡咩咪咮咸咾哀哅員哦哲哳唁唄唇唗唭售唯啈啉啎問啡啵啻啼喕喜喦噅喿嗀嗎嗷嗼嘁噍嚨囗土圣圭圻址坉坋坌坏坒坔坪坭坵垕垞垟垠垢垥垵埜域埡埮埴基堂堊堡堩堵塈塙塱塵塶塹塻墏墓墜墩墫墮壁壂壅壘壟壠壤壿夙多夠夥天夭失奇奜奴奻好奾奿妀妁如妃妘妞妢妣妯妾姀姃姅姓委姛姞姴姻娊娓娕娠娩娮娵娶娹婀婓婚婦婪媝嫟嫢嫩嫫嫲嫳嫷嬭嬿孌孜季尒尕尖尚屇屘屳屴屹屺屻屼屾岆岈岉岊岋岌岏岑岒岓岔岕岝岢岦岧岩岪岫岭岳岵岷岸峆峇峈峉峊峎峏峒峔峖峗峘峛峞峨峮峰峴峸峹崀崆崇崎崑崒崔崖崗崙崚崛崟崠崢崣崦崧崨崩崮崰崱崲崳崴崷崸崼崿嵉嵎嵐嵒嵕嵜嵧嵩嵬嵯嵱嵲嵷嵽嵾嶁嶄嶍嶒嶔嶕嶙嶚嶠嶡嶢嶪嶬嶮嶰嶲嶴嶵嶷嶺嶼嶽巃巍巑巖巘帕帛幕幙幠干幻幼庂庄庲影忉忌忍忏忑忔忘忙㤉忞忠忡忣忥忨忪忯忱忴念忷忺忻忽忿怀怉怍怎怏怑怒怓怔怕怗怙怛怠怡怢怤性怩怫怵恁恕恘恚恛恣恥恨恩恪息恰恲恿悀悆悇悌悍悐悔悖悱悲悶悹悺悼惀惄惆惎惏惑惔惕惟惡惤惷惻愀愁愈愉意愔愘愚愜感慨愩愿慁慇態慎慔慖慚慝慟慧慫慰慱慲慼憃憋憐憚憝憨憫憵憼懃懈懟懤懨懭懰懶戁戇戒抓拿捸揎插揭摩𢲸撝旦旨早旭旮旯旰旱旵旺旻旼旽昂昃昆昇昉昊昋昒易星映昡昢昱昶昹昺晁時晃晄晌晏晑晚晛晜晟晢晨晪景晰晸晾暀暑暈暉暋暌暖暠暫暮暯曄曇曈曉曠曣曦曩曶朄朊朋朏朐朓期朣朦朱朵机杍李杏村杕杗杜杠杲杳杺杼松构枇枌枙枟枲架枷枻柅柋柏某柑柔柘柤查柦柮柰柴柼栞栠栥栨栩栮根桀桃案桉桐桑桫桽梄梅梓梖梪棋棐棑棗棘森棴棷棸棻椎椔椥㮣椯椿楑楘楴概榕榩槁槃槄槊槐槲槳槷槸槻槼樧橁橇橛橢橤橶檄檗檠檢檤檮檷櫐櫑櫳欖此歹殼比毗毘毠毣毦毨毬毯毷毹毼毾毿氀氁氂氄氆氈氉氋氌氍氓氙氣氰汃汆汏汕汝汞江汥汨汫沀沁沂沈沊沐沓沙沝𣲩沬沰沸沺沽泉泊泒泬洎洐洛洖浘浴海涀涌涳淢淦淼渢湀湔湘湟湫源溯溳滱漁漠澼濲濷瀔灇灶炃炅炎炑炚炟炱炳烌烘烡𤉙焚煘煙煩煪熇燧爚爩牡牪犈犒玄玊玩珀珝珼琪琲瓞瓠㼓瓤瓨瓴瓶甜甥男町甾界略異畬百皁皆皇皉皓皯皵皸㿸皾盲盳相盼省眇眊眛眥眯睌睍瞙瞥知石矹泵砅砉砦砬砮硅硨确碁碄磐磛磨磻礅礔礜礧礨礱示禧禽秀秈秋秶種稿穈積䆀穧穴窅窵竄竘𫞼簆籹粊粍粑糜糸絮縠𦊓群羽羾翂翃翅翇翊翋翌翍翎翐翑習翔翕翟翠翡翥翦翨翩翬翭翹翾老耄耆耍耑耔耗耶耷耿聳聾肖肙肚胃胄胊胍胎胐胜胡胥胸脀脁脅腋腎膧臂臥臬臲臸舅舌舕舥色苟茍茧蒺薡虓虢虣虥虮虹虺虻蚊蚎蚔蚕蚖蚝蚞蚡蚥蚹蚻蛀蛙蛚蛜蛤蛬蛷蛾蜂蜃蜄蜇蜊蜑蜒蜘蜙蜚蜞蜤蜥蜮蜰蜱蜴蜿蝂蝌蝝蝦蝪蝴蝵蝸螄螈融螏螫螯螰螸螹蟅蟆蟛蟞蟠蟨蟯蟴蟷蟹蠆蠈蠍蠐蠘蠣蠪蠬衃衍袌裂裏裔裘裝裴褒褭褻覅覛覹角觕觼訄訇訕訾訿詌詞話誣諃諅諆諏謨謷譥警譺讂讆讔谹谿豁豅豈豌貣貧貯賅賌賚賤賦賨賰贇贈贕赨跾蹔躩軻輁輂輩轂轆辛辜辣邑邔邗邛邟邠邡邢邧邱邲邳邴邵邶邸邾郁郊郕郖郙郚郛郜郝郟郤部郪郫郭郱郲郳郵郼都郾鄂鄃鄄鄅鄎鄔鄗鄘鄛鄟鄢鄣鄦鄧鄩鄬鄭鄰鄱鄲鄴鄹鄺酀酁酄酒酓酥酬醇醐醜醝里鈲錞閃閆閑閒阞阬阮阰阱防阹阺阻阽阿陃陊陎陏陓陔陘陜陟陣除陪陭陲陴陼陽隃隅隇隍階隔隗隘際障隮隴隻隿集難靠靾鞍鞗鞤鞷韙韡音須顥颯颺颻颽颿飁飂飄飆飧馱駂駘駪駷騖騙騩驀驁骴髀髍髜髟魂魄魊魎魕魖魦魯魰鮆鮊鮘鮤鯈鯉鯊鯬𩸙鰍鱗鱭鱮鳦鳧鳩鳭鳱鳲鳶鳻鳼鴃鴐鴔鴛鴝鴟鴢鴣鴥鴦鴨鴩鴶鴻鴽鵀鵁鵅鵌鵏鵑鵒鵔鵚鵜鵝鵡鵩鵫鵬鵯鵱鵷鵻鶀鶂鶄鶇鶉鶒鶖鶗鶘鶦鶧鶳鶴鶵鶻鶾鶿鷈鷒鷕鷗鷘鷚鷛鷜鷢鷥鷦鷨鷫鷲鷺鷻鷿鸀鸃鸏鸒鸓鸔鸕鸗鸚鸛鸝鸞鹹鹺麂麊麋麟黂墨黜點黦黰鼏鼯鼶鼸鼽齆齌齜齟齬齱齹齺齾龢


另一個特別的是「'合併', '加'」 這個是一組的，2個必需同時使用。指外面包住裡面。目前只有收到到這幾個：包坐幽。但是規則與「'周圍'＋'中心'」是相似的。

## 附註：
* Dictionary.json 是完整的資料庫，雖然有 8萬字，但是只有「部首」和「筆劃數」是完整的。
* Dictionary_lite.json 是精簡版的資料庫，雖然只有 1萬字，但是可以查「部首」、「筆劃數」、「異體字」、「同義字」和「文字組件」，可以針對不同用途選擇適合您的版本。


## 相關網頁：
* 查「部首」、「筆畫」、「異體字」、「同義字」和「文字組件」的字典
https://max-everyday.com/2020/04/chinese-dictionary-radical/


## 贊助Max

很高興可以替中華民國美學盡一分心力，如果你覺得這篇文章寫的很好，想打賞Max，贊助方式如下：
https://max-everyday.com/about/#donate
