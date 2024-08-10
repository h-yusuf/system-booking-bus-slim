<?php
namespace App\Lib;
/**
 * Auth
 *
 * @author    Hezekiah O. <support@hezecom.com>
 */
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reporting
{
    public static function excel($body, $header,$filename){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //if header exist
        if($header) {
            $sheet->fromArray($header, NULL, 'A1');
            $sheet->fromArray($body, NULL, 'A2');
        }else{
            $sheet->fromArray($body, NULL, 'A1');
        }
        self::output($spreadsheet, $filename);
    }

    public static function output($spreadsheet, $filename){

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if($ext==='xlsx'){
            $writer = new Xlsx($spreadsheet);
            $type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        elseif($ext==='xls'){
            $writer = new Xls($spreadsheet);
            $type = 'application/vnd.ms-excel';
        }
        elseif($ext==='csv'){
            $writer = new Csv($spreadsheet);
            $type = 'text/csv';
        }
        header('Content-Type:'.$type);
        header('Content-Disposition: attachment;filename='. $filename);
        header('Cache-Control: max-age=0');
        //output download
        $writer->save('php://output');
        //save to server
        //$writer->save($filename);
    }


    public static function pdf($html,$filename, $size='A4',$orientation='portrait'){
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper($size, $orientation);
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser
        //$dompdf->stream();
        $dompdf->stream($filename, array("Attachment" => true));
    }

}
