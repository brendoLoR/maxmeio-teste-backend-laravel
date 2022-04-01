<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use App\Models\UserGroups;

class UserControler extends Controller
{
    /**
     * redirect to login page
     */
    public function index()
    {
        return view('auth.login');
    }
    /**
     * delete a user
     */
    public function deleteUser(Request $req)
    {
        // echo 'aq';
        $data = $req->all();
        User::destroy($data['user_id']);
        return redirect('/registration');
    }
    /**
     * edit an user page
     * 
     * take the user data and send to manage user page directly in the form
     */
    public function editUser(Request $req)
    {
        $data = $req->all();

        $user = User::find($data['user_id']);
        return view('auth.registration', [
            'user_edit' => $user,
            'users' => User::all()
        ]);
    }
    /**
     * handle user edit from the manage user page form
     */
    public function editUserConfirm(Request $req)
    {
        $data = $req->all();

        $user = User::find($data['user_id']);
        $user->full_name = $data['full_name'];
        $user->email = $data['email'];
        if (!is_null($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->user_group_id = $data['user_group_id'];
        $user->save();
        return view('auth.registration', [
            'user_edit' => $user,
            'users' => User::all()
        ]);
    }
    /**
     * registration page
     * check if the current user is on admin group
     */
    public function registration(Request $req)
    {
        if (Auth::check()) {
        return view('auth.registration', [
            'user_edit' => null,
            'users' => User::all()
        ]);
        }
        // return redirect("/login")->withSuccess(("You don't have permition to access this content"));
    }
    /**
     * validate info and create a new user
     */
    public function registrate(Request $req)
    {
        $req->validate(
            [
                'full_name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'user_group_id' => 'required'
            ]
        );
        $data = $req->all();
        // var_dump($data);
        $new_user_id = $this->createUser($data);
        return redirect('/registration');
    }
    /**
     * create a new user with the given data
     */
    public function createUser($data)
    {
        return User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_group_id' => $data['user_group_id']
        ]);
    }
    /**
     * check if the given user is an Admin
     */
    public static function checkAdmin(User $user)
    {
        return UserGroups::find($user->user_group_id)->group_name == 'administrator';
    }
}
