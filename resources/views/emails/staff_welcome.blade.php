<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to Eurotaxi Fleet Management</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #1e3a8a; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Eurotaxi Fleet Management</h1>
        </div>
        
        <div style="padding: 30px;">
            <h2 style="color: #1f2937; margin-top: 0;">Welcome, {{ $user->first_name }}!</h2>
            <p style="color: #4b5563; line-height: 1.6;">
                Your staff account has been created by the system administrator. You have been assigned the role of <strong>{{ strtoupper($user->role) }}</strong>.
            </p>
            
            <div style="background-color: #f3f4f6; border-left: 4px solid #3b82f6; padding: 15px; margin: 25px 0;">
                <p style="margin: 0; color: #374151; font-weight: bold;">Your Temporary Login Credentials:</p>
                <p style="margin: 10px 0 5px 0; color: #4b5563;"><strong>Email:</strong> {{ $user->email }}</p>
                <p style="margin: 0; color: #4b5563;"><strong>Password:</strong> <span style="font-family: monospace; font-size: 16px; background-color: #e5e7eb; padding: 2px 6px; border-radius: 4px;">{{ $tempPassword }}</span></p>
            </div>
            
            <p style="color: #dc2626; font-size: 14px; font-weight: bold;">
                ⚠️ IMPORTANT: For your security, you will be required to change this temporary password immediately upon your first login.
            </p>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('login') }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: bold;">Login to Dashboard</a>
            </div>
        </div>
        
        <div style="background-color: #f9fafb; border-top: 1px solid #e5e7eb; padding: 15px; text-align: center;">
            <p style="color: #9ca3af; font-size: 12px; margin: 0;">&copy; {{ date('Y') }} Eurotaxi Inc. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
