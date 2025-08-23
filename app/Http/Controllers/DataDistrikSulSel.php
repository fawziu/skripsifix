<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use function PHPUnit\Framework\returnSelf;

class DataDistrikSulSel extends Controller
{
    public function getData($id){
        if ($id == 648) return $this->makassar;
        elseif ($id == 649) return $this->bantaeng;
        elseif ($id == 650) return $this->barru;
        elseif ($id == 651) return $this->bulukumba;
        elseif ($id == 652) return $this->enrekang;
        elseif ($id == 653) return $this->jeneponto;
        elseif ($id == 654) return $this->tana_toraja;
        elseif ($id == 655) return $this->toraja_utara;
        elseif ($id == 659) return $this->maros;
        elseif ($id == 663) return $this->palopo;
        elseif ($id == 664) return $this->pinrang;
        elseif ($id == 667) return $this->rappang;
        elseif ($id == 670) return $this->wajo;
        elseif ($id == 671) return $this->sinjai;
        elseif ($id == 673) return $this->gowa;
        elseif ($id == 674) return $this->takalar;
        elseif ($id == 676) return $this->bone;
        elseif ($id == 678) return $this->soppeng;
        elseif ($id == 679) return $this->selayar;
        elseif ($id == 680) return $this->pangkep;
        elseif ($id == 683) return $this->parepare;
        elseif ($id == 684) return $this->luwu;
        elseif ($id == 685) return $this->luwu_timur;
        elseif ($id == 686) return $this->luwu_utara;
        else return null;

    }
    public $makassar = [
        ["id" => 6729, "name" => "UJUNG PANDANG"],
        ["id" => 6730, "name" => "TAMALATE"],
        ["id" => 6731, "name" => "BIRING KANAYA"],
        ["id" => 6732, "name" => "BONTOALA"],
        ["id" => 6733, "name" => "MAMAJANG"],
        ["id" => 6734, "name" => "MANGGALA"],
        ["id" => 6735, "name" => "MARISO"],
        ["id" => 6736, "name" => "PANAKKUKANG"],
        ["id" => 6737, "name" => "RAPPOCINI"],
        ["id" => 6738, "name" => "TALLO"],
        ["id" => 6739, "name" => "TAMALANREA"],
        ["id" => 6740, "name" => "UJUNG TANAH"],
        ["id" => 6741, "name" => "WAJO"],
        ["id" => 6742, "name" => "MAKASSAR"]
    ];

