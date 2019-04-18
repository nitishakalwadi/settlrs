<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    public function __construct() {
    }

    public function signup(Request $request) {
        $data = $request->json()->all();
        $rules = [
            'name' => 'required|min:1|max:255',
            'email' => 'required|email|min:1|max:255',
            'password' => 'required|confirmed|min:8|max:255',
            'password_confirmation' => 'required',
        ];
        $validator = \Validator::make($data, $rules);
        
        if($validator->fails()) {
            $return['success'] = false;
            $return['errors'] = $validator->errors();
            return response()->json($return);
        }
        
        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);
        
        try {
            $user->save();
            $return['success'] = true;
            $return['data'] = $user->toArray();
        } catch(\Exception $e) {
            $return['success'] = false;
            $return['errors'] = $e->getMessage();
        }

        return response()->json($return);
    }

    public function login(Request $request) {
        $data = $request->json()->all();
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $validator = \Validator::make($data, $rules);
        
        if($validator->fails()) {
            $return['success'] = false;
            $return['errors'] = $validator->errors();
            return response()->json($return);
        }
        
        $client = new Client();
        $response = $client->request('POST', url('/oauth/token'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => env('OAUTH_PASSWORD_GRANT_CLIENT_ID'),
                'client_secret' => env('OAUTH_PASSWORD_GRANT_SECRET'),
                'username' => $data['email'],
                'password' => $data['password'],
                'scope' => '',
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);
        
        $return['success'] = true;
        $return['token_type'] = $result['token_type'];
        $return['expires_in'] = $result['expires_in'];
        $return['access_token'] = $result['access_token'];
        $return['refresh_token'] = $result['refresh_token'];
        return response()->json($return);
    }
}
