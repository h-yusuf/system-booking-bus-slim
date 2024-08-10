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
use App\Models\Tbusers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

	
class TbusersController extends Controller
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
        return view($response,'admin/tbusers/index.twig');
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
        $query = Tbusers::select('*');
        $primaryKey = 'id';
        $action='<div class="btn-group btn-group-sm" role="group">
				<a href="'.route('tbusers.show',['id'=>'{rowId}']).'"  class="btn"><i class="bi bi-zoom-in font16"></i></a>
				<a href="'.route('tbusers.edit',['id'=>'{rowId}']).'"  class="btn"><i class="bi bi-pencil text-dark font16"></i></a>
				<a href="javascript:void(0)"  class="btn" onclick="deleteRecord(\''.route('tbusers.destroy',['id'=>'{rowId}']).'\')"><i class="bi bi-trash text-danger font16"></i></a>
				</div>';
        json_encode(Datatable::make($query,$primaryKey,$action));
        return $response;
    }
	
    /**
     * This method select tbusers details
     * @param $id
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function show(Request $request, Response $response,$id){$tbusers = Tbusers::findOrFail($id);
          return view($response,'admin/tbusers/show.twig', compact('tbusers'));
    }

    /**
     * This method load tbusers form
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function create(Request $request, Response $response){
        
        return view($response,'admin/tbusers/create.twig');
    }

     /**
     * Insert into database 
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request, Response $response){
        /* validate tbusers data */
        $validation = $this->validator->validate($request, [
            'username' => v::notEmpty(),
              'password' => v::notEmpty(),
              'name' => v::notEmpty(),
              'role' => v::notEmpty(),
              'nik' => v::notEmpty(),
              'no_hp' => v::notEmpty(),
              'email' => v::notEmpty(),
              'jenis_kelamin' => v::notEmpty(),
              
        ]);
        if ($validation->failed()) {
            redirect()->route('tbusers.create');
        }
        else {
            /* get post data */
            $data = $request->getParsedBody();
            /* insert post data */
            $data= Tbusers::create([
                'username' => $data['username'],
              'password' => $data['password'],
              'name' => $data['name'],
              'role' => $data['role'],
              'nik' => $data['nik'],
              'no_hp' => $data['no_hp'],
              'email' => $data['email'],
              'jenis_kelamin' => $data['jenis_kelamin'],
              
            ]);
            
            redirect()->route('tbusers.index')->with('success',lang('record_created'));
        }
    }

    /**
     * Get form for tbusers to edit
     * @param $id
     * @param Request $request
     * @param Response $response
     */
    public function edit(Request $request, Response $response,$id){
         
        $tbusers = Tbusers::findOrFail($id);
        /* pass tbusers data to view and load list view */
        
        return view($response,'admin/tbusers/edit.twig', compact('tbusers' ));
    }

    /**
     * This method process tbusers edit form
     * @param $id
     * @param Request $request
     */
    public function update(Request $request, Response $response){
    
       $data = $request->getParsedBody();
       /* validate tbusers data */
        $validation = $this->validator->validate($request, [
            'username' => v::notEmpty(),
              'password' => v::notEmpty(),
              'name' => v::notEmpty(),
              'role' => v::notEmpty(),
              'nik' => v::notEmpty(),
              'no_hp' => v::notEmpty(),
              'email' => v::notEmpty(),
              'jenis_kelamin' => v::notEmpty(),
              
        ]);
        if ($validation->failed()) {
            redirect()->route('tbusers.edit',['id'=>$data['id']]);
        }
        else {
            $tbusers = Tbusers::findOrFail($data['id']);
            $tbusers->username = $data['username'];
              $tbusers->password = $data['password'];
              $tbusers->name = $data['name'];
              $tbusers->role = $data['role'];
              $tbusers->nik = $data['nik'];
              $tbusers->no_hp = $data['no_hp'];
              $tbusers->email = $data['email'];
              $tbusers->jenis_kelamin = $data['jenis_kelamin'];
              
            
            $tbusers->save();
            redirect()->route('tbusers.edit',['id'=>$data['id']])->with('success',lang('record_updated'));
        }
    }

    /**
    * This method delete record from database
    * @param Request $request
    * @param Response $response
    * @return Response
    */
    public function destroy(Request $request, Response $response, $id){
        if($id) {
            Tbusers::where('id', $id)->delete();
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
    public function deleteFile(Request $request, Response $response, $id){
        if($id) {
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
    public function export(Request $request, Response $response, $type){
        $tbusers = Tbusers::select('username','password','name','role','nik','no_hp','email','jenis_kelamin')->get()->toArray();
        $filename = 'tbusers_'.date('ymd').'.'.$type;
        if($type ==='pdf'){
            $message = render('admin/tbusers/print.twig',compact('tbusers'));
            Reporting::pdf($message, $filename);
        }else{
            $header = ['username','password','name','role','nik','no_hp','email','jenis_kelamin'];
            Reporting::excel($tbusers,$header,$filename);
        }
        return $response;
    }

}
	