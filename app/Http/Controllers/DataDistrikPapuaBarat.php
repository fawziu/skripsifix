<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DataDistrikPapuaBarat extends Controller
{
    public function getData($id){
        if ($id == 546) return $this->sorong;
        elseif ($id == 547) return $this->tambraw;
        elseif ($id == 548) return $this->fakfak;
        elseif ($id == 549) return $this->kaimana;
        elseif ($id == 551) return $this->raja_ampat;
        elseif ($id == 552) return $this->sorong_selatan;
        elseif ($id == 553) return $this->maybrat;
        elseif ($id == 554) return $this->teluk_bintuni;
        elseif ($id == 556) return $this->teluk_wondama;
        elseif ($id == 557) return $this->manokwari;
        elseif ($id == 558) return $this->pegunungan_arfak;
        elseif ($id == 559) return $this->manokwari_selatan;
    }
    public $sorong = [
        ["id" => 5384, "name" => "SORONG"],
        ["id" => 5385, "name" => "MAKBON"],
        ["id" => 5387, "name" => "SALAWATI"],
        ["id" => 5389, "name" => "SEGET"],
        ["id" => 5391, "name" => "AIMAS"],
        ["id" => 5392, "name" => "BERAUR"],
        ["id" => 5394, "name" => "KLAMONO"],
        ["id" => 5395, "name" => "SAYOSA"],
        ["id" => 5396, "name" => "SEGUN"],
        ["id" => 5397, "name" => "SORONG BARAT"],
        ["id" => 5398, "name" => "SORONG TIMUR"],
        ["id" => 5399, "name" => "SORONG KEPULAUAN"],
        ["id" => 5400, "name" => "SORONG UTARA"],
        ["id" => 5401, "name" => "MAYAMUK"],
        ["id" => 5402, "name" => "SALAWATI SELATAN"],
        ["id" => 5403, "name" => "KLABOT"],
        ["id" => 5404, "name" => "KLASO"],
        ["id" => 5405, "name" => "KLAWAK"],
        ["id" => 5406, "name" => "KLAYILI"],
        ["id" => 5407, "name" => "MARIAT"],
        ["id" => 5408, "name" => "MAUDUS"],
        ["id" => 5409, "name" => "MOISEGEN"],
        ["id" => 5410, "name" => "KLAURUNG"],
        ["id" => 5411, "name" => "MALADUM MES"],
        ["id" => 5412, "name" => "MALAIMSIMSA"],
        ["id" => 5413, "name" => "SORONG KOTA"],
        ["id" => 5414, "name" => "SORONG MANOI"],
    ];
    public $tambraw = [
        ["id" => 5386, "name" => "MORAID"],
        ["id" => 5388, "name" => "SAUSAPOR"],
        ["id" => 5390, "name" => "ABUN"],
        ["id" => 5393, "name" => "FEF"],
        ["id" => 5527, "name" => "AMBERBAKEN"],
        ["id" => 5529, "name" => "KEBAR"],
        ["id" => 5555, "name" => "MIYAH"],
        ["id" => 5556, "name" => "MUBRANI"],
        ["id" => 5557, "name" => "SENOPI"],
        ["id" => 5558, "name" => "YEMBUN"],
        ["id" => 5559, "name" => "AMBERBAKEN BARAT"],
        ["id" => 5560, "name" => "ASES"],
        ["id" => 5561, "name" => "BAMUSBAMA"],
        ["id" => 5562, "name" => "BIKAR"],
        ["id" => 5563, "name" => "IRERES"],
        ["id" => 5564, "name" => "KASI"],
        ["id" => 5565, "name" => "KEBAR SELATAN"],
        ["id" => 5566, "name" => "KEBAR TIMUR"],
        ["id" => 5567, "name" => "KWESEFO"],
        ["id" => 5568, "name" => "KWOOR"],
        ["id" => 5569, "name" => "MANEKAR"],
        ["id" => 5570, "name" => "MAWABUAN"],
        ["id" => 5571, "name" => "MIYAH SELATAN"],
        ["id" => 5572, "name" => "MPUR"],
        ["id" => 5573, "name" => "SELEMKAI"],
        ["id" => 5574, "name" => "SYUJAK"],
        ["id" => 5575, "name" => "TINGGOUW"],
        ["id" => 5576, "name" => "TOBOUW"],
        ["id" => 5577, "name" => "WILHEM ROUMBOUTS"],
    ];
    public $fakfak = [
        ["id" => 5415, "name" => "FAKFAK"],
        ["id" => 5416, "name" => "FAKFAK BARAT"],
        ["id" => 5417, "name" => "FAKFAK TIMUR"],
        ["id" => 5418, "name" => "KOKAS"],
        ["id" => 5419, "name" => "BOMBERAY"],
        ["id" => 5420, "name" => "FAKFAK TENGAH"],
        ["id" => 5421, "name" => "KARAS"],
        ["id" => 5422, "name" => "KRAMONGMONGGA"],
        ["id" => 5423, "name" => "TELUK PATIPI"],
        ["id" => 5424, "name" => "ARGUNI"],
        ["id" => 5425, "name" => "FAKFAK TIMUR TENGAH"],
        ["id" => 5426, "name" => "FURWAGI"],
        ["id" => 5427, "name" => "KAYAUNI"],
        ["id" => 5428, "name" => "MBAHAMDANDARA"],
        ["id" => 5429, "name" => "PARIWARI"],
        ["id" => 5430, "name" => "TOMAGE"],
        ["id" => 5431, "name" => "WARTUTIN"]
    ];
    public $kaimana = [
        ["id" => 5432, "name" => "KAIMANA"],
        ["id" => 5433, "name" => "BURUWAY"],
        ["id" => 5434, "name" => "TELUK ARGUNI ATAS"],
        ["id" => 5435, "name" => "TELUK ETNA"],
        ["id" => 5436, "name" => "KAMBRAW (KAMBERAU)"],
        ["id" => 5437, "name" => "YAMOR"],
        ["id" => 5438, "name" => "TELUK ARGUNI BAWAH (YERUSI)"],
    ];

    public $raja_ampat = [
        ["id" => 5439, "name" => "KEPULAUAN AYAU"],
        ["id" => 5440, "name" => "KOFIAU"],
        ["id" => 5441, "name" => "MISOOL (MISOOL UTARA)"],
        ["id" => 5442, "name" => "MISOOL TIMUR"],
        ["id" => 5443, "name" => "SALAWATI UTARA (SAMATE)"],
        ["id" => 5444, "name" => "TELUK MAYALIBIT"],
        ["id" => 5445, "name" => "WAIGEO BARAT"],
        ["id" => 5446, "name" => "WAIGEO SELATAN"],
        ["id" => 5447, "name" => "WAIGEO TIMUR"],
        ["id" => 5448, "name" => "WAIGEO UTARA"],
        ["id" => 5449, "name" => "MEOS MANSAR"],
        ["id" => 5450, "name" => "MISOOL BARAT"],
        ["id" => 5451, "name" => "MISOOL SELATAN"],
        ["id" => 5452, "name" => "WAIGEO BARAT KEPULAUAN"],
        ["id" => 5453, "name" => "WARWABOMI"],
        ["id" => 5454, "name" => "AYAU"],
        ["id" => 5455, "name" => "BATANTA UTARA"],
        ["id" => 5456, "name" => "KEPULAUAN SEMBILAN"],
        ["id" => 5457, "name" => "KOTA WAISAI"],
        ["id" => 5458, "name" => "SALAWATI BARAT"],
        ["id" => 5459, "name" => "SALAWATI TENGAH"],
        ["id" => 5460, "name" => "SUPNIN"],
        ["id" => 5461, "name" => "TIPLOL MAYALIBIT"],
        ["id" => 5462, "name" => "BATANTA SELATAN"],
    ];
    public $sorong_selatan = [
        ["id" => 5463, "name" => "TEMINABUAN"],
        ["id" => 5469, "name" => "INANWATAN (INAWATAN)"],
        ["id" => 5470, "name" => "KAIS (MATEMANI KAIS)"],
        ["id" => 5471, "name" => "KOKODA"],
        ["id" => 5473, "name" => "MOSWAREN"],
        ["id" => 5474, "name" => "SAWIAT"],
        ["id" => 5475, "name" => "SEREMUK"],
        ["id" => 5476, "name" => "WAYER"],
        ["id" => 5477, "name" => "FOKOUR"],
        ["id" => 5491, "name" => "KOKODA UTARA"],
        ["id" => 5492, "name" => "KONDA"],
        ["id" => 5493, "name" => "MATEMANI"],
        ["id" => 5494, "name" => "SAIFI"],
    ];
    public $maybrat = [
        ["id" => 5464, "name" => "AIFAT"],
        ["id" => 5465, "name" => "AIFAT TIMUR"],
        ["id" => 5466, "name" => "AITINYO"],
        ["id" => 5467, "name" => "AYAMARU"],
        ["id" => 5468, "name" => "AYAMARU UTARA"],
        ["id" => 5472, "name" => "MARE"],
        ["id" => 5478, "name" => "AITINYO BARAT"],
        ["id" => 5479, "name" => "AITINYO RAYA"],
        ["id" => 5480, "name" => "AITINYO TENGAH"],
        ["id" => 5481, "name" => "AITINYO UTARA"],
        ["id" => 5482, "name" => "AYAMARU BARAT"],
        ["id" => 5483, "name" => "AYAMARU JAYA"],
        ["id" => 5484, "name" => "AYAMARU SELATAN"],
        ["id" => 5485, "name" => "AYAMARU SELATAN JAYA"],
        ["id" => 5486, "name" => "AYAMARU TENGAH"],
        ["id" => 5487, "name" => "AYAMARU TIMUR"],
        ["id" => 5488, "name" => "AYAMARU TIMUR SELATAN"],
        ["id" => 5489, "name" => "AYAMARU UTARA TIMUR"],
        ["id" => 5490, "name" => "MARE SELATAN"],
        ["id" => 5495, "name" => "AIFAT TIMUR TENGAH"],
        ["id" => 5583, "name" => "AIFAT SELATAN"],
        ["id" => 5584, "name" => "AIFAT TIMUR JAUH"],
        ["id" => 5585, "name" => "AIFAT TIMUR SELATAN"],
        ["id" => 5586, "name" => "AIFAT UTARA"],
    ];
    public $teluk_bintuni = [
        ["id" => 5496, "name" => "BINTUNI"],
        ["id" => 5497, "name" => "ARANDAY"],
        ["id" => 5498, "name" => "BABO"],
        ["id" => 5499, "name" => "FAFURWAR (IRORUTU)"],
        ["id" => 5500, "name" => "WAMESA (IDOOR)"],
        ["id" => 5501, "name" => "KURI"],
        ["id" => 5502, "name" => "MERDEY"],
        ["id" => 5503, "name" => "MOSKONA SELATAN"],
        ["id" => 5504, "name" => "MOSKONA UTARA"],
        ["id" => 5505, "name" => "TEMBUNI"],
        ["id" => 5506, "name" => "AROBA"],
        ["id" => 5507, "name" => "BISCOOP"],
        ["id" => 5508, "name" => "DATARAN BEIMES"],
        ["id" => 5509, "name" => "KAITARO"],
        ["id" => 5510, "name" => "KAMUNDAN"],
        ["id" => 5511, "name" => "MANIMERI"],
        ["id" => 5512, "name" => "MASYETA"],
        ["id" => 5513, "name" => "MAYADO"],
        ["id" => 5514, "name" => "MOSKONA BARAT"],
        ["id" => 5515, "name" => "MOSKONA TIMUR"],
        ["id" => 5516, "name" => "SUMURI (SIMURI)"],
        ["id" => 5517, "name" => "TOMU"],
        ["id" => 5518, "name" => "TUHIBA"],
        ["id" => 5519, "name" => "WERIAGAR"],
    ];
    public $teluk_wondama = [
        ["id" => 5520, "name" => "RUMBERPON"],
        ["id" => 5521, "name" => "WAMESA"],
        ["id" => 5522, "name" => "WASIOR"],
        ["id" => 5523, "name" => "NAIKERE (WASIOR BARAT)"],
        ["id" => 5524, "name" => "WONDIBOY (WASIOR SELATAN)"],
        ["id" => 5525, "name" => "TELUK DUAIRI (WASIOR UTARA)"],
        ["id" => 5526, "name" => "WINDESI"],
        ["id" => 5543, "name" => "KURI WAMESA"],
        ["id" => 5578, "name" => "NIKIWAR"],
        ["id" => 5579, "name" => "ROON"],
        ["id" => 5580, "name" => "ROSWAR"],
        ["id" => 5581, "name" => "SOUG JAYA"],
        ["id" => 5582, "name" => "RASIEI"],
    ];
    public $manokwari = [
        ["id" => 5532, "name" => "WARMARE"],
        ["id" => 5533, "name" => "MASNI"],
        ["id" => 5535, "name" => "PRAFI"],
        ["id" => 5537, "name" => "MANOKWARI BARAT"],
        ["id" => 5538, "name" => "MANOKWARI SELATAN"],
        ["id" => 5539, "name" => "MANOKWARI TIMUR"],
        ["id" => 5540, "name" => "MANOKWARI UTARA"],
        ["id" => 5541, "name" => "SIDEY"],
        ["id" => 5542, "name" => "TANAH RUBUH"],
    ];
    public $pegunungan_arfak = [
        ["id" => 5528, "name" => "ANGGI"],
        ["id" => 5534, "name" => "MINYAMBAOUW"],
        ["id" => 5536, "name" => "SURUREY"],
        ["id" => 5544, "name" => "ANGGI GIDA"],
        ["id" => 5545, "name" => "CATUBOUW"],
        ["id" => 5546, "name" => "DIDOHU"],
        ["id" => 5547, "name" => "HINGK"],
        ["id" => 5548, "name" => "MEMBEY"],
        ["id" => 5549, "name" => "TAIGE"],
        ["id" => 5550, "name" => "TESTEGA"],
    ];
    public $manokwari_selatan = [
        ["id" => 5530, "name" => "ORANSBARI"],
        ["id" => 5531, "name" => "RANSIKI"],
        ["id" => 5551, "name" => "DATARAN ISIM"],
        ["id" => 5552, "name" => "MOMI WAREN"],
        ["id" => 5553, "name" => "NENEY"],
        ["id" => 5554, "name" => "TAHOTA"],
    ];
}
