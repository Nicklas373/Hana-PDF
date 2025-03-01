<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\AppHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\appLogModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function getToken()
    {
        $credentials = request(['email', 'password']);
        $uuid = AppHelper::Instance()->generateUniqueUuid(appLogModel::class, 'processId');
        $Muuid = AppHelper::Instance()->generateUniqueUuid(appLogModel::class, 'groupId');

        if (!$token = auth('api')->attempt($credentials)) {
            appLogModel::create([
                'processId' => $uuid,
                'groupId' => $Muuid,
                'errReason' => 'Auth breach detected, requested with '.json_encode($credentials),
                'errStatus' => 'Access unauthorized'
            ]);
            NotificationHelper::Instance()->sendErrGlobalNotify(
                'api/v1/auth/getToken',
                'Auth',
                'FAIL',
                $Muuid,
                'Access unauthorized',
                'Auth breach detected, requested with '.json_encode($credentials),
                false
            );
            return $this->returnDataMessage(
                401,
                'Access unauthorized',
                null,
                null,
                null,
                null,
                'Auth breach detected, requested with '.json_encode($credentials)
            );
        }

        $user = User::where('email', request('email'))->first();
        $token = auth('api')->login($user);

        return $this->respondWithToken($token);
    }

    public function initToken() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if($validator->fails()){
            return $this->returnTokenMessage(
                400,
                'Validation failed',
                $validator->messages()->first(),
                null,
                null,
                null
            );
        }

        if (User::exists()) {
            return $this->returnTokenMessage(
                401,
                'Auth registration failed',
                'There is another token has registered before',
                null,
                null,
                null
            );
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        return $this->returnTokenMessage(
            201,
            'User has been registered',
            $user,
            null,
            null,
            null
        );
    }

    public function refreshToken()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function revokeToken()
    {
        try {
            auth('api')->logout(true);
            auth('api')->invalidate(true);
        } catch (\Exception $e) {
            return $this->returnTokenMessage(
                400,
                'Failed to revoked token',
                null,
                null,
                null,
                $e->getMessage()
            );
        }

        return $this->returnTokenMessage(
            200,
            'Token revoked',
            null,
            null,
            null,
            null
        );
    }

    protected function respondWithToken($token)
    {
        return $this->returnTokenMessage(
            200,
            'Token generated',
            Auth::user(),
            'bearer',
            $token,
            auth()->factory()->getTTL() * 60
        );
    }
}
