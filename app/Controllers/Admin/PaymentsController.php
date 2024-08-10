<?php
/*
* =======================================================================
* FILE NAME:        BookingsController.php
* DATE CREATED:  	23-06-2024
* FOR TABLE:  		bookings
* AUTHOR:			Sobat Gurun Tech
* CONTACT:			sobatguruntech.com
* =======================================================================
*/

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Auth\Auth;
use App\Lib\Datatable;
use App\Lib\Uploader;
use App\Lib\Reporting;
use App\Models\Payments;
use App\Models\Bookings;
use App\Models\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;


class PaymentsController extends Controller
{
    /**
     * This method initiate the datatable template
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function index(Request $request, Response $response)
    {
        return view($response, 'admin/payments/index.twig');
    }

    public function webhook(Request $request, Response $response)
    {
        // Ini akan menjadi Token Verifikasi Callback Anda yang dapat Anda peroleh dari dasbor.
        // Pastikan untuk menjaga kerahasiaan token ini dan tidak mengungkapkannya kepada siapa pun.
        // Token ini akan digunakan untuk melakukan verfikasi pesan callback bahwa pengirim callback tersebut adalah Xendit
        $xenditXCallbackToken = 'mFSgzlG7Mf8JB15iqXqSZd7lgEiqg8Dl654z0TSq2zdMRLuk';

        // Bagian ini untuk mendapatkan Token callback dari permintaan header, 
        // yang kemudian akan dibandingkan dengan token verifikasi callback Xendit
        $reqHeaders = getallheaders();
        $xIncomingCallbackTokenHeader = isset($reqHeaders['x-callback-token']) ? $reqHeaders['x-callback-token'] : "";
		//var_dump($xIncomingCallbackTokenHeader);
      //var_dump($reqHeaders);
         //   die;
        // Untuk memastikan permintaan datang dari Xendit
        // Anda harus membandingkan token yang masuk sama dengan token verifikasi callback Anda
        // Ini untuk memastikan permintaan datang dari Xendit dan bukan dari pihak ketiga lainnya.
        //if ($xIncomingCallbackTokenHeader === $xenditXCallbackToken) {
            // Permintaan masuk diverifikasi berasal dari Xendit

            // Baris ini untuk mendapatkan semua input pesan dalam format JSON teks mentah
            $rawRequestInput = file_get_contents("php://input");
            // Baris ini melakukan format input mentah menjadi array asosiatif
            $arrRequestInput = json_decode($rawRequestInput, true);
            
            
            $_id = $arrRequestInput['id'];
            $_externalId = $arrRequestInput['external_id'];
            $_userId = $arrRequestInput['payer_email'];
            $_status = $arrRequestInput['status'];
            $_paidAmount = $arrRequestInput['paid_amount'];
            $_paidAt = $arrRequestInput['paid_at'];
            $_paymentChannel = $arrRequestInput['payment_channel'];
            $_paymentDestination = $arrRequestInput['payment_destination'];
			
            // Kamu bisa menggunakan array objek diatas sebagai informasi callback yang dapat digunaka untuk melakukan pengecekan atau aktivas tertentu di aplikasi atau sistem kamu.

        //} else {
            // Permintaan bukan dari Xendit, tolak dan buang pesan dengan HTTP status 403
          //  http_response_code(403);
        //}
       // $data = $request->getParsedBody();
      
    //    $status = $data['status'];
      //  $paymentId = $data['payment_id'];
      $user = User::where('email', $_userId)->first();
		$book = Bookings::where('user_id', $user->id)->first();
      //var_dump($book);
      //die;
        $payment = Payments::where('booking_id', $book->id)->first();
        if ($payment) {
            $payment->status = 'completed';
            $payment->save();
        }
        $responseData = ['status' => 'success', 'message' => 'Payment Success'];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function indexApi(Request $request, Response $response)
    {
        $payments = Payments::select('*')->get();
        $responseData = ['status' => 'success', 'message' => 'Data retrieved successfully', 'data' => $payments];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    /**
     * This method send data to datatable
     * @param $primaryKey is the table primary key
     * @param {rowId} will search and replace with the table primary key
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function datatable(Request $request, Response $response)
    {
        $query = Payments::select('*')->with('bookings');

        $primaryKey = 'id';
        $action = '<div class="btn-group btn-group-sm" role="group">
				<a href="' . route('payments.show', ['id' => '{rowId}']) . '"  class="btn"><i class="bi bi-zoom-in font16"></i></a>
				<a href="' . route('payments.edit', ['id' => '{rowId}']) . '"  class="btn"><i class="bi bi-pencil text-dark font16"></i></a>
				<a href="javascript:void(0)"  class="btn" onclick="deleteRecord(\'' . route('payments.destroy', ['id' => '{rowId}']) . '\')"><i class="bi bi-trash text-danger font16"></i></a>
				</div>';
        json_encode(Datatable::make($query, $primaryKey, $action));
        return $response;
    }

    /**
     * This method select payments details
     * @param $id
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function show(Request $request, Response $response, $id)
    {
        $payments = Payments::select('*')->with('bookings')
            ->where('id', '=', $id)->first();
        return view($response, 'admin/payments/show.twig', compact('payments'));
    }

    /**
     * This method load payments form
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function create(Request $request, Response $response)
    {
        $bookingid = Bookings::limit(50)->get();

        return view($response, 'admin/payments/create.twig', compact('bookingid'));
    }

    /**
     * Insert into database 
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request, Response $response)
    {
        /* validate payments data */
        $validation = $this->validator->validate($request, [
            'booking_id' => v::notEmpty(),
            'amount' => v::notEmpty(),
            'status' => v::notEmpty(),

        ]);
        if ($validation->failed()) {
            redirect()->route('payments.create');
        } else {
            /* get post data */
            $data = $request->getParsedBody();
            /* insert post data */
            $data = Payments::create([
                'booking_id' => $data['booking_id'],
                'amount' => $data['amount'],
                'status' => $data['status'],

            ]);

            redirect()->route('payments.index')->with('success', lang('record_created'));
        }
    }

    /**
     * Get form for payments to edit
     * @param $id
     * @param Request $request
     * @param Response $response
     */
    public function edit(Request $request, Response $response, $id)
    {
        $bookingid = Bookings::limit(50)->get();

        $payments = Payments::findOrFail($id);
        /* pass payments data to view and load list view */

        return view($response, 'admin/payments/edit.twig', compact('payments', 'bookingid'));
    }

    /**
     * This method process payments edit form
     * @param $id
     * @param Request $request
     */
    public function update(Request $request, Response $response)
    {

        $data = $request->getParsedBody();
        /* validate payments data */
        $validation = $this->validator->validate($request, [
            'booking_id' => v::notEmpty(),
            'amount' => v::notEmpty(),
            'status' => v::notEmpty(),

        ]);
        if ($validation->failed()) {
            redirect()->route('payments.edit', ['id' => $data['id']]);
        } else {
            $payments = Payments::findOrFail($data['id']);
            $payments->booking_id = $data['booking_id'];
            $payments->amount = $data['amount'];
            $payments->status = $data['status'];


            $payments->save();
            redirect()->route('payments.edit', ['id' => $data['id']])->with('success', lang('record_updated'));
        }
    }

    /**
     * This method delete record from database
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function destroy(Request $request, Response $response, $id)
    {
        if ($id) {
            Payments::where('id', $id)->delete();
            echo 1;
        }
        return $response;
    }

    /**
     * Delete file
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function deleteFile(Request $request, Response $response, $id)
    {
        if ($id) {
            Uploader::deleteFiles($id);
            echo 1;
        }
        return $response;
    }

    /**
     * Export to excel supports  (xlsx, xls, csv)
     * The @header array can be null to dissable header for your export
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function export(Request $request, Response $response, $type)
    {
        $payments = Payments::select('booking_id', 'amount', 'status')->get()->toArray();
        $filename = 'payments_' . date('ymd') . '.' . $type;
        if ($type === 'pdf') {
            $message = render('admin/payments/print.twig', compact('payments'));
            Reporting::pdf($message, $filename);
        } else {
            $header = ['booking_id', 'amount', 'status'];
            Reporting::excel($payments, $header, $filename);
        }
        return $response;
    }
}
