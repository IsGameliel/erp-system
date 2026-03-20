<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
        <h2>Welcome to {{ config('app.name', 'ERP System') }}</h2>
        <p>Hello {{ $user->name }},</p>
        <p>Welcome to the company. Your account has been created successfully and you can now sign in to start using the platform.</p>
        <p><strong>Login email:</strong> {{ $user->email }}</p>
        <p><strong>Temporary password:</strong> {{ $plainPassword }}</p>
        <p><strong>Role:</strong> {{ ucwords(str_replace('_', ' ', $user->role)) }}</p>
        <p>Please sign in and change your password after your first login.</p>
        <p>Regards,<br>{{ config('app.name', 'ERP System') }}</p>
    </body>
</html>
