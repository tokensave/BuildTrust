<?php

declare(strict_types=1);
declare(ticks=1000);

namespace App\Services\User;

use App\DTO\UserSetting\UserProfileData;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UserProfileService
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(User $user, UserProfileData $data): void
    {
        if ($data->avatar) {
            $user->clearMediaCollection('avatar');
            $user
                ->addMedia($data->avatar)
                ->usingFileName(uniqid('', true) . '.' . $data->avatar->extension())
                ->toMediaCollection('avatar');
        }

        $user->fill([
            'username' => $data->username ?? $user->username,
            'email'    => $data->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->isDirector() && $user->company) {
            $user->company->update([
                'name'     => $data->company_name ?? $user->company->name,
                'inn'      => $data->inn          ?? $user->company->inn,
                'phone'    => $data->phone        ?? $user->company->phone,
                'city'     => $data->city         ?? $user->company->city,
                'address'  => $data->address      ?? $user->company->address,
                'website'  => $data->website      ?? $user->company->website,
                'verified' => $data->verified     ?? $user->company->verified,
            ]);
        }
    }
}
