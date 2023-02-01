@extends('layouts.app')

@section('content')

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="{{ asset('css/pages/login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="wrapper container">
        <div class="title-text">
            <div class="title login">Reset Password Form</div>
        </div>


            <div class="form-inner">

               

                <!-- Start Signup Form -->
                <form method="POST" action="{{route('password.update')}}" class="signup">
                    {{ csrf_field() }}
                    

                    <div class="field input-group">
                        

                        <input type="email" class="form-control" placeholder="Email" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                        @if ($errors->has('email'))
                        <span class="error">
                            {{ $errors->first('email') }}
                        </span>
                        @endif
                    </div>
                    <div class="field input-group input-icons">
                        <input class="form-control" placeholder="Password" id="password" type="password" name="password" required></input>
                        @if ($errors->has('password'))
                        <span class="error">
                            {{ $errors->first('password') }}
                        </span>
                        @endif
                    </div>
                    <div class="field input-group input-icons">
                        <input class="form-control" placeholder="Password Confirmation" id="password_confirmation" type="password" name="password_confirmation" required></input>
                        @if ($errors->has('password'))
                        <span class="error">
                            {{ $errors->first('password') }}
                        </span>
                        @endif
                    </div>
                    <input type="hidden"  placeholder="Email" id="token" type="token" name="token" value="{{$token}}" required>
                    
                    

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
