<?php
namespace App\Controllers\System;

use App\Auth\Auth;
use App\Controllers\Controller;
use App\Lib\Datatable;
use App\Lib\Reporting;
use App\Models\User;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\Role;
use Delight\Auth\UnknownIdException;
use Delight\Auth\UserAlreadyExistsException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class UsersController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function index(Request $request, Response $response)
    {
        return view($response,'system/users/index.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function datatable(Request $request, Response $response){
        $query = User::select('id','name','username','email','status','verified','registered','roles_mask','last_login');
        $primaryKey = 'id';
        $action='<div class="btn-group btn-group-sm" role="group">
                <a href="'.route('user.show',['id'=>'{rowId}']).'"  class="btn"><i class="bi bi-zoom-in font16"></i></a>
				<a href="'.route('user.password',['id'=>'{rowId}']).'"  class="btn"><i class="bi bi-file-lock font16"></i></a>
				<a href="'.route('user.edit',['id'=>'{rowId}']).'"  class="btn"><i class="bi bi-pencil text-dark font16" style="font-size:16px"></i></a>
				<a href="javascript:void(0)"  class="btn" onclick="deleteRecord(\'' . route('user.destroy', ['id' => '{rowId}']) . '\')"><i class="bi bi-trash text-danger font16"></i></a>
				</div>';
        json_encode(Datatable::make($query,$primaryKey,$action));
        return $response;
    }
    /**
     * @param Request $request
     * @param Response $response
     * @param $id
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function show(Request $request, Response $response, $id)
    {
        //$data = $request->getQueryParams();
        $user = User::where('id',$id)->first();
        return view($response,'system/users/show.twig', compact('user'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function create(Request $request, Response $response){
        $role = [
            'admin'=>Role::ADMIN,
            'supper_admin' => Role::SUPER_ADMIN,
            'user' => Role::SUBSCRIBER,
            'author' => Role::AUTHOR
        ];
        return view($response,'system/users/create.twig',compact('role'));
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request, Response $response)
    {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'name' => v::notEmpty(),
            'username' => v::noWhitespace()->notEmpty()->alnum(),
            'password' => v::notEmpty()->stringType()->length(8),
        ]);

        if ($validation->failed()) {
            redirect()->route('user.create');
        }
        $data = $request->getParsedBody();
        try {
            $userId = auth()->admin()->createUser($data['email'],$data['password'],$data['username']);
            auth()->admin()->addRoleForUserById($userId, $data['role']);
        }
        catch (InvalidEmailException $e) {
            redirect()->route('user.create')->with('error',lang('invalid_email'));
        }
        catch (InvalidPasswordException $e) {
            redirect()->route('user.create')->with('error',lang('invalid_password'));
        }
        catch (UserAlreadyExistsException $e) {
            redirect()->route('user.create')->with('error',lang('user_exist'));
        }
        if($userId) {
            User::where('id',$userId)->update([
                'name' => $data['name'],
            ]);
            redirect()->route('user.index')->with('success',lang('record_created'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $id
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function edit(Request $request, Response $response, $id)
    {
        $user = User::where('id',$id)->first();
        return view($response,'system/users/edit.twig', compact('user'));
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function update(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'name' => v::notEmpty(),
            'username' => v::noWhitespace()->notEmpty()->alnum(),
        ]);

        if ($validation->failed()) {
            redirect()->route('user.edit',['id'=>$data['id']]);
        }else{
            User::where('id',$data['id'])->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'status' => $data['status'],
                'verified' => $data['verified'],
                'roles_mask' => $data['role'],
            ]);
            redirect()->route('user.edit',['id'=>$data['id']])->with('success',lang('record_updated'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function destroy(Request $request, Response $response, $id)
    {
        if ($id) {
            auth()->admin()->deleteUserById($id);
            echo 1;
        }
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createPassword(Request $request, Response $response, $id){
        $user = User::where('id',$id)->first();
        return view($response,'system/users/password.twig',compact('user'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function password(Request $request, Response $response){
        $data = $request->getParsedBody();
        $validation = $this->validator->validate($request, [
            'password' => v::notEmpty()->stringType()->length(8),
        ]);
        if ($validation->failed()) {
            redirect()->route('user.password',['id'=>$data['id']]);
        }else{
            try {
                auth()->admin()->changePasswordForUserById($data['id'], $data['password']);
                redirect()->route('user.password',['id'=>$data['id']])->with('success',lang('password_changed'));
            }
            catch (UnknownIdException $e) {
                redirect()->route('user.password')->with('error',lang('invalid_id'));
            }
            catch (InvalidPasswordException $e) {
                redirect()->route('user.password')->with('error',lang('invalid_password'));
            }
        }

    }

    /**
     * Export to excel supports  (xlsx, xls, csv)
     * The @header array can be null to dissable header for your export
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function export(Request $request, Response $response, $type){
        $users = User::select('name','username','email','registered')->get()->toArray();
        $filename = 'users_'.date('ymd').'.'.$type;
        if($type ==='pdf'){
            $message = render('system/users/print.twig',compact('users'));
            Reporting::pdf($message, $filename);
        }else{
            $header = ['Name','Username','Email','Date'];
            Reporting::excel($users,$header,$filename);
        }
        return $response;
    }
}
