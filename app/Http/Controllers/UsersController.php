<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = DB::select('SELECT id, name, email, is_admin
            FROM users');
        return view('admin.users',['users' => json_decode(json_encode($result),true)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('login');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        if ($request->filled(['name', 'email', 'password', 'retyped_password']) && $request->input('password') == $request->input('retyped_password') && filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
        {
            $result = DB::select('SELECT COUNT(id) AS user_count
                FROM users
                WHERE email = :email',
                ['email' => $request->input('email')]);
            if($result[0]->user_count == 0)
            {
                $result = DB::insert('INSERT INTO users (name, email, password, is_admin) VALUES (:name, :email, :password, 0)', [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => password_hash($request->input('password'), PASSWORD_DEFAULT)
                ]);
                session(['userId' => DB::getPdo()->lastInsertId(), 'userName' => $request->input('name'), 'userIsAdmin' => 0]);
                return redirect('/epoch');
            } else{
               return view('/login', ['infoMessage' => 'User already exists!', 'email' => $request->input('email')]);
            }
        } else
        {
            return view('/register', ['registerSuccesful' => false, 'infoMessage' => 'Wrong Data!']);
        }
    }

    public function showLogin()
    {
        return view('login');
    }

    public function showRegister()
    {
        return view('register');
    }

    /**
     * Login for users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if ($request->filled(['email', 'password']))
        {

            $result = DB::select('SELECT id, name, password, is_admin
                FROM users
                WHERE email = :email',
                ['email' => $request->input('email')]);

            if(password_verify($request->input('password'), $result[0]->password))
            {
                session(['userId' => $result[0]->id, 'userName' => $result[0]->name, 'userIsAdmin' => (bool) $result[0]->is_admin]);
                return redirect('/epoch');
            } else
            {
                return view('/login', ['infoMessage' => 'wrong data']);
            }
        }

    }

    /**
     * Logout for users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Session::flush();
        return redirect('/');
    }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show($id)
    // {
    //     if(parent::userIsAuthenticated() && parent::userIsAdmin())
    //     {
    //         $result = DB::select('SELECT id, name, email, is_admin
    //             FROM users
    //             WHERE id = :id',
    //             ['id' => $id]);
    //             print_r($result[0]->name);
    //     } else
    //     {
    //         print_r('Error: ???');
    //         //return redirect('/error');
    //     }
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $result = DB::select('SELECT id, name, email, password, is_admin
            FROM users
            WHERE id = :id',
            ['id' => $id]);

        return view('admin.editUser', ['id' => $result[0]->id, 'name' => $result[0]->name, 'email' => $result[0]->email, 'password' => $result[0]->password, 'is_admin' => $result[0]->is_admin]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->filled(['name', 'email', 'password', 'is_admin']) && filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
        {
            $result = DB::update('UPDATE users
                SET name = :name,
                    email = :email,
                    password = :password,
                    is_admin = :is_admin
                WHERE id = :id', [
                    'id' => $id,
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => password_hash($request->input('password'), PASSWORD_DEFAULT),
                    'is_admin' => $request->input('is_admin')
                ]);
            return view('/action', [$infoMessage = "Update successful"]);
        } else
        {
            return view('/action', [$infoMessage = "Update not successful"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = DB::delete('DELETE
            FROM users
            WHERE id = :id',
            ['id' => $id]);
       if($result)
       {
            return view('/action', [$infoMessage = "User successfully removed"]);
       } else
       {
            return view('/action', [$infoMessage = "User not removed"]);
       }

    }

}
