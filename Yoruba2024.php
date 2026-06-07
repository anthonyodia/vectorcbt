<?php
// yoruba2024.php - Exact Geography Design with Full Yoruba Content
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// The complete 50-question data
$yorubaData = [
    "assessment" => [
        ["id" => 1, "passage" => "Aago márùn-ún idájí ni Şeun ti jí jáde nilé lo wò okò tí ó ń lọ sí Ìdànrè...", "question" => "Bí aago mélòó ni awakò tó sọ pé òun kò le lọ mọ́?", "options" => ["A" => "Méwàá", "B" => "Méje", "C" => "Márùn-ún", "D" => "Mérin"], "answer" => "B", "explanation" => "Awakò sọ pé òun kò lè lọ mọ́ ní Òrè ní aago méje."],
        ["id" => 2, "passage" => "Aago márùn-ún idájí ni Şeun ti jí jáde nilé lo wò okò tí ó ń lọ sí Ìdànrè...", "question" => "Ojú ọ̀jọ́ tí ń pọ̀ fírí túmọ̀ sí pé—", "options" => ["A" => "Ilẹ̀ ti ń ṣú", "B" => "Òjò ń rọ̀", "C" => "Ààjìn ti ń jin", "D" => "Òòrùn ti ń kọ́ àtàrí"], "answer" => "B", "explanation" => "‘Ojú ọ̀jọ́ tí ń pọ̀ fírí’ túmọ̀ sí pé ojú-ọjọ́ ti ń dínà, àmì òjò ni."],
        ["id" => 3, "passage" => "Aago márùn-ún idájí ni Şeun ti jí jáde nilé lo wò okò tí ó ń lọ sí Ìdànrè...", "question" => "Şeun fẹ́ẹ́ yè ara rẹ̀ fún ẹni tí ó jí rí nítorí—", "options" => ["A" => "Òjò rọ̀", "B" => "Awakò kò mọ̀ ọ̀nà", "C" => "Ó gbà pé òun ni ó kó òṣì bá òun", "D" => "Ilẹ̀ ṣú"], "answer" => "C", "explanation" => "Şeun gbà pé ẹni tí ó kọ́kọ́ jí rí ló kó òṣì bá a."],
        ["id" => 4, "passage" => "Aago márùn-ún idájí ni Şeun ti jí jáde nilé lo wò okò tí ó ń lọ sí Ìdànrè...", "question" => "Şeun gbà pé òun ti ṣi rìn nítorí pé—", "options" => ["A" => "Okò ti bàjẹ́ kí wọ́n tó dé Sàgámù", "B" => "Ojú ọ̀jọ́ pọ̀ fírí kí wọ́n tó dé Ìdànrè", "C" => "Ó wá okò fún wákàtí kan", "D" => "Ó kó ọ̀pọ̀ ìṣòro ní ojú ọ̀nà rẹ̀ sí Ìdànrè"], "answer" => "C", "explanation" => "Ó tó wákàtí kan tí Şeun ti ń wá okò tí kò rí."],
        ["id" => 5, "passage" => "Aago márùn-ún idájí ni Şeun ti jí jáde nilé lo wò okò tí ó ń lọ sí Ìdànrè...", "question" => "Àkọlé tí ó bá àyọ̀kà yìí mu jù lọ ni—", "options" => ["A" => "Okò bàjẹ́ sí ọ̀nà Sàgámù", "B" => "Òjò tí ó rọ̀ ní Òrè", "C" => "Ọjọ́ kan tí kò lè gbàgbé", "D" => "Ìrìn-àjò sí Ògbolú"], "answer" => "C", "explanation" => "Ọjọ́ náà jẹ́ ‘ọjọ́ tí kò ní gbàgbé’ fún Şeun."],
        ["id" => 6, "passage" => "Táyé àti Adéolá ń bá ara wọn sọ̀rọ̀ nípa ọmọ tuntun tí ìyá Adéolá bí...", "question" => "Ta ni ó dé bá Táyé àti Adéolá níbi tí wọ́n ti ń sọ̀rọ̀?", "options" => ["A" => "Abósèdé", "B" => "Ajàyí", "C" => "Oláidé", "D" => "Ainá"], "answer" => "C", "explanation" => "Oláidé ló dé bá wọn níbi tí wọ́n ti ń sọ̀rọ̀."],
        ["id" => 7, "passage" => "Táyé àti Adéolá ń bá ara wọn sọ̀rọ̀ nípa ọmọ tuntun tí ìyá Adéolá bí...", "question" => "A máa ń sọ ọmọbìnrin lórúkọ ní ọjọ́—", "options" => ["A" => "Kẹ́fà", "B" => "Kẹ́je", "C" => "Kẹ́jọ", "D" => "Kẹ́sàn-án"], "answer" => "B", "explanation" => "A máa ń sọ ọmọbìnrin lórúkọ ní ọjọ́ kẹ́je."],
        ["id" => 8, "passage" => "Táyé àti Adéolá ń bá ara wọn sọ̀rọ̀ nípa ọmọ tuntun tí ìyá Adéolá bí...", "question" => "Ọ̀kan nínú àwọn ibeji náà jẹ́ Ainá nítorí pé ó—", "options" => ["A" => "Dojú bọlẹ̀", "B" => "Gbé ibi kórùn", "C" => "Jẹ́ obìnrin", "D" => "Jẹ́ okùnrin"], "answer" => "B", "explanation" => "Ainá ni ọmọ tí ó gbé ibi kórùn."],
        ["id" => 9, "passage" => "Táyé àti Adéolá ń bá ara wọn sọ̀rọ̀ nípa ọmọ tuntun tí ìyá Adéolá bí...", "question" => "Ọ̀kan lára ohun èlò ìsómolórúkọ tí yóò bíkú dànù ni—", "options" => ["A" => "Orógbó", "B" => "Ataare", "C" => "Irèké", "D" => "Ọbì"], "answer" => "D", "explanation" => "Ọbì ni ohun èlò tí a máa ń lò bí àlàáfíà, tí yóò sì bíkú dànù."],
        ["id" => 10, "passage" => "Táyé àti Adéolá ń bá ara wọn sọ̀rọ̀ nípa ọmọ tuntun tí ìyá Adéolá bí...", "question" => "Báwo ni ẹni tí ó bí ọmọ ṣe jẹ́ sí Adéolá?", "options" => ["A" => "Ẹ̀gbọ́n rẹ̀", "B" => "Ọ̀rẹ́ rẹ̀", "C" => "Ìyá rẹ̀", "D" => "Abúrò rẹ̀"], "answer" => "C", "explanation" => "Ìyá Adéolá ló bí ọmọ náà."],
        ["id" => 11, "question" => "Òkan lára ìgbésẹ̀ tí ó yà kíkọ lẹ́tà àìgbọ́gbọ́fé sọ́tọ̀ ni—", "options" => ["A" => "Ikádìí", "B" => "Déèti", "C" => "Àdírésì akọ́lé tà", "D" => "Àkọlé lẹ́tà"], "answer" => "A", "explanation" => "Ikádìí ni ohun tí a fi kọ́ lẹ́tà àìgbọ́gbọ́fé lọ́tọ̀."],
        ["id" => 12, "question" => "“Ìjàmbá okò kan tí ó ṣòjú mi” lè jẹ́ orí-òrò fún àròkọ—", "options" => ["A" => "Ajẹmọ́ ìṣipaya", "B" => "Àṣàpẹ̀júwe", "C" => "Àsọtàn", "D" => "Àṣàríyànjiyàn"], "answer" => "A", "explanation" => "Ìtàn ìjàmbá jẹ́ àròkọ ajẹmọ́ ìṣipaya."],
        ["id" => 13, "question" => "“Ilé ayé” lè jẹ́ orí-òrò fún àròkọ—", "options" => ["A" => "Àṣàpẹ̀júwe", "B" => "Ajẹmọ́ ìṣipaya", "C" => "Àṣàríyànjiyàn", "D" => "Àsọtàn"], "answer" => "A", "explanation" => "‘Ilé ayé’ jẹ́ àròkọ àṣàpẹ̀júwe."],
        ["id" => 14, "question" => "“Owó lègbón, ọmọ làbúrò” lè jẹ́ orí-òrò fún àròkọ—", "options" => ["A" => "Àṣàpẹ̀júwe", "B" => "Àṣàríyànjiyàn", "C" => "Àsọtàn", "D" => "Ajẹmọ́ ìṣipaya"], "answer" => "B", "explanation" => "Òrò yìí jé orí-òrò aròko àṣàríyànjiyàn (argumentative)."],
        ["id" => 15, "question" => "Èwo ni kò tònà?—", "options" => ["A" => "Obè", "B" => "Ìgò", "C" => "Akún", "D" => "Olè"], "answer" => "D", "explanation" => "‘Olè’ kì í bá àwọn míì tó jọ ọ́ nínú ẹ̀ka ọrọ̀ kàn."],
        ["id" => 16, "question" => "Fáwèli àránmúpè, àhánúpè ìwájú ni—", "options" => ["A" => "i", "B" => "an", "C" => "in", "D" => "u"], "answer" => "C", "explanation" => "‘in’ ni fáwèli àránmúpè ìwájú."],
        ["id" => 17, "question" => "Mófimù mélòó ló wà nínú ‘òpéléńgé’?", "options" => ["A" => "Méjì", "B" => "Méta", "C" => "Mérin", "D" => "Márùn-ún"], "answer" => "C", "explanation" => "Ọ̀rọ̀ ‘òpéléńgé’ ní mófimù mẹ́rin."],
        ["id" => 18, "question" => "Èwo ni ọ̀nà ìṣenúpè rẹ̀ jẹ́ àsẹ̀sètán?", "options" => ["A" => "[w]", "B" => "[d]", "C" => "[r]", "D" => "[l]"], "answer" => "A", "explanation" => "[w] ni onà iṣenúpè àsẹ̀sètán."],
        ["id" => 19, "question" => "Kóńsónántì tí a ń fi àjà-enu àti àárin ahọ́n pè ni—", "options" => ["A" => "[f]", "B" => "[s]", "C" => "[j]", "D" => "[g]"], "answer" => "C", "explanation" => "[j] ni kóńsónántì tí a fi àjà-enu àti àárin ahọ́n pè."],
        ["id" => 20, "question" => "‘Bóńdùrù’ jẹ́ ọ̀rọ̀ àyálò tí a yá nípasẹ̀—", "options" => ["A" => "Òlàjú", "B" => "Ọ̀rọ̀ ajé", "C" => "Ètò ẹ̀kọ́", "D" => "Èsìn"], "answer" => "B", "explanation" => "‘Bóńdùrù’ jẹ́ ọ̀rọ̀ àyálò látinú ọ̀rọ̀ ajé (‘bonus’)."],
        ["id" => 21, "question" => "Èyí tí ó ní àṣáájú orà-ìṣe nínú ni—", "options" => ["A" => "Ó pàdé mi", "B" => "Ó rò mi rò ire", "C" => "Ó ń fẹ́ lọ", "D" => "Ó ṣe mi lóore"], "answer" => "C", "explanation" => "‘Ó ń fẹ́ lọ’ ní àṣáájú orà-ìṣe ‘ń’."],
        ["id" => 22, "question" => "Ìsọdórúkọ èlò ni—", "options" => ["A" => "Ìpàrà", "B" => "Elégàn", "C" => "Àlọ́wọ́", "D" => "Òsọ́ọ̀sẹ̀"], "answer" => "C", "explanation" => "‘Àlọ́wọ́’ ni ìsọdórúkọ èlò (compound noun)."],
        ["id" => 23, "question" => "Nínú “Ọ̀pọ̀ ènìyàn ló wà níbẹ̀”, ‘pọ̀’ jẹ́ ọ̀rọ̀ orúkọ—", "options" => ["A" => "Ibìkan", "B" => "Àsọ̀yé", "C" => "Àfihàn", "D" => "Ìgbà"], "answer" => "C", "explanation" => "‘Pọ̀’ jẹ́ àfihàn, nítorí ó ń tọ́ka sí opò."],
        ["id" => 24, "question" => "Nínú “Ó pàdé mi”, ‘pàdé’ jẹ́ ọ̀rọ̀-ìṣe—", "options" => ["A" => "Aláiléní", "B" => "Alápèpadà", "C" => "Àkànmórúkọ", "D" => "Agbèrún"], "answer" => "B", "explanation" => "‘Pàdé’ jẹ́ ọ̀rọ̀-ìṣe alápèpadà (reciprocal verb)."],
        ["id" => 25, "question" => "Àpólà orúkọ tí kò ní àpónlé èyàn nínú ni—", "options" => ["A" => "Ilé ńlá méjì", "B" => "Ìyàwó kan ṣoṣo", "C" => "Omi tútù nìní", "D" => "Ènì méjì péré"], "answer" => "D", "explanation" => "‘Ènì méjì péré’ kò ní àpónlé èyàn."],
        ["id" => 26, "question" => "‘Àṣà’ túmọ̀ sí—", "options" => ["A" => "Ìṣe àtàwọn ìgbàgbọ́ ènìyàn", "B" => "Orúkọ àtijọ́", "C" => "Ìwà burúkú", "D" => "Ìtàn àròsọ"], "answer" => "A", "explanation" => "Àṣà ni ìṣe, ìgbọ́gbọ́ àti àfihàn ìwà tí ó jẹ́ ti àwùjọ kan."],
        ["id" => 27, "question" => "Nígbà àtijọ́, àwọn Yorùbá máa ń fi irun dí írun wọn nítorí—", "options" => ["A" => "Láti dára jù lọ", "B" => "Láti fi mọ̀ ìdílé", "C" => "Láti fi jẹ́ pé wọ́n kéré sí i", "D" => "Láti ṣàfihàn àṣejù"], "answer" => "B", "explanation" => "Irun dí ni Yorùbá fi ń fi mọ̀ ìdílé àti ìran."],
        ["id" => 28, "question" => "Àṣà ìyàwó túmọ̀ sí—", "options" => ["A" => "Ìrìn àjò", "B" => "Ìgbéyàwó àti ìwà tó bá a lọ", "C" => "Ìtàn ọmọ", "D" => "Ìròyìn ọjà"], "answer" => "B", "explanation" => "Àṣà ìyàwó jẹ́ ohun tó ní í ṣe pẹ̀lú ìgbéyàwó àti ìbáṣepọ̀."],
        ["id" => 29, "question" => "Ìdí tí wọ́n fi ń ṣe ìsìnkú ni—", "options" => ["A" => "Kí wọ́n lè sin oku", "B" => "Láti ṣe àṣeyọrí", "C" => "Láti fi bú ọ̀tá", "D" => "Láti fi fi ìbáṣepọ̀ hàn"], "answer" => "D", "explanation" => "Ìsìnkú jẹ́ àfihàn ìbáṣepọ̀ àti ìyì fún oku."],
        ["id" => 30, "question" => "“Ènìyàn ní ń fi èrò mọ́ ọkàn ènìyàn” túmọ̀ sí pé—", "options" => ["A" => "Àdúrà ni a máa ń gbà", "B" => "Kí a má ṣe ṣe ẹni búburú", "C" => "Ẹlòmíràn lè mọ ohun tó wà lójú wa", "D" => "A kì í mọ ohun tí ẹlòmíràn ń rò"], "answer" => "D", "explanation" => "Òwe yìí túmọ̀ sí pé a kò lè mọ ohun tí ẹlòmíràn ń rò."],
        ["id" => 31, "question" => "‘Kí àlàáfíà má bàjẹ́’ túmọ̀ sí—", "options" => ["A" => "Kí a má ṣàkíyèsí", "B" => "Kí ìfẹ́ àti àlàáfíà wà", "C" => "Kí a má ṣe bá a lọ", "D" => "Kí ìjìnlẹ̀ òun dájú"], "answer" => "B", "explanation" => "‘Kí àlàáfíà má bàjẹ́’ ni àdúrà kí ìfẹ́ àti àlàáfíà wà."],
        ["id" => 32, "question" => "Àwọn òwe ni a fi ń—", "options" => ["A" => "Ṣàgbékalẹ̀ òfin", "B" => "Ṣe ìtàn ìtàn", "C" => "Ṣàfihàn ọgbọ́n àti ìmọ̀", "D" => "Ṣe àṣàríyànjiyàn"], "answer" => "C", "explanation" => "Òwe ni ọ̀nà tí Yorùbá fi ń fi ọgbọ́n àti ìmọ̀ hàn."],
        ["id" => 33, "question" => "‘Òwe l’ẹṣin ọ̀rọ̀’ túmọ̀ sí—", "options" => ["A" => "Òwe ni ń mú ọ̀rọ̀ rìn", "B" => "Òwe ni ń dá òfin", "C" => "Òwe ni ń kó ìmọ̀ jọ", "D" => "Òwe ni ń sọ́rọ̀ ní àṣírí"], "answer" => "A", "explanation" => "Òwe ni ń mú ọ̀rọ̀ rìn — yíyí ọ̀rọ̀ di mímu mọ́."],
        ["id" => 34, "question" => "“Àìmọ̀ ni ń jẹ́ kí a má mọ ohun tí a ní” túmọ̀ sí pé—", "options" => ["A" => "A mọ ohun tí a ní", "B" => "Àìmọ̀ ni ń mú ká má fi ohun wa ṣe", "C" => "A máa mọ ohun tó dáa", "D" => "Àìmọ̀ kò ní ìtànkálẹ̀"], "answer" => "B", "explanation" => "Òwe yìí ń fi ọgbọ́n hàn pé àìmọ̀ ni ń mú ká má mọ iye ohun wa."],
        ["id" => 35, "question" => "‘Bí a kò bá rìn, a kì í mọ ojú ọ̀nà’ túmọ̀ sí pé—", "options" => ["A" => "A gbọdọ̀ rìn láti mọ ọ̀nà", "B" => "A kì í mọ ìrìn", "C" => "Ọ̀nà ni ń jẹ́ kó ṣòro", "D" => "Rìn dára ju dákẹ́ lọ"], "answer" => "A", "explanation" => "Ó fi hàn pé ìrìn ni ń jẹ́ ká mọ ọ̀nà àti ìrírí ayé."],
        ["id" => 36, "question" => "‘Ọmọ tí a kò kọ́ ni yóò gbé ilé tí a kọ́ jà’ túmọ̀ sí pé—", "options" => ["A" => "A gbọdọ̀ kọ́ ọmọ ní ìwà", "B" => "A kì í kọ́ ilé", "C" => "A gbọdọ̀ gba ọmọ lọ́wọ́ òbí", "D" => "Kí a má fi ọmọ ṣe ohunkóhun"], "answer" => "A", "explanation" => "Òwe yìí ń kìlọ̀ pé a gbọdọ̀ kọ́ ọmọ ní ìwà rere."],
        ["id" => 37, "question" => "Ìtumọ̀ ọ̀rọ̀ ‘ìwà’ ni—", "options" => ["A" => "Ìṣe burúkú", "B" => "Ìbáṣepọ̀", "C" => "Ìṣe rere tàbí búburú ènìyàn", "D" => "Ìtàn àròsọ"], "answer" => "C", "explanation" => "‘Ìwà’ túmọ̀ sí ìṣe rere tàbí búburú ènìyàn."],
        ["id" => 38, "question" => "Òwe ‘Ìwà l’ẹwà obìnrin’ túmọ̀ sí pé—", "options" => ["A" => "Ìwà rere ló yẹ fún obìnrin", "B" => "Ẹwà ni ń dá ìwà", "C" => "Obìnrin kò ní ìwà", "D" => "Obìnrin ni ẹwà"], "answer" => "A", "explanation" => "Òwe yìí ń tọ́ka sí pé ìwà rere ló dá obìnrin lórí."],
        ["id" => 39, "question" => "“A kì í mọ ẹni tó dá wa lórí” túmọ̀ sí pé—", "options" => ["A" => "A mọ gbogbo ènìyàn", "B" => "Kò sí ẹni tó dá wa lórí", "C" => "A ò mọ ẹni tó ń ṣe wa dáadáa", "D" => "A mọ ẹni tó burú"], "answer" => "C", "explanation" => "Túmọ̀ sí pé a kì í mọ ẹni tó ń ṣe wa dáadáa."],
        ["id" => 40, "question" => "‘Kí a má ṣe fi ọ̀rọ̀ ṣẹ́ ẹlòmíràn’ túmọ̀ sí—", "options" => ["A" => "Kí a má sọ ọ̀rọ̀ burúkú", "B" => "Kí a sọ gbogbo ọ̀rọ̀", "C" => "Kí a fi ọ̀rọ̀ ṣe ẹlòmíràn", "D" => "Kí a bá a ṣiṣẹ́"], "answer" => "A", "explanation" => "Ìtumọ̀ ni pé ká má sọ ọ̀rọ̀ tó lè bà ẹlòmíràn nínú."],
        ["id" => 41, "question" => "Ọ̀rọ̀ ‘Ọmọ ènìyàn’ jẹ́ àpẹẹrẹ—", "options" => ["A" => "Àṣàpẹ̀júwe", "B" => "Àpólà orúkọ", "C" => "Ọ̀rọ̀-ìṣe", "D" => "Àfihàn"], "answer" => "B", "explanation" => "‘Ọmọ ènìyàn’ jẹ́ àpólà orúkọ (noun phrase)."],
        ["id" => 42, "question" => "‘Ìjàngbọn’ túmọ̀ sí—", "options" => ["A" => "Ìrìn àjò", "B" => "Ìjà àti àìlera", "C" => "Ìbànújẹ", "D" => "Ìfẹ́ ọ̀rẹ́"], "answer" => "B", "explanation" => "‘Ìjàngbọn’ túmọ̀ sí ìjà àti àìlera ní àwùjọ."],
        ["id" => 43, "question" => "‘Kí a má ṣe jẹ́ ẹni àìmòye’ túmọ̀ sí—", "options" => ["A" => "Kí a ní ìmọ̀", "B" => "Kí a má ní ìmọ̀", "C" => "Kí a má jẹ́ ọlọ́rọ̀", "D" => "Kí a jẹ́ aláìmòye"], "answer" => "A", "explanation" => "Òwe náà ń kìlọ̀ pé ká ní ìmọ̀ àti ọgbọ́n."],
        ["id" => 44, "question" => "“Ọ̀rọ̀ àgbà kì í tán nílé àgbọ̀” túmọ̀ sí pé—", "options" => ["A" => "Àgbọ̀ máa ń sọ gbogbo ọ̀rọ̀", "B" => "Ọ̀rọ̀ àwọn àgbà kò lópin", "C" => "Àwọn àgbà ló mọ ọ̀rọ̀ dáadáa", "D" => "Kò sí àgbà nílé"], "answer" => "B", "explanation" => "Òwe yìí fi hàn pé ọgbọ́n àwọn àgbà kò lópin."],
        ["id" => 45, "question" => "‘A ní kì í sùn, a sùn; a ní kì í jí, a jí’ túmọ̀ sí pé—", "options" => ["A" => "A ń kọ́ni", "B" => "A ń kọ́ àwọn ọmọdé", "C" => "A ṣe ohun tí a sọ pé ká má ṣe", "D" => "A ṣe ohun rere"], "answer" => "C", "explanation" => "Túmọ̀ sí pé a ṣe ohun tí a sọ pé ká má ṣe."],
        ["id" => 46, "question" => "“Ẹnu dùn-ún rofò” túmọ̀ sí pé—", "options" => ["A" => "Ọ̀rọ̀ lè jẹ́ dùn láti sọ", "B" => "Ọ̀rọ̀ ò dùn", "C" => "Ọ̀rọ̀ ni onjẹ", "D" => "Ọ̀rọ̀ ní ẹnu dáadáa"], "answer" => "A", "explanation" => "Òwe yìí fi hàn pé ṣíṣe tàbí sọ ọ̀rọ̀ rọrùn ju ìṣe lọ."],
        ["id" => 47, "question" => "‘A ní ká fi ọwọ́ òsì gba, a fi ọ̀tún’ túmọ̀ sí pé—", "options" => ["A" => "A gbọ́dọ̀ ṣọ́ra", "B" => "A ṣe àkíyèsí", "C" => "A ṣe ohun tó lòdì sí ìlànà", "D" => "A kì í fi ọwọ́ òsì gba"], "answer" => "C", "explanation" => "Túmọ̀ sí pé a ṣe ohun tó lòdì sí ohun tí a sọ."],
        ["id" => 48, "question" => "“A ní ká jẹ, a ń jẹ ká rìn” túmọ̀ sí pé—", "options" => ["A" => "A kì í ṣe ohun kan ní ẹ̀ẹ̀kan", "B" => "A máa ń ṣe ohun tó jọ", "C" => "A ṣe ohun méjì tó yàtọ̀ ní àkókò kan", "D" => "A kò fẹ́ jẹun"], "answer" => "C", "explanation" => "Ó túmọ̀ sí pé a ń ṣe ohun méjì tó yàtọ̀ ní àkókò kan."],
        ["id" => 49, "question" => "‘A ní ká lọ́wọ́, a ní ká ṣáájú’ túmọ̀ sí pé—", "options" => ["A" => "A ń ṣe àfihàn ìmúrasílẹ̀", "B" => "A ṣe ohun tó jọ", "C" => "A ń bínú", "D" => "A ń fi òwe kọ́ni"], "answer" => "A", "explanation" => "Túmọ̀ sí pé a gbọ́dọ̀ ní ìmúrasílẹ̀ àti ṣáájú ohun tí a fẹ́ ṣe."],
        ["id" => 50, "question" => "‘Ọlọ́run ni ń dá ìyanu’ túmọ̀ sí pé—", "options" => ["A" => "Ọlọ́run ló lè ṣe ohun gbogbo", "B" => "Ọlọ́run kì í ṣe ohun tí ó burú", "C" => "Ọlọ́run kò lè dá ohun tó burú", "D" => "Ọlọ́run ni ọ̀tá ènìyàn"], "answer" => "A", "explanation" => "Túmọ̀ sí pé Ọlọ́run ló ní agbára láti ṣe ohun gbogbo."]
    ]
];

