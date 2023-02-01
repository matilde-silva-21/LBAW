@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ asset('css/pages/aboutUs.css') }}">
</head>

<body>

    <div class="about-section">
        <span style="--i:1">R</span>
        <span style="--i:2">E</span>
        <span style="--i:3">-</span>
        <span style="--i:4">E</span>
        <span style="--i:5">V</span>
        <span style="--i:6">E</span>
        <span style="--i:7">N</span>
        <span style="--i:8">T</span>
        <span style="--i:9"></span>

        <p style="font-size: 20px;">Some text about who we are and what we do.</p>
    </div>

    <h1 style="text-align:center; font-size: 50px; margin-top: -5px;">Our Team</h1>

    <div class="container-profile-cards" >
        <!-- AFONSO -->
        <div class="column">
            <div class="profile-card">
                <div class="profile-card-header">
                    <img src="profile_pictures/3.jpg" alt="" class="profile-image">

                    <div class="profile-info">
                        <div class="profile-name" >Afonso Martins </div>
                        <p class="profile-desc">Developer</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="action">
                        <a href="https://github.com/afonsom1719"><button type="button" class="btn btn-blue-outline btn-abt"> Github </button> </a>
                        <a href="mailto:up202005900@fe.up.pt"> <button type="button" class="btn btn-red-outline btn-abt"> E-mail </button> </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- END AFONSO -->

        <!-- EDU -->
        <div class="column">
            <div class="profile-card">
                <div class="profile-card-header">
                    <img src="profile_pictures/2.jpg" alt="" class="profile-image">

                    <div class="profile-info">
                        <div class="profile-name">Eduardo Silva </div>
                        <p class="profile-desc">Developer</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="action">
                        <a href="https://github.com/Eduardo79Silva"> <button type="button" class="btn btn-blue-outline btn-abt"> Github </button> </a>
                        <a href="mailto:up202003529@fe.up.pt"> <button class="btn btn-red-outline btn-abt"> E-mail </button> </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- END EDU -->

        <!-- ZÉ -->

        <div class="column">
            <div class="profile-card">
                <div class="profile-card-header">
                    <img src="profile_pictures/1.jpg" alt="" class="profile-image">

                    <div class="profile-info">
                        <div class="profile-name"> José Diogo </div>
                        <p class="profile-desc">Developer</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="action">
                        <a href="https://github.com/Zediogo96"> <button type="button" class="btn btn-blue-outline btn-abt"> Github </button> </a>
                        <a href="mailto:up202003529@fe.up.pt"> <button class="btn btn-red-outline btn-abt"> E-mail </button> </a>
                    </div>
                </div>
            </div>
        </div>


        <!-- END ZÉ -->

        <!-- MATILDE -->

        <div class="column">
            <div class="profile-card">
                <div class="profile-card-header">
                    <img src="profile_pictures/4.jpg" alt="" class="profile-image">

                    <div class="profile-info">
                        <div class="profile-name"> Matilde Silva </div>
                        <p class="profile-desc">Developer</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="action">
                        <a href="https://github.com/matilde-silva-21"><button type="button" class="btn btn-blue-outline btn-abt"> Github </button> </a>
                        <a href="mailto:up202007928@fe.up.pt"><button class="btn btn-red-outline btn-abt"> E-mail </button> </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- END MATILDE -->
    </div>
</body>

</html>

@endsection
