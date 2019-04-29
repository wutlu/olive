<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Twitter\StreamingUsers;

use App\Elasticsearch\Document;

use System;

use Term;

use App\Olive\Gender;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test komutu.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $gender = new Gender;
        $gender->loadNames();

        $items = [
'RT_Erdogan',
'meral_aksener',
'vekilince',
'hdpdemirtas',
'kilicdarogluk',
'HDPgenelmerkezi',
'Ahmet_Davutoglu',
'06melihgokcek',
'tcbestepe',
'dbdevletbahceli',
'cmlymz',
'KSE0548',
'necatiyilmazank',
'smradyoodulleri',
'tvyocom',
'TCMeclisBaskani',
'solhaberportali',
'AKKADINGM',
'gercekgundem',
'CRI_Turkish',
'MedyaEgeHaber',
'stargazete',
'odatv',
'yeniakit',
'ulketv',
'TTDestek',
'kuveytturk',
'gelecekegitimde',
'haciugurpolat',
'DikenComTr',
'b_hakimiyet',
'temel67',
'sinefesto',
'Ozguruz_org',
'yenisafakwriter',
'platindergisi',
'twitburc',
'kartalbld',
'parafcard',
'sehir_tiyatrosu',
'ibb_kultur',
'EminHalukAyhan',
'FidelOKAN',
'mustafabalbay',
'indigodergisi',
'MedyaEgeGundem',
'arif_cayan',
'trtmuzik',
'Morhipo',
'sabriisbilen',
'feyzullahdogru',
'HuseyinSamani',
'vasipsahin',
'zeyalkis',
'Canon_TR',
'KoctasDestek',
'runnersworldtr',
'HurriyetEmlak',
'KonakBel',
'mavimasa',
'AntalyaTSO',
'ismek_istanbul',
'inciaku',
'adnankosker',
'AjansHaberResmi',
'aheemeklilik',
'ismailedebali',
'MetroTR',
'Enza_Home',
'trlomography',
'semapekdas',
'UIPTurkiye',
'Aksigorta',
'eyupbelediyesi',
'seferlevent',
'JollyJokerAnt',
'kirazzmevsimi',
'SelimTemurci',
'acunilicali',
'XHTurkey',
'ZubizuApp',
'auslupehlivan01',
'SehitkamilBel',
'emir_yaziyor',
'orkid',
'evrengurgenli',
'HyundaiTurkiye',
'TRTEvSinemasi',
'LezzetYolShowTV',
'UnoLezzetleri',
'AlMasdarTurkce',
'tictoctr',
'enveryilmazordu',
'markafoni',
'askidanevar',
'MErgun_Turan',
'KKnesoyledi',
'battalilgezdi34',
'TurkiyeIsKurumu',
'NureddinNebati',
'aliaktas7',
'veneziamega',
'diyarbakirbld',
'ihhgenc',
'HuseyinYayman',
'meteoroloji_twi',
'Aksiyonisci',
'karacaonline',
'alemfmcom',
'idefixcom',
'BurgerKingTR',
'GurselBaran',
'DocDrLutfuSavas',
'fbtv',
'GunaydinGazete',
'AvOrhanKIRCALI',
'YARGICI_TR',
'MuharremErkek17',
'ntvradyo',
'DogaOkullari',
'Pamukkale',
'ahmetakin',
'idrisgulluce',
'sahinbeybel',
'07ibrahimaydin',
'BaybaturMurat',
'kamilaydinmhp',
'huseyinakin_',
'InvestAZ',
'bahcemarket',
'HaberturkRadyo',
'FENERCELL',
'sislimyo',
'BSHTurkiye',
'Can_Cocuk',
'denizlibld',
'boxofficeturkey',
'ozdilekpark_ist',
'MyTEB',
'CNNTURKProgram',
'kamilkoccom',
'_NTCMedya',
'selcukbalcii',
'SakaOfficial',
'akmina',
'BMWMotorrad_TR',
'celal_adan',
'mrtclkr',
'AlgidaTurkiye',
'avonturkiye',
'SelamiSahinOFCL',
'bisavorgtr',
'aktaserdogan',
'Syncaglar',
'akbasogluemin',
'n11com',
'BesiktasJK',
'UlkerAlbeni',
'elifdizisifan',
'hidayet_karaca',
'dw_turkce',
'AhmettPoyraz',
'futbolarena',
'ATuncayOzkan',
'bakiersoymhp',
'hamzayerlikaya',
'akpartiistanbul',
'osmannuritopbas',
'vekilince',
'Tika_Turkey',
'MHP_Bilgi',
'HDPgenelmerkezi',
'Akparti',
'LutfuTurkkan',
'Besirdernegi',
'izmiroozevenue',
'ammaracarlioglu',
'SaadetPartisi',
'MhpTbmmGrubu',
'BirGun_Gazetesi',
'avhuseyinkacmaz',
'Ahaber',
'tcbestepe',
'BeratAlbayrak',
'mehmetozhaseki',
'MSTanrikulu',
'LeventUzumcu',
'GSBgenclikproje',
'ceyhunirgil',
'DudekSenay',
'SavciSayan',
'mahirunal',
'06melihgokcek',
'sinemums',
'gurselerol62',
't24comtr',
'yirmidorttv',
'uzunabdurrahman',
'trt1',
'eskisehirbb',
'veliagbaba',
'firatinblogu',
'CeydaBC47',
'yeniasya',
'tvnet',
'BursaTSO',
'_ibrahimaydemir',
'tv8',
'BesiktasJKMuze',
'CarrefourSA',
'KIATurkiye',
'halkbanksizinle',
'medyafaresi',
'canyayinlari',
'qnbfbdinliyor',
'Nef_Style',
'FuatKoktas55',
'okurdergi',
'FaikTUNAY',
'kocaelivaliligi',
'sagliklicozum',
'duruayfatih',
'pinarsuofficial',
'GOPBelediyesi',
'farukcelikcomtr',
'DBEdbe',
'RufflesTurkiye',
'mngkargo',
'PentaTeknoloji',
'GSSatranc',
'AvivaSAEH',
'ulusal_post',
'radyocularcom',
'SozcuEgitim',
'MvMehmetdemir',
'GSKKurek',
'Alikev_org',
'ziyaselcuk',
'yavuzagiraliog',
'myeneroglu',
'gazetesozcu',
'akgencistanbul',
'cnnturk',
'HelalEtTRT',
'cbabdullahgul',
'ekrem_imamoglu',
'mhpesinkara',
'BA_Yildirim',
'ImamTascier',
'AlaattinCAGIL',
'eczozgurozel',
'evrenselgzt',
'ayhanbilgen',
'fatihportakal',
'halktvcomtr',
'Y_Buyukersen',
'MedyaEge',
'abcgazete',
'ofatihsayan',
'canitti',
'cumhuriyetgzt',
'SonDakikaTurk',
'umitozdag',
'dirilispostasi',
'Vahit_Kiler',
'ali_ihsanarslan',
'drkerem',
'ensonhaber',
'forzabesiktas',
'mansuryavas06',
'tele1comtr',
'nevintaslicay',
'gokcenenc07',
'bayramsenocak',
'abuyukgumus',
'Besiktas',
'oznurcalik',
'AnkaraValiligi',
'KahramanMemis',
'jsarieroglu',
'ulusalkanal',
'OyveOtesi',
'ntv',
'Sabah',
'selahattingrkn',
'recep_konuk',
'bulentmumay',
'aykuterdogdu',
'myildizdogann',
'bulenttufenkci',
'haber365',
'trthaber',
'orhansaribalchp',
'HKahtal',
'murataydintr',
'halukpeksen',
'rfadiloglu',
'kilicdarogluk',
'sozluk',
'hakanhanozcan',
'gergerliogluof',
'MustafaSentop',
'MetinUca',
'startv',
'aslandegirmenci',
'Alinuraktas70',
'FatihYagciResmi',
'dinlekazantv',
'mehmetcinar44',
'atabenli',
'haluk_levent',
'atvcomtr',
'GoalTurkiye',
'serdar_cam',
'necdetunuvar',
'tunayuxel',
'jandarma',
'kelyazar',
'TVSemerkand',
'yasinkartoglu',
'MehmetBerkErgin',
'euronews_tr',
'enveraysevera',
'mtahmazoglu',
'cgtyakmn',
'EvaHamamci28',
'TSKGnkur',
'cakir_rusen',
'iyiparti',
'E_SemihYalcin',
'takvim',
'fuatoktay06',
'MMustafaSaritas',
'BBahadirErdem',
'OsmanGulacarTR',
'markaresayan',
'ultrAslan',
'ucurbenipegasus',
'hotar_nukhet',
'alperaltun',
'MeterM',
'bahcesehir_k12',
'drhaluksavas',
'NTVBilimveTek',
'Muratozen1903',
'DirilisDizisi',
'mevlutuysal_bsk',
'yenisafak',
'EremSenturk',
'htahsinusta',
'ackilic76',
'suleymansoylu',
'abdullahciftcib',
'SevketApuhan',
'utkucakirozer',
'savascifox',
'anadoluajansi',
'Muazzezorhn',
'cihangirislam',
'beINSPORTS_TR',
'arzuerdemDB',
'fahrettinaltun',
'menderesturel',
'GrihatHaber',
'MaliGuller',
'ozgulofficial',
'Haberturk',
'AKGenclikGM',
'ahmetcakir44',
'aktif_haber',
'MehmetCanbegOfc',
'AFADBaskanlik',
'ismailsaymaz',
'huryazarlar',
'AACanli',
'EmineErdogan',
'omerrcelik',
'zeynabelle',
'TwiterSonDakika',
'SeymaDgc',
'defacto',
'ahmetyaziyo',
'f5haber',
'BeyogluBld',
'AydinlikGazete',
'slymnoz',
'gurseltekin34',
'sercanhamzaolu',
'iletisim',
'turksat',
'GultekinBerkant',
'BozgeyikKenan',
'iffetpolat',
'nejatkocer',
'TCTarim',
'Aksam',
'suyorumcusu',
'akinipek01',
'asporcomtr',
'CNNTURKSpor',
'aydinsengul35',
'Metin_Senturk',
'BocekMuhittin',
'Sibelyigitalp',
'selvacam',
'AdemMuratYucel',
'yenisafakspor',
'Bursasporumcom',
'trpresidency',
'enezozenreal',
'kamudanhaber',
'trtarsiv',
'yukselmuslum27',
'yildirimkaya40',
'zentoz',
'ceza_ed',
'orhanaydin6',
'SedefKabas',
'memurlarnet',
'istabip',
'K24Turkce',
'CagdasYasamDD',
'ozanbingoll',
'DrKerimoglu',
'VeyselEroglu',
'HilmiDemir60',
'ihsansenocak',
'irfanaktans',
'ankarayazifilm',
'icesur',
'kilicarslan_is',
'alparslankuytul',
'huseyintarik',
'saidercan',
'Digiturk',
'mehmedmus',
'TayipTemel',
'Huseyin072',
'avhilmibilgin',
'faikoztrak',
'hasansoylemez',
'ABabuscu',
'tgrthabertv',
'DIBAliErbas',
'cumaliatilla21',
'kemalcelik007',
'ustunezer',
'TurkgunGazetesi',
'tcmeb',
'Yildiraycicek9',
'akerimtas',
'T24spor',
'hilmiturkmen34',
'Bilalisgoren',
'halilozcan63',
'islamsozler',
'sinanates16',
'TcellPlatinum',
'PopSciT',
'OlcayKilavuz',
'csbgovtr',
'avrasyaanket',
'DrHabip',
'mkulunk',
'BloombergHT',
'Antalyaspor',
'55erhanusta',
'ShowTV',
'FatmaSahin',
'LokmanCagirici',
'oiszor',
'haykobagdat',
'itu1773',
'AAsumanErdogan',
'cansunali29',
'mustafatuna0606',
'gazeteduvar',
'ckalebelediye',
'Cekmekoybeltr',
'CRizesporAS',
'abdibaktur',
'TCBEBKA',
'ZeybekciNihat',
'bakisimsekmhp',
'izmirhim',
'akpartimalatya',
'milliyet',
'UfukDemiray',
'mkirikkanat',
'aydinmsavas',
'selinsayekboke',
'tugvaTR',
'isyatirim',
'Adem_Gunes',
'mehmetsekmen',
'zorlu77',
'isbakas',
'EnverYucel',
'Acuncom',
'eminsimsekmus',
'SinpasGYO',
'NadirOfc',
'zekikayahan',
'_yusufcoskun',
'keremsedef',
'GalatasaraySK',
'harun_tekin',
'KadirMisiroglu',
'hasantuncel61',
'MehmetAliDim',
'bbismailerdem',
'veyisates',
'_fatihsahin',
'superhaberspor',
'uzunkokcan',
'HanefiAvci2014',
'mustafaatas',
'NTVKSanat',
'gunes_gazetesi',
'noluyotv',
'murat_kurum',
'kanal7',
'MilatGazete',
'Kandilli_info',
'Hasanozgunes1',
'Siir_Sokaktadir',
'tcsavunma',
'hasansvri',
'habererk',
'orhanyegin',
'kadirsekerci3',
'HemmedAliAslan',
'onudabitirin',
'kbbmustafaak',
'azmiekinci34',
'pironic2121',
'HaberturkTV',
'tahsintarhan',
'iBRAHiMAY_',
'EmniyetGM',
'meraldanis',
'kemalpekoz',
'Avkthalilozturk',
'evrengoz',
'SerpilKemalbay',
'ihacomtr',
'mackolik',
'Gazete_Yenicag',
'ORHANOSMANOGLU',
'GundemOtesi',
'KadriGursel',
'Dogruhaber',
'Ali_Oztunc',
'mt_goksu',
'apolatduzgun',
'Teknoloji',
'IlknurInceoz',
'GSfutbolakademi',
'akittv',
'ismailozdemirrr',
'koncabesime',
'gztcom',
'fizy',
'resitkurtt',
'FOXTurkiye',
'DemokratHaber',
'malicevheri',
'gulenderacanal',
'Keskin_Huseyin',
'siring',
'denizcoban_',
'istanbulbld',
'KararHaber',
'AyseSibelErsoy',
'thehukumdar',
'av_ishakgazel',
'AvHalilFirat',
'A_Boynukalin',
'ahmet_uzer27',
'TC_icisleri',
'ihhinsaniyardim',
'NumanKurtulmus',
'enginaltaychp',
'trttv',
'Haber7',
'mehmeterdogan27',
'MustafaCanbey',
'sabihadogann',
'gokeroloji',
'chemedya',
'velisacilik',
'shiftdeletenet',
'MehmetFendogluu',
'siirist',
'fanatikcomtr',
'ulkucumedyacom',
'oencueonur',
'CelikcanMahmut',
'HaberRotaniz',
'AlperTasdelen06',
'ingbankturkiye',
'haydaraliyildiz',
'YapiKredi',
'cenginyurt52',
'timasyayingrubu',
'avaliozkaya',
'GSBgenclik',
'varank',
'Anadolu_Univ',
'avishaksaglam',
'FikretHayali',
'osmanllocaklari',
'HasanCucuk',
'Hayati_Yazici',
'a_pekyatirmaci',
'milligazetecom',
'AbdulkadirOzl',
'HaberSau',
'TekinYayinevi',
'ozatasomer',
'NevvalS',
'abdullahdogru01',
'ZehraZumrutS',
'AkpartiAnkara',
'UNICAnkara',
'gulaysamanci42',
'emrullahisler',
'zaytung',
'CHPKadinKolu',
'NiluferBel',
'KadirGokmenOgut',
'saffetsancakli',
'Demirsporum',
'AdsKulubu',
'trtavaz',
'kadirguntepe',
'HBTurkoglu',
'marmaraunv',
'DenizDinliyor',
'arahmangok',
'webtekno',
'BskAliKilic',
'bornovabld',
'PembeNarCom',
'trtspor',
'Vatan',
'DiyanetTV',
'Vatan_Partisi',
'HaberGlobal',
'turkiyegazetesi',
'HuseyinSozlu',
'sefa_said',
'drsadiyazici',
'DiyanetVakfi',
'ziraatbankasi',
'PrHasanKalyoncu',
'TurkcellTVPlus',
'VakifKatilim',
'KVKKurumu',
'leylasahinusta',
'FBBasketbol',
'arkiteracom',
'Akin_Yavuz',
'cnnturktekno',
'melihaltinok',
'fotomac',
'drtasdogan',
'_aliyalcin_',
'AhmetAYDIN_02',
'barbarosansalfn',
'umraniyebeltr',
'baybadu',
'Haberdar',
'SYKPgenelmerkez',
'timeturk',
'KanalD',
'AirportHaber',
'raninitv',
'trtturk',
'ozkan_yalim',
'MSaitKirazoglu',
'konyaspor',
'agdorgtr',
'baskanzorluoglu',
'ismailaga_cami',
'mehmetmusic',
'munirkaraloglu',
'TmrOsmanagaoglu',
'internethaber',
'AdnanKavustur',
'enverkilicaslan',
'zaraofficialtr',
'hopdedikayhan',
'AntalyaValilik',
'sedat_peker',
'donanimhaber',
'UmutOranCHP',
'cemkucuk55',
'M_Sarigul',
'burcu_biricik',
'ahmet__yurdakul',
'istikbal',
'aefakibaba',
'OncuGenclik',
'MustafaKurdas',
'ahhakverdi',
'ilkeHaberAjansi',
'Kosgeb',
'aysekesir',
'StarDisHaberler',
'SafakSina1903',
'ibbPR',
'csagir2015',
'yusufbeyazit60',
'TRKaganKaya',
'w0xic',
'CerModern',
'yasaryldrmMHP',
'GSTV',
'alibabaoglan',
'adil_celik10',
'TFF_Org',
'StarbucksTR',
'webaslan',
'Bilfen',
'gokhanozbek',
'CandanBadem',
'HurriyetSeyahat',
'saglikbakanligi',
'EnsarVakfi',
'avhamzadag',
'SIRIN_UNAL',
'erdogantok55',
'tarimyazari',
'muhammedcevdet',
'CEVDETKAVLAK',
'avSelmanEser',
'SuleymanKrmn',
'Ankaragucu',
'fikret_karakaya',
'sbYusufAlemdar',
'TwitBakani',
'unsalim',
'AlshababClubUAE',
'tivibuspor',
'furkan_azeri',
'gelisimedu',
'buyukataman',
'HVahapoglu',
'halitbekiroglu',
'recepgurkan',
'61saat',
'msubasioglu',
'emrepolatlive',
'c_ahmethoca',
'botan_lezgin',
'ufukcoskunn',
'MehmetPerincek',
'adalet_bakanlik',
'genclikbirligi',
'gencliksporbak',
'ulvisaran',
'myksosyalmedya',
'av_osmantoprak',
'didem_soydan',
'SivassporKulubu',
'TulayHatim',
'ahmetyesiltepe',
'T_Karamollaoglu',
'KSKBasket',
'ordusporkulubu',
'drfahrettinkoca',
'okanbayulgen',
'erkanakcay45',
'habibeocal46',
'belginuygur10',
'_cevdetyilmaz',
'VolkanMCoskun',
'shaber_com',
'KeLebegindansi',
'Metiskitap',
'MehmetErsoyTR',
'sevilayyaziyor',
'corbadatznolsun',
'oktay_saral',
'temavakfi',
'TCKulturTurizm',
'HudaKaya777',
'ceydaerenler',
'HTutuncu',
'ipekyolubel',
'MemorialSaglik',
'losev1998',
'drmehmet_goker',
'yeldagokcan',
'ntvspor',
'A_HasimBaltaci',
'DenizUlke',
'maveritast',
'ethemsedef',
'emrebagce',
'Sdk56hdp',
'EceSeckinCom',
'istanbuledutr',
'TRTRadyo3',
'TCEnerji',
'atilaaydiner',
'muhammetbalta61',
'CaferUZUNKAYA',
'fkurtulan33',
'hozsavli_',
'hsanverdi',
'BiPMobil',
'hayriituncc',
'tulaykaynarca',
'PinarAYDINLAR',
'malatyagercek',
'molatik',
'EbruGundes',
'EbubekirBal2',
'tvbjk',
'MilliTakimlar',
'IKUedu',
'aylin_kotil',
'mustafarmagan',
'sahin_tin',
'avyurdunuseven',
'KamuranToktanis',
'AcibademSaglik',
'TK_HelpDesk',
'geccecom',
'ibrhmvarli',
'RemziyeTosunHDP',
'MuratKazanasmaz',
'teblekadin',
'Harun_Karacan',
'Hakan_cavusoglu',
'haliletyemez',
'GayeUsluer',
'onediocom',
'Bigumigu',
'mehmetakarca',
'muratemirchp',
'pegasusdestek',
'iozyavuz1963',
'bekirpakdemirli',
'1Diyemedim',
'VodafoneDestek',
'officialalihan',
'metroistanbul_',
'Kizilay',
'ucankuscom',
'PayitahtEsFilm',
'jiskenderoglu',
'mvcemalozturk',
'u_ibrahim_altay',
'MEnesimir',
'modasahnesi',
'canankalsin',
'OZLEM_CERCIOGLU',
'RenaultTurkey',
'umutkervanivkf',
'ukarakullukcu',
'AmberTurkmen',
'UABakanligi',
'huseyinburge',
'turgev',
'TRSonyMusic',
'tv360comtr',
'kafadergisi',
'uskudarbld',
'FBvoleybol',
'etnospor',
'ttborgtr',
'VahdetErdogan',
'arslandidem',
'HARUNKARACA1',
'husnuarkan',
'Fenerbahce',
'say_cem',
'ugur_isilak',
'ArnavutkoyBel',
'DrSinanOgan',
'vysltiryaki',
'ANTALYABB',
'mehmetsari2727',
'yusufsametcakir',
'CengizDMRKY47',
'efkanala',
'ajansspor',
'AdiyamanBelTR',
'avahmetozdemir',
'mgirgin54',
'mutlaydemir',
'halitertugrull',
'Tamer_Dagli',
'AliSahin501',
'MaltepeBelTr',
'SelmanOztrk',
'MucahitArinc',
'KRPkitap',
'turkihracat',
'KralFM',
'DeliSaykolog',
'asimbalci',
'ibbBeyazmasa',
'Turkcell',
'CK_Samsun',
'bingolvaliligi',
'konhaber',
'selim_karadavut',
'mustafadundar65',
'AhmedYusufm',
'Av_umituysal',
'sanliurfabld',
'esindarcabogaz',
'baskanbsakalli',
'Kemalbulbull',
'y_ozgurpolitika',
'mgulluoglu',
'Akif_Hamzacebi',
'hayratyardim',
'canugur1987',
'GokhanBicici',
'demirdenizchp',
'BursasporSk',
'bpasabelediyesi',
'AvFatmaBenli',
'sporx',
'Tdagbld',
'nadiryldrm_',
'ahmetselimkul',
'osmanzolan',
'izmirbld',
'cagdassevinc',
'CHPKartalilce',
'FatihBelediye',
'TMMOB1954',
'halilikTR',
'SonDakikaMAG',
'Tamindir',
'drbetulsayan',
'Haberler',
'ebrudestaninan',
'fatmakaplan',
'TurkParalimpik',
'HBRTurkiye',
'resmiyunusemre',
'mislicom',
'radyospor',
'siyervakfi',
'vygurel016',
'SporSonDakikaTR',
'AvSerkanBayram',
'eksiseyler',
'ozcanisiklar',
'setavakfi',
'EverestKitap',
'ensartopcu',
'ErikliLezzeti',
'feyzaltun',
'sezerkatirciogl',
'ASRoma_Turkey',
'avabdullahguler',
'Argostroloji',
'HalilSoyletmez',
'deikiletisim',
'goyucel',
'kutupzencisi',
'Saliha_Aydeniz',
'kultur_istanbul',
'MazlumCimen',
'ersoyruhi',
'multecihakder',
'BilalEksiTHY',
'FeyziBerdibek1',
'CHPKAZIMKURT',
'erolkaya_ist',
'NailOlpak',
'YildizzTilbee',
'yunusemresel',
'AhmetSCeylan',
'bariishbozkurt',
'yasarkirkpinarr',
'kadira59',
'Olcanadin',
'ntv_yasam',
'isilyucesoy_123',
'DrCetinArik',
'EmineNurGunay',
'ispark',

'BesiktasBel',
'pronetguvenlik',
'garantiemekllk',






















'gurbuzemre',
'mynet',
'gazeteistiklal',
'GarantiyeSor',
'ailevecalisma',
'DigiturkDestek',
'sabah_tv',
'hmutluakpinar',
'netgazete',
'ahmet_iyimaya',
'ebebek',
'TC_istanbul',
'mustafademir',
'beylikduzubeltr',
'32gunTV',
'NergisAtci',
'tepebasibeltr',
'ayedasdestek',
'CNNTURKSaglik',
'salimuslu__',
'GittiGidiyor',
'TEB',
'qnbfinansbank',
'devtiyatro',
'IsBankasiDestek',
'kadinvekadin',
'SodexoAvantaj',
'TatbikatSahnesi',
'Toki_Kurumsal',
'Migros_Turkiye',
'Adana_Bld',
'boyneronline',
'NTVEmlak',
'SeninBankan',
'gizlikitaplik',
'kulturveyasam',
'istacistanbul',
'eminevim',
'sehir_hatlari',
'kocaelibld',
'balparmak',
'Ayrinti_Dergi',
'anadolujet',
'zenpirlanta',
'ozdilekavm',
'Guldur_Guldur',
'bursasportv73',
'AytekinAtas',
'BHTYRNGN',
'ekonomi_isbank',
'AnadoluGrubu',
'paraniyonet',
'harunserkan',
'mustafashn44',
'SozcuHayat',
'mbfh',
'sefkatcetin',
'mimarlikarsivi',
'inkilapkitabevi',
'sensodynetr',
'mavimusteri',
'AxessCard',
'Deniz_Yatirim',
'Dr_Demircan',
'UlkerHanimeller',
'DaskTr',
'familyandlabour',
'Gezimanya',
'winneRTweet',














'BeyazGazete',
'dhainternet',
'BKocamaz33',
'm_eminyildirim',
'TRTBelgesel',
'haber_61',
'teyitorg',
'CankayaBelediye',
'olgunatila',
'OlayHaber_Bursa',
'NazimMavis',
'TBMMresmi',
'Hasan_Guzeloglu',
'iettdestek',
'AKUT_Dernegi',
'ahmetgundogdu01',
'BagcilarBld',
'EToprakCHP',
'FatmaToru',
'nihatsirdar',
'istanbulotobus',
'bigparacom',
'BusinessHTcom',
'cestomina',
'bursabuyuksehir',
'GratisTr',
'Anadolu_Sigorta',
'beINIZTV',
'TimeOutIstanbul',
'aykurtnuh',
'filliboya',
'KoksalAras',
'LittleCaesarsTR',
'UlkerMIM',
'CNNTURKKSanat',
'rasimacar',
'avcimucahit',
'DreamTvOfficial',
'NNTurkiye',
'BizimToptan',
'MuratpasaBld',
'mebpersonelCom',
'colins',
'kutahyaporselen',
'rizasumer',
'idea_soft',
'engintekintas',
'gulayyedekci',
'_onurakay_',
'JOLLYJOKERank',
'beta_tea',
'onderihl',
'cekulvakfi',
'bellona',
'UPSDestek',
'LUKOILTurkey',
'sendegonulver',
'BusinessweekTR',
'HusnuyeErdogan',
'TEVKurumsal',
'BizimMutfak',
'Sanliurfaspor',
'M_BilalAydin',
'UlkerSportArena',
'HeForSheTurkiye',
'HudaParKadin',
'ibrahimtenekeci',
'cnnturkuni',
'AhmtCamyar',
'bybekirbozdag',
'siberalemcom',
'AYARHikmet',
'ozkanyaziyor',
'VeysiKaynak',
'usaksportif',
'tv100',




















'TMOK_Olimpiyat',
'Etstur',
'mustafaelitas',
'MensHealth_TR',
'_TJK_',
'ifarukaksu',
'samiltayyar27',
'akgencfatih',
'leyladansonra',
'gokcebugra',
'lofficieltr',
'nihatcftc',
'OkcularVakfi',
'NuhAlbayrak',
'alimtunc',
'MAtillaKaya',
'yenergunes',
'ciftpelin',
'm_ilicali',
'cihanpektas1',
'uncuoglurecep',
'aytugatici',
'BugdayDernegi',
'vahap_coskun',
'CNNTURKEko',
'TURKPATENT',
'Enparacom',
'trtcocuk',
'yalitimveenerji',
'BKMExpress',
'yayinekrani',
'hayatinsesiniac',
'hayribaracli',
'Dogtas',
'tesyev',
'cumaicten',
'MINI_Turkiye',
'Darussafaka',
'burakballii',
'BorusanOto',
'NurettinnSimsek',
'TurkSporu',
'halisdalkilic',
'BrisaTurkiye',
'ayyapim',
'EyyupYanac',
'SuperFreshTR',
'denizfeneriorg',
'OfficialAlican',
'Tubitak',
'TRThaberekonomi',
'GIMDES',
'KSurekli',
'Tahir_Akyurek',
'zahidinkosus',
'habervaktim',
'Trt1Milat',
'gencsaadetist',
'KizlarSoruyor',
'AydinliogluA',
'baskentunv',
'nebilevren',
'AyhanSeferUSTUN',
'Number1tvfm',
'Dove_TR',
'KASIM_BOSTAN',
'grupanya',
'cemalokanyuksel',
'KKKfilm',
'VeetTR',
'YandexDestek',














'BSKulgurgokhan',
'GazisehirFK',
'emrealmas_',
'herkesicinCHP',
'sadakatasi',
'dr_sozdemir',
'iddef',
'JOLLYTUR',
'TEBleGirisim',
'cemal_canpolat',
'kulmustafa77_ac',
'barisesen',
'kazimarslan20',
'TV8Bucuk',
'serdarbagtir',
'AlemDergisi',
'FurkanSak2',
'ufuk_akkaya',
'Nadirkitap',
'kelkitlioglumrt',
'ayyildirim1',
'DenizDepboylu',
'gamzeilgezdi',
'Sabahekonomi',
'TVForgtr',
'KocaeliBaskani',
'Toyota_Turkiye',
'TYB1978',
'SerkanYetkin',
'hkunv',
'TOBBiletisim',
'Eczacibasi',
'DoguMarmaraMrka',
'Bkmonline',
'BTKKurumsal',
'sahapkavcioglu',
'oguzatbas',
'DMCcomtr',
'HadiOzisik',
'UskCocukUniv',
'Dogadan_Cay',
'survivorturkiye',
'Merkez_Bankasi',
'mediamarkt_tr',
'belbimibb',
'VedatTurgut',
'NtvEgitim',
'rockfestivali',
'dodcomtr',
'beyzapilic',
'lgturkiye',
'AltinorduFK',
'okuyanmehmet',
'HikmetAnil',
'menderesedutr',
'sekerbank',
'abugrasimsek',
'PegasusYayinevi',
'seyhanmuzikcom',
'OnurAir',
'TRT4K',
'mhuseyinyilmaz',
'halim_ylmaz',
'sukru_kolukisa',
'osesturkiye',
'yasartuzun06',
'DominosTR',
'SkylifeDergisi',
'oguzhankocakli',
'mutlumutfaklar',
'MPayakkabi',
'Ulker',
'TCAytunCiray',
'dankek8kek',
'GokhanAkar',
'hilaltv',






















'ersinceliq',
'davutgoksu',
'EAksunger',
'borsaistanbul',
'drilkercitil',
'ZeynelEmre_',
'ilyasseker41',
'efekuruscu',
'iyifikirtrt1',
'ramazancan0071',
'TurkTelekom',
'aykutkuskaya',
'ilhankesici',
'oguzguven_',
'aliozgunduz',
'm_akaydin',
'aysesula',
'emirsarigul',
'bilalcetin1',
'serapduygulu',
'KalkanNecip',
'ciglibelediyesi',
'arslanhasan35',
'BKaraburun',
'TatilBudur',
'CAltunay',
'lescon',
'garantiyatirim',
'corasalih',
'SabanciHolding',
'eskoca26',
'fordturkiye',
'NTVOtomobil',
'taffpics',
'drnevzattarhan',
'cahiteftekin',
'lifecell',
'kariyernet',
'DSmartDunyasi',
'DenizBank',
'mttborgtr',
'sinemasalorg',
'FIAT_TR',
'MBMilliTakimlar',
'bskyapim',
'ismailbilen45',
'mavi',
'niyazinefikara',
'YYD_tr',
'Koctas',
'mehmetpaksu',
'Cesimgokce',
'FGulencomTR',
'ActiviaTurkiye',
'yavuz_cobanoglu',
'kekillimurat',
'MinTMotionPics',
'Umraniyesporr',
'cardfinans',
'mehmetparsak',
'seksendort',
'BelediyeKaresi',
'HayriDemir_',
'LCWaikiki',
'SeyhanBelediye',
'omersoztutan',
'suheylbatum',
'GSDergi',
'banvit',
'buyurburdanbak',


















'abdulhamitgul',
'Emine_Gozgec16',
'radyotrafik',
'SunayAkin',
'zkakkaya',
'AErzurum',
'CHPMuratBakan',
'selcukozdag',
'haber1903com',
'EdremitBelediye',
'MehmetEkinci63',
'CandanYceer',
'AkbankDestek',
'CokGuzelHareket',
'FilizKer',
'KartalAKGenclik',
'AR_TEKCAN',
'CananCCelik',
'mehmeterdem09',
'OfficialOrhan69',
'ceyhunyilmaz',
'SahniSemanorg',
'mehmetgunal07',
'onursaladiguzel',
'CHPTV',
'senayaydemir',
'Kaplanseren',
'SamsunDemir',
'KemalZeybek55',
'odunpazari',
'iekarayel',
'MTanal',
'23YANILMAZ',
'PTTKurumsal',
'leventerden',
'gencmusiad',
'BiTaksi',
'VodafoneTR',
'SBBBasket',
'Akdenizun',
'BTKgovtr',
'yakupsaglam',
'TRTTSR',
'nurselaydogan58',
'OlcayKabaktepe',
'FMVIsikUniv',
'MarketingTR',
'eskiyatv',
'TBF',
'AdnanAybaba_tv',
'yardimeli',
'passolig',
'ibrahimozdis01',
'gokdagantep',
'iyilikhane',
'HudaParMedya',
'GFeyman',
'RadyoFenerbahce',
'msnazli',
'HAYIRPLATFORM',
'asumandabak01',
'mridvano',
'Teknosa',
'BsBasketbol',
'yekta_sarac',
'nahitduru',
'fikircibey',
'Gurkanavci_',
'oyungezer',
'muratcemcir',
'BildenHalis',
'Milliyet_Kitap',
'StoriaTurkiye',















'yilmaztunc',
'a2turkiye',
'kadimdurmaz',
'OpetTr',
'busrasanay',
'UlviYonter',
'av_ihalilyildiz',
'tevfik_uyar',
'MSErdinc',
'serkantopalchp',
'atifcicekli',
'SemihKahramannn',
'turyildizbicer',
'TekinBingolCHP',
'ArzuErdogral',
'recebterzi',
'muratd37',
'aybers',
'nacibostanci',
'mdurmusoglu',
'serbaymansur',
'MevlutDudu',
'KSKorgtr',
'sultangazibeled',
'balikessirbld',
'simitsarayi',
'kalemciler',
'selimdursun06',
'HatayBSB',
'karnaval',
'Goztepe',
'ithakiyayinlari',
'MSerifDurmaz',
'merveeoflaz',
'OA_Bak',
'kanyon_da',
'hsyk35',
'SelcukluBel',
'CitroenTurkiye',
'MaximilesKart',
'MagnumTurkiye',
'ruhsardemirel',
'EmseyHospital',
'PanaFilm',
'peraband',
'ahmetcan',
'TurkiyeXBOX',
'gencsaadet',
'suathayriaka',
'TarlaciSultan',
'mhrrmcan',
'KardesPayiStar',
'ozaysendir',
'KTDestek',
'trtstadyum',
'UgurSogutma',
'OsmanSoyler',
'YapiKrediWorld',
'MorogluCHP',
'avremziaydin',
'gezegen_mehmet',
'Ozgurbacaksiz',
'SosyalDoku',
'aliaslikk',
'iskenderbaydar',
'DrSalihFirat',
'SeksenlerMinT',
'AYMBASKANLIGI',
'OzgurEvren_H',
'PetkimOfficial',
'KnightOnline',
'yildirimbehcet',
'UgurBayraktutan',
'AliYigitCHP',













'fdemetsari',
'hidoturkoglu15',
'Radyo7',
'Turkiye_Spor',
'YediHilal',
'cuneytozdemir',
'SultanbeyliBel',
'aa_spor',
'semihguvenn',
'ac_yaycili',
'Akasyaa',
'GSB_KYK',
'AtillaSertell',
'Hilmi_Dulger79',
'mak_cen',
'alicandir',
'showanahaber',
'atakankurt2',
'makif_yilmaz',
'CanAnl',
'Salimmanav',
'kemalcan',
'bulenttezcanchp',
'CHPBulentOz',
'Lokman_Erturk',
'odakitap',
'Trabzonspor',
'obirseyyah',
'MelisGuvenc',
'akenantanrikulu',
'nihatipoglu',
'iscepisbankasi',
'selcuk_dereli',
'AvsarFilm',
'dogansenturk',
'SiemensTurkiye',
'gkhndinc',
'MuezzinogluDR',
'YasarDogan__',
'orhankrkrt',
'omerdongeloglu',
'TeknoKulis',
'avyusufbaser',
'TBHForgtr',
'barolar',
'canyilmaz1',
'Ahbap_Medya',
'Leadergamer',
'ademgeveri',
'Denizlispor_',
'furkantorlak',
'semakirci',
'onur_akcay',
'muhabbet_kapisi',
'gokselonline',
'aydin_orak',
'HBTONBUL',
'TopacaErcan',
'inan_sur',
'suatkilic',
'MehmetYavuz_06',
'Avturkmen',
'ahmetyildirihdp',
'ErolBilecik',
'UnileverTurkiye',
'bursastore1963',
'enesbatur00',
'AvisTurkiye',
'Galatasaray_TTA',
'Sinan_Yagmur',
'FigenYuksekdag',
'Hseyinzgrgn',
'TRTBelgeselFilm',
'Toramanibrahim',
'nestle_ibmy',
'Ortaoyuncular',
'enginozkoc',


















'sorgunahmet',
'diyanetbasin',
'hulya_unver',
'celalettingvnc',
'alikeskin_tr',
'chiponline',
'goklevent',
'lutfielvan',
'kamilsindir',
'HaberalErkan',
'CetinOsmanBudak',
'FatihOzcan_GS',
'barisince82',
'xtraderx_',
'turanbulent',
'SezaiTemelli',
'SuheylaYS',
'gaziantepUniv',
'tacettinbayir',
'mesutcevik',
'Arifanifav',
'didem_sarman',
'mFatihAktas',
'AdemOzbay',
'FatihAydemirTR',
'Sait__Sahin',
'AhmetYeseviUni',
'ferhatttunc',
'EdirneBel',
'SunExpress',
'OktayOzturk_MHP',
'AktuelArkeoloji',
'trt',
'CglynOgulcan',
'husnususlu',
'AliNaciKucuk',
'MehmetKuzulugil',
'ozgurozkaya',
'erdilyasaroglu',
'tumsiad',
'erdalimrek',
'mert__firat',
'alitekincelik',
'Zinonutumer',
'sehzadedemir',
'gecemusic',
'k_karabukspor',
'fatihyagmur',
'T3Vakfi',
'Mhsnkrmzgl',
'HasanAkbas_',
'bekirgizligider',
'YeditepeUni',
'Voleybol_Bjk',
'ozgenbingol',
'PepsiCoTurkiye',
'YapiKrediPlay',
'YildizHoldingTR',
'YapiKrediadios',
'Kerimalastal',
'intizarOfficial',
'kamalakmustafa',
'HudaParGenclik',
'kenanonalan',
'boracengiz',
'ybagislar',
'PazarlamaTRcom',
'tarkanakilli',
'Barbaros_Akkas',
'DJYASINKELES',
'alpertekbas',
'mancolog',
'infoturkiye',


















'hmzaydg',
'NovartisTurkiye',
'BASF_Turkiye',
'ozanonat',
'_SelinaDogan',
'adnanturkkan',
'OzlemGurses',
'Bilyoner',
'mrecepercin',
'sporxtv',
'ozcanpurcu',
'pbattal',
'esenlerbelediye',
'OzturkSemih',
'DrRecepAkdag',
'elazigspororgtr',
'Huzeyfe_Ylmz',
'ciceksepeti',
'Yenisehirlioglu',
'avcagdascelik',
'Salih_Kapusuz',
'musayildiz',
'MHRS_182',
'cantanriyar1907',
'barispehlivan',
'kzldemirmahmut',
'ekapi',
'gazetecialev',
'dmtevgar',
'tanerkrmn',
'Askdoktoru',
'ciftciece',
'AliTufanKirac',
'emreylmz33',
'UgurMeleke',
'ayhan_ogan',
'losante',
'leventkartop',
'Alanyaspor',
'GSBekagenmud',
'ticaret',
'MetroTurizm',
'savasugurlu2023',
'OSMANLISPOR_FK',
'ilyasakkoyun_',
'Ahmet_Davutoglu',
'Akbank',
'emresaygitv',
'ilyasyalcintas',
'cngzerdem',
'irfanbozan70',
'zyapicioglu',
'haylazmusicorj',
'erhanglryz',
'BuyukcekmeceBK',
'BirlikLeyla',
'avismailaydin',
'selamisekerci',
'AhmetEdip_Ugur',
'oyadogan',
'VG247turkiye',
'AytugAkdogan',
'hasanbasrikurt',
'sinematurk',
'adem_kilicci',
'BaranSeyhan',
'cerkezkoybld',
'MelikeBasmac',
'ulkerstadyumu',
'TIMSProd',
'hayrettin',
'KemalBasbug',

















'nibenka',
'ertemsener',
'drhandantoprak',
'TwitPrensi',
'adnangunnar',
'hursitgunes',
'bahisanalizcom',
'barisakpolat',
'nrdnnereyefilmi',
'Ugurkoc_',
'vekilmahirpolat',
'sonerolgun',
'BeykentUnv',
'MRTTVL',
'AASSM_',
'ozguraras',
'ahmetarslan36',
'ozgurcetin',
'ahmetakpolat',
'niyazikoyuncu',
'AliPulcu',
'hakanergunrock',
'kitapdunyasi',
'fikriisik',
'mneuzunyol',
'yektakopan',
'CinarOzer',
'duzceuniversite',
'nihatakdagon',
'ahmetusta',
'livhospital',
'NefisYT',
'EyyubiyeBel',
'buyuksehirkm',
'KayseriBSB',
'kazarre13',
'avcilarbel',
'Hozkomurcu',
'suatkocer',
'omerserdarcomtr',
'kenanbolukbas',
'mentesebelediye',
'Burberry_Turkey',
'EbruYasarOnline',
'kadirkonuksever',
'emrekbol',
'fsevgili',
'mustafacol_',
'umiterdim',
'koenagadol',
'vwarena',
'EmaarTurkey',
'NormEnder',
'NihatKahveci8',
'hamditopcu53',
'OzcanDeniz',
'GiderAyhan',
'fundaarar',
'gunaydingokhan',
'profdrhalukkoc',
'halklailiskiler',
'kmcmustapha',
'bilgizone',
'AhmetGunestekin',
'ytezcan33',
'borusanotomotiv',
'ahmetbozkus',
'OZZgeozder',
'MYAskiderun',
'emrachuskovski',
'necdetatasev',
'berkatan',
'fpalali',
'kahramanarazz',
















'onuraydin_',
'ersoydede',
'metingunordu',
'drrecepseker',
'gizem_ozdilli',
'mmahmutkilinc',
'BJKOzkaynak',
'BluTVofficial',
'HAYKOCPQN',
'astronomTurk',
'TofasSporKulubu',
'sislibelediyesi',
'sedatyesilkaya1',
'lambda_istanbul',
'haozdemir',
'onurtugrul',
'gokhanzofficial',
'SMinsolmaz',
'bizimyazar',
'altunsalih',
'GucluGokozan',
'muratpazarbasi',
'asumanbayrak',
'esrefziya',
'resulkurt34',
'H_KABZE',
'muratguloglu',
'erdal_elibuyuk',
'ozturkyilmazCHP',
'basketsuperligi',
'guvenlinet',
'senalsarihanchp',
'abdullahbasci',
'BahadirTatlioz',
'hayratvakfi',
'aycanirmez',
'YMSkulubu',
'buraksatibol',
'ztarikdaroglu',
'serdargokalp',
'kocholding',
'ietttr',
'acsancar',
'sinanakcil',
'okanyuksel',
'TCDDemiryollari',
'UniBogazici',
'esinovet',
'sebnembursali',
'AliErcoskun',
'KomediKanali',
'ntahiroglu',
'basakcubukcu',
'cahit_berkay',
'RamazanCakirci',
'DogusUniv',
'alpkirsan',
'Ozge_Uzun',
'cgdgenelmerkez',
'GSSuSporlari',
'KOErdemir',
'dmtzdmr',
'ademoslu',
'seohocasi',
'DDeryaBaykal',
'Canerisb',
'AYSFKRT',
'NilhanSultan',
'ismailkvk',
'ayrintiyayinevi',
'UfukSarica15',
'ihtiyacofficial',
'mustafaceceli',
'_burakcosan',






















'HakanHatipoglu',
'EbubekirSifil',
'sayedar',
'cnnturkoto',
'narvent',
'serkanaltunigne',
'HTSpor',
'Nescafe3u1Arada',
'Emre_Tilev',
'AdemMetan',
'yurtdisiturkler',
'maNgaMusic',
'rahsangulsan',
'ayseacar_b',
'SrTahirBilici',
'umutmurare',
'mmehmetbozdag',
'tarikbeyhan',
'necmihatipoglu',
'Gungorenbld',
'GaziantepBeld',
'borusanholding',
'Astromatikk',
'yeeorgtr',
'ulas_ugras',
'aselimkoroglu',
'Can_Karyagdi61',
'kdurkal',
'mustafaakis',
'Ateizm_Dernegi',
'ERDEMYENER',
'serkanncagri',
'mizginirgathdp',
'gokhantepemusic',
'isbankasi',
'muratgunes_',
'pauedutr',
'VakifBank',
'MesutSarayy',
'cigdemceylannn',
'gripinonline',
'antucom',
'seremtan',
'handefrt',
'gokcedi',
'bulentserttas23',
'Sisman_Kiz',
'CoffeeMateTR',
'omeraydogmus66',
'GokhanSaralTS',
'ClubMedTR',
'eseryenenler',
'Agirmimar',
'hakan_gulec',
'YavuzYigit',
'HakanYilmaz',
'NilhanOsmanoglu',
'ibayram',
'VolkanGorendag',
'Galip_Ozturk',
'utkudemirsoy',
'SuadiyeOvenc',
'DogusOfficial',
'coskuncelik',
'ozkandogan10',
'CenkTorun',
'1VolkanDEMIREL',
'TRTTVFilmleri',
'mahmutboz26',
















'BekirDeveli',
'GQTurkiye',
'gantepso',
'Yaziyor',
'EratalaySevinc',
'utkukali',
'serapyahsiyasar',
'aysekardass',
'MahmutSamiMALLI',
'AxeTurkiye',
'erkankandemir',
'GSEsports',
'hsncml',
'ismail_baki',
'dalierzincanli',
'JihatG',
'BuyukcekmeceBld',
'sedef_sahin',
'kaanmuratyanik',
'DrSuatOnal',
'kahvedunyasi',
'gokceozcancom',
'IbrahimBAYLAN',
'ipeksorak',
'AtilimUniv',
'omradymn',
'Boluspor',
'gulbenergen',
'bediaozgokce',
'lsvdukkan',
'UykusuzDergi',
'OzelTurkoglu',
'TaylanKulacogIu',
'emrekaya',
'moralabs',
'NevComTr',
'AysegulAldinc',
'nrglyslcy',
'sevvalsam',
'TwitDayi',
'ismailAltnsry',
'ironikadamm',
'TugbaHezer',
'duygubal',
'Bahcesehir',
'musacam',
'selenistan',
'etunaakdemir',
'birolguven',
'akinevrim',
'cemseymen',
'FerudunOzdemir',
'emrekarayel01',
'DerdaYasirYenal',
'semra_guzel',
'oguz_tor',
'mehmetzeyd',
'erman35kilic',
'HAK3',
'gulseyazar',
'eceerken',
'ismaildemirci',
'burcugunes',
'Gulden_Sonmez',
'renanbilek',
'neco_ates',
'TwitPrensesi',
'Arzuozyigit',
'OguzBerkayFidan',
'handesubasicom',
'ToxinOfficial',
'aysenbatigun',
'MYildirimResmi',
'TSayisman',
'HTC_TR',

























'missturut',
'DasdemirHilmi',
'baris_oruc',
'enesolgun',
'SariyerBelediye',
'piristinalevent',
'ozgurbakar',
'abdurrahmankuzu',
'mustinetnet',
'ilhancihaner',
'akadir137',
'borakozge',
'AybenOfficial',
'ugurbati',
'aDilipak',
'perizekaliyim',
'DemetAkalin',
'barburjehan',
'gokceozcan',
'OSYMbaskanligi',
'bordomavinet',
'muglaedutr',
'EkremBEkinci',
'TCSanayi',
'Kalan_Muzik',
'yunusemrebld',
'AhmetRifatAlbuz',
'BodrumBel',
'SenayLambaoglu',
'cnnturkoyun',
'BJK_Basketbol',
'kubatcomtr',
'ipekbagriacikk',
'Grogionline',
'unibjkcom',
'bilim_man',
'Turkrapfm',
'BatuhanMUMCU',
'omersntrk',
'CahitBagci',
'tugbayurt',
'SHGM',
'canacun',
'LivaneliZulfu',
'ibrahimmturhan',
'canikligil',
'sahinirmak',
'koksalih',
'HakanKutlu',
'sezgo75',
'fuldenuras',
'AhmetLutfiAkar',
'_selcuksahin',
'umitkantarcilar',
'SamiDundar',
'Rapozof',
'Bengu',
'guvercinihsan',
'terziogluburcin',
'febyotasel',
'muratakin32',
'ezgimola',
'gokhankeser',
'24managementee',
'kimseyokmu_',
'BirhanV',
'kondaarastirma',
'naci_agbal',
'saffetulusoy',



















'Samsunspor',
'hakanhepcan',
'bekirsalim',
'barkin',
'arzovaone',
's_hablemitoglu',
'yilmazodabasi',
'NAGiHANKARADERE',
'SaadetBecerekli',
'emelyalcin',
'MehmetArdic_',
'firattanis',
'taci_kalkavan',
'samedagirbas',
'ugur_tasdemir',
'esiniris',
'eminonen',
'girginsgokhan',
'AnkaraSgkilmd',
'TDVKAGEM',
'Omralishnr',
'issanat',
'elifkarl',
'GSStore',
'WWF_TURKIYE',
'ahmetsivaslii',
'YHTTCDD',
'kizilirmakilkay',
'LeManBeyoglu',
'BehlulOzkan',
'OrhanOlmezVip',
'aytayt35',
'ovuncozdem',
'sedatbalun',
'zeynepturkes',
'dorockxl',
'LiptonTurkiye',
'ekaraku',
'ziraatkatilim',
'fettahcan',
'gkayravul',
'egzOzpirincci',
'SemihaYanki',
'AkdenizKarpaz',
'yudummuzik',
'istanbulpride',
'gurayervin',
'alpklnctr',
'CyberWarriorTIM',
'boraduran',
'ktazeoglu',
'NAZLIonline',
'kahramanhakan',
'Doarutkay',
'BJKHentbol',
'RadyoD104',
'hakanozoguz',
'ClearMenTurkiye',
'sezerzmen',
'bugraaf',
'AyseHatunOnal',
'sukranovali',
'izzetunver',
'yigityoney',
'kadirdogulu',
'tilbesenyurek',
'kymintl',
'OmerTemelli',
'melihmahmutoglu',
'SerkanKzlbyr',
'ugur3333',
'koksalbaba1461',

























'MevlutCavusoglu',
'UnalCevikoz',
'ekrem_eskinat',
'FaysalSaryldz',
'GerekeniYapcom',
'radyomehtap',
'emreaydin',
'firatdemirel',
'HakanPkr',
'enverkoparmal',
'HsnSnlndrc',
'miracturut',
'BuseTerim',
'tkucukcan',
'Volkan_Agir',
'atasever_vildan',
'tankurtmanas',
'bulent_korucu',
'ezop2011',
'fenerium',
'mysl17',
'iskignmudurlugu',
'emrekonukk',
'oykucengiz',
'Dr_Faruk_Ozlu',
'demetsabanci',
'a_altiparmak',
'SabihaGokcen',
'efekan9csr',
'yaktay',
'CumaliOzkaya',
'SuatKaya_8',
'deryaulugg',
'1461trabzonk',
'Yasarripek',
'sansar34',
'ekremdumanli',
'aniltetik',
'sedeffavcii',
'PervinBuldan',
'anilpiyanci',
'Sertac_Kayar',
'TurkiyeSirki',
'ipektuzcuoglu',
'hakikicemarslan',
'atancagdas',
'RoyaOfficial',
'sameterdigeyik9',
'mujdeuzman',
'ugur_akyurek',
'bakanseda',
'oguzhankoc',
'gulizayla',
'KKasabali',
'iremderici',
'SuatAydogan',
'matakanfoca',
'yaziciyusuf97',
'YusufEsenkal',
'muratseymen',
'duygucetinkaya',
'OfficialDuman',
'Selim_Bayer',
'ErtugrulTSKRN',
'emrahbassan',
'BJKsatranc',
'acunmedya',






















'banuguven',
'atakanarslan',
'irfanozata',
'TurkiyeBurslari',
'tuncayakgun_',
'YoncaLodi17',
'afrasaracogIu',
'Minetugay',
'naciyorulmaz',
'uralakuzum',
'ecegoksedef',
'hakansukur',
'batuhaneksi',
'tugbaozerk',
'HopiApp',
'hisyarozsoy',
'AlpNavruz',
'bayanyani',
'SonerArica',
'EmrahIs',
'SerenayAktas',
'TAVairports',
'haleaydogmus',
'RumeysaKadak',
'Erhan_Celikk',
'mbabaoglu',
'TTKariyer',
'NuriFthAydn',
'dkavranoglu',
'DuymazAlperen',
'cemadrian',
'tanrisevsin',
'CihanAmasyali',
'61goksenin',
'selinlikli',
'zeynepmansur',
'gokceyanardag',
'Proliderya',
'hayatiinanc',
'incisozluk',
'uguraksay',
'gyvorgtr',
'AysunKaraytug',
'athena_official',
'TarikCamdal77',
'percin_deniz',
'djmerthakan',
'HandeErcel',
'onurcalban',
'Gocidaresi',
'EbruBaki',
'atademirer',
'murat_bereket',
'berkan10durmaz',
'YusufYerkel',
'ozanmusluoglu',
'birsudemir',
'BillurYapici',
'Ugurkurul',
'DIBMehmetGormez',
'djilkanGunuc',
'BURCUKIRATLI23',
'FurkanKorkmaz',
'corayse13',
'onlineemir',
'itiresen',
'yunusakgun17',
'_gokhangonul',
'borankuzum',
'yilmazburak17',





















'IAUKampus',
'ercantofficial',
'emrealkin1969',
'TCHamburgBK',
'KurdiHDP',
'TanjuBilgic',
'BEDASDestek',
'AlgEke',
'melihesatacil',
'oguzozturk',
'Kaan_Terzioglu',
'pelinunker',
'BorusanContempo',
'istanbulairport',
'Diyarbakirbaro',
'aynurdogan',
'ibrahimkaragul',
'ulusalajans',
'hacisabanci',
'trademasterfx',
'Hiphoplife',
'karamanhikmet',
'TarhanTelli',
'mlbaydar',
'CanerErkin',
'KAFTco',
'fzaofficial',
'AlmanyaBE',
'Emrah_Karaduman',
'expo2016antalya',
'tarkan',
'himmetkaradag',
'TurkicCouncil',
'metinhocaefendi',
'memetsimsek',
'cosknbekr2',
'osmansrt',
'dogandemirhande',
'ibnhalduni',
'MuriqiVedat',
'ertugrulsakari',
'SenaSenerMusic',
'iremsak',
'EnginEsen',
'yigitkonur',
'fulinnet',
'arisnalci',
'HazalKaya110',
'resmisibelcan',
'genartmedya',
'muyorbir',
'barishersek',
'OmerUgurata',
'ceceydates',
'CaglaBuyukakcay',
'kenanspahi',
'mertekren',
'YasinKesen',
'safakedge',
'cemucann',
'emireksioglu',
'Tumosan',
'IsraelIstanbul',
'cemalkilic',
'cengizunder',
'SrnySrkyResmi',
'popvizyon',
'GokdenizK61',
'ilknkrmn',
'torunkani',
'turkceder',
'ArdaTuran',



















































'ilkhakurdi',
'edisgorgulu',
'YeniSafakArabic',
'TRConsuLA',
'bianet_eng',
'yenisafakEN',
'HDNER',
'tika_english1',
'MustafaEdib',
'tcbestepe_fr',
'Communications',
'MFATurkey',
'TurkEmbOttawa',
'anadoluimages',
'askiankara',
'istanbul_cvb',
'MFATurkeyFrench',
'MFATurkeyArabic',
'tascitugba',
'trtavazkirgizca',
'BesiktasJKDergi',
'TC_BerlinBE',
'kaytazog',
'IlkhaAgency',
'GodivaTurkiye',
'Turkish_Technic',
'IHHen',
'ozannkosee',
'letgoturkiye',
'UCLGMEWAorg',
'TIKA_Kosova',
'TRConsulBoston',
'tchannover',
'WillStevens_',
'ekollogistics',
'diyanet_de',
'djvolkanuca',
'AFADTurkey',
'lifecellDigital',
'misselifcelik',
'Elkabir88',
'dzokora5',
'Ali_Unal',
'DumbledogeLoL',
'OSOLET_26',
'SkylifeMagazine',
'ICTATurkey',
'TurabiKayan',
'marshallturkiye',











































































































'CentralBank_TR',
'kitapyurducom',
'MediaCat',
'nutellaturkiye',
'auditurkiye',
'SamsungTurkiye',
'CornettoTR',



















































































































'rexonaturkiye',
'TUSIAD',
'mserdark',



















































































































'toygarisikli',
'gozdeney',



















































































































'TEPAV',
'meliskarr',




















































































































'PsikiyatriDer',

































































































































'ultrAslanUNI',
'Kucukcekmecemiz',
'Ziverozdemir',
'KayserisporFK',
'mustafaesgin016',
'valicetinoktay',
'HurDavaPartisi',
'medyatavacom',
'AbbasGucluTR',
'mblci',
'hasan_can',
'kaesangp',
'vedatdemiroz',
'yucelyilmazkrsi',
'Armagan_caglaya',
'mustafahos',
'AvGurkanKorkmaz',
'canatilla_news',
'NisantasiEdu',
'serbaysenkal',
'sahibindencom',
'liberaLDP',
'mcihadgunes',
'ineziroglu',
'sabahspor1',
'ZiyaAltunyaldiz',
'halukhepkon',
'autherok',
'mhkose',
'bugraayan',
'DrAydinGok',
'danlabilic',
'demetakbag',
'picadambaattin',
'AbdurrahmanCENS',
'erkamtufan',
'DrMehdiEker',
'Beyazperde',
'aleynatilki',
'GSVoleybol1905',
'AtillaOdunc16',
'scakir20',
'TRTKurdiTV',
'EsenyurtBLDYS',
'MemurSenKonf',
'ctaslaman',
'avcahitozkan',
'VGM_VAKIFLARGM',
'GSBasketbol',
'AvOzlemZengin',
'Himesovski',
'tsclub',
'TroyOdeme',
'Nilgun_OK',
'barisaydin06',
'abdurrahmanbask',
'Sabahatozgursoy',
'PinhaniTakip',
'CKoncagul',
'Mustafa_Destici',
'MehmetSercanOge',
'fpolat69',
'BMWTurkiye',
'RamazanBingol',
'hamdiucar67',
'ozgunugurlu',
'draliseker',
'teknopark_ist',
'dilan_dirayet',
'ilksen_kurt19',
'mustafaislamogl',
'YildizFeti',
'E_N_Celkan',
'ahmethc',
'tebkobi',
'eminile',
'PartiyaDozaAzad',
'mnsazak',
'MustafaOzyar',
'barisssn',
'alicumhurtaskin',
'bursadabugun',
'linetmenasi',
'bursacom16',
'dr_ialtan',
'ZTaskesenlioglu',
'turgaydemirr',
'yasinugurmv',
'ismailumut',
'SukruKarabacak',
'hakki_alkan',
'avmetincelik',
'muhteremince',
'BorsaIstanbulEN',
'HDPkadin',
'yyasaryyavuz',
'SportandoTR',
'AvSabriOzturk',
'TCDDTasimacilik',
'kitapkritik24',
'TLENKS',
'Seyit_TORUN',
'nuran_imir',
'farukozcelikgsb',
'enesozdemirnet',
'teve2Official',
'atayuslu',
'receptezcan',
'Abdullahkochdp',
'sefakarahasan',
'yasinerofc',
'melihbayramdede',
'LigRadyo',
'mleventbulbul',
'ferhatturko',
'ErolKat',
'proftameryilmaz',
'1907unifeb',
'Figenozavci',
'RehberOnline',
'ihsanKoca44',
'Sakaryaspor',
'YapiKrediHizmet',
'mstfyilmaz06',
'mucahitkyilmaz',
'tuncaogreten',
'kapaklibld',
'MuratCepniHDP',
'ASUSTR',
'fatihtezcan',
'undpturkiye',
'm_berdibek',
'Aslnmhmt',
'HWAGaming',
'KarakayaMevlut',
'cnnturkrenk',
'hasanturantr',
'lalekarabiyik',
'IstanbulAFAD',
'AlenMarkaryan',
'SinasiYurtsever',
'OdunHerif',
'selimgultekn51',
'serkantaranoglu',
'mahmutbarut',
'AhmetMisbah',
'oat',
'serhatyabanci',
'24Burhancakir',
'eziskenderoglu',
'tcbestepe_ar',
'HurEkip',
'drsoysal',
'yaseminoney34',
'HasanDavulcu',
'rterdogan_ar',
'TRTFM',
'resatkasap',
'irfankartal_65',
'ferhatgocer',
'Basaksehir_Bld',
'Ekremkonur',
'semraguzelhdp',
'togrularya',
'nazan_oncel',
'TC_Disisleri',
'satiroglu2018',
'OsmanBoyraz',
'mehvesevin',
'serkanfidan',
'biryudumkitapp',
'vahitatalan',
'lemandergisi',
'sahmetsahmet',
'MirgunCabas',
'av_ebrugunay',
'efkanbolac',
'HamdullahTasali',
'ekinturkmen',
'KSalcioglu',
'AydilgeSarp',
'erhan_basyurt',
'necipnasir',
'bekirkuvveterim',
'dkocdemir',
'bulentparlak',
'eronat_oya',
'selim_yagci',
'ZekaVakfi',
'erenerdemnet',
'AvEminGUNES',
'HandeBerktan',
'MehmetGurbuzOrg',
'ahbapplatformu',
'avoguzhankaya',
'1SnDurGitme',
'ozkanmehmetali',
'refik_ozen16',
'zeynepglylmaz1',
'sporarena',
'AnkaraUni',
'Belmasatir',
'cigdematabek',
'SelcukMizrakli',
'glstnkocyigit',
'ar_aydn',
'ahmetozyurekmhp',
'fatmanuraltun',
'aa_kurumsal',
'aliececom',
'MKalayci42',
'erdalerzincan',
'MustafaYel59',
'yilmaz_ismet58',
'zaferisik016',
'chpaliozcan',
'ahmethamdicamli',
'MustafaYMN',
'tika_fr',
'simgefstk',
'ahmetsalihdal',
'halileldemir',
'tolgaakpinar',
'MufitAydin16',
'yunuskilic36',
'EfsaneFotospor',
'suleymanarslan_',
'Selami__Altinok',
'AhmetKayaGam',
'yhyustun',
'muhammet_durmaz',
'seferaycan',
'KmlttinYlmztkn',
'avmustafakose',
'Cbekle',
'atasehirbld',
'Ramazannkasli',
'eyupozsoymv',
'halitalbayraktr',
'alpertaban16',
'OsmanMesten16',
'rizaposaci',
'saitbilgin',
'ercanyyilmaz',
'anlamayacalisan',
'ejderacikkapi',
'deryabakbak27',
'sefikaygol',
'yunusgoksu',
'MercedesTurkiye',
'PaulDoany',
'SERDARAYYILDIZ',
'omer_sahinn',
'yesimmutlu71',
'eczumityilmaz',
'alper_tas',
'baristerkoglu',
'kayaismail80',
'm_kendirli40',
'ismailgunes64',
'hakanmenguc',
'turan_haci',
'Mcahidturhan',
'TeknolojiOku',
'erguntasci52',
'YusufKenan_',
'ahmetakayurfa',
'aawsat_turkce',
'mnyukselir',
'aynurayaz',
'dfikrisaglar',
'sadirdurmaz',
'leventkemaI',
'e_erkanbalta',
'drmehmetcerci',
'kaanarli',
'mhrmkasitoglu',
'kurretulayn',
'AdemSozuer',
'sametejem',
'fsdenizolgun',
'suayipbirinci',
'oerdem42',
'fgulsenkocak',
'aysenurafyon',
'zyildiz_',
'aliihsanyavuz54',
'otisabi',
'Bayramburdur',
'apolaslermi',
'mlkarahocagil',
'ArmanAyse',
'meanlol1',
'osmanakgul29',
'mesutyar',
'ArvasAbdulahat',
'kadikoychp',
'MalatyaBelTr',
'AdanasporResmi',
'agencehanbabis',
'turkocaklari',
'BugunGuncel',
'azeynepb',
'TahirSarikaya06',
'HAVELSANResmi',
'gokcekirgizz',
'AhmetTasgetiren',
'tuncaysonel',
'totaltr',
'SaruhanOluc',
'AnkaraAgri',
'omerocalann',
'drmaydin',
'SerminBalik23',
'Atlasglobal',
'inegolbld',
'zeytinburnubld',
'b4l1c',
'BilfenYayncilik',
'AyseSurucuUrfa',
'medyaradar',
'Biruniedu',
'cnnturksndakika',
'Marmaraytcdd',
'PhilipsTurkiye',
'Beykoz_Bld',
'Pendik_Belediye',
'Tuzlabelediyesi',
'kemalozturk2020',
'OrduBBld',
'DellDestekPRO',
'dogrulukpayicom',
'otdergi',
'cahitbingol',
'sgksosyalmedya',
'sabahavrupa',
'abkarabulut',
'alikenanoglu',
'Murat_Alparslan',
'NTV_Saglik',
'denizkestane',
'AKUTASSOCIATION',
'ybuankara',
'skaplankivircik',
'mhabibsoluk',
'agacvepeyzajas',
'watsonsturkiye',
'MUSIAD',
'mahmutovur',
'konyasporbasket',
'datcabelediyesi',
'uresin_sibel',
'albarakacomtr',
'ittihadululema',
'halilibosonmezz',
'HalicUni1998',
'tkpninsesi',
'ZarokTV',
'BTKbasin',
'zelihasrc',
'AkbankSanat',
'YeniYuzyilEduTR',
'TurkiyeEskrimF',
'BskMustafaCelik',
'ibfk2014',
'dimestr',
'Okayokuslu',
'TRTKurumsal',
'kadikoybelediye',
'Rizgin__Birlik',
'didemozeltumer',
'trtmemleketimfm',
'trtavazozbekce',
'romantikturk',
'GE_Turkiye',
'haliliye_bld',
'BasindaBTK',
'yavasozge',
'vodafonered',
'fibabanka',
'turkjudo',
'odeabank',
'turkonfed',
'zuleyhagulum',
'altinkitaplar',
'EnesUnal16',
'Alimivurdular',
'140journos',
'ahmettan43',
'BestFM',
'mvcengizaydogdu',
'MelihaAkyol77',
'sabanciu',
'quicksigorta',
'garantibasket',
'IpekyuzNecdet',
'lutfikasikci',
'vjbulentt',
'serrafine',
'OyaErsoy',
'rojdademirer__',
'kucukali_M',
'AtillaMenderes',
'DemirDokum',
'BursaValiligi',
'hkitapsanat',
'SancaktepeBeltr',
'mondimobilya',
'emrahelciboga',
'UPSTurkiye',
'Eskisehirspor',
'esestv_online',
'ramizcoskun',
'Ataullahhamidi',
'ayasofyamuze',
'kemal_ozdal',
'KocaeliBilim',
'bayraklibld',
'movenpickizmir',
'muratsarisac',
'BaysalDeniz',
'fcbescolaist',
'Halkbank',
'oktayvural',
'Basak__Demirtas',
'ErbakanFatih',
'zulfutolgagar',
'FevziDemirkol',
'muhammedavci53',
'3Dortgen',
'alpayozalan35',
'MaximumKart',
'baydemirosman',
'TurkiyeFinans',
'baskentray',
'tanjuozcanchp',
'dilsatcanbaz',
'RamazanogluDr',
'HulusiEfendiVkf',
'SignifyTurkiye',
'AKimranAK',
'umayaktas',
'Hadise',
'HrantDinkVakfi',
'NTV_Dunya',
'kademorgtr',
'SCivitcioglu',
'berdan_ozturk',
'oguzksalici',
'abdlhaslan',
'NescafeXpress',
'sarpapak81',
'mvyavuzergun',
'hwa_kofte',
'mutluulusoy',
'AYVALIKBELEDIYE',
'elifbusedgn',
'CevkoVakfi',
'bbbasin',
'AvMahmutSahin',
'senelyediyildiz',
'oakaragozoglu',
'ozyeginuni',
'TugrulTurkes',
'perodundar',
'mvltblt',
'dincka77',
'MahmutOrhann',
'YavuzSubasi',
'mcihatsezal',
'uskudaruni',
'MHazinedar',
'yzyilmaz55',
'BiLGiOfficial',
'AKaanTR',
'Gazi_Universite',
'YurticiKargo',
'haciozkan33',
'jessicamay_br',
'cemil_yaman',
'KadirAydin28',
'nurettincanikli',
'IpsosTurkiye',
'KIZILAYKART',
'AvErdalAydemir',
'mutluaku',
'Prontotour',
'CorluBelediyesi',
'cangox',
'MeteksanSavunma',
'AdemC06',
'isteuni',
'VodafoneMedya',
'LoLTurkiye',
'YasarUniv',
'tika_arabic',
'zulfudemirbag23',
'kidZaniaist',
'SeyhmusTanrkulu',
'GilletteTR',
'SavunmaSanayii',
'yatasbedding',
'Mbirpinar',
'ezgisnlr',
'VahapErenTR',
'emrecolak1010',
'emregrafsonplak',
'bulutlarayazin',
'biletall',
'WeAreTennisTR',
'uguraydemir45',
'chpgenclikgm',
'halukyazgi',
'KizilayDestek',
'StarOtomobil',
'tuhid',
'CansuyuDernegi',
'kasimpasa',
'CEPTETEB',
'recep_altepe',
'eteemre',
'AkGencDargecit',
'Dogu_Perincek',
'bayramzilan',
'BJKKartalYuvasi',
'alarko_carrier',
'sesverturkiye',
'EBofis',
'tika_deutsch',
'DrSaitDede',
'AvMustafaArslan',
'TEBKariyer',
'sosyalben',
'mbulentkaratas',
'KralTV',
'DrRidvanTuran',
'SOCAR_Turkiye',
'ilmietudler',
'fatih_donmez',
'yclblt06',
'METU_ODTU',
'camlicagazozu',
'kitapeki_info',
'recepcaliskan',
'erenbilal',
'abdullah_eren',
'Pentagramzrkbl',
'PeugeotTurkiye',
'Mehmet__Akyurek',
'OzkalHatice',
'relaxgmn',
'mvitamer',
'hakanaltunmusic',
'Akhisarspor',
'Vestel',
'tuzcuoglurefik',
'areledu',
'MehmetFatihOruc',
'Ebru_Polat',
'dralpmese',
'limonilezeytin',
'RotasizSeyyah',
'irfandonat',
'servet_cetin76',
'metinbulut23',
'HasanSahin_023',
'DrHalefYilmaz',
'SinanCanan',
'EKNOLCAYTO',
'EmreDorman',
'yeldac',
'NescafeGold_TR',
'mithatsancarr',
'fizikist',
'cmlcoban',
'BattalFetani',
'KacakGelinler',
'hwa_senna',
'tihek_kurumsal',
'ilyaskaya0621',
'sonmezumit',
'byMustafaTuncer',
'OAkpinar',
'bhr9',
'colbaydamla',
'TTForgtr',
'BekirAgirdir',
'SAMETSERBEST',
'hudayivakfi',
'AdnanBoynukara',
'esaskenan',
'BTurgu',
'cdavran',
'erkancapraz',
'ilknurbektas',
'arzuyldzz',
'avrupamuzik',
'recepozel32',
'TSEKurumsal',
'kiliccomer',
'metinyavuz009',
'mtahagergerli',
'mozkilincweb',
'KizilayKurumsal',
'MehmetEsin_',
'erkan_online',
'ihsanselimb',
'emoorgtr',
'mnedimyamali',
'TC_BerlinBK',
'gantepbasketbol',
'ntvhava',
'AVCIKORAY',
'hwa_unex',
'AliBizden',
'odealApp',
'OsmanYeken',
'KenanYavuzCEO',
'istanbulkart',
'BahcelievlerBel',
'tahaozer',
'SemaYARAR_',
'A_Yildiz_',
'flymepegasus',
'ihsaneliacik',
'kedimbileyok',
'eti',
'trabzoncbs',
'BridgestoneTR',
'VitrATurkiye',
'YildizEdu',
'GameSultan',
'erzurumbld',
'burhanettinuysl',
'TC_HAK_',
'MetinKiratli69',
'cenkerenonline',
'kenandogulu',
'igairport',
'NihatDoganVevo',
'genckizilay',
'ibbduyuru',
'mecertas',
'BurakSahin35',
'AYemeniciler',
'hwa_eliwood',
'namikhavutca',
'sokakkafasi',
'ramizeerer',
'hwa_roulette',
'mervezby',
'UniversiteEge',
'merdogan_48',
'RamazanCASUK',
'tgrthaberspor',
'mserden',
'tilbetilbesaran',
'HWA_Joker',
'MehmetAlBayhan',
'MErdiCak',
'ahmetzenbilci',
'DersimDag21',
'AskinCihat',
'kararliseyler',
'aytaclezzetleri',
'dogaicincal',
'UgurAkkafa',
'Baki_Mercimek',
'ertanaydin',
'CemBelevi',
'aylincoskun',
'zfrcbkc',
'FilintaTV',
'israfilkisla',
'guldalaksit',
'AHMTKURAL',
'canererdeniz',
'BesiktasEnglish',
'Milliyet_Sanat',
'semihkaya_26',
'ErolKAVUNCU',
'nurullahcahan',
'HWA_StarScreen',
'MehmetDincerler',
'GSKJudo',
'aytaner',
'derintarih',
'mehmetaltay64',
'ismailemanet',
'RizaKocaoglu',
'aakyildizblog',
'TurkKizilay',
'mesutucakan',
'Mehmeterol09',
'sadullahkisacik',
'GalipEnsarioglu',
'meminadiyaman_',
'inanckoc12',
'zekiyildirim',
'KemalYurtnac',
'ozanakbaba',
'usakbld',
'coskuncakir60',
'BaydurTURGAY',
'trtavazturkmen',
'AkbankYatirimci',
'ankaraairport',
'CappyTR',
'Borusanlojistik',
'Berdan_Mardini',
'SermiyanMidyat',
'no309fox',
'AskYenidenDizi',
'LandRover_TR',
'dbdevletbahceli',
'yemlihatoker',
'cundubeyoglu',
'ozgebulurr',
'mdemiryas',
'EuromasterTR',
'ElidorTurkiye',
'bynogame',
'tefalturkiye',
'metinfeyzioglu',
'sgunday',
'KagithaneBelTR',
'djkaangokman',
'AylinaKilic',
'SerkanCayoglu',
'dehabilimlier',
'EkremEksioglu',
'HamdiAlkan',
'SozMatick',
'Farukkcomtr',
'ummiyekocak',
'nusrettin_macin',
'yildiz1071',
'Yenibiris',
'TolgaGecim',
'Galatasaray_Uni',
'kursatergun',
'yubl_app',
'duygubingol',
'acsivrikaya',
'kamufle34',
'GokhannGunes',
'csknseyda',
'MazlumNurlu',
'silebld',
'WingsCard',
'nvidiageforcetr',
'ekizilkaya',
'armutlol',
'closerlol1',
'TheVigilantelol',
'dozdemiroglu',
'YakupAkkayaCHP',
'egetanman',
'hdpdemirtas',
'erdoganozegenn',
'ufuk_ceylan',
'BarisManco_',
'baharayvazoglu',
'AliAtalan68',
'FerhatEncu',
'RTErdogan',
'bskazizkocaoglu',
'AliserDelek',
'sinanaksu',
'BasakSayan',
'ismailcingoz1',
'dogansenli',
'TezcanTopall',
'denizatam',
'nurettin_demir',
'hulyakombe',
'hilmiyarayici',
'MetinAROLAT',
'pinar_soykan',
'cansudicletosun',
'turkiyegroupama',
'EmreOzkan24',
'Devraniskenderr',
'Kadir__Topbas',
'bernalacin35',
'bordomavitur',
'ferganmirkelam',
'NihatDogan_ND',
'YoncaEvcimik',
'KadriKabak',
'ErtugrulErkisi',
'necatisler',
'AlevAlatli',
'rehaerdemm',
'trt23nisan',
'AvSerkanRamanli',
'HamdiCelikbas',
'koprudebulusma',
'dogakonakoglu',
'tebius_09',
'SeckinOzdemir',
'Okanbostanci',
'mimarserdarinan',
'Manisasporresmi',
'ozgurcek60',
'AkalinDemet',
'MyyKosem',
'sevdegul80',
'pekkan1',
'usmedorgtr',
'ArcelikDestek',
'AlEmbassyTurkey',
'geergen',
'aynurofficial',
'turgayguler',
'cartedorturkiye',
'ayembeko',
'Murat__Erkan',
'Calve_TR',
'tugcemurat35',
'gulogludavut',
'Durex_TR',
'BY',
'egehanarna',
'yerlikayaali34',
'mercan_resifi',
'ibrahimbaltay',
'YeniAlisan',
'badenzi',
'hulyavsar',
'ozcivit_burak',
'taylan1789',
'Kvnc01',
'FAZILSAY1',
'aysbaceoglu',
'TANYELIyle',
'kadirgaliss',
'hilalcebeciii',
'benguofficial',
'cenkeren1',
'smuratdalkilic',
'cansellelcin',
'saahinirmak',
'sevincerbulak',
'cem_ozer',
'srknaltunorak',
'dogulukenan',
'Lerzan_Mutlu',
'ozgeulusoynew',
'alipoyrazoglu',
'Akelebru',
'komedyieni',
'nedimsaban',
'imparatoribo',
'HadiseTV',
'demetsagiroglu',
'sinanakciloffcl',
'SRNGLofficial',
'eceerkenn',
'fatihurekk',
'canligastespor',
'Armagan',
'Petekdncz',
'tanemsivar',
'sebnembozoklu',
'tubaunsal',
'ece_sukan',
'bozzmurat',
'Begum_Kutuk',
'SahnazCakiralp',
'GaniMujde',
'biriciksuden',
'NhrErd',
'Seray_Sever',
'FadikAtasoy',
'NukhetDuru',
'gulsenin',
'mazharalanson12',
'ugurryucel',
'MelisBirkan',
'yesimsalkim',
'ferhat_gocer',
'AtiyeD',
'ozerktugba',
'yavuzseckin',
'HergunYeniBilg',
'barisyarkadas',
'rtugbagok',
'cicekabbasbilo',
'FatihMacoglu',
'yuksekk_kamuran',
'pancuniyoldas',
'Gokhenks',
'tgmcelebi',
'alkayaorhan',
'caglarcilara',
'acikcenk',
'alicankose1',
'BugrahanSefik',
'yagizbsrn',
'musmutluseyler',
'tuncsoyer',
'AtillaTasNet',
'nevsinmengu',
'CHP_istanbulil',
'OzgurKarabatCHP',
'drhasanakgun',
'senerabdullatif',
'emrkongar',
'nadirfotograf',
'iyipartitbmm',
'OnedioSpor',
'oynakazanapp',
'arsennurmag',
'simerazzi',
'eknkc',
'buyukozcu',
'direncigdem',
'fikretsahin10',
'ednanarslanchp',
'KursatErcan',
'Av_Burcu03',
'chptekirdag_59',
'emreersan',
'gebzeiyiparti',
'birsentezer',
'SCavun',
'ArtiTV_',
'Bedri_SERTER',
'baranendersinan',
'sinsinar',
'Kahvecibrahim',
'bengisukadi',
'anilarpat',
'mesutmetin',
'erbilaydnlkCHP',
'soydanalkan',
'Nesrinnas',
'demarkajj',
'omerturantv72',
'drerdemozdemir',
'EceGunerToprak',
'Ertan_Aksoy',
'agkiraz',
'GWakabayashi_',
'RustemBatum',
'fulyaoktem',
'fazilkasap',
'FurkanMuratKaya',
'mustafabozbey',
'yekefendi',
'muhdansaglam',
'samuelbbeckett',
'ucansupurgeukff',
'necopinate',
'NerdenNurdan',
'Luciderr',
'JafariPeyman',
'NazireUyanik',
'fatihaltun',
'DayanismaMor',
'ArsivUnutmaz',
'avhaklarigrubu',
'canwaves',
'kaoshareketi',
'CilerDursun',
'pirimkorkmaz',
'bunsenbeki',
'TeamGaripler',
'uzunnpinar',
'VatanSaban',
'AyhanBarut01',
'morcativakfi',
'kadinkolektifi',
'avtutdere',
'cem_tv',
'dbcocukhaklari',
'add_genelmerkez',
'HuseyinAygun62',
'ondesfuat',
'avtufanKose',
'aktasdilsat',
'alpertasbeyoglu',
'MGokhanAhi',
'kerimaksu28',
'aksubora',
'saimtokacoglu',
'isikkansu',
'EnderUnutan',
'avkarakaschp',
'mehmethasancebi',
'nygunaychp',
'chpscedid',
'grevgozcusu_',
'HurAyse',
'VACPrevention',
'kacsaatolduson',
'cargilliscikom',
'Catlak_zemin',
'gokhanyukselist',
'Speaker_Agency',
'cenkyigiter',
'SafakRuzgar',
'fidanaeroglu',
'AvkMuratBektas',
'muallavarol',
'AnkaraKadin',
'nhkose',
'orkunsiktasli',
'yenicizgigazete',
'HukukChp',
'gonulluist',
'Free_Yezidi',
'krmzkedikitap',
'jalenursullu',
'SeyitTosun83',
'SatukBugra3',
'necati2883',
'orhanssumer',
'bzkrtdnz',
'CHP_Ekonomi_M',
'guldunyakitap',
'KaosGL',
'yildiz_tar',
'hlyglbahar',
'bayhakangungor',
'OzgenNAMA',
'selimkotil34',
'GamzeBurcuGul',
'serdildara',
'Ferayicinadale1',
'verikaynagi',
'yasarokuyan',
'HaberdeCocuk',
'CoskunTosunTR',
'muammerkeskin75',
'secimarenasi',
'ismail_emre',
'avdenizyucel',
'sonerozimer',
'GorguAysegul',
'erdemmgul',
'DtAhmetAtac',
'SaffetDagbakan',
'RizaAkpolat',
'arifkizilyalin',
'ChpGaziemir',
'CaglaKocoglu',
'ChpBeykozGenc',
'fatosguner33',
'ucimorgtr',
'fadik_t',
'Beyzaayavuz',
'kadindavalar',
'egitimsen',
'hakimmurataydin',
'AysuBankoglu',
'canpoyraz',
'irfankaplanchp',
'sahiciy',
'AylinNazliaka',
'CHPTBMM',
'fethigurer',
'gulciftci',
'gamzetascier',
'TuranHancerli',
'alihaydarfirat',
'imambakirukus',
'nznmor',
'gtahincioglu',
'yucelceylancom',
'meldaonur',
'cavitdursun',
'CokoyHilal',
'kadinplatformu2',
'filhakika2018',
'Halkevleri',
'KartalHkukcular',
'Genclik2019CHP',
'kadikoygencligi',
'MaltepeChp',
'CHPGencBagcilar',
'chpgungorengenc',
'CHPBesiktasGenc',
'CHPBeyogluGenc',
'CHPadalett',
'CHPSancakGenc',
'CHPGOPGenclik',
'chpcatalcagenc',
'chpsisligenc',
'haydarkocak06',
'ndeveci',
'semradincerchp',
'SecerVahap',
'ecapa_aklinizi',
'temcikterelelli',
'KBGuclu',
'Elifhevav',
'AlperUcok',
'gokhanbozkurt09',
'ISTsozlesmesi',
'AliYigitKaraca',
'GurkaynakGonenc',
'ankaracbs',
'cerendamarsenel',
'muratbuyukyilmz',
'umidgurbanov',
'tv5televizyonu',
'sunauaygun',
'GenderKhas',
'ABuyukkarakas',
'aaykarmela',
'hazalmintas',
'Merttimurr',
'doguscanaygun',
'tolgagurakar',
'julide_emre',
'ElfinTataroglu',
'CHPManisaGK',
'CHPManisailbsk',
'erkkayabas',
'ProfPervinSomer',
'TurkAlevisi',
'SMEYDAN',
'hmtkrz',
'Nurhayaten6',
'avukatergunn',
'nurtensbeyi',
'CagriGruscu',
'EzgiDenizUrunga',
'CHP_Kritik',
'TweetCHP',
'gazetekritik',
'SeroChp',
'womantvtr',
'celalulgen',
'GulizarBicer',
'Rumi_Quote',
'yeminetlan',
'ALIATIFBIR',
'burkayduzce',
'chpbesiktas',
'ChpSisliilceBsk',
'BahcelievlerCHP',
'CHP_Basaksehir',
'CHP_Beylikduzu',
'silivrichp',
'CHPBEYKOZ',
'chpeyupsultan',
'BAKIRKOY_CHP',
'CHPKucukcekmece',
'CHPUskudarilce',
'Chpkagithanee',
'ChpZeytinburnu',
'CHPSileilce',
'Chpbagcilarilce',
'CHP_BEYOGLU',
'ChpUmraniyeIlce',
'CHPGungorenilce',
'chp_sultanbeyli',
'CHPArnavutky',
'avcilar_chp',
'bayrampasachp',
'ChpBayrampasa',
'ChpBcekmeceilce',
'CHP_Esenyurt',
'CHP_ESENLER',
'CHPCatalca',
'CHPFatih_resmi',
'CHPGOP',
'ilcechppendik',
'ilcesancaktepe',
'CHPsariyer',
'CHPTuzlaist',
'CHPLELE1',
'CHPsultangazi',
'chpizmiril',
'yukselmk',
'gokanzeybekCHP',
'gulizaremecan',
'FethiAcikel',
'CHPTurgayGenc',
'avfikretilkiz',
'HASANKILIC__',
'Tuluhantekeli',
'duygudemirdag',
'nurhayataltaca',
'avharveyspecter',
'chpatagenc',
'CHP_Maltepe',
'CHPAtasehirilce',
'chpgencistanbul',
'CHPKadinIstanbl',
'yesimagirman',
'alimahir',
'CHPEsenlerilce',
'fatihaltayli',
'zihinselorgazm',
'aycaakpek',
'ozgurceylanchp',
'bertiloder',
'tolgashirin',
'MuhsinKocak1',
'yaltakgurbuz',
'ataturkcu8138',
'cigdem_ozer',
'BarisAkademik',
'SaadetOzkanEfe',
'sevdaarslanmy',
'pipetlielmasuyu',
'robinbaran',
'anildeniiz',
'korcanucman',
'haydarakar',
'CHPGenclikBursa',
'KA_DER_',
'erkanecz',
'MelihMorsnbl',
'MvSuatOzcan',
'aydinozer07',
'cengiz_gokcel',
'CelikBaskan06',
'ismetokdemirchp',
'cigdemtoker',
'NESLiHANCIOGLU',
'AhmetKayaCHP',
'eczburhan',
'sabansevinc2',
'Serhan_Asker',
'laleozanarslan',
'hasanbaltacichp',
'MericSelahattin',
'erkbas',
'ilerihaber',
'naimkarakaya',
'barisatay',
'esitiz',
'Kadavist',
'kadinih',
'FeministSozluk',
'FeministMutfak',
'cagdasses',
'ugurdundarsozcu',
'omerkavili',
'kemalgoktas',
'ibrahimkonarr',
'ccanannnnnn',
'feministkadin',
'kadin_dv',
'17AleviKadinlar',
'muratkantekin',
'avcaglayan',
'yunusemre',
'SEVDAERDAN',
'aydonerbaki',
'kanibekochp',
'cezaeviihlaller',
'PartiOkuluCHP',
'CHPYerel',
'tkdfederasyon',
'cs_mucadele',
'OzgeAkman2019',
'AvTuranAydogan',
'AysenEceKavas',
'esithaklar',
'ihdistanbul',
'ZehraPalaAte',
'GalmaAkdeniz',
'zaferalgoz',
'oya_lamaca',
'yasemininceoglu',
'bianet_org',
'OznurSevdiren',
'SenelNecip',
'UfukOzkap',
'gamzepamuk',
'ibrahimkaboglu',
'OguzhanUgur',
'ISILACIKKAR',
'drbahartezcan',
'nasuhbektas',
'AysenurArslantv',
'gurkanhacir',
'ETemelkuran',
'gokcegokcen142',
'BanuOzdemirChp',
'avantmen33',
'avsbulbul',
'Deniz_Zeyrek',
'fatmakose44',
'avumuryildirim',
'turgut_tarhanli',
'degirmencirfan',
'EkremKeremOktay',
'KeremALTIPARMAK',
'burcuas',
'_burcuaslan_',
'suleicinadalet',
'yagizsenkal',
'Ipek_Bozkurt',
'ecezereycan',
'organize4zim',
'kadinkoalisyonu',
'YolTV',
'tubaemlek',
'av_ugurpoyraz',
'patronlar',
'ilericikadin',
'perapea',
'korayaydintr',
'avleylasuren',
'morhipopotam',
'Ridvaanc',
'ersoyakif1',
'firatp63',
'SavashPorgham',
'bilim_adami',
'06melihchina',
'DURAKOGLU2016',
'kagider',
'cansucanantv',
'cansucananozgen',
'dasdasistanbul',
'NazirekalkanGur',
'recelblog',
'KSKMuslumanlar',
'feministgundem',
'feministavukat',
'SKorurFincanci',
'fidanataselim',
'serbestkursuu',
'kfetoplumsaldil',
'Evseksisi',
'Azuth',
'Filmmor_',
'KadinCinayeti',
'ufukkarcii',
'BuketUzuner',
'feminineforcekf',
'cocugunsesii',
'emekcianneler',
'kadininfenni',
'RTEdijital',
'yunari06',
'ankara_kusu',
'lordsinov',
'SelimAtalayNY',
'ferhan_sensoy',
'meliksahtas',
'sevketcoruh_',
'SezginKaymazz',
'ugurses',
'ErkinSahinoz',
'adilsecimnet',
'DurmusYillmaz',
'Turkiye',
'zumrutarol',
'geziciarastirma',
'tuhafdergi',
'tugce_senogul',
'TrakyaBalkan',
'yilmaz_hakan',
'ziangil',
'ahmethtakan',
'ttractatus',
'ertgrlalbyrk',
'mahfiegilmez',
'Figenozdenak',
'GaripAmaGerceek',
'ersozhuseyin',
'baydagul',
'metecubukcu',
'AjansBaskani',
'hediyelevent',
'KurtogluKagan',
'ORHANBURSALI',
'tezkanmehmet',
'aliturksen',
'gurmant_',
'tyahuditoplumu',
'drdursuncicek',
'ezgibasaran',
'hsoneryalcin',
'metehan_demir',
'unluferhat',
'Overlokcu12',
'satrayni',
'nlgnblgn',
'cetinasemih',
'yvzah',
'makifbeki',
'elifcakirr',
'Serkanbkm',
'SALT_Online',
'istanbulmodern_',
'deryakaradas1',
'SSabanciMuze',
'hasibe_eren',
'dunya_halleri',
'TurkishIndy',
'coderbirisi',
'NurettinColak',
'ebekirsahin',
'ercanbayrak13',
'rtukkurumsal',
'MCuneytAksoy',
'zabeyazgul',
'eha_medya',
'ESAREA',
'ilkerinblog',
'EmreUslu',
'aktif_haber',
'sendika_org',
'farketmezolmya',
'heayseyy',
'MBekaroglu',
'GonulKirca35',
'AyrisNvr_GS',
'YoungSoldierr2',
'HasanGunaltay',
'biruygurcadiri',
'HsnBozkurt',
'dokuz8haber',
'bilio_muydunuz',
'bekiservet',
'lvntozrn',
'Durunun_Annesi2',
'Caner_Toprak35',
'parasizceo',
'volkanoge',
'Mamafih365',
'ifsasss',
'Murturkofficial',
'AyseFayset',
'eminpazarci',
'slmhktn',
'hikmetgenc',
'trippinfatih',
'CenabiAllah',
'_GizliArsivTR',
'ismailcakar701i',
'tselmanoglu',
'hocaniztarih',
'FEMEN_Turkey',
'themarginale',
'_galatimeshur',
'ilyasslman',
'sco1905',
'1mecit1',
'_Hayalet___',
'sertvurgular',
'KarahanZelal',
'erseljira',
'_EmiNe_K__',
'Acizbirkul3434',
'turgayyildiz965',
'politikaloji',
'DurBirDinlee',
'yabidinlebi',
'EylemSen',
'SDKoleksiyon',
'siyasifenomen',
'ilhantasci',
        ];

        foreach ($items as $name)
        {
            $g = $gender->detector([ $name ]);

            $this->{$g == 'm' ? 'info' : ($g == 'f' ? 'line' : 'error')}($name.' - ['.$g.']');
        }
    }
}
