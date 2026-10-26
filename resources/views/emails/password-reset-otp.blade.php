<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>

<body>
    <h2>Password Reset</h2>

    <p>You requested to reset your password.</p>

    <p>Your OTP is:</p>

    <h1>{{ $otp }}</h1>

    <p>This OTP will expire in 10 minutes.</p>

    <p>If you did not request a password reset, you can ignore this email.</p>
</body>
</html>