    public $bantaeng = [
        ["id" => 6743, "name" => "BANTAENG"],
        ["id" => 6744, "name" => "BISSAPPU"],
        ["id" => 6745, "name" => "TOMPOBULU"],
        ["id" => 6746, "name" => "EREMERASA"],
        ["id" => 6747, "name" => "PAJUKUKANG"],
        ["id" => 6748, "name" => "ULUERE"],
        ["id" => 6749, "name" => "SINOA"],
        ["id" => 6750, "name" => "GANTARANG KEKE (GANTARENG KEKE)"]
    ];
    public $barru = [
        ["id" => 6751, "name" => "BARRU"],
        ["id" => 6752, "name" => "MALLUSETASI"],
        ["id" => 6753, "name" => "SOPPENG RIAJA"],
        ["id" => 6754, "name" => "TANETE RIAJA"],
        ["id" => 6755, "name" => "TANETE RILAU"],
        ["id" => 6756, "name" => "BALUSU"],
        ["id" => 6757, "name" => "PUJANANTING"]
    ];
    public $bulukumba =[
        ["id" => 6758, "name" => "BULUKUMBA (BULUKUMPA)"],
        ["id" => 6759, "name" => "BONTO BAHARI"],
        ["id" => 6760, "name" => "BONTOTIRO"],
        ["id" => 6761, "name" => "GANTORANG/GANTARANG (GANGKING)"],
        ["id" => 6762, "name" => "HERO LANGE-LANGE (HERLANG)"],
        ["id" => 6763, "name" => "KAJANG"],
        ["id" => 6764, "name" => "KINDANG"],
        ["id" => 6765, "name" => "RILAU ALE"],
        ["id" => 6766, "name" => "UJUNG BULU"],
        ["id" => 6767, "name" => "UJUNG LOE"]
    ];
    public $enrekang = [
        ["id" => 6768, "name" => "ENREKANG"],
        ["id" => 6769, "name" => "ALLA"],
        ["id" => 6770, "name" => "ANGGERAJA"],
        ["id" => 6771, "name" => "BARAKA"],
        ["id" => 6772, "name" => "MAIWA"],
        ["id" => 6773, "name" => "BAROKO"],
        ["id" => 6774, "name" => "MALUA"],
        ["id" => 6775, "name" => "CENDANA"],
        ["id" => 6776, "name" => "BUNGIN"],
        ["id" => 6777, "name" => "CURIO"],
        ["id" => 6778, "name" => "BUNTU BATU"],
        ["id" => 6779, "name" => "MASALLE"]
    ];
    public $jeneponto = [
        ["id" => 6780, "name" => "BANGKALA"],
        ["id" => 6781, "name" => "BATANG"],
        ["id" => 6782, "name" => "KELARA"],
        ["id" => 6783, "name" => "TAMALATEA"],
        ["id" => 6784, "name" => "BANGKALA BARAT"],
        ["id" => 6785, "name" => "BINAMU"],
        ["id" => 6786, "name" => "BONTORAMBA"],
        ["id" => 6787, "name" => "TURATEA"],
        ["id" => 6788, "name" => "ARUNGKEKE"],
        ["id" => 6789, "name" => "TAROWANG"],
        ["id" => 6790, "name" => "RUMBIA"]
    ];
    public $tana_toraja = [
        ["id" => 6791, "name" => "MAKALE"],
        ["id" => 6792, "name" => "BONGGAKARADENG"],
        ["id" => 6793, "name" => "MENGKENDEK"],
        ["id" => 6794, "name" => "SALUPUTTI"],
        ["id" => 6795, "name" => "SANGALLA (SANGGALA)"],
        ["id" => 6800, "name" => "BITTUANG"],
        ["id" => 6802, "name" => "RANTETAYO"],
        ["id" => 6804, "name" => "SIMBUANG"],
        ["id" => 6806, "name" => "GANDANG BATU SILLANAN"],
        ["id" => 6807, "name" => "KURRA"],
        ["id" => 6808, "name" => "MAKALE SELATAN"],
        ["id" => 6809, "name" => "MAKALE UTARA"],
        ["id" => 6810, "name" => "MAPPAK"],
        ["id" => 6811, "name" => "MASANDA"],
        ["id" => 6812, "name" => "MALIMBONG BALEPE"],
        ["id" => 6813, "name" => "RANO"],
        ["id" => 6814, "name" => "REMBON"],
        ["id" => 6828, "name" => "SANGALLA UTARA"],
        ["id" => 6829, "name" => "SANGALLA SELATAN"]
    ];
    public $toraja_utara = [
        ["id" => 6796, "name" => "RANTEPAO"],
        ["id" => 6797, "name" => "RINDINGALLO"],
        ["id" => 6798, "name" => "SANGGALANGI"],
        ["id" => 6799, "name" => "SESEAN"],
        ["id" => 6801, "name" => "BUNTAO"],
        ["id" => 6803, "name" => "SA'DAN"],
        ["id" => 6805, "name" => "TONDON"],
        ["id" => 6815, "name" => "SESEAN SULOARA"],
        ["id" => 6816, "name" => "AWAN RANTE KARUA"],
        ["id" => 6817, "name" => "BANGKELEKILA"],
        ["id" => 6818, "name" => "BARUPPU"],
        ["id" => 6819, "name" => "BUNTU PEPASAN"],
        ["id" => 6820, "name" => "DENDE' PIONGAN NAPO"],
        ["id" => 6821, "name" => "KAPALLA PITU (KAPALA PITU)"],
        ["id" => 6822, "name" => "KESU"],
        ["id" => 6823, "name" => "NANGGALA"],
        ["id" => 6824, "name" => "RANTEBUA"],
        ["id" => 6825, "name" => "SOPAI"],
        ["id" => 6826, "name" => "TALLUNGLIPU"],
        ["id" => 6827, "name" => "TIKALA"],
        ["id" => 6830, "name" => "BALUSU"],
    ];

    public $maros = [
        ["id" => 6847, "name" => "BANTIMURUNG"],
        ["id" => 6848, "name" => "CAMBA"],
        ["id" => 6849, "name" => "MALLAWA"],
        ["id" => 6850, "name" => "MANDAI"],
        ["id" => 6851, "name" => "BONTOA (MAROS UTARA)"],
        ["id" => 6852, "name" => "TANRALILI"],
        ["id" => 6853, "name" => "LAU"],
        ["id" => 6854, "name" => "MAROS BARU"],
        ["id" => 6855, "name" => "MARUSU"],
        ["id" => 6856, "name" => "MONCONGLOE"],
        ["id" => 6857, "name" => "SIMBANG"],
        ["id" => 6858, "name" => "TOMPU BULU"],
        ["id" => 6859, "name" => "TURIKALE"],
        ["id" => 6860, "name" => "CENRANA"],
    ];

