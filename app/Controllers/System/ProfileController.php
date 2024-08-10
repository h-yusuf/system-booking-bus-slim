<?php
namespace App\Controllers\System;
/**
 * Auth
 *
 * @author    
 */
use App\Auth\Auth;
use App\Controllers\Controller;
use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class ProfileController extends Controller
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
        $user = User::where('id',Auth::id())->first();
        return view($response,'system/profile/index.twig',compact('user'));
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function update(Request $request, Response $response){
        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()->stringType(),
            'username' => v::notEmpty()->stringType()->length(5),
        ]);

        if ($validation->failed()) {
            redirect()->route('profile');
        }else {
            $data = $request->getParsedBody();
            User::where('id',Auth::id())->update([
                'name' => $data['name'],
                'username' => $data['username'],
            ]);
            redirect()->route('profile')->with('success',lang('update_successful'));
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createChangePassword(Request $request, Response $response){
        return view($response,'system/profile/changePassword.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function changePassword(Request $request, Response $response){
        $data = $request->getParsedBody();
        $validation = $this->validator->validate($request, [
            'old_password' => v::notEmpty(),
            'new_password' => v::notEmpty()->stringType()->length(8),
        ]);
        if ($validation->failed()) {
            redirect()->route('change.password');
        }else{
            Auth::changeCurrentPassword($data['old_password'], $data['new_password']);
            redirect()->route('change.password')->with('success',lang('password_changed'));
        }

    }
}
