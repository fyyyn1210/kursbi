namespace Fyyyn1210;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class KursBI
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
    protected function generateUserAgent()
    {
        $os_list = [
            "Windows NT 10.0",
            "Windows NT 6.3",
            "Windows NT 6.1",
            "Macintosh; Intel Mac OS X 10_15_7",
            "Macintosh; Intel Mac OS X 10_14_6",
            "X11; Linux x86_64",
            "X11; Ubuntu; Linux x86_64"
        ];
        $browser_list = [
            ["name" => "Chrome", "versions" => ["90.0.4430.212", "91.0.4472.124", "92.0.4515.107", "93.0.4577.63"]],
            ["name" => "Firefox", "versions" => ["88.0", "89.0", "90.0", "91.0"]],
            ["name" => "Safari", "versions" => ["14.1", "14.0", "13.1.2", "15.0"]],
            ["name" => "Edge", "versions" => ["91.0.864.59", "92.0.902.67", "93.0.961.38"]]
        ];
        $os = $os_list[array_rand($os_list)];
        $browser = $browser_list[array_rand($browser_list)];
        $browser_name = $browser["name"];
        $browser_version = $browser["versions"][array_rand($browser["versions"])];
        switch ($browser_name) {
            case "Chrome":
                return "Mozilla/5.0 ($os) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/$browser_version Safari/537.36";
            case "Firefox":
                return "Mozilla/5.0 ($os; rv:$browser_version) Gecko/20100101 Firefox/$browser_version";
            case "Safari":
                return "Mozilla/5.0 ($os) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/$browser_version Safari/605.1.15";
            case "Edge":
                return "Mozilla/5.0 ($os) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/$browser_version Safari/537.36 Edg/$browser_version";
            default:
                return "Mozilla/5.0 ($os) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/$browser_version Safari/537.36";
        }
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
                    'User-Agent' => $this->generateUserAgent(),
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