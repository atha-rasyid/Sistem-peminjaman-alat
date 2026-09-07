<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Requesta\User\StoreUserRequest;
use App\Http\Requesta\User\UpdateUserRequest;
use App\Http\resource\UserResource;
use App\Models\user;
use Illuminate\Http\JsonResponse;
use Illuminate\support\facades\DB;
use Illuminate\support\Facades\Hash;
use Illuminate\support\Facades\storage;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
            return response()->json([
          'data' =>  UserResource::collection($users)
          'message' => 'daftar pengguna berhasil diambil. ',
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($request, $data) {
            $data['password'] = Hash::make($data['password']);
            if ($request->hasFile('foto_profil')) {
                $data['foto_profile'] = $request->file('foto_profil')->store('profiles', 'public');
        }
        return User::create($data);
        });
        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Pengguna berhasil ditambahkan.',
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = collect($request->validated());
        DB::transaction(function () use ($request, $user, $data) {
            if ($data->get('password')) {
                $data['password'] = Hash::make($data['password']);
            } else {
                $data->forget('password');
            }            

            if ($request->hasFile('foto_profil')) {
                if ($user->foto_profil) {
                    Storage::disk('public')->delete($user->foto_profil);
                }
                $data['foto_profil'] = $request->file('foto_profil')->store('profiles', 'public');
            }
            $user->update($data->toArray());
        });
        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Pengguna berhasil diperbarui.',
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        DB::transaction(function () use ($user) {
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $user->delete();
        });
        return response()->json([
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}
