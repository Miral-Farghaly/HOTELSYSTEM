<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

 //use App\Models\User;
class UserController extends Controller
{


    public function login (Request $request )
    {
       $data = $request->all(); // now this works
        // return response()->json([
        //     'success' => true
            
        // ]);
        $email = $request->email;
        $password = $request->password;
        $role = $request->role;
        $user = User::where ('email', $email)->first();

        if($user && $password == $user->password && $role == $user->role){
              return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
        ]);
        }
        else 
             return response()->json([
            'success' => false,]);
    }

    public function register(Request $request){

        // $request->validate([
        //     'username' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|string|min:8',
        //     'phone' => 'required|string|max:20',
        //     'age' => 'required|integer|min:18',
            
        //   ]);

         $data = $request->all(); 
        //  return response()->json([
        //     'data'=> $data,
        //   'success' => true,
        //   'WORKIIIIN
        //   GGG'=> ' workingggg'
            
        //  ]);

    // Store values in variables
    $name = $data['username'];
    $email = $data['email'];
    $password = $data['password'];
    $phone = $data['phone'];
    $age = $data['age'];

    // Check if user already exists
    $exists = User::where('email', $email)->first();

    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'User already exists',
            'user' => null
        ]);
    }
    else {
    // Create user
    //   $user = User::create([
    //     'name' => $name,
    //     'email' => $email,
    //     'password' => $password,
    //     'phone' => $phone,
    //     'age' => $age,
    // ]);

    $user = new User();
        $user['name'] = $data['username'];
        $user['email'] = $data['email'];
        $user['password'] = $data['password'];
        $user['phone'] = $data['phone'];
        $user['age'] = $data['age'];
    $user->save();


      // Return success response
      return response()->json([
        'success' => true,
        'message' => 'User registered successfully',
        'user' => $user // $user
      ]);
    }
}

    }



