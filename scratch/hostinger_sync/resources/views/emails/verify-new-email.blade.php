@component('mail::message')
# Verify Your New Email Address

Hello {{ $user->full_name }},

You have requested to change your email address to **{{ $user->pending_email }}**.

Please click the button below to verify and complete the change:

@component('mail::button', ['url' => route('my-account.verify-email', ['token' => $user->email_change_token])])
Verify New Email
@component('mail::footer')
If you did not request this change, please ignore this email.
@endcomponent
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