    public $palopo = [
        ["id" => 6869, "name" => "TELLUWANUA"],
        ["id" => 6870, "name" => "WARA"],
        ["id" => 6871, "name" => "WARA SELATAN"],
        ["id" => 6872, "name" => "WARA UTARA"],
        ["id" => 6873, "name" => "BARA"],
        ["id" => 6874, "name" => "WARA TIMUR"],
        ["id" => 6875, "name" => "WARA BARAT"],
        ["id" => 6876, "name" => "MUNGKAJANG"],
        ["id" => 6877, "name" => "SENDANA"],
    ];

    public $pinrang = [
        ["id" => 6878, "name" => "CEMPA"],
        ["id" => 6879, "name" => "DUAMPANUA"],
        ["id" => 6880, "name" => "LEMBANG"],
        ["id" => 6881, "name" => "MATTIRO BULU"],
        ["id" => 6882, "name" => "MATTIRO SOMPE"],
        ["id" => 6883, "name" => "PATAMPANUA"],
        ["id" => 6884, "name" => "SUPPA"],
        ["id" => 6885, "name" => "BATULAPPA"],
        ["id" => 6886, "name" => "LANRISANG"],
        ["id" => 6887, "name" => "PALETEANG"],
        ["id" => 6888, "name" => "TIROANG"],
        ["id" => 6889, "name" => "WATANG SAWITTO"],
    ];

    public $rappang = [
        ["id" => 6906, "name" => "BARANTI"],
        ["id" => 6907, "name" => "DUA PITUE"],
        ["id" => 6908, "name" => "PANCA RIJANG"],
        ["id" => 6909, "name" => "PANCA LAUTAN (LAUTANG)"],
        ["id" => 6910, "name" => "TELLU LIMPOE"],
        ["id" => 6911, "name" => "WATANG PULU"],
        ["id" => 6912, "name" => "KULO"],
        ["id" => 6913, "name" => "MARITENGNGAE"],
        ["id" => 6914, "name" => "PITU RAISE/RIASE"],
        ["id" => 6915, "name" => "PITU RIAWA"],
        ["id" => 6916, "name" => "WATTANG SIDENRENG (WATANG SIDENRENG)"],
    ];

    public $wajo = [
        ["id" => 6917, "name" => "BELAWA"],
        ["id" => 6918, "name" => "MAJAULENG"],
        ["id" => 6919, "name" => "MANIANGPAJO"],
        ["id" => 6920, "name" => "PAMMANA"],
        ["id" => 6921, "name" => "PITUMPANUA"],
        ["id" => 6922, "name" => "SABANGPARU"],
        ["id" => 6923, "name" => "SAJOANGING"],
        ["id" => 6924, "name" => "TAKKALALLA"],
        ["id" => 6925, "name" => "TANASITOLO"],
        ["id" => 6926, "name" => "BOLA"],
        ["id" => 6927, "name" => "GILIRENG"],
        ["id" => 6928, "name" => "KEERA"],
        ["id" => 6929, "name" => "PENRANG"],
        ["id" => 6930, "name" => "TEMPE"],
    ];

    public $sinjai = [
        ["id" => 6931, "name" => "SINJAI BARAT"],
        ["id" => 6932, "name" => "SINJAI SELATAN"],
        ["id" => 6933, "name" => "SINJAI TENGAH"],
        ["id" => 6934, "name" => "SINJAI TIMUR"],
        ["id" => 6935, "name" => "SINJAI UTARA"],
        ["id" => 6936, "name" => "BULUPODDO"],
        ["id" => 6937, "name" => "PULAU SEMBILAN"],
        ["id" => 6938, "name" => "SINJAI BORONG"],
        ["id" => 6939, "name" => "TELLU LIMPOE"],
    ];

