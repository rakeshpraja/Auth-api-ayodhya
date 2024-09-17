<!DOCTYPE html>
<html>

<head>
    <title>Custom Email</title>
</head>

<body>
    <h1>{{ $details['user']['name'] }}</h1>
    <p>To complete your registration, please verify your registraion by clicking the link below:
    </p>
    <a href="{{route('verify.user',$details['token'])}}">Register Verify</a>
</body>

</html>