@extends('layouts.app')

@section('content')

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Password Form</title>
    <link href="{{ asset('css/pages/login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="wrapper container">
        <div class="title-text">
            <div class="title login">Recover Password Form</div>
        </div>

            <div class="form-inner">

               

                <!-- Start Signup Form -->
                <form method="POST" action="{{route('password.email')}}" class="signup" id="__registerUserForm">
                    {{ csrf_field() }}

                    <div class="field input-group">
                        <input class="form-control" placeholder="Email Address" id="email" type="email" name="email" value="{{ old('email') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    

                    <div class="field input-group">
                        <input type="submit" value="Submit">
                    </div>



                </form>

            </div>
        </div>
    </div>
</body>

</html>
@endsection