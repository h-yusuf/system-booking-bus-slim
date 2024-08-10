<?php

namespace App\Controllers\Auth;

use App\Auth\Auth;
use App\Controllers\Controller;
use App\Models\User;
use Delight\Auth\Role;
use Respect\Validation\Validator as v;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * AuthController
 *
 * @author    Hezekiah O. <support@hezecom.com>
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createRegister(Request $request, Response $response)
    {
        return view($response, 'auth/register.twig');
    }

    public function detailUserApi(Request $request, Response $response)
    {
        $userId = Auth::user()['id'];
        $user = User::find($userId);

        if ($user) {
            $responseData = [
                'status' => 'success',
                'data' => $user,
            ];
        } else {
            $responseData = [
                'status' => 'error',
                'message' => 'User not found',
            ];
        }

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Delight\Auth\AuthError
     */
    public function register(Request $request, Response $response)
    {

        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'username' => v::noWhitespace()->notEmpty()->alnum(),
            'password' => v::notEmpty()->stringType()->length(8),
        ]);

        if ($validation->failed()) {
            redirect()->route('register');
        }
        $data = $request->getParsedBody();
        $auth = Auth::create($data['email'], $data['password'], $data['username']);
        if ($auth) {
            //check for first entry
            if (User::count() === 1) {
                redirect()->route('login');
            } else {
                User::where('id', $auth)->update([
                    'roles_mask' => Role::SUBSCRIBER,
                    'verified' => 1,
                ]);
                $msg = '<a href="' . route('verify.email.resend', [], ['email' => $data['email']]) . '">Resend email</a>';
                //flash('success', 'We have send you a verification link to ' . $data['email'] . ' <br>' . $msg);
                redirect()->route('login')->with('success', 'We have send you a verification link to ' . $data['email'] . ' <br>' . $msg);
            }
        }
    }

    public function registerApi(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        // var_dump($data);
        // die;
        $auth = Auth::createApi($data['email'], $data['password'], $data['username']);
        // var_dump($auth);
        // die;
        if ($auth) {
            //check for first entry
            if (User::count() === 1) {
                $responseData = ['status' => 'success', 'message' => 'Registration successful'];
            } else {
                User::where('id', $auth)->update([
                    'roles_mask' => Role::SUBSCRIBER,
                    'nik' => $data['nik'],
                    'no_hp' => $data['no_hp'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'verified' => 1,
                ]);
                $msg = '<a href="' . route('verify.email.resend', [], ['email' => $data['email']]) . '">Resend email</a>';
                $responseData = ['status' => 'success', 'message' => 'We have send you a verification link to ' . $data['email'] . ' <br>' . $msg];
            }
        } else {
            $responseData = ['status' => 'error', 'message' => 'Registration failed'];
        }

        // var_dump($responseData);
        // die;
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function verifyEmailResend(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        Auth::ResendVerification($data['email']);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function verifyEmail(Request $request, Response $response)
    {
        //confirm email
        $data = $request->getQueryParams();
        Auth::verifyEmail($data['selector'], $data['token']);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createLogin(Request $request, Response $response)
    {
        return view($response, 'auth/login.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AttemptCancelledException
     * @throws \Delight\Auth\AuthError
     */
    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        if (isset($data['remember'])) {
            $remember = $data['remember'];
        } else {
            $remember = null;
        }
        $login = Auth::login($data['email'], $data['password'], $remember);
        if ($login === true)
            if (Auth::hasRole('super_admin')) {
                redirect()->route('home');
            }
        redirect()->route('profile');
    }

    public function loginApi(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $login = Auth::login($data['email'], $data['password']);
        
        if ($login === true) {
            $role = '';
    
            if (Auth::hasRole('super_admin')) {
                $role = 'super_admin';
            } elseif (Auth::hasRole('admin')) {
                $role = 'admin';
            } elseif (Auth::hasRole('user')) {
                $role = 'user';
            }
    
            $responseData = [
                'status' => 'success',
                'message' => 'Login successful',
                'role' => $role
            ];
        } else {
            $responseData = [
                'status' => 'error',
                'message' => 'Login failed'
            ];
        }
    
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    public function roleApi(Request $request, Response $response)
    {
        // var_dump(Auth::hasAnyRole());
        // die;
        // $data = $request->getParsedBody();
        // $login = Auth::login($data['email'], $data['password']);
        $arr = array('super_admin', 'admin', 'user');
        // var_dump($arr);
        // die;
        // if ($login === true) {
        $hasRole = Auth::hasRole('user');
        // $hasRole = Auth::hasAnyRole(['super_admin', 'admin', 'user']);
        // var_dump($hasRole);
        // die;
        $responseData = ['status' => 'success', 'message' => $hasRole];
        // } else {
        //     $responseData = ['status' => 'error', 'message' => 'Role is invalid'];
        // }

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @throws \Delight\Auth\AuthError
     */
    public function logout()
    {
        Auth::logout();
        redirect()->route('login');
    }

    public function logoutApi(Request $request, Response $response)
    {
        Auth::logout();
        $responseData = ['status' => 'success', 'message' => 'Logout successful'];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}