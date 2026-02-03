<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Services\AddressService;
use App\Models\FilePath;

class MeController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        if ($user->is_company === null) {
            return response()->json([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'isCompany' => $user->is_company,
                ],
            ]);
        }
        
        if ($user->is_company === true) {
            $user->load('companyProfile.address');
            if($user->companyProfile){

                $user->companyProfile->address->makeHidden(['id', 'company_profile_id']);
            }
            return response()->json([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'isCompany' => $user->is_company,
                    'companyProfile' => $user->companyProfile,
                ],
            ]);
        }
        
        $user->load('userProfile.address', 'userProfile.identityDocument', 'userProfile.resumeFile');
        $profile = $user->userProfile;
        $address = $profile->address;
        
        return response()->json([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'isCompany' => $user->is_company,
                'userProfile' => [
                    'tel' => $profile->tel,
                    'lastName' => $profile->last_name,
                    'lastNameKana' => $profile->last_name_kana,
                    'firstName' => $profile->first_name,
                    'firstNameKana' => $profile->first_name_kana,
                    'birthday' => $profile->birthday,
                    'gender' => $profile->gender,
                    'biography' => $profile->biography,
                    'address' => [
                        'postalCode' => $address->postal_code,
                        'prefecture' => $address->prefecture,
                        'city' => $address->city,
                        'town' => $address->town,
                        'addressLine' => $address->address_line,
                        'buildingName' => $address->building_name,
                        'latitude' => $address->latitude,
                        'longitude' => $address->longitude,
                        'lineName' => $address->line_name,
                        'nearestStation' => $address->nearest_station,
                        'walkingMinutes' => $address->walking_minutes,
                    ],
                    'identityDocument' => $profile->identityDocument ? [
                        'path' => url('storage/' . $profile->identityDocument->path),
                        'extension' => $profile->identityDocument->extension,
                    ] : null,
                    'resumeFile' => $profile->resumeFile ? [
                        'path' => url('storage/' . $profile->resumeFile->path),
                        'extension' => $profile->resumeFile->extension,
                    ] : null,
                ],
            ],
        ]);
    }

    public function storeUserProfile(StoreUserProfileRequest $request)
    {
        $user = $request->user();
        
        $address = AddressService::create(
            postalCode: $request->postalCode,
            prefecture: $request->prefecture,
            addressLine: $request->address
        );
        
        // 身分証明書ファイルを保存
        $identityDoc = $request->file('identityDocument');
        $identityPath = $identityDoc->store('identity_documents', 'public');
        $identityFile = FilePath::create([
            'path' => $identityPath,
            'extension' => $identityDoc->getClientOriginalExtension(),
        ]);
        
        // 履歴書ファイルを保存
        $resumeDoc = $request->file('resumeFile');
        $resumePath = $resumeDoc->store('resumes', 'public');
        $resumeFile = FilePath::create([
            'path' => $resumePath,
            'extension' => $resumeDoc->getClientOriginalExtension(),
        ]);
        
        $profile = $user->userProfile()->create([
            'address_id' => $address->id,
            'identity_document_id' => $identityFile->id,
            'resume_file_id' => $resumeFile->id,
            'tel' => $request->tel,
            'last_name' => $request->lastName,
            'last_name_kana' => $request->lastNameKana,
            'first_name' => $request->firstName,
            'first_name_kana' => $request->firstNameKana,
            'birthday' => $request->birthDate,
            'gender' => $request->gender,
            'biography' => $request->biography,
        ]);
        
        $user->update(['is_company' => false]);
        
        $user->load('userProfile');
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function updateUserProfile(UpdateUserProfileRequest $request)
    {
        $user = $request->user();
        $profile = $user->userProfile;
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'messages' => ['プロフィールが存在しません。'],
            ], 404);
        }
        
        \DB::beginTransaction();
        try {
            // 住所が送信された場合のみ更新
            if ($request->prefecture && $request->address) {
                $fullAddress = $request->prefecture . $request->address;
                $geocode = AddressService::getGeocode($fullAddress);
                $parsed = AddressService::parse($fullAddress);
                $stationInfo = AddressService::getNearestStation($geocode['latitude'], $geocode['longitude']);
                
                $profile->address->update([
                    'postal_code' => $request->postalCode,
                    'prefecture' => $request->prefecture,
                    'city' => $parsed['city'],
                    'town' => $parsed['town'],
                    'address_line' => $parsed['address_line'],
                    'building_name' => $parsed['building_name'],
                    'latitude' => $geocode['latitude'],
                    'longitude' => $geocode['longitude'],
                    'line_name' => $stationInfo['line_name'],
                    'nearest_station' => $stationInfo['nearest_station'],
                    'walking_minutes' => $stationInfo['walking_minutes'],
                ]);
            }
            
            // 身分証明書ファイルが更新された場合
            if ($request->hasFile('identityDocument')) {
                // 古いファイルを削除
                if ($profile->identityDocument) {
                    \Storage::disk('public')->delete($profile->identityDocument->path);
                    $oldIdentityId = $profile->identity_document_id;
                }
                
                // 新しいファイルを保存
                $identityDoc = $request->file('identityDocument');
                $identityPath = $identityDoc->store('identity_documents', 'public');
                $identityFile = FilePath::create([
                    'path' => $identityPath,
                    'extension' => $identityDoc->getClientOriginalExtension(),
                ]);
                
                $profile->update(['identity_document_id' => $identityFile->id]);
                
                // 古いレコードを削除
                if (isset($oldIdentityId)) {
                    FilePath::find($oldIdentityId)?->delete();
                }
            }
            
            // 履歴書ファイルが更新された場合
            if ($request->hasFile('resumeFile')) {
                // 古いファイルを削除
                if ($profile->resumeFile) {
                    \Storage::disk('public')->delete($profile->resumeFile->path);
                    $oldResumeId = $profile->resume_file_id;
                }
                
                // 新しいファイルを保存
                $resumeDoc = $request->file('resumeFile');
                $resumePath = $resumeDoc->store('resumes', 'public');
                $resumeFile = FilePath::create([
                    'path' => $resumePath,
                    'extension' => $resumeDoc->getClientOriginalExtension(),
                ]);
                
                $profile->update(['resume_file_id' => $resumeFile->id]);
                
                // 古いレコードを削除
                if (isset($oldResumeId)) {
                    FilePath::find($oldResumeId)?->delete();
                }
            }
            
            // プロフィール情報を更新
            $profile->update([
                'tel' => $request->tel,
                'last_name' => $request->lastName,
                'last_name_kana' => $request->lastNameKana,
                'first_name' => $request->firstName,
                'first_name_kana' => $request->firstNameKana,
                'birthday' => $request->birthDate,
                'gender' => $request->gender,
                'biography' => $request->biography,
            ]);
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'messages' => ['更新に失敗しました。'],
            ], 500);
        }
    }

    public function storeCompanyProfile(Request $request)
    {
        
    }

    public function updateCompanyProfile(Request $request)
    {
        //
    }
}
