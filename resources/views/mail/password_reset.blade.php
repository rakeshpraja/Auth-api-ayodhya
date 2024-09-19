<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
    <h1>Reset Password</h1>
    <p>Hello,{{$user->user}}</p>
    <p>We received a request to reset your password. Please use the following OTP (One Time Password) to reset your password:</p>
    
    <h2>Your OTP: {{ $otp }}</h2>
    
    <p>This OTP is valid for the next {{$expires_at}} minutes. If you did not request a password reset, please ignore this email.</p>
    
    <p>Thank you,</p>
  
</body>

</html>