    public $gowa = [
        ["id" => 6940, "name" => "BAJENG"],
        ["id" => 6941, "name" => "BONTOMARANNU"],
        ["id" => 6942, "name" => "BONTONOMPO"],
        ["id" => 6943, "name" => "BUNGAYA"],
        ["id" => 6944, "name" => "PALLANGGA"],
        ["id" => 6945, "name" => "PARANGLOE"],
        ["id" => 6946, "name" => "TINGGIMONCONG"],
        ["id" => 6947, "name" => "TOMPOBULU"],
        ["id" => 6948, "name" => "BAROMBONG"],
        ["id" => 6949, "name" => "BIRINGBULU"],
        ["id" => 6950, "name" => "SOMBA OPU (UPU)"],
        ["id" => 6951, "name" => "TOMBOLO PAO"],
        ["id" => 6952, "name" => "PATTALLASSANG"],
        ["id" => 6953, "name" => "PARIGI"],
        ["id" => 6954, "name" => "BONTONOMPO SELATAN"],
        ["id" => 6955, "name" => "BONTOLEMPANGANG"],
        ["id" => 6956, "name" => "BAJENG BARAT"],
        ["id" => 6957, "name" => "MANUJU"],
    ];
    public $takalar = [
        ["id" => 6958, "name" => "GALESONG SELATAN"],
        ["id" => 6959, "name" => "GALESONG UTARA"],
        ["id" => 6960, "name" => "MANGARA BOMBANG"],
        ["id" => 6961, "name" => "MAPPAKASUNGGU"],
        ["id" => 6962, "name" => "PATALLASSANG"],
        ["id" => 6963, "name" => "POLOMBANGKENG SELATAN (POLOBANGKENG)"],
        ["id" => 6964, "name" => "POLOMBANGKENG UTARA (POLOBANGKENG)"],
        ["id" => 6965, "name" => "SANROBONE"],
        ["id" => 6966, "name" => "GALESONG"],
    ];

