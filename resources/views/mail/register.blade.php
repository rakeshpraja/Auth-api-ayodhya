<!DOCTYPE html>
<html>

<head>
    <title>Custom Email</title>
</head>


   


    <body>
    <h2>Hi {{ $details['user']['name'] }},</h2>
    <p>Thank you for registering with us!</p>
    <p>To complete your registration, please verify your email by using the OTP below:</p>
    <h3 style="color: #2e6c80;">Your OTP: <strong>{{$details['otp'] }}</strong></h3>
    <p>This OTP will expire in {{$details['expires_at'] }} minutes.</p>
    <p>If you didn't request this, please ignore this email.</p>
    <br>
    <p>Best regards,</p>
    

</body>

</html>