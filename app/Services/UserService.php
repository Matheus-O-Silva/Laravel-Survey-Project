<?php
declare(strict_types=1);

namespace App\Services;

use App\Repository\Eloquent\UserRepository;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Balance;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserService
{

    private $userRepository;

    /**
     * construct
     *
     * @param App\Repository\Eloquent\UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        return $this->userRepository = $userRepository;
    }

    /**
     * Register new User
     *
     * @throws \Exception
     * @return Array
     */
    public function store(RegisterRequest $registerRequest)
    {
        try{
            DB::beginTransaction();

            $user = $this->userRepository->create([
                'name'           => $registerRequest->name,
                'email'          => $registerRequest->email,
                'device_number'  => $registerRequest->device_number,
                'password'       => bcrypt($registerRequest->password)
            ]);
            DB::commit();

            $user->token = $user->createToken('main')->plainTextToken;

        }catch(Exception $e){
            Log::error($e->getMessage());
            DB::rollback();
        }

        return $user;
    }

    /**
     * Authenticate a user
     *
     * @throws \Exception
     * @return object
     */
    public function authenticate(LoginRequest $loginRequest) : object
    {
        $user = User::where('email', $loginRequest->email)->first();

        $remember = $loginRequest->remeber ?? false;

        if (! $user || ! Hash::check($loginRequest->password, $user->password) || $remember) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if($loginRequest->has('logout_others_devices')){
            $user->tokens()->delete();
        }

        $user->token = $user->createToken('main')->plainTextToken;

        return $user;
    }

    /**
    * Invalidate the access token of the authenticated user and delete all its tokens from the database.
    *
    * @throws \Exception
    * @return void
    */
    public function logout(): void
    {
        auth()->user()->tokens()->delete();
    }
}
