<?php

namespace App\Services;

use App\Repository\User\IUserRepository;
use App\Repository\Goal\IGoalRepository;
use App\Validator\UserValidator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Exception;

class UserService
{
    private $_userRepository;
    private $_goalRepository;
    private $_userValidator;
    private $_timezone;

    public function __construct(IUserRepository $userRepository, 
                                IGoalRepository $goalRepository, 
                                UserValidator $userValidator)
    {
        $this->_userRepository = $userRepository;
        $this->_goalRepository = $goalRepository;
        $this->_userValidator = $userValidator;
        $this->_timezone = date_default_timezone_set('America/Sao_Paulo');
    }

    public function register($user)
    {
        $validator = $this->_userValidator->register($user);
        $createUser = $this->_userRepository->create($user);
    }

    public function update($user)
    {
        $id = Auth::user()->id;
        $validator = $this->_userValidator->update($user);
        $update = $this->_userRepository->update($user, $id);
        return $update;
    }

    public function getDailyMealGoals($date)
    {
        $id = Auth::user()->id;
        $user = $this->_userRepository->find($id);

        //Tipos de refeição.
        $breakfast = $this->_goalRepository->findByDateAndMealType($date, 1);
        $lunch = $this->_goalRepository->findByDateAndMealType($date, 2);
        $snack = $this->_goalRepository->findByDateAndMealType($date, 3);
        $dinner = $this->_goalRepository->findByDateAndMealType($date, 4);
        $preWorkout = $this->_goalRepository->findByDateAndMealType($date, 5);
        $postWorkout = $this->_goalRepository->findByDateAndMealType($date, 6);

        //Metas estabelecidas pelo usuário.
        if ($user) {
            $goalCalories = $user['daily_calories'];
            $goalCarbohydrate = $user['daily_carbohydrate'];
            $goalProtein = $user['daily_protein'];
            $goalTotalFat = $user['daily_fat'];
        }

        //Resultados do dia.
        $caloriesOfTheDay = array_sum([
            $breakfast->sum('calories'),
            $lunch->sum('calories'),
            $snack->sum('calories'),
            $dinner->sum('calories'),
            $preWorkout->sum('calories'),
            $postWorkout->sum('calories')
        ]);

        $carbohydratesOfTheDay = array_sum([
            $breakfast->sum('carbohydrate'),
            $lunch->sum('carbohydrate'),
            $snack->sum('carbohydrate'),
            $dinner->sum('carbohydrate'),
            $preWorkout->sum('carbohydrate'),
            $postWorkout->sum('carbohydrate')
        ]);

        $proteinOfTheDay = array_sum([
            $breakfast->sum('protein'),
            $lunch->sum('protein'),
            $snack->sum('protein'),
            $dinner->sum('protein'),
            $preWorkout->sum('protein'),
            $postWorkout->sum('protein')
        ]);

        $totalFatOfTheDay = array_sum([
            $breakfast->sum('total_fat'),
            $lunch->sum('total_fat'),
            $snack->sum('total_fat'),
            $dinner->sum('total_fat'),
            $preWorkout->sum('total_fat'),
            $postWorkout->sum('total_fat')
        ]);

        return [
            'user' => $user,
            'breakfast' => $breakfast,
            'lunch' => $lunch,
            'snack' => $snack,
            'dinner' => $dinner,
            'preWorkout' => $preWorkout,
            'postWorkout' => $postWorkout,
            'goalCalories' => $goalCalories,
            'goalCarbohydrate' => $goalCarbohydrate,
            'goalProtein' => $goalProtein,
            'goalTotalFat' => $goalTotalFat,
            'caloriesOfTheDay' => $caloriesOfTheDay,
            'carbohydratesOfTheDay' => $carbohydratesOfTheDay,
            'proteinOfTheDay' => $proteinOfTheDay,
            'totalFatOfTheDay' => $totalFatOfTheDay
        ];
    }

    public function resetPassword($user){
        $status = Password::reset(
            $user,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        return $status;
    }

    public function followUser($nickname){
        try{
            $authUser = Auth::user();
            $userNicknameToFollow = $this->_userRepository->searchUserByNickname($nickname);
            if(Auth::user()->following()->where('user_id', $userNicknameToFollow->id)->exists()){
                throw new Exception("Você já está seguindo $nickname.", 409); // 409 - conflito
            }
            $this->_userRepository->followUser($authUser, $userNicknameToFollow->id);
        }catch(Exception $e){
            return back()->with('status', $e->getMessage());
        }
    }

    public function unfollowUser($nickname){
        try{
            $authUser = Auth::user();
            $userNicknameToUnfollow = $this->_userRepository->searchUserByNickname($nickname);
            if($userNicknameToUnfollow){
                $this->_userRepository->unfollowUser($authUser, $userNicknameToUnfollow->id);
            }
            throw new Exception("Você não está mais seguindo $nickname.", 409);
        }catch(Exception $e){
            return back()->with('status', $e->getMessage());
        }
    }
}
