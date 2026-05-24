<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        return view('pages.profile.index', [
            'user' => $user,
        ]);
    }

    public function updateDetails(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where('tenant_id', \App\Support\TenantSettings::tenantId())->ignore($user->id),
            ],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        Audit::log('profile.updated', $user, [
            'email' => $user->email,
        ]);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = $data['password'];
        $user->save();

        Audit::log('profile.password_updated', $user);

        return back()->with('status', 'Password updated.');
    }

    public function updatePhoto(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $file = $data['photo'];
        $ext = $file->getClientOriginalExtension() ?: 'jpg';

        $safeName = Str::of($user->name)->lower()->replaceMatches('/[^a-z0-9]+/i', '-')->trim('-')->toString();
        $filename = ($safeName ?: 'user').'-'.$user->id.'-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(3)).'.'.$ext;

        $path = $file->storeAs('profile-photos', $filename, 'uploads');
        $path = str_replace('\\', '/', (string) $path);

        $old = $user->profile_photo ? str_replace('\\', '/', (string) $user->profile_photo) : null;
        if ($old && $old !== $path) {
            Storage::disk('uploads')->delete($old);
        }

        $user->profile_photo = $path;
        $user->save();

        Audit::log('profile.photo_updated', $user, ['path' => $path]);

        return back()->with('status', 'Profile photo updated.');
    }

    public function destroyPhoto(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->profile_photo) {
            Storage::disk('uploads')->delete(str_replace('\\', '/', $user->profile_photo));
        }

        $user->profile_photo = null;
        $user->save();

        Audit::log('profile.photo_removed', $user);

        return back()->with('status', 'Profile photo removed.');
    }
}
