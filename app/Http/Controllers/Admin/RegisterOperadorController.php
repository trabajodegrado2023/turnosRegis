<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUser;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class RegisterOperadorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modulos = Modulo::whereNull('user_id')->where('estadomodulo',6)->get();
        return view('SuperAdmin.RegistroOperadores', compact('modulos'));
    }

    
public function inhabilitado(){

    $operadores = User::where('idestado',7)->get();

    $modulos = Modulo::where('estadomodulo',6)->where('user_id',null)->get();

    return view('SuperAdmin.Habilitar' ,compact('operadores','modulos'));
}



public function habilitar(Request $request){

    $id = $request->user;
    $idmodulo =$request->modulo;
    $operadores = User::where('id',$id )->first();
    $operadores->idestado = 6;
    $operadores->save();
  
   
    $modulos = Modulo::where('id',$idmodulo)->first();
    $modulos->estadomodulo=6;
    $modulos->user_id=$id;
    $modulos->save();

    Session::flash('success', 'El Operador ha Sido Habilitado');
    return redirect()->back();
}
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('SuperAdmin.RegistroOperadores');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreUser $request)
    {
        $moduloId = $request->modulo;

        $password = $request->password;
        $users = User::all();

        foreach ($users as $user) {
            if (Hash::check($password, $user->password)) {
                return redirect()->back()->withInput()->withErrors(['password' => 'La contraseña corresponde a otro usuario']);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'idestado' => 6
        ]);

        // Asignar un módulo al usuario
        $modulo = Modulo::find($moduloId);
        if ($modulo) {
            $modulo->user_id = $user->id;
            $modulo->save();
            $user->assignRole('Operador');
        }

        Session::flash('success', 'Registro exitoso');
        return redirect()->back();
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

        $usuario = Modulo::where('user_id', $user->id)->first();
        $user->modulo = $usuario->nameModulo;

       // return $usuario;
       return view('SuperAdmin.EditarOperadores', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
{
    // Obtener los correos electrónicos diferentes al correo del usuario actual
    $otherEmails = User::where('id', '!=', $user->id)
        ->pluck('email')
        ->toArray();

    // Verificar si el correo del nuevo registro coincide con alguno de los correos obtenidos
    if (in_array($request->email, $otherEmails)) {
        return redirect()->back()->withInput()->withErrors(['email' => 'El corro le pertenece a otro Usuario']);
    }

    // Verificar si la contraseña del nuevo registro coincide con alguna de las contraseñas obtenidas
    $otherPasswords = User::where('id', '!=', $user->id)
        ->pluck('password')
        ->toArray();

    foreach ($otherPasswords as $password) {
        if (Hash::check($request->password, $password)) {
            return redirect()->back()->withInput()->withErrors(['password' => 'Contraseña le pertenece a Otro Usuario']);
        }
    }

    // Resto del código...

    $validator = Validator::make($request->all(), [
        'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'],
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withInput()->withErrors($validator);
    }

    if ($request->password == $request->password_confirmation) {
        // Actualizar la contraseña encriptada si se proporcionó una nueva contraseña
        
        $user->name = $request->name;
        $user->email = $request->email;
        // Verificar si se proporcionó una nueva contraseña
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
    
        // Guardar los cambios en la base de datos

        $user->save();
        Session::flash('success', 'Actualización correcta');
        return redirect()->back();
    } else {
        return redirect()->back()->withInput()->withErrors(['password' => 'Las Contraseñas no Coinciden']);
    }
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function eliminando(User $user)
    {
        
        return view('SuperAdmin.EliminarOperador',compact('user'));
    }


    public function destroy(User $user)
    {
        
        $user->modulos()->update(['user_id' => null]);
        $user->idestado = 7;
        $user->save();
       
        return redirect()->route('admin.Gestion');
    }
}
