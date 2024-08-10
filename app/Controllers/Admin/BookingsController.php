<?php


namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Auth\Auth;
use App\Lib\Datatable;
use App\Lib\Uploader;
use App\Lib\Reporting;
use App\Models\Bookings;
use App\Models\Tbusers;
use App\Models\Buses;
use App\Models\Payments;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class BookingsController extends Controller
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
        return view($response, 'admin/bookings/index.twig');
    }

    public function indexApi(Request $request, Response $response)
    {
        $bookings = Bookings::select('*')->with('tbusers', 'buses')->get();
        if (Auth::hasRole('user') == 1) {
            $bookings = Bookings::select('*')->with('tbusers', 'buses', 'payments')->where('user_id', Auth::user()['id'])->get();
        }
        $responseData = ['status' => 'success', 'message' => 'Data retrieved successfully', 'data' => $bookings];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function datatable(Request $request, Response $response)
    {
        $query = Bookings::select('*')->with('tbusers', 'buses');

        $primaryKey = 'id';
        $action = '<div class="btn-group btn-group-sm" role="group">
				<a href="' . route('bookings.show', ['id' => '{rowId}']) . '"  class="btn"><i class="bi bi-zoom-in font16"></i></a>
				<a href="' . route('bookings.edit', ['id' => '{rowId}']) . '"  class="btn"><i class="bi bi-pencil text-dark font16"></i></a>
				<a href="javascript:void(0)"  class="btn" onclick="deleteRecord(\'' . route('bookings.destroy', ['id' => '{rowId}']) . '\')"><i class="bi bi-trash text-danger font16"></i></a>
				</div>';
        json_encode(Datatable::make($query, $primaryKey, $action));
        return $response;
    }

    public function show(Request $request, Response $response, $id)
    {
        $bookings = Bookings::select('*')->with('tbusers', 'buses')
            ->where('id', '=', $id)->first();
        return view($response, 'admin/bookings/show.twig', compact('bookings'));
    }
    public function showApi(Request $request, Response $response, $id)
    {
        $bookings = Bookings::select('*')->with('tbusers', 'buses')
            ->where('id', '=', $id)->first();
        $responseData = ['status' => 'success', 'message' => 'Data retrieved successfully', 'data' => $bookings];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response)
    {
        $userid = Tbusers::limit(50)->get();
        $busid = Buses::limit(50)->get();

        return view($response, 'admin/bookings/create.twig', compact('userid', 'busid'));
    }

    public function createApi(Request $request, Response $response)
    {
        $userid = Tbusers::limit(50)->get();
        $busid = Buses::limit(50)->get();
        $responseData = ['status' => 'success', 'message' => 'Data retrieved successfully', 'data' => ['userid' => $userid, 'busid' => $busid]];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response)
    {
        /* validate bookings data */
        $validation = $this->validator->validate($request, [
            'user_id' => v::notEmpty(),
            'bus_id' => v::notEmpty(),
            'seat_id' => v::notEmpty(),
            'from_location' => v::notEmpty(),
            'to_location' => v::notEmpty(),
            'date' => v::notEmpty(),
            'time_start' => v::notEmpty(),
            'time_end' => v::notEmpty(),
            'status' => v::notEmpty(),
        ]);
        if ($validation->failed()) {
            redirect()->route('bookings.create');
        } else {
            /* get post data */
            $data = $request->getParsedBody();
            /* insert post data */
            $data = Bookings::create([
                'user_id' => $data['user_id'],
                'bus_id' => $data['bus_id'],
                'seat_id' => $data['seat_id'],
                'from_location' => $data['from_location'],
                'to_location' => $data['to_location'],
                'date' => $data['date'],
                'time_start' => $data['time_start'],
                'time_end' => $data['time_end'],
                'status' => $data['status'],
            ]);

            redirect()->route('bookings.index')->with('success', lang('record_created'));
        }
    }

    public function storeApi(Request $request, Response $response)
    {
        /* validate bookings data */
        $validation = $this->validator->validate($request, [
            'user_id' => v::notEmpty(),
            'bus_id' => v::notEmpty(),
            'seat_id' => v::notEmpty(),
            'from_location' => v::notEmpty(),
            'to_location' => v::notEmpty(),
            'date' => v::notEmpty(),
            'time_start' => v::notEmpty(),
            'time_end' => v::notEmpty(),
            'status' => v::notEmpty(),
            'email' => v::notEmpty()->email(),  // Pastikan email ditambahkan
            'amount' => v::notEmpty()->number(),
        ]);

        if ($validation->failed()) {
            $responseData = ['status' => 'error', 'message' => 'Validation failed'];
        } else {
            /* get post data */
            $data = $request->getParsedBody();
            $amount = $data['amount'];
            $email = Auth::user()['email'];  // $data['email'];
            /* insert booking data */

            $bus = Buses::find($data['bus_id']);
            // Check if the seat is already booked for the given bus, date, and time range
            $existingBooking = Bookings::where('bus_id', $data['bus_id'])
                ->where('seat_id', $data['seat_id'])
                ->where('date', $data['date'])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('time_start', [$data['time_start'], $data['time_end']])
                        ->orWhereBetween('time_end', [$data['time_start'], $data['time_end']]);
                })
                ->first();

            if ($existingBooking) {
                $responseData = ['status' => 'error', 'message' => 'Seat is already booked for this time range'];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json');
            }

            // Check if bus is full
            if ($bus->jumlah_kursi <= $bus->seat_booked) {
                $responseData = ['status' => 'error', 'message' => 'Bus is full'];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json');
            }
            // if ($bus->jumlah_kursi <= $bus->seat_booked) {
            //     $responseData = ['status' => 'error', 'message' => 'Bus is full'];
            //     $response->getBody()->write(json_encode($responseData));
            //     return $response->withHeader('Content-Type', 'application/json');
            // }
            $booking = Bookings::create([
                'user_id' => $data['user_id'],
                'bus_id' => $data['bus_id'],
                'seat_id' => $data['seat_id'],
                'from_location' => $data['from_location'],
                'to_location' => $data['to_location'],
                'date' => $data['date'],
                'time_start' => $data['time_start'],
                'time_end' => $data['time_end'],
                'status' => $data['status'],
            ]);

            if ($booking) {
                $payment = Payments::create([
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                $bus->update(['seat_booked' => $bus->seat_booked + 1]);

                // Set API Key for Xendit
                Configuration::setXenditKey('xnd_development_QYWKySk2RjzurbClTPYlXMYLcTZIzyp1cohH5dGICtKMjDQB081c98pmohGS9');
                // Configuration::setXenditKey('xnd_development_q9qH0rbS0aYeVCThknFwY8r4fF9ymYvtpP84nFeAMaKABViIm1wVN8CdNfCWgoKG');

                $apiInstance = new InvoiceApi();

                try {
                    $create_invoice_request = new \Xendit\Invoice\CreateInvoiceRequest([
                        'external_id' => (string) $payment->id,
                        'payer_email' => $email,
                        'description' => 'Payment for booking',
                        'amount' => $amount,
                        'invoice_duration' => 86400,  // 1 day in seconds
                        'currency' => 'IDR',
                        'should_send_email' => true,
                        'success_redirect_url' => base_url() . 'success',  // base_url() is a helper function to get the base URL (e.g. http://localhost:8000
                        'failure_redirect_url' => base_url() . 'failure',
                    ]);

                    $result = $apiInstance->createInvoice($create_invoice_request);
                    $payment->update(['link' => $result->getInvoiceUrl()]);

                    $responseData = ['status' => 'success', 'message' => 'Record created', 'payment_link' => $result->getInvoiceUrl()];
                } catch (\Xendit\XenditSdkException $e) {
                    $responseData = ['status' => 'error', 'message' => 'Xendit API error: ' . $e->getMessage()];
                }
            } else {
                $responseData = ['status' => 'error', 'message' => 'Failed to create booking'];
            }
        }

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function edit(Request $request, Response $response, $id)
    {
        $userid = Tbusers::limit(50)->get();
        $busid = Buses::limit(50)->get();

        $bookings = Bookings::findOrFail($id);
        /* pass bookings data to view and load list view */

        return view($response, 'admin/bookings/edit.twig', compact('bookings', 'userid', 'busid'));
    }

    public function update(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        /* validate bookings data */
        $validation = $this->validator->validate($request, [
            'user_id' => v::notEmpty(),
            'bus_id' => v::notEmpty(),
            'seat_id' => v::notEmpty(),
            'from_location' => v::notEmpty(),
            'to_location' => v::notEmpty(),
            'date' => v::notEmpty(),
            'time_start' => v::notEmpty(),
            'time_end' => v::notEmpty(),
            'status' => v::notEmpty(),
        ]);
        if ($validation->failed()) {
            redirect()->route('bookings.edit', ['id' => $data['id']]);
        } else {
            $bookings = Bookings::findOrFail($data['id']);
            $bookings->user_id = $data['user_id'];
            $bookings->bus_id = $data['bus_id'];
            $bookings->seat_id = $data['seat_id'];
            $bookings->from_location = $data['from_location'];
            $bookings->to_location = $data['to_location'];
            $bookings->date = $data['date'];
            $bookings->time_start = $data['time_start'];
            $bookings->time_end = $data['time_end'];
            $bookings->status = $data['status'];

            $bookings->save();
            redirect()->route('bookings.edit', ['id' => $data['id']])->with('success', lang('record_updated'));
        }
    }

    public function destroy(Request $request, Response $response, $id)
    {
        if ($id) {
            Bookings::where('id', $id)->delete();
            echo 1;
        }
        return $response;
    }

    public function deleteFile(Request $request, Response $response, $id)
    {
        if ($id) {
            Uploader::deleteFiles($id);
            echo 1;
        }
        return $response;
    }

    public function export(Request $request, Response $response, $type)
    {
        $bookings = Bookings::select('user_id', 'bus_id', 'seat_id', 'from_location', 'to_location', 'date', 'time_start', 'time_end', 'status')->get()->toArray();
        $filename = 'bookings_' . date('ymd') . '.' . $type;
        if ($type === 'pdf') {
            $message = render('admin/bookings/print.twig', compact('bookings'));
            Reporting::pdf($message, $filename);
        } else {
            $header = ['user_id', 'bus_id', 'seat_id', 'from_location', 'to_location', 'date', 'time_start', 'time_end', 'status'];
            Reporting::excel($bookings, $header, $filename);
        }
        return $response;
    }
}