    public $bone = [
        ["id" => 6967, "name" => "AJANGALE"],
        ["id" => 6968, "name" => "BAREBBO"],
        ["id" => 6969, "name" => "BONTOCANI"],
        ["id" => 6970, "name" => "CINA"],
        ["id" => 6971, "name" => "DUA BOCCOE"],
        ["id" => 6972, "name" => "KAHU"],
        ["id" => 6973, "name" => "KAJUARA"],
        ["id" => 6974, "name" => "LAMURU"],
        ["id" => 6975, "name" => "LAPPARIAJA"],
        ["id" => 6976, "name" => "LIBURENG"],
        ["id" => 6977, "name" => "MARE"],
        ["id" => 6978, "name" => "PONRE"],
        ["id" => 6979, "name" => "SALOMEKKO"],
        ["id" => 6980, "name" => "SIBULUE"],
        ["id" => 6981, "name" => "TELLU SIATTINGE"],
        ["id" => 6982, "name" => "TONRA"],
        ["id" => 6983, "name" => "ULAWENG"],
        ["id" => 6984, "name" => "AMALI"],
        ["id" => 6985, "name" => "AWANGPONE"],
        ["id" => 6986, "name" => "BENGO"],
        ["id" => 6987, "name" => "PALAKKA"],
        ["id" => 6988, "name" => "PATIMPENG"],
        ["id" => 6989, "name" => "TANETE RIATTANG"],
        ["id" => 6990, "name" => "TANETE RIATTANG BARAT"],
        ["id" => 6991, "name" => "TANETE RIATTANG TIMUR"],
        ["id" => 6992, "name" => "CENRANA"],
        ["id" => 6993, "name" => "TELLU LIMPOE"],
    ];
    public $soppeng = [
        ["id" => 6994, "name" => "DONRI-DONRI"],
        ["id" => 6995, "name" => "LILIRAJA (LILI RIAJA)"],
        ["id" => 6996, "name" => "LILI RILAU"],
        ["id" => 6997, "name" => "MARIO RIWAWO"],
        ["id" => 6998, "name" => "MARIO RIAWA"],
        ["id" => 6999, "name" => "GANRA"],
        ["id" => 7000, "name" => "LALABATA"],
        ["id" => 7001, "name" => "CITTA"],
    ];
    public $selayar = [
        ["id" => 7002, "name" => "BENTENG"],
        ["id" => 7003, "name" => "BONTOSIKUYU"],
        ["id" => 7004, "name" => "BONTOMATENE"],
        ["id" => 7005, "name" => "PASIMARANNU"],
        ["id" => 7006, "name" => "PASIMASSUNGGU"],
        ["id" => 7007, "name" => "BONTOHARU"],
        ["id" => 7008, "name" => "BONTOMANAI"],
        ["id" => 7009, "name" => "PASILAMBENA"],
        ["id" => 7010, "name" => "TAKABONERATE"],
        ["id" => 7011, "name" => "PASIMASUNGGU TIMUR"],
        ["id" => 7012, "name" => "BUKI"],
    ];
    public $pangkep = [
        ["id" => 7013, "name" => "PANGKAJENE"],
        ["id" => 7014, "name" => "BALOCCI"],
        ["id" => 7015, "name" => "BUNGORO"],
        ["id" => 7016, "name" => "LABAKKANG"],
        ["id" => 7017, "name" => "LIUKANG TANGAYA"],
        ["id" => 7018, "name" => "LIUKANG TUPABBIRING"],
        ["id" => 7019, "name" => "MARANG (MA RANG)"],
        ["id" => 7020, "name" => "SEGERI"],
        ["id" => 7021, "name" => "MANDALLE"],
        ["id" => 7022, "name" => "MINASA TENE"],
        ["id" => 7023, "name" => "TONDONG TALLASA"],
        ["id" => 7024, "name" => "LIUKANG KALMAS (KALUKUANG MASALIMA)"],
        ["id" => 7025, "name" => "LIUKANG TUPABBIRING UTARA"],
    ];
    public $parepare = [
        ["id" => 7026, "name" => "BACUKIKI"],
        ["id" => 7027, "name" => "SOREANG"],
        ["id" => 7028, "name" => "UJUNG"],
        ["id" => 7029, "name" => "BACUKIKI BARAT"],
    ];
    public $luwu = [
        ["id" => 7030, "name" => "BELOPA"],
        ["id" => 7031, "name" => "BAJO"],
        ["id" => 7032, "name" => "BASSESANG TEMPE (BASTEM)"],
        ["id" => 7033, "name" => "BUA"],
        ["id" => 7034, "name" => "BUA PONRANG (BUPON)"],
        ["id" => 7035, "name" => "KAMANRE"],
        ["id" => 7036, "name" => "LAMASI"],
        ["id" => 7037, "name" => "LAROMPONG"],
        ["id" => 7038, "name" => "LAROMPONG SELATAN"],
        ["id" => 7039, "name" => "LATIMOJONG"],
        ["id" => 7040, "name" => "PONRANG"],
        ["id" => 7041, "name" => "SULI"],
        ["id" => 7042, "name" => "WALENRANG"],
        ["id" => 7043, "name" => "WALENRANG UTARA"],
        ["id" => 7044, "name" => "WALENRANG TIMUR"],
        ["id" => 7045, "name" => "WALENRANG BARAT"],
        ["id" => 7046, "name" => "SULI BARAT"],
        ["id" => 7047, "name" => "PONRANG SELATAN"],
        ["id" => 7048, "name" => "LAMASI TIMUR"],
        ["id" => 7049, "name" => "BELOPA UTARA"],
        ["id" => 7050, "name" => "BAJO BARAT"],
        ["id" => 7051, "name" => "BASSE SANGTEMPE UTARA"],
    ];

    public $luwu_timur = [
        ["id" => 7052, "name" => "MALILI"],
        ["id" => 7053, "name" => "ANGKONA"],
        ["id" => 7054, "name" => "BURAU"],
        ["id" => 7055, "name" => "MANGKUTANA"],
        ["id" => 7056, "name" => "NUHA"],
        ["id" => 7057, "name" => "TOMONI"],
        ["id" => 7058, "name" => "TOWUTI"],
        ["id" => 7059, "name" => "WOTU"],
        ["id" => 7060, "name" => "WASUPONDA"],
        ["id" => 7061, "name" => "TOMONI TIMUR"],
        ["id" => 7062, "name" => "KALAENA"],
    ];
    public $luwu_utara = [
        ["id" => 7063, "name" => "MASAMBA"],
        ["id" => 7064, "name" => "BAEBUNTA"],
        ["id" => 7065, "name" => "BONE-BONE"],
        ["id" => 7066, "name" => "LIMBONG"],
        ["id" => 7067, "name" => "MALANGKE"],
        ["id" => 7068, "name" => "MALANGKE BARAT"],
        ["id" => 7069, "name" => "MAPPEDECENG"],
        ["id" => 7070, "name" => "RAMPI"],
        ["id" => 7071, "name" => "SABBANG"],
        ["id" => 7072, "name" => "SEKO"],
        ["id" => 7073, "name" => "SUKAMAJU"],
        ["id" => 7074, "name" => "TANA LILI"],
        ["id" => 7123, "name" => "SUKAMAJU SELATAN"],
    ];


}
