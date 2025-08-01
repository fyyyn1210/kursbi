namespace KursBI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class KursService
{
    protected $client;
    protected $baseUrl = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3';

    public function __construct()
    {
        $this->client = new Client([
            'timeout'         => 25,
            'connect_timeout' => 19,
        ]);
    }

    public function getKurs(
        string $mataUang  = 'USD',
        string $startDate = null,
        string $endDate   = null,
        int    $limit     = null,
        float  $jumlah    = null
    ): array {
        $startDate = $startDate ?? date('Y-m-d');
        $endDate   = $endDate ?? date('Y-m-d');

        $currencies = array_map('trim', explode(',', strtoupper($mataUang)));

        $allResults = [];

        foreach ($currencies as $code) {
            $cacheKey = "kursbi_{$code}_{$startDate}_{$endDate}";
            $data     = Cache::remember($cacheKey, now()->addHours(6), function () use ($code, $startDate, $endDate) {
                return $this->fetchKursFromBI($code, $startDate, $endDate);
            });

            $processed    = $this->processKursData($data, $code, $startDate, $endDate, $limit, $jumlah);
            $allResults[] = $processed;
        }

        return [
            'success'         => true,
            'mata_uang_count' => count($currencies),
            'data'            => $allResults
        ];
    }

    protected function fetchKursFromBI(string $code, string $startDate, string $endDate): array
    {
        try {
            $params = [
                'mts'       => $code,
                'startdate' => $startDate,
                'enddate'   => $endDate,
            ];

            $response = $this->client->request('GET', $this->baseUrl, [
                'query'   => $params,
                'headers' => [
                    'User-Agent' => 'Laravel-Kurs-Service',
                    'Accept'     => 'application/xml',
                ]
            ]);

            $xmlString = $response->getBody()->getContents();
            $xml       = simplexml_load_string($xmlString);
            $xml->registerXPathNamespace('diffgr', 'urn:schemas-microsoft-com:xml-diffgram-v1');

            return $xml->xpath('//NewDataSet/Table') ?? [];
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    protected function processKursData(array $data, string $code, string $start, string $end, ?int $limit, ?float $jumlah): array
    {
        $result            = [];
        $totalBeli         = 0;
        $totalJual         = 0;
        $totalKonversiBeli = 0;
        $totalKonversiJual = 0;

        foreach ($data as $item) {
            $beli = (float) $item->beli_subkurslokal;
            $jual = (float) $item->jual_subkurslokal;

            $row = [
                'tanggal' => (string) $item->tgl_subkurslokal,
                'nilai'   => (float) $item->nil_subkurslokal,
                'beli'    => $beli,
                'jual'    => $jual,
            ];

            if ($jumlah !== null) {
                $row['konversi_beli']  = round($jumlah * $beli, 2);
                $row['konversi_jual']  = round($jumlah * $jual, 2);
                $totalKonversiBeli    += $row['konversi_beli'];
                $totalKonversiJual    += $row['konversi_jual'];
            }

            $totalBeli += $beli;
            $totalJual += $jual;

            $result[] = $row;
        }

        if ($limit !== null) {
            $result = array_slice($result, 0, $limit);
        }

        $count   = count($result);
        $avgBeli = $count ? round($totalBeli / $count, 2) : 0;
        $avgJual = $count ? round($totalJual / $count, 2) : 0;

        return [
            'mata_uang' => $code,
            'periode'   => [
                'start' => $start,
                'end'   => $end,
            ],
            'jumlah_input' => $jumlah,
            'jumlah_data'  => $count,
            'rata_rata'    => [
                'beli' => $avgBeli,
                'jual' => $avgJual,
            ],
            'total_konversi' => $jumlah !== null ? [
                'beli' => $totalKonversiBeli,
                'jual' => $totalKonversiJual,
            ] : null,
            'data' => $result
        ];
    }
}