// AJAX logic
if ($action === 'get_questions') {
    header('Content-Type: application/json');
    $out = [];
    foreach($yorubaData['assessment'] as $q) {
        $opts = [];
        foreach($q['options'] as $key => $val) { $opts[] = ['optionId' => $key, 'text' => $val]; }
        $out[] = [
            'questionId' => $q['id'],
            'question' => $q['question'],
            'instruction' => $q['passage'] ?? null,
            'options' => $opts
        ];
    }
    echo json_encode(['success' => true, 'questions' => $out]);
    exit();
}
// Scoring Logic
if ($action === 'submit') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $userAnswers = $input['answers'] ?? [];
    $score = 0; $total = count($yorubaData['assessment']);
    foreach($yorubaData['assessment'] as $index => $q) {
        $submitted = $userAnswers[$index + 1] ?? '';
        if (strtoupper($submitted) === strtoupper($q['answer'])) $score++;
    }
    echo json_encode(['success' => true, 'score' => $score, 'total' => $total, 'percentage' => round(($score/$total)*100, 2)]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="yo">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Vector Learn — Yorùbá CBT</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #fefdfc; margin: 0; padding: 0; display: flex; justify-content: center; min-height: 100vh; }
        .container { max-width: 900px; width: 100%; margin: 40px auto; background: white; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding-bottom: 20px; }
        .steps { display: flex; justify-content: space-between; padding: 12px 20px; background: #f7f7f7; font-size: 14px; border-bottom: 1px solid #eaeaea; border-radius: 40px; margin: 20px auto; width: 85%; }
        .steps .active { color: #007aff; font-weight: 600; }
        .form-box { background: linear-gradient(90deg, #4facfe, #43e97b); color: white; text-align: center; padding: 15px; font-weight: 600; margin: 0 25px 20px 25px; border-radius: 12px; }
        .question-container { margin: 0 25px 25px 25px; padding: 25px; border: 1px solid #e6eaf0; border-radius: 12px; background: #fafafa; min-height: 250px; }
        .instruction-box { background: #e3f2fd; border-left: 5px solid #007aff; padding: 12px; margin-bottom: 15px; font-size: 15px; font-style: italic; color: #333; line-height: 1.6; }
        .question-text { font-size: 18px; font-weight: 600; color: #243246; margin-bottom: 20px; }
        label.option-label { display: block; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #e0e0e0; cursor: pointer; transition: 0.2s; background: white; }
        label.option-label:hover { background: #f0f7ff; }
        .nav-footer { display: flex; justify-content: space-between; padding: 0 25px; gap: 10px; }
        .nav-btn { background: #43e97b; border: none; color: white; font-weight: bold; padding: 12px 25px; border-radius: 8px; cursor: pointer; flex: 1; }
        .nav-btn:disabled { background: #ccc; }
        .question-nav { text-align: center; margin: 20px 0; padding: 0 25px; display: grid; grid-template-columns: repeat(10, 1fr); gap: 5px; }
        .question-nav a { display: inline-block; padding: 8px 0; background: #f0f0f0; color: #007aff; text-decoration: none; border-radius: 50%; font-weight: 600; font-size: 12px; }
        .question-nav a.active { background: #007aff; color: white; }
        .question-nav a.answered { background: #4caf50; color: white; }
    </style>
</head>
<body>

<?php include 'topnavbar.php'; ?>

<div class="container" id="exam-container">
    <div class="steps">
        <span class="active">Ìpín Kìíní</span>
        <span>Àbájáde</span>
    </div>
    
    <div class="form-box" id="header-title">Vector Yorùbá CBT 2024</div>

    <div class="question-container">
        <div id="instruction-area" class="instruction-box" style="display:none;"></div>
        <div id="question-text" class="question-text">Loading...</div>
        <div id="options-area"></div>
    </div>

    <div class="nav-footer">
        <button class="nav-btn" id="prev-btn" onclick="nav(-1)">Sẹ́yìn</button>
        <button class="nav-btn" id="next-btn" onclick="nav(1)">Tẹ̀síwájú</button>
    </div>

    <div class="question-nav" id="grid-nav"></div>
    <div style="padding: 0 25px;"><button class="nav-btn" style="width:100%; margin-top:10px; background:#dc3545;" onclick="submitExam()">Pari Idanwò</button></div>
</div>

<script>
let questions = [];
let currentIndex = 0;
let userAnswers = {};

async function init() {
    const res = await fetch('?action=get_questions');
    const data = await res.json();
    questions = data.questions;
    renderGrid();
    show(0);
}

function show(idx) {
    currentIndex = idx;
    const q = questions[idx];
    document.getElementById('header-title').innerText = `Yorùbá CBT 2024 — Ìbéèrè ${idx + 1}`;
    document.getElementById('question-text').innerText = `${q.questionId}. ${q.question}`;
    
    const inst = document.getElementById('instruction-area');
    if(q.instruction) { inst.innerHTML = `<b>Àyọkà:</b><br>${q.instruction}`; inst.style.display = 'block'; }
    else { inst.style.display = 'none'; }

    const optArea = document.getElementById('options-area');
    optArea.innerHTML = '';
    q.options.forEach(o => {
        const checked = userAnswers[q.questionId] === o.optionId ? 'checked' : '';
        optArea.innerHTML += `<label class="option-label"><input type="radio" name="q" value="${o.optionId}" ${checked} onchange="save('${q.questionId}','${o.optionId}')"> ${o.optionId}. ${o.text}</label>`;
    });

    document.getElementById('prev-btn').disabled = idx === 0;
    document.getElementById('next-btn').innerText = idx === questions.length - 1 ? "Ìparí" : "Tẹ̀síwájú";
    renderGrid();
}

function save(id, val) { userAnswers[id] = val; renderGrid(); }

function nav(step) { let n = currentIndex + step; if(n >= 0 && n < questions.length) show(n); }

function renderGrid() {
    const g = document.getElementById('grid-nav'); g.innerHTML = '';
    questions.forEach((q, i) => {
        let cls = i === currentIndex ? 'active' : (userAnswers[q.questionId] ? 'answered' : '');
        g.innerHTML += `<a href="javascript:void(0)" class="${cls}" onclick="show(${i})">${i+1}</a>`;
    });
}

async function submitExam() {
    if(!confirm("Ṣé o fẹ́ fi iṣẹ́ rẹ ránṣẹ́?")) return;
    const res = await fetch('?action=submit', { method: 'POST', body: JSON.stringify({answers: userAnswers}) });
    const data = await res.json();
    document.getElementById('exam-container').innerHTML = `<div class="form-box"><h1>Àbájáde: ${data.score} / ${data.total} (${data.percentage}%)</h1><button class="nav-btn" onclick="location.reload()">Tun Ṣe</button></div>`;
}

init();
</script>


<?php include 'footer.php'; ?></body>
</html>