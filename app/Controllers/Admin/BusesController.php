<?php


namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Auth\Auth;
use App\Lib\Datatable;
use App\Lib\Uploader;
use App\Lib\Reporting;
use App\Models\Buses;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;


class BusesController extends Controller
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
        return view($response, 'admin/buses/index.twig');
    }

    public function indexApi(Request $request, Response $response)
    {
        $buses = Buses::select('*')->get();
        $responseData = ['status' => 'success', 'message' => 'Data retrieved successfully', 'data' => $buses];
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
        $query = Buses::select('*');
        $primaryKey = 'id';
        $action = '<div class="btn-group btn-group-sm" role="group">
				<a href="' . route('buses.show', ['id' => '{rowId}']) . '"  class="btn"><i class="bi bi-zoom-in font16"></i></a>
				<a href="' . route('buses.edit', ['id' => '{rowId}']) . '"  class="btn"><i class="bi bi-pencil text-dark font16"></i></a>
				<a href="javascript:void(0)"  class="btn" onclick="deleteRecord(\'' . route('buses.destroy', ['id' => '{rowId}']) . '\')"><i class="bi bi-trash text-danger font16"></i></a>
				</div>';
        json_encode(Datatable::make($query, $primaryKey, $action));
        return $response;
    }

    /**
     * This method select buses details
     * @param $id
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function show(Request $request, Response $response, $id)
    {
        $buses = Buses::findOrFail($id);
        return view($response, 'admin/buses/show.twig', compact('buses'));
    }

    public function showApi(Request $request, Response $response, $id)
    {
        $buses = Buses::findOrFail($id);
        $responseData = ['status' => 'success', 'message' => 'Data retrieved successfully', 'data' => $buses];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    /**
     * This method load buses form
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function create(Request $request, Response $response)
    {

        return view($response, 'admin/buses/create.twig');
    }

    /**
     * Insert into database 
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request, Response $response)
    {
        /* validate buses data */
        $validation = $this->validator->validate($request, [
            'kode_bus' => v::notEmpty(),
            'plat_bus' => v::notEmpty(),
            'jumlah_kursi' => v::notEmpty(),

        ]);
        if ($validation->failed()) {
            redirect()->route('buses.create');
        } else {
            /* get post data */
            $data = $request->getParsedBody();
            /* insert post data */
            $data = Buses::create([
                'kode_bus' => $data['kode_bus'],
                'plat_bus' => $data['plat_bus'],
                'jumlah_kursi' => $data['jumlah_kursi'],

            ]);

            redirect()->route('buses.index')->with('success', lang('record_created'));
        }
    }

    public function createApi(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $buses = Buses::create($data);
        $responseData = ['status' => 'success', 'message' => 'Data created successfully', 'data' => $buses];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function storeApi(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $buses = Buses::create($data);
        $responseData = ['status' => 'success', 'message' => 'Data created successfully', 'data' => $buses];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get form for buses to edit
     * @param $id
     * @param Request $request
     * @param Response $response
     */
    public function edit(Request $request, Response $response, $id)
    {

        $buses = Buses::findOrFail($id);
        /* pass buses data to view and load list view */

        return view($response, 'admin/buses/edit.twig', compact('buses'));
    }

    /**
     * This method process buses edit form
     * @param $id
     * @param Request $request
     */
    public function update(Request $request, Response $response)
    {

        $data = $request->getParsedBody();
        /* validate buses data */
        $validation = $this->validator->validate($request, [
            'kode_bus' => v::notEmpty(),
            'plat_bus' => v::notEmpty(),
            'jumlah_kursi' => v::notEmpty(),

        ]);
        if ($validation->failed()) {
            redirect()->route('buses.edit', ['id' => $data['id']]);
        } else {
            $buses = Buses::findOrFail($data['id']);
            $buses->kode_bus = $data['kode_bus'];
            $buses->plat_bus = $data['plat_bus'];
            $buses->jumlah_kursi = $data['jumlah_kursi'];


            $buses->save();
            redirect()->route('buses.edit', ['id' => $data['id']])->with('success', lang('record_updated'));
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
            Buses::where('id', $id)->delete();
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
        $buses = Buses::select('kode_bus', 'plat_bus', 'jumlah_kursi')->get()->toArray();
        $filename = 'buses_' . date('ymd') . '.' . $type;
        if ($type === 'pdf') {
            $message = render('admin/buses/print.twig', compact('buses'));
            Reporting::pdf($message, $filename);
        } else {
            $header = ['kode_bus', 'plat_bus', 'jumlah_kursi'];
            Reporting::excel($buses, $header, $filename);
        }
        return $response;
    }
}
