<?php

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Array2XML;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/finance', function () {
    return view('index');
});

Route::get('/upload', function () {
    return view('upload');
});

function dos2unix($s) {
    $s = str_replace("\r\n", "\n", $s);
    $s = str_replace("\r", "\n", $s);
    $s = preg_replace("/\n{2,}/", "\n\n", $s);
    return $s;
}

function csv_to_array($csv, $delimiter=',', $header_line=true) {
    // CSV from external sources may have Unix or DOS line endings. str_getcsv()
    // requires that the "delimiter" be one character only, so we don't want
    // to pass the DOS line ending \r\n to that function. So first we ensure
    // that we have Unix line endings only.
    $csv = str_replace("\r\n", "\n", $csv);

    // Read the CSV lines into a numerically indexed array. Use str_getcsv(),
    // rather than splitting on all linebreaks, as fields may themselves contain
    // linebreaks.
    $all_lines = str_getcsv($csv, "\n");
    if (!$all_lines) {
        return false;
    }

    $csv = array_map(
        function(&$line) use ($delimiter) {
            return str_getcsv($line, $delimiter);
        },
        $all_lines
    );

    if ($header_line) {
        // Use the first row's values as keys for all other rows.
        array_walk(
            $csv,
            function(&$a) use ($csv) {
                $a = array_combine($csv[0], $a);
            }
        );
        // Remove column header row.
        array_shift($csv);
    }

    return $csv;
}


Route::post('/upload', function (Request $request) {

    $input = dos2unix($request->input('finance'));

    $json_array = csv_to_array($input);

    dd($json_array);
//
//    $json_array = [
//        [671697,22787,"2018-05-25",500,"HCST",740.14,0,542,"N",0,14,"S","1966-10-25","DY3 1AA",2364,"S","C","ET",""  ],
//        [671697,22603,"2018-04-04",500,"HCST",658.17,0,563,"N",0,26,"S","1963-11-20","SS14 1QD",2000,"S","C","ET",""  ],
//        [671697,22728,"2018-05-09",500,"HCST",720.14,0,542,"N",0,14,"S","1966-10-25","DY3 1AA",2364,"S","C","ET",""  ]
//    ];

    $data = [];
    foreach ($json_array as $fca) {
        /*
        1	FirmReferenceNumber
        2	TransactionRef
        3	TransactionDate
        4	LoanAmount
        5	LoanType
        6	APR
        7	ArrangementFee
        8	TotalAmountPayable
        9	Rollover
        10	OrderOfRollover
        11	LengthOfTerm
        12	ReasonForLoan
        13	DOBOfBorrower
        14	PostCode
        15	MonthlyIncomeOfBorrower
        16	MaritalStatusOfBorrower
        17	ResidentialStatusOfBorrower
        18	Employment_status_of_borrower
         */
        $array_data = array(
            'CoreItems' => array(
                'FirmReferenceNumber' => $fca[0],
                'TransRef' => $fca[1],
                'Cancellation' => false
            ),
            'ShortTermLoans' => array(
                'TransactionDate' => date('Y-m-d', strtotime($fca[2])),
                'LoanAmount' => intval($fca[3]),
                'LoanType' => $fca[4],
                'APR' => number_format($fca[5], 2),
                'ArrangementFee' => intval($fca[6]),
                'TotalAmountPayable' => intval($fca[7]),
                'Rollover' => $fca[8],
                'OrderOfRollover' => $fca[9],
                'LengthOfTerm' => $fca[10],
                'ReasonForLoan' => $fca[11],
                'DOBOfBorrower' => date('Y-m-d', strtotime($fca[12])),
                'PostCode' => $fca[13],
                'MonthlyIncomeOfBorrower' => intval($fca[14]),
                'MaritalStatusOfBorrower' => $fca[15],
                'ResidentialStatusOfBorrower' => $fca[16],
                'EmploymentStatusOfBorrower' => $fca[17]
            ),
        );
        array_push($data, $array_data);
    }
    $productSalesData = array(
        '@attributes' => array(
            'xmlns' =>"urn:fsa-gov-uk:MER:PSD006:1",
            'xmlns:xsi' =>"http://www.w3.org/2001/XMLSchema-instance",
            'xsi:schemaLocation' =>"urn:fsa-gov-uk:MER:PSD006:1 http://www.fsa.gov.uk/MER/DRG/PSD006/v1/PSD006-Schema.xsd"
        ),
        'PSDFeedHeader' => array(
            'Submitter' => array(
                'SubmittingFirm' => 671697
            ),
            'ReportDetails' => array(
                'ReportCreationDate' => date('Y-m-d'),
                'ReportIdentifier' => 'UniqueReportv2',
            ),
        ),
        'PSD006FeedMsg' => $data,
    );
    $version ='1.0';
    $encoding = 'UTF-8';
    Array2XML::init($version, $encoding);
    $xml = Array2XML::createXML('PSD006-ShortTermLoans', $productSalesData);

    $filename = 'ProductSalesData' . date('Yms_Hms') . '.xml';
    header('Content-type:"text/xml"; charset="utf8"');
    header('Content-disposition: attachment; filename="'.$filename.'"');
    echo $xml->saveXML();

});


