<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Validator\UserValidator;
use App\Repository\User\IUserRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Mail\MailProvider;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Storage;


class UserManagementController extends Controller
{
    private $_request;
    private $_userService;
    private $_userValidator;
    private $_userRepository;
    private $_mailProvider;

    public function __construct(
        Request $request, 
        UserService $userService,  
        UserValidator $userValidator, 
        IUserRepository $userRepository,
        MailProvider $mailProvider)
    {
        $this->_request = $request;
        $this->_userService = $userService;
        $this->_userValidator = $userValidator;
        $this->_userRepository = $userRepository;
        $this->_mailProvider = $mailProvider;
    }

    public function updateProfile()
    {
        try{
            $this->middleware('auth');
            $id = Auth::user()->id;
            $user = $this->_request->only([
                'gender',
                'age',
                'weight',
                'stature',
                'activity_rate_factor',
                'objective',
                'type_of_diet',
                'imc',
                'water',
                'basal_metabolic_rate',
                'daily_calories',
                'daily_protein',
                'daily_carbohydrate',
                'daily_fat',
                'daily_protein_kcal',
                'daily_carbohydrate_kcal',
                'daily_fat_kcal'
            ]);
            $validator = $this->_userValidator->update($user);
            if($validator != null){
                throw new Exception(json_encode($validator->messages()), 422); // Unprocessable Entity.
            }
            $update = $this->_userRepository->update($user, $id);
            return redirect()->route('profile');
        }catch(Exception $e){
            $errors = json_decode($e->getMessage(), true);
            return redirect()->back()->withErrors($errors);
        }
    }

    public function register(){
        try{
            $user = $this->_request->only([
                'name',
                'nickname',
                'email',
                'password',
                'password_confirmation',
                'confirm_terms',
                'gender',
                'age',
                'weight',
                'stature',
                'activity_rate_factor',
                'objective',
                'type_of_diet',
                'imc',
                'water',
                'basal_metabolic_rate',
                'daily_calories',
                'daily_protein',
                'daily_carbohydrate',
                'daily_fat',
                'daily_protein_kcal',
                'daily_carbohydrate_kcal',
                'daily_fat_kcal'
            ]);
            $validator = $this->_userValidator->register($user);
            if($validator != null){
                throw new Exception(json_encode($validator->messages()), 422); // Unprocessable Entity.
            }
            $register = $this->_userRepository->create($user);
            Auth::login($register);
            return redirect()->route('welcome');
        }catch(Exception $e){
            $errors =json_decode($e->getMessage(), true);
            return redirect()->back()->withErrors($errors);
        }
    }

    public function login(){
        $credentials = $this->_request->only(['email', 'password']);
        if(Auth::attempt($credentials)) {
            if ($this->_request->hasSession()) {
                $this->_request->session()->put('auth.password_confirmed_at', time());
            }
            return redirect()->route('welcome');
        }
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(){
        $this->middleware('auth');
        Auth::logout();
        $this->_request->session()->invalidate();
        $this->_request->session()->regenerateToken();
        return redirect('/');
    }

    public function sendEmailToRecoverPassword(){ // Precisa verificar se é uma conta google.
        try{
            $this->_request->validate(['email' => 'required|email']);
            $user = $this->_userRepository->findUserByEmail($this->_request->email);
            if($user['google_id']){
                throw new Exception('Você é um usuário do Google. Use sua conta do Gmail para fazer login.');
            }
            if($user && $user['google_id'] === null){
                $this->_mailProvider->recoverPassword($user);
                $messages = $this->_request->session()->get('errors')->all();
                return back()->with(['status' => $messages[0]]);
            } 
            throw new Exception('Erro no envio do e-mail.');
        }catch(Exception $e){
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function resetPassword(){
        $user = $this->_request->only(['token', 'email', 'password', 'password_confirmation']);
        $validate = $this->_userValidator->resetPassword($user);
        $status = $this->_userService->resetPassword($user);
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }

    public function publicProfileUpdate(){ 
        try{
            $this->middleware('auth');
            $user = Auth::user();
            $data = $this->_request->only(['name', 'bio']);
            if($data['name'] != $user->name || $data['bio'] != $user->bio){
                $update = $this->_userRepository->publicSettingsUpdate($data, $user->id);
            }
            return redirect()->route('user.settings');
        }catch(Exception $e){
            return back()->withErrors(['profile_image' => $e->getMessage()]);
        }
    }

    public function profilePictureUpdate(){
        try{
            $this->middleware('auth');
            $user = Auth::user();
            if($this->_request->hasFile('profile_image')){
                $file = $this->_request->file('profile_image');
                $size = $file->getSize(); 
                $type = $file->getMimeType(); 
                if($type == 'image/jpeg' || $type == 'image/jpg' || $type == 'image/png'){
                    if($user->profile_image){
                        $deletePreviousImage = Storage::delete($user->profile_image);
                    }
                    $path = $file->store('profile');
                    $user = ['id' => $user->id, 'profile_image' => $path];
                    $updateProfileImage = $this->_userRepository->profileImageUpdate($user);
                }else{
                    throw new Exception('Apenas imagens nos formatos PNG, JPG e JPEG podem ser enviadas.', 415);
                }  
                return redirect()->route('user.settings');
            }
            throw new Exception('Não existe nada para ser atualizado.', 415);
        }catch(Exception $e){
            return back()->withErrors(['profile_image' => $e->getMessage()]);
        }
    }
    
    public function searchUsers(){
        try{
            $idAuthUser = Auth::user()->id;
            $name = $this->_request->input('name');
            $users = $this->_userRepository->searchUser($name, $idAuthUser);
            if(empty($users->items())){
                throw new Exception('Não existe nada para ser atualizado.', 404);
            }
            return view('community.index', compact('users'));
        }catch(Exception $e){
            return back()->withErrors(['profile_image' => $e->getMessage()]);
        }
    }

    public function followUser($nickname){
        $userId = Auth::user()->id;
        $userService = $this->_userService->followUser($nickname);
        return back()->with('status', "Agora você está seguindo $nickname.");
    }

    public function unfollowUser($nickname){
        $userId = Auth::user()->id;
        $userService = $this->_userService->unfollowUser($nickname);
        return back()->with('status', "Você parou de seguir $nickname.");
    }
}
