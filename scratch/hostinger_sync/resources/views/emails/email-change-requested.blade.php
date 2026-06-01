@component('mail::message')
# Email Change Request Alert

Hello {{ $user->full_name }},

A request has been made to change your account's email address from **{{ $user->email }}** to **{{ $newEmail }}**.

**Authorize Change:**
If you initiated this change, please click the button below to **AUTHORIZE** the update. This will immediately change your login email.

@component('mail::button', ['url' => route('my-account.verify-email', ['token' => $user->email_change_token])])
Authorize Email Change
@endcomponent

**Not you?**
If you did NOT request this change, please ignore this email and contact system administration immediately to secure your account. Your email will not be changed unless the button above is clicked.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