Route::get('test', function () {


    $json_array = [
        [671697,22787,"2018-05-25",500,"HCST",740.14,0,542,"N",0,14,"S","1966-10-25","DY3 1AA",2364,"S","C","ET",""  ],
        [671697,22603,"2018-04-04",500,"HCST",658.17,0,563,"N",0,26,"S","1963-11-20","SS14 1QD",2000,"S","C","ET",""  ],
        [671697,22728,"2018-05-09",500,"HCST",720.14,0,542,"N",0,14,"S","1966-10-25","DY3 1AA",2364,"S","C","ET",""  ]
    ];
//	var_dump($json_array);
//
//	$array_data = array(
//		'CoreItems' => array(
//			'FirmReferenceNumber' => '123456',
//			'TransRef' => 'UniqueTransRef-JanFile-02',
//			'Cancellation' => false
//
//		),
//		'ShortTermLoans' => array(
//			'TransactionDate' => '2015-02-20',
//			'LoanAmount' => rand(100, 10000),
//			'LoanType' => LOAN_TYPE,
//			'APR' => rand(9, 158),
//			'ArrangementFee' => rand(50, 200),
//			'TotalAmountPayable' => rand(200, 1200),
//			'Rollover' => 'N',
//			'OrderOfRollover' => 0,
//			'LengthOfTerm' => rand(1,31),
//			'ReasonForLoan' => 'S',
//			'DOBOfBorrower' => '1980-04-18',
//			'PostCode' => 'SW1 1WS',
//			'MonthlyIncomeOfBorrower' => rand(2100, 7000),
//			'MaritalStatusOfBorrower' => 'M',
//			'ResidentialStatusOfBorrower' => 'O',
//			'EmploymentStatusOfBorrower' => 'EF'
//		),
//	);
    $data = [];
    foreach ($json_array as $fca) {
        /*
        1	FirmReferenceNumber
        2	TransactionRef
        3	TransactionDate
        4	LoanAmount
        5	LoanType
        6	APR
        7	ArrangementFee
        8	TotalAmountPayable
        9	Rollover
        10	OrderOfRollover
        11	LengthOfTerm
        12	ReasonForLoan
        13	DOBOfBorrower
        14	PostCode
        15	MonthlyIncomeOfBorrower
        16	MaritalStatusOfBorrower
        17	ResidentialStatusOfBorrower
        18	Employment_status_of_borrower
         */
        $array_data = array(
            'CoreItems' => array(
                'FirmReferenceNumber' => $fca[0],
                'TransRef' => $fca[1],
                'Cancellation' => false
            ),
            'ShortTermLoans' => array(
                'TransactionDate' => date('Y-m-d', strtotime($fca[2])),
                'LoanAmount' => intval($fca[3]),
                'LoanType' => $fca[4],
                'APR' => number_format($fca[5], 2),
                'ArrangementFee' => intval($fca[6]),
                'TotalAmountPayable' => intval($fca[7]),
                'Rollover' => $fca[8],
                'OrderOfRollover' => $fca[9],
                'LengthOfTerm' => $fca[10],
                'ReasonForLoan' => $fca[11],
                'DOBOfBorrower' => date('Y-m-d', strtotime($fca[12])),
                'PostCode' => $fca[13],
                'MonthlyIncomeOfBorrower' => intval($fca[14]),
                'MaritalStatusOfBorrower' => $fca[15],
                'ResidentialStatusOfBorrower' => $fca[16],
                'EmploymentStatusOfBorrower' => $fca[17]
            ),
        );
        array_push($data, $array_data);
    }
    $productSalesData = array(
        '@attributes' => array(
            'xmlns' =>"urn:fsa-gov-uk:MER:PSD006:1",
            'xmlns:xsi' =>"http://www.w3.org/2001/XMLSchema-instance",
            'xsi:schemaLocation' =>"urn:fsa-gov-uk:MER:PSD006:1 http://www.fsa.gov.uk/MER/DRG/PSD006/v1/PSD006-Schema.xsd"
        ),
        'PSDFeedHeader' => array(
            'Submitter' => array(
                'SubmittingFirm' => 671697
            ),
            'ReportDetails' => array(
                'ReportCreationDate' => date('Y-m-d'),
                'ReportIdentifier' => 'UniqueReportv2',
            ),
        ),
        'PSD006FeedMsg' => $data,
    );
    $version ='1.0';
    $encoding = 'UTF-8';
    Array2XML::init($version, $encoding);
    $xml = Array2XML::createXML('PSD006-ShortTermLoans', $productSalesData);
    $filename = 'ProductSalesData' . date('Yms_Hms') . '.xml';
    header('Content-type:"text/xml"; charset="utf8"');
    header('Content-disposition: attachment; filename="'.$filename.'"');
    echo $xml->saveXML();
});
